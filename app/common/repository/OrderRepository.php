<?php
/**
 * Created by xtshop
 * Class OrderRepository
 * Description:大订单表数据处理类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-26 11:13
 */
namespace app\common\repository;

use app\common\model\Order;
use app\common\model\Product;
use app\common\model\ProductOrder;
use app\common\model\SkuProduct;
use app\common\model\OrderDelivery;
use think\facade\Db;
class OrderRepository extends BaseRepository
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
     * @param array $data
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取订单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 11:14
     */
    public function getList($data=[],$page=1,$listRow=10){
        $where=[];
        $whereSql='';
        if(!empty($data['order_status'])){
            $orderStatus=$data['order_status'];
            if($orderStatus==-1){
                $orderStatus=0;
            }
            $where[]=['order_status','=',$orderStatus];
        }
        if(!empty($data['order_no'])){
            $where[]=['order_no','=',$data['order_no']];
        }
        if(!empty($data['create_time'])){
            $where[]=['create_time','between',
                [strtotime($data['create_time'].' 00:00:00'),strtotime($data['create_time'].' 24:00:00')]];
        }
        if(!empty($data['receiver_keyword'])){
            $whereSql='username like "'.$data['receiver_keyword'].'%" OR phone like "'.$data['receiver_keyword'].'%"';
        }
        if(!empty($whereSql)) {
            $allCount = $this->orderModel
                ->where($where)
                ->whereRaw($whereSql)->count();
        }else{
            $allCount = $this->orderModel
                ->where($where)
                ->count();
        }
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'暂时没有订单！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        if(!empty($whereSql)) {
            $orderResult = $this->orderModel
                ->field('id,uid,order_no,total_price,source_type,pay_type,
                username,phone,address,order_status,product_num,pay_time,create_time')
                ->where($where)
                ->whereRaw($whereSql)
                ->with(['user','delivery'])
                ->page($page, $listRow)
                ->order('id DESC')
                ->select();
        }else{
            $orderResult = $this->orderModel
                ->field('id,uid,order_no,total_price,source_type,pay_type,
                username,phone,address,order_status,product_num,pay_time,create_time')
                ->where($where)
                ->with('user')
                ->page($page, $listRow)
                ->order('id DESC')
                ->select();
        }
        if($orderResult->isEmpty()){
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        return [
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>[
                'list'=>$orderResult,
                'page' => $page,
                'list_row' => $listRow,
                'total' => $allCount,
                'totalPage' =>ceil($allCount/$listRow),
            ],
        ];
    }

    /**
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取订单详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 15:57
     */
    public function getDetailInfo($data){
        $result=new Class{};
        if(empty($data['order_id'])){
            return [
                'status'=>0,
                'msg'=>'未获取到订单ID',
                'data'=>$result
            ];
        }
        $orderId=$data['order_id'];
        $orderRes=$this->orderModel
            ->where('id',$orderId)
            ->with(['user','product_order','delivery','manage_log'])
            ->find();
        if(!empty($orderRes)){
            $productOrderInfo=$orderRes->product_order;
            foreach ($productOrderInfo as $pk=>$pv){
                $productOrderInfo[$pk]['product_img']=get_real_url($pv['product_img']);
            }
            return [
                'status'=>1,
                'msg'=>'获取成功',
                'data'=>$orderRes
            ];
        }else{
            return [
                'status'=>0,
                'msg'=>'未获取到订单信息',
                'data'=>$result
            ];
        }
    }

    /**
     * @param $data
     * @param $userInfo
     * @return array
     * Description:修改收货地址信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 9:51
     */
    public function receiverInfo($data,$userInfo){
        if(empty($data['order_id'])){
            return [
                'status'=>0,
                'msg'=>'没有订单ID',
                'data'=>[]
            ];
        }
        $orderId=intval($data['order_id']);
        if(empty($data['username'])||empty($data['phone'])||empty($data['address'])){
            return [
                'status'=>0,
                'msg'=>'地址信息有误',
                'data'=>[]
            ];
        }
        if(!$orderInfo=$this->orderModel
            ->field('id,username,phone,address')
            ->where('id',$orderId)->find()){
            return [
                'status'=>0,
                'msg'=>'未找到订单信息',
                'data'=>[]
            ];
        }
        $saveData=[
            'username'=>$data['username'],
            'phone'=>$data['phone'],
            'address'=>$data['address'],
        ];
        if(!$this->orderModel->where('id',$orderId)
        ->update($saveData)){
            return [
                'status'=>0,
                'msg'=>'修改订单信息失败',
                'data'=>[]
            ];
        }else{
            \think\facade\Event::trigger('OrderManageLog',
                ['type'=>1,'user'=>$userInfo,'old'=>$orderInfo,'new'=>$saveData]);
            return [
                'status'=>1,
                'msg'=>'修改成功',
                'data'=>[]
            ];
        }
    }

    /**
     * @param $dataInfo
     * @param $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:订单发货
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 10:35
     */
    public function delivery($dataInfo,$userInfo){
        //传来的是数组所以只取第一个
        $data=$dataInfo[0];
        if(empty($data['order_id'])){
            return [
                'status'=>0,
                'msg'=>'没有订单ID',
                'data'=>[]
            ];
        }
        $orderId=intval($data['order_id']);
        if(empty($data['delivery_company'])||empty($data['delivery_sn'])){
            return [
                'status'=>0,
                'msg'=>'发货信息有误',
                'data'=>[]
            ];
        }
        if(!$orderInfo=$this->orderModel
            ->field('id,username,phone,address,order_status')
            ->where('id',$orderId)->find()){
            return [
                'status'=>0,
                'msg'=>'未找到订单信息',
                'data'=>[]
            ];
        }
        if($orderInfo['order_status']>2){
            return [
                'status'=>0,
                'msg'=>'该订单已发货',
                'data'=>[]
            ];
        }
        $uid=$userInfo['id'];
        $saveData=[
            'uid'=>$uid,
            'order_id'=>$orderId,
            'username'=>$data['username'],
            'phone'=>$data['phone'],
            'address'=>$data['address'],
            'delivery_company'=>$data['delivery_company'],
            'delivery_sn'=>$data['delivery_sn'],
        ];
        $orderDeliverModel=new OrderDelivery();
        if(!$orderDeliverModel->save($saveData)){
            return [
                'status'=>0,
                'msg'=>'保存订单快递信息失败',
                'data'=>[]
            ];
        }else{
            if(!$this->orderModel->where('id',$orderId)
            ->update(['order_status'=>3,'update_time'=>time()])){
                return [
                    'status'=>0,
                    'msg'=>'保存订单状态信息失败',
                    'data'=>[]
                ];
            }
            \think\facade\Event::trigger('OrderManageLog',
                ['type'=>2,'user'=>$userInfo,'record_content'=>$saveData]);
            return [
                'status'=>1,
                'msg'=>'修改成功',
                'data'=>[]
            ];
        }
    }

    /**
     * @param $data
     * @param $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:订单备注
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 15:21
     */
    public function remark($data,$userInfo){
        if(empty($data['order_id'])){
            return [
                'status'=>0,
                'msg'=>'没有订单ID',
                'data'=>[]
            ];
        }
        $orderId=intval($data['order_id']);
        if(empty($data['remark'])){
            return [
                'status'=>0,
                'msg'=>'备注信息为空',
                'data'=>[]
            ];
        }
        if(!$orderInfo=$this->orderModel
            ->field('id,order_status')
            ->where('id',$orderId)->find()){
            return [
                'status'=>0,
                'msg'=>'未找到订单信息',
                'data'=>[]
            ];
        }
        $saveData=[
            'remark'=>$data['remark'],
        ];
        \think\facade\Event::trigger('OrderManageLog',
            ['type'=>3,'user'=>$userInfo,'record_content'=>$saveData,'order_info'=>$orderInfo]);
        return [
            'status'=>1,
            'msg'=>'保存成功',
            'data'=>[]
        ];
    }

    /**
     * @param $data
     * @param $userInfo
     * @return array
     * Description:取消订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 17:06
     */
    public function cancelOrder($data,$userInfo){
        if(empty($data['order_id'])) {
            return [
                'status' => -10000,
                'msg' => '关闭订单失败！',
                'data' => []
            ];
        }
        $orderId=$data['order_id'];
        if(!$orderInfo=$this->orderModel
            ->field('id,order_status')
            ->where('id',$orderId)->find()){
            return [
                'status'=>-10000,
                'msg'=>'未找到订单信息！',
                'data'=>[]
            ];
        }
        if($orderInfo['order_status']!=1){
            return [
                'status'=>-10000,
                'msg'=>'订单状态有误！',
                'data'=>[]
            ];
        }
        $nowTime=time();
        Db::startTrans();
        try {
            $saveData=[
                'order_status'=>0,
                'update_time'=>$nowTime
            ];
            if(!$this->orderModel
                ->where('id',$orderId)->update($saveData)){
                Db::rollback();
                return [
                    'status'=>-10000,
                    'msg'=>'关闭订单失败！',
                    'data'=>[]
                ];
            }
            if(!$this->productOrderModel
                ->where('order_id',$orderId)
                ->update($saveData)){
                Db::rollback();
                return [
                    'status'=>-10000,
                    'msg'=>'关闭订单失败！',
                    'data'=>[]
                ];
            }
            Db::commit();
            \think\facade\Event::trigger('OrderManageLog',
                ['type' => 4, 'user' => $userInfo,'order_info' => $orderInfo]);
            return [
                'status'=>1,
                'msg'=>'关闭订单成功！',
                'data'=>[]
            ];
        }catch (\Exception $e){
            Db::rollback();
            return [
                'status'=>-10000,
                'msg'=>'关闭订单失败！',
                'data'=>[]
            ];
        }
    }

    /**
     * @param $data
     * @param $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:确认订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 17:52
     */
    public function confirmOrder($data,$userInfo){
        if(empty($data['order_id'])) {
            return [
                'status' => -10000,
                'msg' => '确认收货失败！',
                'data' => []
            ];
        }
        $orderId=$data['order_id'];
        if(!$orderInfo=$this->orderModel
            ->field('id,order_status')
            ->where('id',$orderId)->find()){
            return [
                'status'=>-10000,
                'msg'=>'未找到订单信息！',
                'data'=>[]
            ];
        }
        if($orderInfo['order_status']!=3){
            return [
                'status'=>-10000,
                'msg'=>'订单状态有误！',
                'data'=>[]
            ];
        }
        $nowTime=time();
        Db::startTrans();
        try {
            $saveData=[
                'order_status'=>4,
                'update_time'=>$nowTime
            ];
            if(!$this->orderModel
                ->where('id',$orderId)->update($saveData)){
                Db::rollback();
                return [
                    'status'=>-10000,
                    'msg'=>'确认收货失败！',
                    'data'=>[]
                ];
            }
            if(!$this->productOrderModel
                ->where('order_id',$orderId)
                ->update($saveData)){
                Db::rollback();
                return [
                    'status'=>-10000,
                    'msg'=>'确认收货失败！',
                    'data'=>[]
                ];
            }
            Db::commit();
            \think\facade\Event::trigger('OrderManageLog',
                ['type' => 5, 'user' => $userInfo,'order_info' => $orderInfo]);
            return [
                'status'=>1,
                'msg'=>'确认收货成功！',
                'data'=>[]
            ];
        }catch (\Exception $e){
            Db::rollback();
            return [
                'status'=>-10000,
                'msg'=>'确认收货失败！',
                'data'=>[]
            ];
        }
    }
}