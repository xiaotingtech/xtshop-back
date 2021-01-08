<?php
/**
 * Created by xtshop
 * Class ProductOrderRepository
 * Description:商品订单数据处理类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 16:00
 */
namespace app\common\repository;

use app\common\model\Product;
use app\common\model\SkuProduct;
use app\common\model\ProductOrder;
use app\common\model\Order;
use app\common\model\User;
use app\common\model\Cart;
use think\facade\Db;
use think\facade\Event;
class ProductOrderRepository extends BaseRepository
{
    //Product的model
    protected $productModel;
    //SkuProduct的model
    protected $skuProductModel;
    //大订单order的model
    protected $orderModel;
    //Product_order的model
    protected $productOrderModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->skuProductModel = new SkuProduct();
        $this->orderModel=new Order();
        $this->productOrderModel = new ProductOrder();
    }

    /**
     * @param $orderNo
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:根据订单号查询订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:35
     */
    public function getOrderByNo($orderNo){
        if($orderInfo=$this->orderModel
            ->field('id,uid,pay_type,order_no,api_type,coupon_id
            ,price,total_price,benefit_price,order_status,create_time')
            ->where('order_no',$orderNo)
            ->order('id DESC')->find()){
            return $orderInfo;
        }else{
            return [];
        }
    }

    /**
     * @param $user
     * @param $data
     * @param int $payType
     * @param int $apiType
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:创建订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:14
     */
    public function createOrder($user,$data,$payType=1,$apiType=1){
        if(empty($user)){
            return [
                'status'=>-10000,
                'msg'=>'用户不存在！'
            ];
        }
        $uid=$user['id'];
        //如果存在订单ID则直接查询订单后返回
        if(!empty($data['order_id'])){
            $orderId=$data['order_id'];
            $orderInfo=$this->orderModel
                ->field('id,uid,pay_type,order_no,price,total_price,order_status')
                ->where('uid',$uid)
                ->where('id',$orderId)
                ->where('order_status',1)
                ->where('pay_type',$payType)
                ->find();
            //更新支付方式
            if($payType!=$orderInfo['pay_type']){
                //更新支付类型
                $this->orderModel->where('id',$orderId)
                    ->update(['pay_type'=>$payType,'update_time'=>time()]);
            }
            $orderInfo['order_title']='XTSHOP订单购买'.$orderInfo['order_no'];
            return [
                'status'=>1,
                'msg'=>'创建订单成功！',
                'data'=>$orderInfo
            ];
        }
        if(empty($data['product_data'])){
            return [
                'status'=>-10000,
                'msg'=>'商品为空！'
            ];
        }
        $productDataInfo=$data['product_data'];
        if(empty($data['address_data'])){
            return [
                'status'=>-10000,
                'msg'=>'请先选择地址！'
            ];
        }
        $addressData=$data['address_data'];
        //商品ID
        $productIds=[];
        //SKU商品ID
        $skuProductIds=[];
        //商品总价计算值
        $skuProductTotalPrice=0;
        //SKU商品对应的购买数量
        $skuProductBuyNums=[];
        //所有产品数的计算
        $skuProductNums=0;
        //购物车数据ID，用来最后清空购物车里购买过得商品
        $cartIds=[];
        foreach ($productDataInfo as $pk=>$pv) {
            if (empty($pv['product_id'])) {
                return [
                    'status' => -10000,
                    'msg' => '商品ID为空！'
                ];
            }else{
                $productIds[]=$pv['product_id'];
            }
            if(!empty($pv['sku_product_id'])){
                $skuProductIds[]=$pv['sku_product_id'];
                $nowProductNum=intval($pv['product_num']);
                $skuProductBuyNums[$pv['sku_product_id']]=$nowProductNum;
                $skuProductNums+=$nowProductNum;
                $skuProductTotalPrice+=round(floatval($pv['product_price'])*$nowProductNum,2);
            }
            if(!empty($pv['id'])){
                $cartIds[]=$pv['id'];
            }
        }
        //比较总价和传来的值，有问题提示
        if($skuProductTotalPrice!=floatval($data['total_price'])){
            return [
                'status' => -10000,
                'msg' => '价格有误！'
            ];
        }
        //查询商品是否存在
        $skuProductDataRes=$this->skuProductModel->field('id,product_id,sku_price,sku_stock,property_value')
            ->where('id','IN',$skuProductIds)
            ->select();
        if($skuProductDataRes->isEmpty()){
            return [
                'status'=>-10000,
                'msg'=>'未查询到商品信息！'
            ];
        }
        $skuProductData=$skuProductDataRes->toArray();
        //判断商品数量的库存是否还够
        foreach ($skuProductBuyNums as $spbk=>$spbv){
            $exitFlag=false;
            foreach ($skuProductData as $spk=>$spv){
                if($spbk==$spv['id']){
                    $exitFlag=true;
                    if($spbv>$spv['sku_stock']){
                        $productErrorTitle='';
                        foreach ($productDataInfo as $productErrorInfo){
                            if($spv['product_id']==$productErrorInfo['product_id']){
                                $productErrorTitle=$productErrorInfo['product_name'];
                                break;
                            }
                        }
                        return [
                            'status'=>-10000,
                            'msg'=>$productErrorTitle.' '.$spv['property_value'].'库存不够！'
                        ];
                    }
                }
            }
            if(!$exitFlag){
                return [
                    'status'=>-10000,
                    'msg'=>'未查询到商品信息！'
                ];
            }
        }
        //否则开始创建订单
        $orderPrice=$skuProductTotalPrice;
        $nowTime=time();
        Db::startTrans();
        try {
            $nowOrderNo=$this->createOrderNo();
            $orderData = [
                'uid' => $uid,
                'api_type' => $apiType,
                'pay_type' => $payType,
                'order_no' => $nowOrderNo,
                'price' => $orderPrice,
                'total_price' => $orderPrice,
                'username'=>$addressData['username'],
                'phone'=>$addressData['phone'],
                'address'=>$addressData['address'].$addressData['address_door'],
                'product_num' => $skuProductNums,
                'order_status' => 1,
                'create_time'=>$nowTime,
                'update_time'=>$nowTime
            ];
            //生成一条待支付订单
            if (!$insertOrderId=$this->orderModel->insertGetId($orderData)) {
                Db::rollback();
                return [
                    'status' => -10000,
                    'msg' => '创建订单失败！'
                ];
            } else {
                //创建子订单
                $skuOrderData=[];
                foreach ($productDataInfo as $productInfo){
                    $skuOrderData[]=[
                        'uid'=>$uid,
                        'order_id'=>$insertOrderId,
                        'order_no'=>$nowOrderNo,
                        'product_id'=>$productInfo['product_id'],
                        'sku_product_id'=>$productInfo['sku_product_id'],
                        'sku_property'=>$productInfo['sku_property'],
                        'property_value'=>$productInfo['property_value'],
                        'product_name'=>$productInfo['product_name'],
                        'order_title'=>$productInfo['product_name'].$productInfo['property_value'],
                        'product_img'=>filter_pics_url($productInfo['img']),
                        'total_price'=>round(floatval($productInfo['product_price'])*intval($productInfo['product_num']),2),
                        'price'=>$productInfo['product_price'],
                        'product_num'=>$productInfo['product_num'],
                        'create_time'=>$nowTime,
                        'update_time'=>$nowTime
                    ];
                }
                if (!$this->productOrderModel->insertAll($skuOrderData)) {
                    Db::rollback();
                    return [
                        'status' => -10000,
                        'msg' => '创建子订单失败！'
                    ];
                }
                if(!empty($cartIds)){
                    $cartModel=new Cart();
                    if(!$cartModel->destroy($cartIds)){
                        Db::rollback();
                        return [
                            'status' => -10000,
                            'msg' => '删除购物车失败！'
                        ];
                    }
                }
                Db::commit();
                $orderData['order_title']='XTSHOP订单购买'.$orderData['order_no'];
                return [
                    'status' => 1,
                    'msg' => '创建订单成功！',
                    'data' => $orderData
                ];
            }
        }catch (\Exception $e){
            Db::rollback();
            return [
                'status' => -10000,
                'msg' => '创建订单失败！'
            ];
        }
    }

    /**
     * @return string
     * Description:创建订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:12
     */
    private function createOrderNo(){
        $current_year =date('Y'); //4
        $current_month =date('m'); //2
        $current_day =date('d'); //2
        $current_time =substr(time(), -4) ; //4
        $mir =substr(microtime(), 2, 5);//5
        $range =sprintf('%02d', rand(0, 999)); //3
        $serverIp=config('pay.server_ip');
        if(empty($serverIp)){
            $serverIp='127.0.0.1';
        }
        //替换.，然后取后五位
        $serverIp=str_replace('.','',$serverIp);
        $serverIpNum =substr($serverIp, -5) ;//5
        $str = $current_year.$current_month.$current_day.$current_time.$mir.$serverIpNum.$range;
        if (strlen($str)==25){
            return 'XTSGO'.$str;
        }else {
            $bq =25-strlen($str);
            $b =9;
            $kq =1;
            for ($i=0;$i<$bq-1;$i++){
                $b .=9;
                $kq .=0;
            }
            if ($b !=''){
                $range =sprintf('%02d', rand($kq, $b));
                return 'XTSGO'.$str.(int)$range;
            }
            return 'XTSGO'.$str;
        }
    }

    /**
     * @param $orderInfo
     * @param $data
     * @return array
     * Description:异步通知处理类
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:36
     */
    public function notify($orderInfo,$data){
        Db::startTrans();
        $nowTime=time();
        try{
            $uid=$orderInfo['uid'];
            if($this->orderModel->field('id')->where('trade_no',$data['trade_no'])
                ->where('pay_type',$orderInfo['pay_type'])->find()){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'更新支付表失败，因为该第三方订单号已经存在！，用户ID是'.$uid
                ];
            }
            //更新订单表为已支付
            if(!$this->orderModel->where('id',$orderInfo['id'])
                ->update(['order_status'=>2,'trade_no'=>$data['trade_no'],
                    'pay_status'=>1,'pay_time'=>$nowTime,'update_time'=>$nowTime])){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'更新r订单表失败！，订单id是'.$orderInfo['id']
                ];
            }
            $productOrderData=$this->productOrderModel
                ->field('id,sku_product_id,product_num')
                ->where('order_id',$orderInfo['id'])
                ->select();
            $productOrderIds=[];
            $skuProductIds=[];
            foreach ($productOrderData as $productOrderVal){
                $productOrderIds[]=$productOrderVal['id'];
                $skuProductIds[]=$productOrderVal['sku_product_id'];
            }
            if(!empty($skuProductIds)) {
                $arrayCountIds=array_count_values($skuProductIds);
                $arrayCountValues=array_unique(array_values($arrayCountIds));
                $needSaveNum=[];
                foreach ($arrayCountValues as $count){
                    foreach ($arrayCountIds as $id=>$countVal){
                        if($count==$countVal){
                            $needSaveNum[$count][]=$id;
                        }
                    }
                }
                foreach ($needSaveNum as $num=>$saveIds){
                    if(!Db::table(env('database.prefix','xt_').'sku_product')
                        ->where('id', 'IN', $saveIds)
                        ->dec('sku_stock',intval($num))->update()){
                        Db::rollback();
                        return [
                            'status'=>0,
                            'msg'=>'更新数据失败！'
                        ];
                    }
                }
            }
            //更新子订单表为已支付
            if(!$this->productOrderModel->where('id','IN',$productOrderIds)
                ->update(['order_status'=>2,'trade_no'=>$data['trade_no'],'update_time'=>$nowTime])){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'更新子订单表失败！，订单id是'.$orderInfo['id']
                ];
            }
            //查询用户信息
            $userModel=new User();
            if(!$user=$userModel->field('id,score_num,update_time')->where('id',$uid)->where('status',1)->find()){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'未查询到用户信息！，用户ID是'.$uid
                ];
            }
            $changeScore=$orderInfo['total_price'];
            $nowScore=$user['score_num']+$changeScore;
            //增加经验值
            $scoreData=[
                'uid'=>$uid,
                'order_id'=>$orderInfo['id'],
                'score_type'=>1,
                'amount'=>$changeScore,
                'remain_score'=>$nowScore
            ];
            if(!app('app\common\repository\UserScoreLogRepository')
                ->saveData($scoreData)){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'保存经验流水失败!'
                ];
            }
            if (!$userModel->where('id', $uid)
                ->where('update_time', strtotime($user['update_time']))
                ->update(['score_num' => $nowScore, 'update_time' => $nowTime])) {
                Db::rollback();
                return [
                    'status' => 0,
                    'msg' => '更新用户表会员时间失败！，用户ID是' . $uid
                ];
            }
            //增加收支明细表数据插入
            $logData=[
                'uid'=>$uid,
                'price'=>$orderInfo['total_price'],
                'user_product_order_id'=>$orderInfo['id'],
            ];
            if(!app('app\common\repository\UserMoneyLogRepository')
                ->saveData($logData)){
                Db::rollback();
                return [
                    'status'=>0,
                    'msg'=>'保存流水表失败!'
                ];
            }
            Event::trigger('ProductBuy', $orderInfo);
            Db::commit();
            return [
                'status' => 1,
                'msg' => '处理完成，没问题！'
            ];
        }catch (\Exception $e){
            Db::rollback();
            return [
                'status'=>0,
                'msg'=>'更新数据出错！'.$e->getMessage()
            ];
        }
    }

    /**
     * @param $data
     * @param $user
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:支付结果查询
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:50
     */
    public function callbackV1($data,$user){
        if(empty($data['order_no'])){
            return [
                'status'=>-10000,
                'msg'=>'订单号不能为空！！'
            ];
        }
        $orderNo=$data['order_no'];
        $uid=$user['id'];
        if($hasOrder=$this->orderModel->field('id,order_no,total_price,order_status')
            ->where('uid',$uid)
            ->where('order_no',$orderNo)
            ->find()){
            return [
                'status'=>1,
                'msg'=>'查询成功！',
                'data'=>$hasOrder
            ];
        }else{
            return [
                'status'=>-10000,
                'msg'=>'未查询到该订单！',
                'data'=>[]
            ];
        }
    }

    /**
     * @param array $data
     * @param array $user
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取订单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 18:41
     */
    public function getList($data,$user=[],$page=1,$listRow=10){
        $where=[];
        if(!empty($data['order_status'])){
            $where[]=['order_status','=',intval($data['order_status'])];
        }
        $uid=$user['id'];
        $productOrderModel=$this->orderModel;
        $allCount=$productOrderModel->where('uid',$uid)
            ->where($where)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'暂时没有订单！',
                'data'=>[
                    'list'=>[],
                    'total'=>$allCount,
                    'pages'=>ceil($allCount/$listRow)
                ],
            ];
        }
        $orderResult=$this->orderModel
            ->field('id,order_no,total_price,order_status,product_num,create_time')
            ->where('uid',$uid)
            ->where($where)
            ->page($page,$listRow)
            ->order('id DESC')
            ->select();
        if($orderResult->isEmpty()){
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'total'=>$allCount,
                    'pages'=>ceil($allCount/$listRow)
                ],
            ];
        }
        $orderIds=[];
        $productOrderData=$orderResult->toArray();
        foreach ($productOrderData as $orderKey=>$orderVal){
            $orderIds[]=$orderVal['id'];
        }
        if(!empty($orderIds)){
            $productOrderInfoRes=$this->productOrderModel
                ->field('id,order_id,product_id,sku_product_id,property_value,price,product_img,product_num')
                ->where('order_id','IN',$orderIds)
                ->select();
            $productOrderInfo=[];
            if(!$productOrderInfoRes->isEmpty()){
                $productOrderInfo=$productOrderInfoRes->toArray();
            }
            foreach ($productOrderData as $pok=>$pov){
                $orderSkuProduct=[];
                foreach ($productOrderInfo as $productOrderVal){
                    if($pov['id']==$productOrderVal['order_id']){
                        $productOrderVal['product_img']=get_real_url($productOrderVal['product_img']);
                        $orderSkuProduct[]=$productOrderVal;
                    }
                }
                $productOrderData[$pok]['product_order']=$orderSkuProduct;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>$productOrderData,
                    'total'=>$allCount,
                    'pages'=>ceil($allCount/$listRow)
                ],
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'total'=>$allCount,
                    'pages'=>ceil($allCount/$listRow)
                ],
            ];
        }
    }

    /**
     * @param $data
     * @param array $user
     * @return array
     * Description:取消订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 19:15
     */
    public function cancel($data,$user=[]){
        if(empty($user)) {
            return [
                'status' => -10000,
                'msg' => '没有用户信息！',
            ];
        }
        if(empty($data['id'])) {
            return [
                'status' => -10000,
                'msg' => '没有订单ID！',
            ];
        }
        $orderId=$data['id'];
        if($this->productOrderModel
            ->where('id',$orderId)
            ->update(['order_status'=>0])){
            return [
                'status' => 1,
                'msg' => '取消成功！',
            ];
        }else{
            return [
                'status' => -10000,
                'msg' => '取消失败！',
            ];
        }
    }

    /**
     * @param $data
     * @param $user
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取订单详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 15:33
     */
    public function detail($data,$user){
        if(empty($user)) {
            return [
                'status' => 0,
                'msg' => '没有用户信息！',
            ];
        }
        if(empty($data['id'])) {
            return [
                'status' => 0,
                'msg' => '没有订单ID！',
            ];
        }
        $uid=$user['id'];
        $orderId=$data['id'];
        $ProductOrderResult=$this->productOrderModel
            ->field('id,order_title,order_no,product_id,total_price,benefit_price,start_date,end_date,day,order_status,create_time')
            ->where('id',$orderId)
            ->where('uid',$uid)
            ->find();
        if(empty($ProductOrderResult)){
            return [
                'status' => 0,
                'msg' => '没有订单信息！',
            ];
        }
        $productData=$this->productModel
            ->field('id,season_type,type,title,price,join_num')
            ->where('id',$ProductOrderResult['product_id'])
            ->find();
        if(empty($productData)){
            return [
                'status' => 0,
                'msg' => '没有商品信息！',
            ];
        }
        $seasonType=config('Product.season_type');
        $ProductOrderResult['start_timestamp']=strtotime($ProductOrderResult['start_date'].' 00:00:00');
        $ProductOrderResult['start_date']=date_special_str($ProductOrderResult['start_date']);
        $ProductOrderResult['end_date']=date_special_str($ProductOrderResult['end_date']);
        $ProductOrderResult['Product']=$productData;
        return [
            'status' => 1,
            'msg' => '获取成功！',
            'data'=>$ProductOrderResult
        ];
    }

    /**
     * @param $user
     * @return array
     * Description:订单数量
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-19 13:00
     */
    public function orderNum($user)
    {
        $uid=$user['id'];
        $unPayNum=$this->productOrderModel->where('uid',$uid)
            ->where('order_status',1)->count();
        $payNum=$this->productOrderModel->where('uid',$uid)
            ->where('order_status',2)->count();
        $payAllNum=$this->productOrderModel->where('uid',$uid)->count();
        $payOpenNum=$this->productOrderModel->where('uid',$uid)
            ->where('order_status',3)->count();
        $payOverNum=$this->productOrderModel->where('uid',$uid)
            ->where('order_status',4)->count();
        return [
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>[
                'all_num'=>$payAllNum,
                'unpay_num'=>$unPayNum,
                'pay_num'=>$payNum,
                'pay_open_num'=>$payOpenNum,
                'pay_over_num'=>$payOverNum
            ]
        ];
    }

    /**
     * @param $time
     * @return array
     * Description:脚本取消订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-20 21:41
     */
    public function cancelOrder($time){
        if(empty($time)) {
            $time=1;
        }
        $timePeriod=$time*3600;
        $nowTime=time();
        try {
            ProductOrder::field('id')
                ->where('create_time', '<', $nowTime- $timePeriod)
                ->where('order_status','=',1)
                ->chunk(50, function ($data) {
                    $orderIds = [];
                    foreach ($data as $val) {
                        $orderIds[] = $val['id'];
                    }
                    if (!$this->productOrderModel
                        ->where('id', 'IN', $orderIds)
                        ->where('order_status','=',1)
                        ->update(['order_status' => 0])) {
                        return [
                            'status' => -10000,
                            'msg' => '取消失败！',
                        ];
                    }
                });
            return [
                'status' => 1,
                'msg' => '取消成功！',
            ];
        }catch (\Exception $e){
            return [
                'status' => -10000,
                'msg' => '取消失败！'.$e->getMessage(),
            ];
        }
    }
}