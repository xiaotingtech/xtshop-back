<?php
/**
 * Created by xtshop
 * Class OssFileService
 * Description:Oss服务类
 * @package app\common\service
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 15:49
 */
namespace app\common\service;

use OSS\OssClient;
use OSS\Core\OssException;
use think\App;
class OssFileService extends BaseService
{
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;
    protected static $instance;
    protected $ossClient;
    protected $config;

    /**
     * OssFileService constructor.
     * @param App $app
     * @param $config
     * @throws \Exception
     */
    public function __construct(App $app,$config)
    {
        parent::__construct($app);
        if (!empty($config) && empty($config['access_key_id'])
            && !empty($config['access_key_secret'])
            && !empty($config['end_point'])
        ) {
            $this->accessKeyId = $config['access_key_id'];
            $this->accessKeySecret = $config['access_key_secret'];
            $this->endpoint = $config['end_point'];
            $this->bucket=config('oss.OSS_BUCKET');
        }else{
            $this->accessKeyId = config('oss.ALI_ACCESS_KEY_ID');
            $this->accessKeySecret = config('oss.ALI_ACCESS_KEY_SECRET');
            $this->endpoint = config('oss.OSS_ENDPOINT');
            $this->bucket=config('oss.OSS_BUCKET');
        }
        try {
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        } catch (OssException $e) {
            throw($e);
        }
        $this->config = $config;
    }

