<?php
/**
 * Created by xtshop
 * Class Product
 * Description:商品管理
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 9:38
 */
namespace app\api\controller;

class Product extends Base
{
    protected $productRepository;

    public function initialize()
    {
        parent::initialize();

        $this->productRepository = app('app\common\repository\ProductRepository');
    }

    /**
     * @return \think\response\Json
     * Description:根据分类获取商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 9:38
     */
    public function getCategoryList(){
        $data=$this->postData;
        if(empty($data['order'])){
            $order='sort_num DESC,id DESC';
        }else{
            if($data['order']==1){
                $order='sale_num DESC,id DESC';
            }else if($data['order']==2){
                if($data['price_order']==1) {
                    $order = 'price ASC,id DESC';
                }else{
                    $order = 'price DESC,id DESC';
                }
            }else{
                $order='sort_num DESC,id DESC';
            }
        }
        $page=!empty($data['page'])?intval($data['page']):1;
        $listRow=!empty($data['list_row'])?intval($data['list_row']):10;
        $result=$this->productRepository->getCategoryList($data,$page,$listRow,$order);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:首页推荐商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 16:06
     */
    public function getHomeRecommendList(){
        $data=$this->postData;
        $order='sort_num DESC,id DESC';
        $page=!empty($data['page'])?intval($data['page']):1;
        $listRow=!empty($data['list_row'])?intval($data['list_row']):4;
        $result=$this->productRepository->getHomeRecommendList($data,$page,$listRow,$order);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:推荐列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 16:18
     */
    public function getRecommendList(){
        $data=$this->postData;
        if(empty($data['order'])){
            $order='sort_num DESC,id DESC';
        }else{
            if($data['order']==1){
                $order='sale_num DESC,id DESC';
            }else if($data['order']==2){
                if($data['price_order']==1) {
                    $order = 'price ASC,id DESC';
                }else{
                    $order = 'price DESC,id DESC';
                }
            }else{
                $order='sort_num DESC,id DESC';
            }
        }
        $page=!empty($data['page'])?intval($data['page']):1;
        $listRow=!empty($data['list_row'])?intval($data['list_row']):10;
        $result=$this->productRepository->getRecommendList($data,$page,$listRow,$order);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:专题列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 15:00
     */
    public function getSubjectList(){
        $data=$this->postData;
        if(empty($data['order'])){
            $order='sort_num DESC,id DESC';
        }else{
            if($data['order']==1){
                $order='sale_num DESC,id DESC';
            }else if($data['order']==2){
                if($data['price_order']==1) {
                    $order = 'price ASC,id DESC';
                }else{
                    $order = 'price DESC,id DESC';
                }
            }else{
                $order='sort_num DESC,id DESC';
            }
        }
        $page=!empty($data['page'])?intval($data['page']):1;
        $listRow=!empty($data['list_row'])?intval($data['list_row']):10;
        $result=$this->productRepository->getSubjectList($data,$page,$listRow,$order);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:获取推荐专题列表商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 15:44
     */
    public function getSubjectRecommendList(){
        $data=$this->postData;
        if(empty($data['order'])){
            $order='sort_num DESC,id DESC';
        }else{
            if($data['order']==1){
                $order='sale_num DESC,id DESC';
            }else if($data['order']==2){
                if($data['price_order']==1) {
                    $order = 'price ASC,id DESC';
                }else{
                    $order = 'price DESC,id DESC';
                }
            }else{
                $order='sort_num DESC,id DESC';
            }
        }
        $page=!empty($data['page'])?intval($data['page']):1;
        $listRow=!empty($data['list_row'])?intval($data['list_row']):6;
        $result=$this->productRepository->getSubjectRecommendList($data,$page,$listRow,$order);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:商品详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 9:38
     */
    public function detail(){
        $data=$this->postData;
        $userRes=$this->getUser();
        if($userRes['code']!=1){
            $userInfo=[];
        }else {
            $userInfo = $userRes['data'];
        }
        $result=$this->productRepository->detail($data,$userInfo);
        if($result['status']==1) {
            return api_res($result['status'], $result['msg'], $result['data']);
        }else{
            return api_res($result['status'], $result['msg']);
        }
    }
}