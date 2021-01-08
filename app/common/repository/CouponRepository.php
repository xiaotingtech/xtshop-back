<?php
/**
 * Created by xtshop
 * Class CouponRepository
 * Description:优惠券处理类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-04 19:06
 */
namespace app\common\repository;

use app\common\model\Coupon;
class CouponRepository extends BaseRepository
{
    //coupon的model
    protected $couponModel;
    public function __construct()
    {
        $this->couponModel=new Coupon();
    }

    /**
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:判断是否是可用的券码
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 19:17
     */
    public function isValid($data){
        if(empty($data['code'])){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        $code=$data['code'];
        if(!is_coupon_code($code)){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        $couponData=$this->couponModel->field('id,price,price_rate,expire_time,status')
            ->where('code',$code)->find();
        if(empty($couponData)){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        if($couponData['expire_time']<time()){
            return [
                'status'=>-10000,
                'msg'=>'券码过期'
            ];
        }
        if($couponData['status']!=1){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        return [
            'status'=>1,
            'msg'=>'获取成功',
            'data'=>['price_rate'=>$couponData['price_rate']]
        ];
    }

    /**
     * @param $code
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:根据券码获取信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 19:15
     */
    public function getInfoByCode($code){
        if(!is_coupon_code($code)){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        $couponData=$this->couponModel->field('id,price,price_rate,Goods_id,expire_time,status,update_time')
            ->where('code',$code)->find();
        if(empty($couponData)){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        if($couponData['expire_time']<time()){
            return [
                'status'=>-10000,
                'msg'=>'券码过期'
            ];
        }
        if($couponData['status']!=1){
            return [
                'status'=>-10000,
                'msg'=>'无效券码'
            ];
        }
        return [
            'status'=>1,
            'msg'=>'获取成功',
            'data'=>$couponData
        ];
    }

    /**
     * @param $couponData
     * @return Coupon
     * Description:将优惠券状态改为使用中
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 20:07
     */
    public function dealCouponUse($couponData)
    {
        return $this->couponModel->where('id',$couponData['id'])
            ->where('update_time',strtotime($couponData['update_time']))
            ->update(['status'=>2,'update_time'=>time()]);
    }
}