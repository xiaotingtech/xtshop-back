<?php
/**
 * Created by xtshop
 * Class CartRepository
 * Description:购物车仓库类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 10:47
 */
namespace app\common\repository;

use app\common\model\Cart;
use app\common\validate\Cart as CartValidate;
use think\facade\Db;

class CartRepository extends BaseRepository
{
    //cart的model
    protected $cartModel;

    protected $cartValidate;

    public function __construct()
    {
        $this->cartModel = new Cart();

        $this->cartValidate = new CartValidate();
    }

    /**
     * @param $data
     * @param array $userInfo
     * @return array
     * Description:添加购物车
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 15:32
     */
    public function addCart($data,$userInfo=[]){
        if(empty($userInfo)){
            return [
                'status'=>-10020,
                'msg'=>'请先登录'
            ];
        }
        $data['uid']=$userInfo['id'];
        if(empty($data['cart_id'])) {
            if (!$this->cartValidate->scene('save')->check($data)) {
                return ['status' => 0, 'msg' => $this->cartValidate->getError()];
            }
            $where = [
                'uid' => $data['uid'],
                'product_id' => $data['product_id'],
                'sku_product_id' => $data['sku_product_id'],
            ];
            if ($cartRes = $this->cartModel->field('id')->where($where)->find()) {
                if (!Db::table(env('database.prefix', 'xt_') . 'cart')
                    ->where('id', '=', $cartRes['id'])
                    ->inc('product_num', 1)->update()) {
                    return [
                        'status' => -10000,
                        'msg' => '加入购物车失败'
                    ];
                }
            } else {
                if (!empty($data['img'])) {
                    $data['img'] = filter_pics_url($data['img']);
                }
                if (!$this->cartModel
                    ->save($data)) {
                    return [
                        'status' => -10000,
                        'msg' => '加入购物车失败'
                    ];
                }
            }
        }else{
            if (!Db::table(env('database.prefix', 'xt_') . 'cart')
                ->where('id', '=', intval($data['cart_id']))
                ->inc('product_num', 1)->update()) {
                return [
                    'status' => -10000,
                    'msg' => '加入购物车失败'
                ];
            }
        }
        return [
           'status'=>1,
           'msg'=>'加入购物车成功！'
        ];
    }

    /**
     * @param $data
     * @param array $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:减少商品数量
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:07
     */
    public function decCart($data,$userInfo=[]){
        if(empty($userInfo)){
            return [
                'status'=>-10020,
                'msg'=>'请先登录'
            ];
        }
        $cartId=$data['cart_id'];
        $uid=$userInfo['id'];
        $where=[
            'uid'=>$uid,
            'id'=>$cartId
        ];
        if($cartRes=$this->cartModel->field('id,product_num')->where($where)->find()){
            if($cartRes['product_num']<=1){
                return [
                    'status'=>-10000,
                    'msg'=>'无法再减少数量了'
                ];
            }
            if(!Db::table(env('database.prefix','xt_').'cart')
                ->where('id', '=', $cartRes['id'])
                ->dec('product_num',1)->update()){
                return [
                    'status'=>-10000,
                    'msg'=>'加入购物车失败'
                ];
            }
        }else{
            return [
                'status'=>-10000,
                'msg'=>'未加入购物车商品'
            ];
        }
        return [
            'status'=>1,
            'msg'=>'减少购物车商品数量成功！'
        ];
    }

    /**
     * @param $data
     * @param array $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:删除购物车商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:10
     */
    public function delCart($data,$userInfo=[]){
        if(empty($userInfo)){
            return [
                'status'=>-10020,
                'msg'=>'请先登录'
            ];
        }
        $cartId=$data['cart_id'];
        $uid=$userInfo['id'];
        $where=[
            'uid'=>$uid,
            'id'=>$cartId
        ];
        if($cartRes=$this->cartModel->field('id,product_num')->where($where)->find()){
            if(!$this->cartModel->destroy($cartRes['id'])){
                return [
                    'status'=>-10000,
                    'msg'=>'从购物车删除失败！'
                ];
            }
        }else{
            return [
                'status'=>-10000,
                'msg'=>'未找到加入购物车商品'
            ];
        }
        return [
            'status'=>1,
            'msg'=>'删除购物车商品数量成功！'
        ];
    }

    /**
     * @param array $userInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:清空购物车
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:12
     */
    public function clearCart($userInfo=[]){
        if(empty($userInfo)){
            return [
                'status'=>-10020,
                'msg'=>'请先登录'
            ];
        }
        $uid=$userInfo['id'];
        $where=[
            'uid'=>$uid,
        ];
        if(!$this->cartModel->destroy($where)){
            return [
                'status'=>-10000,
                'msg'=>'清空购物车失败！'
            ];
        }
        return [
            'status'=>1,
            'msg'=>'清空购物车成功！'
        ];
    }
    /**
     * @param $where
     * @param string $sort
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取购物车信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 11:23
     */
    public function getList($where,$sort='id DESC'){
        $count=$this->cartModel->where($where)->count();
        if($count>0) {
            $list = $this->cartModel
                ->field('id,product_id,sku_product_id,product_name,
                img,sku_property,property_value,product_price,product_num,product_stock')
                ->where($where)
                ->order($sort)
                ->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                foreach ($listData as $lk=>$lv){
                    if(!empty($lv['img'])){
                        $listData[$lk]['img']=get_real_url($lv['img']);
                    }
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $listData,
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }
}