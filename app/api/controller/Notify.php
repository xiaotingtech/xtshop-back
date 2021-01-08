<?php
/**
 * Created by xtshop
 * Class Notify
 * Description:支付通知处理控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 16:27
 */
namespace app\api\controller;

use think\facade\Request;
use app\BaseController;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use Yansongda\Pay\Exceptions\InvalidSignException;
class Notify extends BaseController
{
    protected $wechatConfig = [];

    private $orderNotifyRepository;

    public function initialize()
    {
        parent::initialize();
        //微信
        $this->wechatConfig=config('pay.wechat_pay');

        $this->orderNotifyRepository = app('app\common\repository\OrderNotifyRepository');
    }

    /**
     * Description:微信异步通知
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-09-10 13:57
     */
    public function wechatNotify(){
        $requestData=Request::param();
        $notifyLog=$this->orderNotifyRepository;
        $wechatPay = Pay::wechat($this->wechatConfig);
        $orderType=1;
        try{
            $data = $wechatPay->verify(); // 是的，验签就这么简单！
            $orderNo=$data['out_trade_no'];
            //判断是素材订单还是VIP订单
            //截取前5位判断是素材购买还是VIP购买
            $typeStr=substr($orderNo,0,5);
            if($typeStr=='XTSGO'){
                $orderType=1;
                return $notifyLog->wechatProductNotify($data,$requestData);
            }else{
                $orderType=0;
                //记录日志--start
                $notifyLog->save(['msg'=>'订单类型不正确'],2,$orderType,2,'');
                //记录日志--end
                Log::info('Wechat notify',$requestData);
                echo 'error';exit();
            }
        }catch (InvalidSignException $e){
            //记录日志--start
            $notifyLog->save(['msg'=>'验签失败：'.$e->getMessage()],2,$orderType,2,'');
            //记录日志--end
            Log::info('Wechat notify',$requestData);
            echo 'error';exit();
        }catch (\Exception $e) {
            //记录日志--start
            $notifyLog->save(['msg'=>$e->getMessage().'，错误文件：'.$e->getFile().'，错误行号'.$e->getLine()],2,$orderType,2,'');
            //记录日志--end
            Log::info('Wechat notify',$requestData);
            echo 'error';exit();
        }
    }
}