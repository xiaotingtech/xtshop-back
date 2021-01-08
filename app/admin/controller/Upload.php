<?php
namespace app\admin\controller;

use think\Request;
use app\common\model\Attachment;
use app\common\service\OssFileService;
use think\App;
use think\File;
/**
 * Class Upload
 * @package app\index\controller
 * Description:编辑器保存文件类
 * User: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2019-08-14 15:51
 */
class Upload extends AuthBase 
{
    private $CONFIG;
    protected $request;
    protected $app;

    public function __construct(App $app,Request $request = null)
    {
        parent::__construct($app);
        $this->app=$app;
        $this->request=$request;
        $this->CONFIG=array(
            'imagePathFormat'=>'',
            'imageMaxSize'=>2048000,
            'imageAllowFiles'=> ["png", "jpg", "jpeg", "gif", "bmp"],
            'imageFieldName'=>'upload',
            'scrawlPathFormat'=>'',
            'scrawlMaxSize'=>2048000,
            'scrawlFieldName'=>'upload',
            'videoPathFormat'=>'',
            'videoMaxSize'=>51200000,
            'videoAllowFiles'=>[
                "flv", "swf", "mkv", "avi", "rm", "rmvb", "mpeg", "mpg",
                "ogg", "ogv", "mov", "wmv", "mp4", "webm", "mp3", "wav", "mid"],
            'videoFieldName'=>'upload',
            'filePathFormat'=>'',
            'fileMaxSize'=>51200000,
            'fileAllowFiles'=>[
                "png", "jpg", "jpeg", "gif", "bmp",
                "flv", "swf", "mkv", "avi", "rm", "rmvb", "mpeg", "mpg",
                "ogg", "ogv", "mov", "wmv", "mp4", "webm", "mp3", "wav", "mid",
                "rar", "zip", "tar", "gz", "7z", "bz2", "cab", "iso",
                "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pdf", "txt", "md", "xml"
            ],
            'fileFieldName'=>'upload',
        );
    }
    public function save(){
        if($this->request->isOptions()){
            return json('',200,$header=array('Access-Control-Allow-Origin'=>'*',
                'Access-Control-Allow-Headers'=>'content-type,Authorization',
                'access-control-allow-methods'=>'GET, POST, OPTIONS, PUT, PATCH, DELETE'));
        }
        $action=input('get.action','','string');
        $base64=false;
        $paste=0;
        switch ($action) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $this->CONFIG['imagePathFormat'],
                    "maxSize" => $this->CONFIG['imageMaxSize'],
                    "allowFiles" => $this->CONFIG['imageAllowFiles']
                );
                $fieldName = $this->CONFIG['imageFieldName'];
                $result = $this->saveFile($fieldName,$config,$base64);
                break;
            case 'uploadimagepaste':
                $paste=1;
                $config = array(
                    "pathFormat" => $this->CONFIG['imagePathFormat'],
                    "maxSize" => $this->CONFIG['imageMaxSize'],
                    "allowFiles" => $this->CONFIG['imageAllowFiles']
                );
                $fieldName = $this->CONFIG['imageFieldName'];
                $result = $this->saveFile($fieldName,$config,$base64);
                break;
                /* 上传涂鸦 */
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $this->CONFIG['scrawlPathFormat'],
                    "maxSize" => $this->CONFIG['scrawlMaxSize'],
                    "oriName" => "scrawl.png",
                    "allowFiles"=>[],
                );
                $fieldName = $this->CONFIG['scrawlFieldName'];
                $base64=true;
                $result = $this->saveFile($fieldName,$config,$base64);
                break;
                /* 上传视频 */
            case 'uploadvideo':
                $config = array(
                    "pathFormat" =>$this->CONFIG['videoPathFormat'],
                    "maxSize" => $this->CONFIG['videoMaxSize'],
                    "allowFiles" => $this->CONFIG['videoAllowFiles']
                );
                $fieldName = $this->CONFIG['videoFieldName'];
                $result = $this->saveFile($fieldName,$config,$base64);
                break;
                /* 上传文件 */
            case 'uploadfile':
                $config = array(
                    "pathFormat" => $this->CONFIG['filePathFormat'],
                    "maxSize" => $this->CONFIG['fileMaxSize'],
                    "allowFiles" =>$this->CONFIG['fileAllowFiles']
                );
                $fieldName = $this->CONFIG['fileFieldName'];
                $result = $this->saveFile($fieldName,$config,$base64);
                break;
            default:
                $result = array(
                    'uploaded'=>false,
                    'msg'=>'请求地址出错！'
                );
                break;
        }
        $callback=input('get.callback',1,'string');
        if(!empty($paste)) {
            if ($result['uploaded']) {
                $data=[
                    'uploaded'=>1,
                    'fileName'=>basename($result['url']),
                    'url'=>$result['url'],
                ];
                return json_encode($data);
            } else {
                $data=[
                    'uploaded'=>1,
                    'fileName'=>'',
                    'url'=>'',
                    'error'=>['message'=>$result['msg']]
                ];
                return json_encode($data);
            }
        }else{
            /* 输出结果 */
            if (!empty($callback)) {
                if ($result['uploaded']) {
                    $url = $result['url'];
                    return $this->ck_js($callback, $url);
                } else {
                    return $this->ck_js($callback, '', $result['msg']);
                }
            } else {
                if ($result['uploaded']) {
                    $url = $result['url'];
                    return $this->ck_js(1, $url);
                } else {
                    return $this->ck_js(1, '', $result['msg']);
                }
            }
        }
    }

    /**
     * @param $fieldName
     * @param $config
     * @param $base64
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:上传具体文件
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 11:34
     */
    private function saveFile($fieldName,$config,$base64){
        //如果是base64
        if($base64){
            $base64Data = input('post.'.$fieldName,'','string');
            $img = base64_decode($base64Data);
            $ext=strtolower(strrchr($config['oriName'], '.'));
            $timeName = date('Ymd', time());
            $pic_dir_name = config('filesystem.disks.public.root').DIRECTORY_SEPARATOR.'ckeditor' . DIRECTORY_SEPARATOR . $timeName;
            $pic_file_name = uniqid() . '.' . $ext;
            if (!file_exists($pic_dir_name)) {
                mkdir($pic_dir_name, 0777, true);
            }
            $new_pic_path = $pic_dir_name . DIRECTORY_SEPARATOR . $pic_file_name;
            $ret = file_put_contents($new_pic_path, $img);
            if (!$ret) {
                return  array(
                    'uploaded'=>false,
                    'msg'=>'文件保存失败！'
                );
            }
            $file=new File($new_pic_path);
            $fileMd5 = $file->hash('md5');
            //判断文件是否已经存在
            if ($fileExit = Attachment::field('id,url')->where(['status' => 1, 'md5' => $fileMd5])->find()) {
                return [
                    'uploaded'=>true,
                    'msg'=>'成功！',
                    'url'=> get_real_url($fileExit['url']),
                ];
            }
            $fileSize=$file->getSize();
            if($fileSize>$config['maxSize']){
                return array(
                    'uploaded'=>false,
                    'msg'=>'文件大小过大，超过最大值'.($config['maxSize']/1024).'Kb限制'
                );
            }
            $isOss=config('file.is_oss');
            if($isOss) {
                try {
                    $ossFile = OssFileService::getInstance($this->app);
                    $ret = $ossFile->upload('ckeditor/' . $timeName . '/' . $pic_file_name, $new_pic_path);
                    if ($ret["status"] != 1) {
                        @unlink($new_pic_path);
                        return array(
                            'uploaded' => false,
                            'msg' => '文件保存失败！'
                        );
                    }
                } catch (\Exception $e) {
                    @unlink($new_pic_path);
                    return array(
                        'uploaded' => false,
                        'msg' => '文件保存失败！' . $e->getMessage()
                    );
                }
                @unlink($new_pic_path);
            }
            return array(
                "uploaded" =>true,
                'msg'=>'成功！',
                "url" => get_real_url('/'.'ckeditor/'.$timeName.'/'.$pic_file_name),
            );
        }else{
            //如果不是
            $file=$this->request->file($fieldName);
            if($file){
                $uploadPath=config('filesystem.disks.public.root');
                $fileType = $file->extension();
                $imageMime = $config['allowFiles'];
                if (!empty($imageMime) && !empty($fileType)) {
                    if (!in_array($fileType, $imageMime)) {
                        $res = array(
                            'uploaded'=>false,
                            'msg' => '文件类型不正确'
                        );
                        return $res;
                    }
                }
                if($file->getSize()>$config['maxSize']){
                    $res = array(
                        'uploaded'=>false,
                        'msg' => '文件大小不能超过'.($config['maxSize']/1024).'Kb'
                    );
                    return $res;
                }
                $saveName = \think\facade\Filesystem::disk('public')->putFile( 'ckeditor', $file);
                $originalFile=$uploadPath.DIRECTORY_SEPARATOR.$saveName;
                $timeName = date('Ymd', time());
                $fileName=basename($saveName);
                $isOss=config('file.is_oss');
                if($isOss) {
                    try {
                        $ossFile = OssFileService::getInstance($this->app);
                        $ret = $ossFile->upload('ckeditor/' . $timeName . '/' . $fileName, $originalFile);
                        if ($ret["status"] != 1) {
                            @unlink($originalFile);
                            return array(
                                'uploaded' => false,
                                'msg' => '文件保存失败!'
                            );
                        }
                    } catch (\Exception $e) {
                        @unlink($originalFile);
                        return array(
                            'uploaded' => false,
                            'msg' => '文件保存失败，' . $e->getMessage()
                        );
                    }
                    @unlink($originalFile);
                }
                return array(
                    'uploaded'=>true,
                    'msg'=>'成功！',
                    'url'=> get_real_url('/'.DIRECTORY_SEPARATOR.$saveName)
                );
            }else{
                return array(
                    'uploaded'=>false,
                    'msg'=>'未获取到文件信息'
                );
            }
        }
    }

    /**
     * 返回ckeditor编辑器上传文件时需要返回的js代码
     * @param string $callback 回调
     * @param string $file_path 文件路径
     * @param string $error_msg 错误信息
     * @author 蔡伟明 <314013107@qq.com>
     * @return string
     */
    function ck_js($callback = '', $file_path = '', $error_msg = '')
    {
        return "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback, '$file_path' , '$error_msg');</script>";
    }
}