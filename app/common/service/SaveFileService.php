<?php
/**
 * Created by xtshop
 * Class SaveFileService
 * Description:文件处理类
 * @package app\common\service
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 15:52
 */
namespace app\common\service;

use app\common\model\Attachment;
use think\App;
class SaveFileService extends BaseService
{
    //oss的服务类
    private $ossFile;

    /**
     * SaveFileService constructor.
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->ossFile = OssFileService::getInstance($app);
    }

    /**
     * @param $file
     * @param array $user
     * @param string $module
     * @return array
     * Description:保存文件并存到附件表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 16:25
     */
    public function saveImageSvgFile($file,$user=[],$module=''){
        try {
            $fileMd5 = $file->hash('md5');
            //判断文件是否已经存在
            if ($fileExit = Attachment::field('id,type,path,url')->where(['status' => 1, 'md5' => $fileMd5])->find()) {
                $fileExit['old_url']=$fileExit['url'];
                $fileExit['url']=get_real_url($fileExit['url']);
                return [
                    'status' => 1,
                    'msg' => '上传成功！',
                    'data' => $fileExit
                ];
            }
            $type = 0;
            $dirName = "file";
            $imageWidth = 0;
            $imageHeight = 0;
            $fileType = $file->getOriginalMime();
            $imageMime = config('file.image_svg_mime');
            if (!empty($imageMime) && !empty($fileType)) {
                if (in_array($fileType, $imageMime)) {
                    $type = 1;
                    $dirName = 'image';
                }else{
                    $res = array(
                        'status' => 0,
                        'msg' => '文件类型不正确'
                    );
                    return $res;
                }
            }
            $svgMime = config('file.svg_mime');
            if (!empty($svgMime) && !empty($fileType)) {
                if (in_array($fileType, $svgMime)) {
                    $type = 2;
                    $dirName = 'svg';
                }
            }
            if($file->getSize()>config('file.svg_max_size')){
                $res = array(
                    'status' => 0,
                    'msg' => '文件大小不能超过'.(config('file.svg_max_size')/1024).'Kb'
                );
                return $res;
            }
            $saveName = \think\facade\Filesystem::disk('public')->putFile( 'images', $file);
            //本地路径
            $newFilePath = config('filesystem.disks.public.root').DIRECTORY_SEPARATOR. $saveName;
            $isOss=config('file.is_oss');
            if($isOss) {
                //OSS路径
                $ossFilePath = $dirName . '/' . $saveName;
                $ret = $this->ossFile->upload($ossFilePath, $newFilePath);
                if ($ret["status"] != 1) {
                    //保存失败删除
                    @unlink($newFilePath);
                    $res = array(
                        'status' => 0,
                        'msg' => $ret["msg"]
                    );
                    return $res;
                }
                $saveOssPathName = '/' . $dirName . '/' .$saveName;
            }else{
                $saveOssPathName='/'.$saveName;
            }
            $uid = 0;
            if (!empty($user)) {
                $uid = $user['id'];
            }
            //获取存储attachment的数据
            $attachmentData = [
                'uid' => $uid,
                'type' => $type,
                'name' => $file->getOriginalName(),
                'mime' => $fileType,
                'path' => $newFilePath,
                'url' => $saveOssPathName,
                'ext' => $file->extension(),
                'size' => $file->getSize(),
                'md5' => $fileMd5,
                'sha1' => $file->hash('sha1'),
                'module' => $module,
                'width' => $imageWidth,
                'height' => $imageHeight,
            ];
            if ($fileAdd = Attachment::create($attachmentData)) {
                $attachmentData['id'] = $fileAdd['id'];
                $attachmentData['old_url']=$attachmentData['url'];
                $attachmentData['url']=get_real_url($attachmentData['url']);
            }
            if($isOss){
                //删除本地文件
                @unlink($newFilePath);
            }
            $res = array(
                'status' => 1,
                'msg' => '上传成功！',
                'data' => $attachmentData
            );
            return $res;
        }catch (\Exception $e){
            if(!empty($newFilePath)){
                //保存失败删除
                @unlink($newFilePath);
            }
            return [
                'status'=>0,
                'msg'=>'文件保存失败'.$e->getMessage().$e->getLine().$e->getFile()
            ];
        }
    }