    /**
     * @param App $app
     * @param array $config
     * @return OssFileService
     * @throws \Exception
     * Description:获取单例
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:58
     */
    public static function getInstance(App $app,$config = array())
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($app,$config);
        }
        return self::$instance;
    }

    //防止克隆对象
    private function __clone()
    {

    }

    /**
     * @param string $bucket
     * @return array
     * Description:创建bucket
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:57
     */
    public function createBucket($bucket = '')
    {
        if (empty($bucket)) {
            $data = array(
                'status' => 0,
                'msg' => '未获取到bucket的名称！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->createBucket($bucket)) {
                $data = array(
                    'status' => 1,
                    'msg' => '创建bucket成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '创建bucket失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '创建bucket出错，' . $e->getMessage(),
            );
            return $data;
        }
    }

    /**
     * @param string $bucket
     * @return array
     * Description:删除bucket
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 15:59
     */
    public function deleteBucket($bucket = '')
    {
        if (empty($bucket)) {
            $data = array(
                'status' => 0,
                'msg' => '未获取到bucket的名称！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->deleteBucket($bucket)) {
                $data = array(
                    'status' => 1,
                    'msg' => '删除bucket成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '删除bucket失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '删除bucket出错，' . $e->getMessage(),
            );
            return $data;
        }
    }

    /**
     * 本地上传文件
     * @param string $bucket (bucket名字，空即默认)
     * @param string $object (新的文件名带扩展)
     * @param string $content (本地文件路径)
     * @return array
     * User: sunnier
     * Email:xiaoyao_xiao@126.com
     */
    public function upload($object = '', $content = '', $bucket = '')
    {
        if (empty($bucket)) {
            $bucket = $this->bucket;
        }
        if (empty($object)) {
            $data = array(
                'status' => 0,
                'msg' => '没有新文件名！',
            );
            return $data;
        }
        if (empty($content)) {
            $data = array(
                'status' => 0,
                'msg' => '没有文件路径！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->multiuploadFile($bucket, $object, $content)) {
                $data = array(
                    'status' => 1,
                    'msg' => '上传成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '上传失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '上传文件出错，' . $e->getMessage(),
            );
            return $data;
        }
    }
    /**
     * 本地文件夹上传文件
     * @param string $bucket (bucket名字，空即默认)
     * @param string $object (新的文件名带扩展)
     * @param string $content (本地文件路径)
     * @param boolean $is_recurse (是否递归文件夹内容)
     * @return array
     * User: sunnier
     * Email:xiaoyao_xiao@126.com
     */
    public function uploadDir($object = '', $content = '', $is_recurse=false,$bucket = '')
    {
        if (empty($bucket)) {
            $bucket = $this->bucket;
        }
        if (empty($object)) {
            $data = array(
                'status' => 0,
                'msg' => '没有新文件夹名称！',
            );
            return $data;
        }
        if (empty($content)) {
            $data = array(
                'status' => 0,
                'msg' => '没有文件路径！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->uploadDir($bucket, $object, $content,'',$is_recurse)) {
                $data = array(
                    'status' => 1,
                    'msg' => '上传成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '上传失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '上传文件出错，' . $e->getMessage(),
            );
            return $data;
        }
    }
    /**
     *
     * @param string $object
     * @param string $bucket
     * @return array
     * User: sunnier
     * Email:xiaoyao_xiao@126.com
     */
    public function delete($object = '', $bucket = '')
    {
        if (empty($bucket)) {
            $bucket = $this->bucket;
        }
        if (empty($object)) {
            $data = array(
                'status' => 0,
                'msg' => '没有文件名！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->deleteObject($bucket, $object)) {
                $data = array(
                    'status' => 1,
                    'msg' => '删除成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '删除失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '删除文件出错，' . $e->getMessage(),
            );
            return $data;
        }
    }
    /**
     * 创建目录
     * @param string $bucket (bucket名字，空即默认)
     * @param string $object (新的目录名，不带/结尾)
     * @return array
     * User: sunnier
     * Email:xiaoyao_xiao@126.com
     */
    public function createDir($object = '',$bucket = '')
    {
        if (empty($bucket)) {
            $bucket = $this->bucket;
        }
        if (empty($object)) {
            $data = array(
                'status' => 0,
                'msg' => '没有新文件名！',
            );
            return $data;
        }
        try {
            if ($res = $this->ossClient->createObjectDir($bucket, $object)) {
                $data = array(
                    'status' => 1,
                    'msg' => '创建目录成功！',
                    'data' => $res,
                );
                return $data;
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '创建目录失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '创建目录出错，' . $e->getMessage(),
            );
            return $data;
        }
    }

    /**
     * @param $bucket
     * @param $object
     * @return array
     * Description:获取文件大小
     * User: 孙春晓
     * Date: 2019-03-21
     * Time: 14:38
     */
    public function getMetaInfoSize($object='',$bucket=''){
        if (empty($bucket)) {
            $bucket = $this->bucket;
        }
        if (empty($object)) {
            $data = array(
                'status' => 0,
                'msg' => '没有文件名！',
            );
            return $data;
        }
        $object=ltrim($object,'/');
        try {
            if ($res = $this->ossClient->getObjectMeta($bucket, $object)) {


                if(!empty($res['content-length'])) {
                    $data = array(
                        'status' => 1,
                        'msg' => '获取文件信息成功！',
                        'data' => $res['content-length'],
                    );
                    return $data;
                }else{
                    $data = array(
                        'status' => 0,
                        'msg' => '获取文件信息失败！',
                    );
                    return $data;
                }
            } else {
                $data = array(
                    'status' => 0,
                    'msg' => '获取文件信息失败！',
                );
                return $data;
            }
        } catch (OssException $e) {
            $data = array(
                'status' => 0,
                'msg' => '获取文件信息失败',
            );
            return $data;
        }
    }

    /**
     * @param string $object
     * @param string $localfile
     * @param string $bucket
     * @return array
     * Description:下载
     * Author: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019/8/5
     * Time: 15:10
     */
    public function down($object='',$localfile='',$bucket=''){

	    if (empty($bucket)) {
		    $bucket = $this->bucket;
	    }
	    if (empty($object)) {
		    $data = array(
			    'status' => 0,
			    'msg' => '没有文件的路径！',
		    );
		    return $data;
	    }
	    $options = [];
	    try{
		    $result = 	$this->ossClient->getObject($bucket,$object,$options);
	      	return  ['status'=>1,'msg'=>'获取文件流成功','data'=>$result];
	    }catch (\Exception $exception ){
	      return ['status'=>0,'msg'=>$exception->getMessage()];

	    }
    }
}