<?php
/**
 * Created by xtshop
 * Class Order
 * Description:订单后台管理
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-26 11:11
 */
namespace app\admin\controller;

class Order extends AuthBase
{
    protected $orderRepository;

    public function initialize()
    {
        parent::initialize();
        $this->orderRepository
            = app('app\common\repository\OrderRepository');
    }

    /**
     * @return string
     * @throws \Exception
     * Description:订单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 22:42
     */
    public function orderList()
    {
        $pageView = input('get.page',1,'int');
        $listRow = input('get.list_row',config('paginate.list_rows'),'int');
        $where =$this->request->except(['page','list_row'],'get');
        $result =$this->orderRepository->getList($where,$pageView,$listRow);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:获取订单详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 16:02
     */
    public function info(){
        $data =$this->postData;
        $result =$this->orderRepository->getDetailInfo($data);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:修改收货人信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 9:37
     */
    public function receiverInfo(){
        $data =$this->postData;
        $userRes=$this->getUser();
        $userInfo=$userRes['data'];
        $result =$this->orderRepository->receiverInfo($data,$userInfo);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:发货
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 10:52
     */
    public function delivery(){
        $data =$this->postData;
        $userRes=$this->getUser();
        $userInfo=$userRes['data'];
        $result =$this->orderRepository->delivery($data,$userInfo);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:订单备注
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 15:21
     */
    public function remark(){
        $data =$this->postData;
        $userRes=$this->getUser();
        $userInfo=$userRes['data'];
        $result =$this->orderRepository->remark($data,$userInfo);
        return show($result['status'],$result['msg'],$result['data']);
    }
}