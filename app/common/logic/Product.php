<?php
/**
 * Created by xtshop
 * Class Curse
 * Description:商品数据逻辑处理类
 * @package app\common\logic
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 16:22
 */
namespace app\common\logic;

class Product extends BaseLogic
{
    /**
     * @param $data
     * @param array $user
     * @return array
     * Description:处理需要保存的数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-08-09 11:55
     */
    public function saveData($data,$user=[]){
        try {
            $uid=0;
            if(!empty($user)){
                $uid=$user['id'];
            }
            if(!empty($data['id'])){
                $productData=[
                    'id'=>$data['id'],
                    'uid'=>$uid,
                    'title'=>$data['title'],
                    'sub_title'=>$data['sub_title'],
                    'description'=>$data['description'],
                    'category_parent_id'=>$data['category_parent_id'],
                    'product_category_id'=>$data['product_category_id'],
                    'product_attribute_category_id'=>$data['product_attribute_category_id'],
                    'subject_id'=>$data['subject_id'],
                    'img'=>filter_pics_url($data['img']),
                    'product_sn'=>$data['product_sn'],
                    'delete_status'=>$data['delete_status'],
                    'publish_status'=>$data['publish_status'],
                    'new_status'=>$data['new_status'],
                    'sort_num'=>$data['sort_num'],
                    'sale_num'=>$data['sale_num'],
                    'price'=>$data['price'],
                    'original_price'=>$data['original_price'],
                    'stock'=>$data['stock'],
                    'unit'=>$data['unit'],
                    'low_stock'=>$data['low_stock'],
                    'weight'=>$data['weight'],
                    'keywords'=>$data['keywords'],
                    'album_pic'=>filter_pics_url($data['album_pic']),
                    'product_category_name'=>$data['product_category_name'],
                ];
            }else{
                $productData=[
                    'uid'=>$uid,
                    'title'=>$data['title'],
                    'sub_title'=>$data['sub_title'],
                    'description'=>$data['description'],
                    'category_parent_id'=>$data['category_parent_id'],
                    'product_category_id'=>$data['product_category_id'],
                    'product_attribute_category_id'=>$data['product_attribute_category_id'],
                    'subject_id'=>$data['subject_id'],
                    'img'=>filter_pics_url($data['img']),
                    'product_sn'=>$data['product_sn'],
                    'delete_status'=>$data['delete_status'],
                    'publish_status'=>$data['publish_status'],
                    'new_status'=>$data['new_status'],
                    'sort_num'=>$data['sort_num'],
                    'sale_num'=>$data['sale_num'],
                    'price'=>$data['price'],
                    'original_price'=>$data['original_price'],
                    'stock'=>$data['stock'],
                    'unit'=>$data['unit'],
                    'low_stock'=>$data['low_stock'],
                    'weight'=>$data['weight'],
                    'keywords'=>$data['keywords'],
                    'album_pic'=>filter_pics_url($data['album_pic']),
                    'product_category_name'=>$data['product_category_name'],
                ];
            }
            //先判断商品需要保存的SKU数据
            $productSkuData=[];
            if(!empty($data['sku_stock_list'])){
                $productSkuData=$data['sku_stock_list'];
                unset($data['sku_stock_list']);
            }else{
                return [
                    'status'=>0,
                    'msg'=>'必须选择商品规格'
                ];
            }
            //数据里的属性数据
            $productAttrData=[];
            if(!empty($data['product_attribute_value_list'])){
                $productAttrData=$data['product_attribute_value_list'];
                unset($data['product_attribute_value_list']);
            }else{
                return [
                    'status'=>0,
                    'msg'=>'必须选择商品参数'
                ];
            }
            //然后单独保存详情数据
            if(!empty($data['detail_id'])) {
                $detailHtmlContent=strip_tags($data['detail_html']);
                $productDetailData = [
                    'detail_id'=>$data['detail_id'],
                    'product_id'=>$data['id'],
                    'detail_title' => mb_substr($detailHtmlContent,0,50,'UTF-8'),
                    'detail_desc' =>mb_substr($detailHtmlContent,0,160,'UTF-8'),
                    'detail_html' => htmlentities($data['detail_html']),
                    'detail_mobile_html' => htmlentities($data['detail_mobile_html']),
                ];
                unset($data['detail_id']);
                unset($data['detail_title']);
                unset($data['detail_desc']);
                unset($data['detail_html']);
                unset($data['detail_mobile_html']);
            }else{
                $detailHtmlContent=strip_tags($data['detail_html']);
                $productDetailData = [
                    'detail_title' => mb_substr($detailHtmlContent,0,50,'UTF-8'),
                    'detail_desc' =>mb_substr($detailHtmlContent,0,160,'UTF-8'),
                    'detail_html' => htmlentities($data['detail_html']),
                    'detail_mobile_html' => htmlentities($data['detail_mobile_html']),
                ];
                unset($data['detail_html']);
                unset($data['detail_mobile_html']);
            }
            return [
                'status'=>1,
                'msg'=>'处理完成！',
                'data'=>[
                    'product_data'=>$productData,
                    'product_sku_data'=>$productSkuData,
                    'product_attr_data'=>$productAttrData,
                    'product_detail_data'=>$productDetailData,
                ]
            ];
        }catch (\Exception $e){
            return [
                'status'=>0,
                'msg'=>'处理数据出错！错误信息：'.$e->getMessage().'错误文件：'.$e->getFile().'，错误行号：'.$e->getLine()
            ];
        }
    }
}