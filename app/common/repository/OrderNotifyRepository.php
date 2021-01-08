<?php
/**
 * Created by xtshop
 * Class OrderNotifyRepository
 * Description:通知类的数据处理
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 16:30
 */
namespace app\common\repository;

use app\common\model\OrderNotify;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;
class OrderNotifyRepository extends BaseRepository
{
    protected $aliConfig = [];

    protected $wechatConfig = [];
    //订单通知日志model类
    private $orderNotifyModel;
    //用户购买商品订单服务类
    private $productOrderRepository;

    public function __construct()
    {
        //微信
        $this->wechatConfig=config('pay.wechat_pay');

        $this->orderNotifyModel=new OrderNotify();

        $this->productOrderRepository = app('app\common\repository\ProductOrderRepository');
    }
    /**
     * @param $data
     * @param $requestData
     * @return \Symfony\Component\HttpFoundation\Response
     * Description:微信商品购买通知
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:30
     */
    public function wechatProductNotify($data,$requestData){
        //购买商品
        $orderType = 2;
        try {
            $wechatPay = Pay::wechat($this->wechatConfig);
            //素材
            $orderNo = $data['out_trade_no'];
            $tradeStatus = $data['return_code'];
            if ($tradeStatus === 'SUCCESS' && ($data['result_code'] === 'SUCCESS')) {
                $orderInfo = $this->productOrderRepository->getOrderByNo($orderNo);
                if (empty($orderInfo)) {
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, '没查到订单数据');
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    echo 'error';
                    exit();
                }
                if ($orderInfo['order_status'] > 1) {
                    //说明已经支付操作过了，不再处理
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, '该订单状态是' . $orderInfo['order_status'] . '，不是未支付');
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    if($orderInfo['order_status']==2) {
                        return $wechatPay->success()->send();
                    }else{
                        echo 'error';
                        exit();
                    }
                }
                //比较金额等参数，无误后将订单改为已支付
                if ((float)($orderInfo['total_price'] * 100) != (float)($data['total_fee'])) {
                    //说明支付成功，然后进行其他验证
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, '支付金额验证失败');
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    echo 'error';
                    exit();
                }
                if ($this->wechatConfig['miniapp_id'] != $data['appid']) {
                    //说明支付成功，然后进行其他验证
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, 'APPID验证失败');
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    echo 'error';
                    exit();
                }
                if ($this->wechatConfig['mch_id'] != $data['mch_id']) {
                    //说明支付成功，然后进行其他验证
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, 'mch_id验证失败');
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    echo 'error';
                    exit();
                }
                //一次处理多种数据
                $data['trade_no'] = $data['transaction_id'];
                $notifyRes = $this->productOrderRepository->notify($orderInfo, $data);
                if ($notifyRes['status'] == 1) {
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, '支付成功，' . $notifyRes['msg']);
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    return $wechatPay->success()->send();
                } else {
                    //记录日志--start
                    $this->save($data, 1, $orderType, 2, $orderNo, $notifyRes['msg']);
                    //记录日志--end
                    //记录文件日志--start
                    Log::info('Wechat notify', $data->all());
                    //记录文件日志--end
                    echo 'error';
                    exit();
                }
            }
            //记录日志--start
            $this->save($data, 1, $orderType, 2, $orderNo);
            //记录文件日志--start
            Log::info('Wechat notify', $data->all());
            //记录文件日志--end
            echo 'error';
            exit();
        }catch (\Exception $e){
            //记录日志--start
            $this->save(['msg'=>$e->getMessage().'，错误文件：'.$e->getFile().'，错误行号'.$e->getLine()],2,$orderType,2,'');
            //记录日志--end
            Log::info('Wechat notify',$requestData);
            echo 'error';exit();
        }
    }

    /**
     * @param $data
     * @param int $type
     * @param int $orderType
     * @param int $notifyType
     * @param string $orderNo
     * @param string $orderResult
     * Description:记录回调日志
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:32
     */
    public function save($data,$type=1,$orderType=1,$notifyType=2,$orderNo='',$orderResult=''){
        $saveData=[
            'order_type'=>$orderType,
            'type'=>$type,
            'notify_time'=>time(),
            'notify_type'=>$notifyType,
            'order_no'=>$orderNo,
            'content'=>json_encode($data),
            'result'=>$orderResult
        ];
        $this->orderNotifyModel->save($saveData);
    }
}