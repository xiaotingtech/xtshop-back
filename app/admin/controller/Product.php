<?php
/**
 * Created by xtshop
 * Class Product
 * Description:商品控制器
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 11:05
 */
namespace app\admin\controller;

use app\common\model\Product as ProductModel;
use think\facade\Request;
class Product extends AuthBase
{
    protected $productModel;

    public function initialize()
    {
        parent::initialize();
        $this->productModel=new ProductModel();
    }

    /**
     * @return string
     * @throws \Exception
     * Description:商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 14:52
     */
    public function productList()
    {
        $pageView = input('get.page',1,'int');
        $listRow = input('get.list_row',config('paginate.list_rows'),'int');
        $where =[];
        //商品名称
        $title=input('get.keywords','','string');
        if(!empty($title)){
            $where[]=['title','like','%'.$title.'%'];
        }
        //货号
        $productSn=input('get.productSn','','string');
        if(!empty($productSn)){
            $where[]=['product_sn','like',$productSn.'%'];
        }
        //商品分类
        $productCategoryId=input('get.productCategoryId','0','int');
        if(!empty($productCategoryId)){
            $where[]=['product_category_id','=',$productCategoryId];
        }
        //上下架
        $publishStatus=input('get.publishStatus');
        if(isset($publishStatus)&&(!$publishStatus==null)){
            $where[]=['publish_status','=',intval($publishStatus)];
        }
        $result=app('app\common\repository\ProductRepository')->getBackList($where,$pageView,$listRow);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:商品新增修改
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-27 18:19
     */
    public function productAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productRepository=app('app\common\repository\ProductRepository');
            $result=$productRepository->save($data,$this->user);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            return show(0,'请求方式不正确！');
        }
    }

    /**
     * @param $id
     * @return \think\response\Json
     * Description:商品信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 16:35
     */
    public function info($id){
        //查询商品详情
        $productData=$this->productModel->where('id',$id)
            ->with(['detail','sku_stock_list','product_attribute_value_list'])->find();
        if(!empty($productData->detail_html)) {
            $productData->detail_html = html_entity_decode($productData->detail_html);
        }
        if(!empty($productData->detail_mobile_html)){
            $productData->detail_mobile_html = html_entity_decode($productData->detail_mobile_html);
        }
        if(!empty($productData->img)){
            $productData->img = deal_pics_url($productData->img);
        }
        if(!empty($productData->album_pic)){
            $productData->album_pic = deal_pics_url($productData->album_pic);
        }
        return show(1,'获取成功',$productData);
    }

    /**
     * @return \think\response\Json
     * Description:获取商品的SKU数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 10:41
     */
    public function productSku(){
        $id=input('get.product_id',0,'int');
        if(empty($id)){
            return show(0,'未获取到商品ID');
        }
        $productData=$this->productModel->where('id',$id)
            ->with('sku_stock_list')->find();
        return show(1,'获取成功',$productData->sku_stock_list);
    }

    /**
     * @return \think\response\Json
     * Description:修改商品的SKU信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 10:49
     */
    public function productSkuUpdate(){
        if(Request::isPost()){
            $data=input('post.');
            $productRepository=app('app\common\repository\ProductRepository');
            $result=$productRepository->updateSkuInfo($data);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            return show(0,'请求方式不正确！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:删除商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 9:53
     */
    public function deleteStatus(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $deleteStatus=input('post.deleteStatus',0,'int');
        if($this->productModel->where('id','IN',$ids)->update(['delete_status'=>$deleteStatus])){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:推荐商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 9:53
     */
    public function recommendStatus(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $recommendStatus=input('post.recommendStatus',0,'int');
        if($this->productModel->where('id','IN',$ids)->update(['recommend_status'=>$recommendStatus])){
            return show(1, '推荐状态修改成功！');
        }else{
            return show(0, '推荐状态修改失败！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:发布状态
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 9:55
     */
    public function publishStatus(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $publishStatus=input('post.publishStatus',0,'int');
        if($this->productModel->where('id','IN',$ids)->update(['publish_status'=>$publishStatus])){
            return show(1, '发布状态修改成功！');
        }else{
            return show(0, '发布状态修改失败！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:上新状态
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 9:55
     */
    public function newStatus(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $newStatus=input('post.newStatus',0,'int');
        if($this->productModel->where('id','IN',$ids)->update(['new_status'=>$newStatus])){
            return show(1, '上新状态修改成功！');
        }else{
            return show(0, '上新状态修改失败！');
        }
    }
}