    /**
     * @param $file
     * @param array $user
     * @param string $module
     * @return array
     * Description:上传视频
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 15:47
     */
    public function saveVideoFile($file,$user=[],$module=''){
        try {
            $fileMd5 = $file->hash('md5');
            //判断文件是否已经存在
            if ($fileExit = Attachment::field('id,type,path,url')->where(['status' => 1, 'md5' => $fileMd5])->find()) {
                $fileExit['old_url']=$fileExit['url'];
                $fileExit['url']=get_real_url($fileExit['url']);
                return [
                    'status' => 1,
                    'msg' => '上传成功！',
                    'data' => $fileExit
                ];
            }
            $type = 3;
            $dirName='video';
            $fileType = $file->getOriginalMime();
            $imageMime = config('file.video_mime');
            if (!empty($imageMime) && !empty($fileType)) {
                if (in_array($fileType, $imageMime)) {
                    $type = 3;
                }else{
                    $res = array(
                        'status' => 0,
                        'msg' => '文件类型不正确'
                    );
                    return $res;
                }
            }
            if($file->getSize()>config('file.video_max_size')){
                $res = array(
                    'status' => 0,
                    'msg' => '视频文件大小不能超过'.(config('file.video_max_size')/1024).'Kb'
                );
                return $res;
            }
            $saveName = \think\facade\Filesystem::disk('public')->putFile( 'video', $file);
            //本地路径
            $newFilePath = config('filesystem.disks.public.root').DIRECTORY_SEPARATOR. $saveName;
            $isOss=config('file.is_oss');
            if($isOss) {
                //OSS路径
                $ossFilePath = $dirName . '/' . $saveName;
                $ret = $this->ossFile->upload($ossFilePath, $newFilePath);
                if ($ret["status"] != 1) {
                    //保存失败删除
                    @unlink($newFilePath);
                    $res = array(
                        'status' => 0,
                        'msg' => $ret["msg"]
                    );
                    return $res;
                }
                $saveOssPathName = '/' . $dirName . '/' .$saveName;
            }else{
                $saveOssPathName='/'.$saveName;
            }
            $uid = 0;
            if (!empty($user)) {
                $uid = $user['id'];
            }
            //获取存储attachment的数据
            $attachmentData = [
                'uid' => $uid,
                'type' => $type,
                'name' => $file->getOriginalName(),
                'mime' => $fileType,
                'path' => $newFilePath,
                'url' => $saveOssPathName,
                'ext' => $file->extension(),
                'size' => $file->getSize(),
                'md5' => $fileMd5,
                'sha1' => $file->hash('sha1'),
                'module' => $module,
            ];
            if ($fileAdd = Attachment::create($attachmentData)) {
                $attachmentData['id'] = $fileAdd['id'];
                $attachmentData['old_url']=$attachmentData['url'];
                $attachmentData['url']=get_real_url($attachmentData['url']);
            }
            if($isOss){
                //删除本地文件
                @unlink($newFilePath);
            }
            $res = array(
                'status' => 1,
                'msg' => '上传成功！',
                'data' => $attachmentData
            );
            return $res;
        }catch (\Exception $e){
            if(!empty($newFilePath)){
                //保存失败删除
                @unlink($newFilePath);
            }
            return [
                'status'=>0,
                'msg'=>'文件保存失败'.$e->getMessage()
            ];
        }
    }
    /**
     * @param $file
     * @param array $user
     * @param string $module
     * @return array
     * Description:存储Image和SVG文件不存附件表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:57
     */
    public function saveBannerImageSvgFile($file,$user=[],$module=''){
        try {
            $dirName = "file";
            $fileType = $file->getOriginalMime();
            $imageMime = config('file.image_svg_mime');
            if (!empty($imageMime) && !empty($fileType)) {
                if (in_array($fileType, $imageMime)) {
                    $dirName = 'image';
                }else{
                    $res = array(
                        'status' => 0,
                        'msg' => '文件类型不正确'
                    );
                    return $res;
                }
            }
            if($file->getSize()>config('file.svg_max_size')){
                $res = array(
                    'status' => 0,
                    'msg' => '文件大小不能超过'.(config('file.svg_max_size')/1024).'Kb'
                );
                return $res;
            }
            $saveName = \think\facade\Filesystem::disk('public')->putFile( 'images', $file);
            //本地路径
            $newFilePath = config('filesystem.disks.public.root').DIRECTORY_SEPARATOR. $saveName;
            $isOss=config('file.is_oss');
            if($isOss) {
                //OSS路径
                $ossFilePath = $dirName . '/' . $saveName;
                $ret = $this->ossFile->upload($ossFilePath, $newFilePath);
                if ($ret["status"] != 1) {
                    //保存失败删除
                    @unlink($newFilePath);
                    $res = array(
                        'status' => 0,
                        'msg' => $ret["msg"]
                    );
                    return $res;
                }
                $saveOssPathName = '/' . $dirName . '/' . $saveName;
            }else{
                $saveOssPathName='/'.$saveName;
            }
            $uid = 0;
            if (!empty($user)) {
                $uid = $user['id'];
            }
            //获取存储attachment的数据
            $attachmentData = [
                'uid' => $uid,
                'name' => $file->getOriginalName(),
                'mime' => $fileType,
                'path' => $newFilePath,
                'url' => $saveOssPathName,
                'ext' => $file->extension(),
                'size' => $file->getSize(),
                'sha1' => $file->hash('sha1'),
                'module' => $module,
            ];
            $attachmentData['id'] = 0;
            $attachmentData['old_url']=$attachmentData['url'];
            $attachmentData['url']=get_real_url($attachmentData['url']);
            if($isOss) {
                //保存成功删除本地
                @unlink($newFilePath);
            }
            $res = array(
                'status' => 1,
                'msg' => '上传成功！',
                'data' => $attachmentData
            );
            return $res;
        }catch (\Exception $e){
            if(!empty($newFilePath)){
                //保存失败删除
                @unlink($newFilePath);
            }
            return [
                'status'=>0,
                'msg'=>'文件保存失败'.$e->getMessage().$e->getFile().$e->getLine()
            ];
        }
    }
    /**
     * @param $localFile
     * @param string $path
     * @param int $del
     * @return array
     * Description:将本地文件保存得到OSS
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:57
     */
    public function saveLocalToOss($localFile,$path='zip',$del=0){
        try {
            //OSS路径
            $ossFilePath = $path . '/' . basename($localFile);
            $ret = $this->ossFile->upload($ossFilePath, $localFile);
            if ($ret["status"] != 1) {
                if($del){
                    @unlink($localFile);
                }
                $res = array(
                    'status' => 0,
                    'msg' => $ret["msg"]
                );
                return $res;
            }
            if($del){
                @unlink($localFile);
            }
            return [
                'status' => 1,
                'msg' => '保存成功',
                'data' => '/' . $ossFilePath
            ];
        }catch (\Exception $e) {
            if((!empty($localFile))&&$del){
                @unlink($localFile);
            }
            $res = array(
                'status' => 0,
                'msg' => '保存到OSS出错！'.$e->getMessage()
            );
            return $res;
        }
    }
    /**
     * @param $file
     * @param string $typePath
     * @return array
     * Description:直接将文件保存到OSS
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:56
     */
    public function saveFileToOss($file,$typePath='file'){
        try {
            $upload_path = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'temp';
            $file_dir_name = $upload_path . DIRECTORY_SEPARATOR . $typePath;
            if (!file_exists($file_dir_name)) {
                mkdir($file_dir_name, 0777, true);
            }
            $info=$file->validate(['size' => config('file.file_max_size')])->move($file_dir_name);
            if (!$info) {
                $res = array(
                    'status' => 0,
                    'msg' => $file->getError()
                );
                return $res;
            }
            $saveName=$info->getSaveName();
            $ret = $this->ossFile->upload($typePath.'/'.$saveName, $file_dir_name.'/'.$saveName);
            if ($ret["status"] != 1) {
                @unlink( $file_dir_name.'/'.$saveName);
                $res = array(
                    'status' => 0,
                    'msg' => $ret["msg"]
                );
                return $res;
            }
            @unlink( $file_dir_name.'/'.$saveName);
            $res = array(
                'status' => 1,
                'msg' => '保存成功！',
                'data' => '/'.$typePath.'/'.$saveName
            );
            return $res;
        }catch (\Exception $e){
            return [
                'status'=>0,
                'msg'=>'文件保存失败'.$e->getMessage()
            ];
        }
    }
}