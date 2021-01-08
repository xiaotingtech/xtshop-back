<?php
/**
 * Created by xtshop
 * Class Express
 * Description:快递接口
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-11-26 15:20
 */
namespace app\api\controller;

class Express extends Base
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
     * 2020-11-26 15:20
     */
    public function query(){
        $data=$this->postData;
        $result=$this->expressService->queryByOrderId($data);
        return api_res($result['status'],$result['msg'],$result['data']);
    }
}