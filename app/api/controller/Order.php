<?php
/**
 * Created by xtshop
 * Class Order
 * Description:订单控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 18:24
 */
namespace app\api\controller;

class Order extends Base
{
    protected $productOrderRepository;

    protected $orderRepository;

    public function initialize()
    {
        parent::initialize();

        $this->productOrderRepository=app('app\common\repository\ProductOrderRepository');

        $this->orderRepository=app('app\common\repository\OrderRepository');
    }

    /**
     * @return \think\response\Json
     * Description:获取订单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 18:31
     */
    public function getList(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $page=!empty($data['page'])?$data['page']:1;
        $listRow=!empty($data['list_row'])?$data['list_row']:config('extend.list_row');
        $result=$this->productOrderRepository->getList($data,$userInfo,$page,$listRow);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:订单详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 15:25
     */
    public function detail(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $postData=$this->postData;
        $result=$this->productOrderRepository->detail($postData,$userInfo);
        if($result['status']==1) {
            return api_res($result['status'], $result['msg'],$result['data']);
        }else{
            return api_res($result['status'], $result['msg']);
        }
    }

    /**
     * @return \think\response\Json
     * Description:订单数
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-19 13:05
     */
    public function orderNum(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $result=$this->productOrderRepository->orderNum($userInfo);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:确认订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 17:51
     */
    public function confirmOrder(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result=$this->orderRepository->confirmOrder($data,$userInfo);
        return api_res($result['status'],$result['msg'],$result['data']);
    }
    /**
     * @return \think\response\Json
     * Description:关闭订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 17:16
     */
    public function closeOrder(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result=$this->orderRepository->cancelOrder($data,$userInfo);
        return api_res($result['status'],$result['msg'],$result['data']);
    }
}