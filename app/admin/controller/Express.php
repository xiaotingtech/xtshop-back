<?php
/**
 * Created by xtshop
 * Class Express
 * Description:快递查询控制器
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-11-26 19:09
 */
namespace app\admin\controller;

class Express extends AuthBase
{
    protected $expressService;

    public function initialize()
    {
        parent::initialize();

        $this->expressService=app('app\common\service\ExpressService');
    }

    /**
     * @return \think\response\Json
     * Description:快递查询接口
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-11-26 19:21
     */
    public function query(){
        $data=$this->postData;
        $result=$this->expressService->queryByOrderId($data);
        return show($result['status'],$result['msg'],$result['data']);
    }
}