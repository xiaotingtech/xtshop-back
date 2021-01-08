<?php
/**
 * Created by xtshop
 * Class File
 * Description:文件处理控制器
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 16:23
 */
namespace app\admin\controller;

use think\facade\Request;
class File extends AuthBase
{
    protected $saveFileService;

    public function initialize()
    {
        parent::initialize();
        $this->saveFileService = app('app\common\service\SaveFileService');
    }

    /**
     * Description:上传文件到OSS
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 16:31
     */
    public function upload()
    {
        $file = Request::file('file');
        $userRes = $this->getUser();
        if($userRes['code']!=1){
            $user=[];
        }else{
            $user=$userRes['data'];
        }
        $result = $this->saveFileService->saveImageSvgFile($file, $user, 'admin');
        if ($result['status'] != 1) {
            return show($result['status'], $result['msg']);
        } else {
            return show($result['status'], $result['msg'], $result['data']);
        }
    }

    /**
     * Description:上传视频
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 15:46
     */
    public function uploadVideo()
    {
        $file = Request::file('file');
        $user = $this->getUser();
        $result = $this->saveFileService->saveVideoFile($file, $user, 'index');
        if ($result['status'] != 1) {
            return show($result['status'], $result['msg']);
        } else {
            return show($result['status'], $result['msg'], $result['data']);
        }
    }

    /**
     * Description:上传banner文件
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 16:31
     */
    public function uploadBanner()
    {
        $file = Request::file('file');
        $user = $this->getUser();
        $result = $this->saveFileService->saveImageSvgFile($file, $user, 'admin');
        if ($result['status'] != 1) {
            return show($result['status'], $result['msg']);
        } else {
            return show($result['status'], $result['msg'], $result['data']);
        }
    }
}