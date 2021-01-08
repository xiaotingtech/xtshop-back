<?php
/**
 * Created by xtshop
 * Class ExpressService
 * Description:快递查询服务类
 * @package app\common\service
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-11-26 15:22
 */
namespace app\common\service;

use think\App;
use \Liaosp\Express\Express;
use app\common\model\OrderDelivery;
class ExpressService extends BaseService
{
    //快递查询对象
    protected $expressObj;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->expressObj = new Express();
        $this->expressObj->setExpress('kuaidi100');
        $this->expressObj->setExpress('ickd');
    }

    /**
     * @param $data
     * @return array
     * Description:快递查询
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-11-26 14:54
     */
    public function query($data){
        try {
            if(empty($data['number'])){
                return [
                    'status'=>-10000,
                    'msg'=>'没有订单号！',
                    'data'=>[]
                ];
            }
            $number=strval($data['number']);
            $result = $this->expressObj->number($number);
            foreach ($result as $channel=>$cVal){
                if($cVal['status']==$this->expressObj::SUCCESS){
                    if($channel=='kuaidi100'){
                        $cValRes=$cVal['result'][0];
                        if($cValRes['status']==200&&$cValRes['error_code']==5) {
                            return [
                                'status' => 1,
                                'msg' => '获取成功！',
                                'data' => [
                                    'data'=>array_values($cValRes['data']),
                                    'logistics_company'=>$cValRes['logistics_company'],
                                    'logistics_bill_no'=>$cValRes['logistics_bill_no'],
                                ]
                            ];
                        }
                    }else{
                        $cValRes=$cVal['result'];
                        if($cValRes['error_code']==0) {
                            return [
                                'status' => 1,
                                'msg' => '获取成功！',
                                'data' => [
                                    'data'=>array_values($cValRes['data']),
                                    'logistics_company'=>$cValRes['logistics_company'],
                                    'logistics_bill_no'=>$cValRes['logistics_bill_no'],
                                ]
                            ];
                        }
                    }
                }
            }
            return [
                'status'=>-10000,
                'msg'=>'查询失败！',
                'data'=>[]
            ];
        }catch (\Exception $e){
            return [
                'status'=>-10000,
                'msg'=>'查询失败！'.$e->getMessage(),
                'data'=>[]
            ];
        }
    }

    /**
     * @param $data
     * @return array
     * Description:根据订单号查询物流信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-11-26 17:37
     */
    public function queryByOrderId($data){
        if(empty($data['order_id'])){
            return [
                'status'=>-10000,
                'msg'=>'没有订单号！',
                'data'=>[]
            ];
        }
        $deliveryModel=new OrderDelivery();
        $deliveryData=$deliveryModel
            ->field('delivery_company,delivery_sn')
            ->where('order_id',$data['order_id'])
            ->find();
        if(empty($deliveryData)){
            return [
                'status'=>-10000,
                'msg'=>'没有物流信息！',
                'data'=>[]
            ];
        }
        $queryData=[
            'number'=>$deliveryData['delivery_sn']
        ];
        return $this->query($queryData);
    }
}