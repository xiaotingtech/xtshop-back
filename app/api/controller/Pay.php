<?php
/**
 * Created by xtshop
 * Class Pay
 * Description:支付相关
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 15:46
 */
namespace app\api\controller;

class Pay extends Base
{
    protected $payService;

    public function initialize()
    {
        parent::initialize();

        $this->payService=app('app\common\service\PayService');
    }
    /**
     * @return \think\response\Json
     * Description:购买VIP
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-09-11 20:06
     */
    public function buy(){
        $data=$this->postData;
        $userRes=$this->getUser();
        if($userRes['code']!=1){
            return api_res($userRes['code'],$userRes['msg']);
        }
        $res=$this->payService->buyV1($data,$userRes['data']);
        if ($res['status']==1) {
            return api_res(1,$res['msg'],$res['data']);
        }
        return api_res(-10000, $res['msg']);
    }

    /**
     * @return \think\response\Json
     * Description:支付查询接口
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:49
     */
    public function vipCallBackV1(){
        $data=$this->postData;
        $userRes=$this->getUser();
        if($userRes['code']!=1){
            return api_res($userRes['code'],$userRes['msg']);
        }
        $res=$this->payService->callbackV1($data,$userRes['data']);
        if ($res['status']==1) {
            return api_res(1,$res['msg'],$res['data']);
        }
        return api_res(-10000,  '支付失败！');
    }
}