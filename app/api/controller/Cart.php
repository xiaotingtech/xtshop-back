<?php
/**
 * Created by xtshop
 * Class Cart
 * Description:购物车接口控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 10:49
 */
namespace app\api\controller;

class Cart extends Base
{
    protected $cartRepository;

    public function initialize()
    {
        parent::initialize();

        $this->cartRepository = app('app\common\repository\CartRepository');
    }

    /**
     * @return \think\response\Json
     * Description:添加购物车
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 14:58
     */
    public function addCart(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result = $this->cartRepository->addCart($data,$userInfo);
        return api_res($result['status'], $result['msg']);
    }

    /**
     * @return \think\response\Json
     * Description:减少购物车商品数量
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:13
     */
    public function decCart(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result = $this->cartRepository->decCart($data,$userInfo);
        return api_res($result['status'], $result['msg']);
    }

    /**
     * @return \think\response\Json
     * Description:删除购物车商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:13
     */
    public function deleteCart(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result = $this->cartRepository->delCart($data,$userInfo);
        return api_res($result['status'], $result['msg']);
    }

    /**
     * @return \think\response\Json
     * Description:清空购物车
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:14
     */
    public function clearCart(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $result = $this->cartRepository->clearCart($userInfo);
        return api_res($result['status'], $result['msg']);
    }

    /**
     * @return \think\response\Json
     * Description:获取购物车列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 11:25
     */
    public function listCart(){
        $where=[];
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        if(!empty($userInfo)){
            $where[]=['uid','=',$userInfo['id']];
        }
        $result = $this->cartRepository->getList($where);
        return api_res($result['status'], $result['msg'], $result['data']);
    }
}