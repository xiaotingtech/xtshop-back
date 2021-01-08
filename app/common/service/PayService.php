<?php
/**
 * Created by xtshop
 * Class PayService
 * Description:支付相关服务类
 * @package app\common\service
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 15:52
 */
namespace app\common\service;

use think\App;
use Yansongda\Pay\Pay;
class PayService extends BaseService
{
    protected $productOrderRepository;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->productOrderRepository=app('app\common\repository\ProductOrderRepository');
    }
    /**
     * @param $data
     * @param $user
     * @return array
     * Description:调起支付
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 15:52
     */
    public function buyV1($data,$user){
        //先创建订单记录，然后小程序支付，默认小程序支付
        $payType=!empty($data['pay_type'])?$data['pay_type']:1;
        if($payType==1) {
            $orderDataRes=$this->createOrder($user,$data,$payType,1);
            if($orderDataRes['status']!=1){
                return [
                    'status'=>$orderDataRes['status'],
                    'msg'=>$orderDataRes['msg']
                ];
            }
            $orderData=$orderDataRes['data'];
            try {
                $orderInfo=[
                    'out_trade_no' => $orderData['order_no'],
                    'total_fee' =>  $orderData['total_price']*100,
                    'body' =>  $orderData['order_title'],
                    'openid'=>$user['openid']
                ];
                $payData = Pay::wechat(config('pay.wechat_pay'))->miniapp($orderInfo);
                return ['status' => 1, 'msg' => '创建订单成功！', 'data' => ['pay_str' => $payData,'order_no'=>$orderData['order_no']]];
            } catch (\Exception $e) {
                return [
                    'status' => -10000,
                    'msg' => '创建订单失败！，因为' . $e->getMessage()
                ];
            }
        }else{
            return [
                'status' => -10000,
                'msg' => '请选择支付方式！'
            ];
        }
    }

    /**
     * @param $user
     * @param $data
     * @param int $payType
     * @param int $apiType
     * @return array
     * Description:创建订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 15:58
     */
    public function createOrder($user,$data,$payType=1,$apiType=1){
        //查询是否有3秒之内的未支付订单，有的话返回此订单记录
        return $this->productOrderRepository->createOrder($user,$data,$payType,$apiType);
    }

    /**
     * @param $data
     * @param $user
     * @return mixed
     * Description:支付查询
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:51
     */
    public function callbackV1($data,$user){
        return $this->productOrderRepository->callbackV1($data,$user);
    }
}