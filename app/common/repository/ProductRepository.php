<?php
/**
 * Created by xtshop
 * Class TeacherRepository
 * Description:商品数据处理类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 17:31
 */
namespace app\common\repository;

use app\common\model\Product;
use app\common\model\ProductDetail;
use app\common\model\SkuProduct;
use app\common\model\ProductAttribute;
use app\common\model\ProductSkuFilter;
use app\common\model\ProductSkuRelation;
use think\facade\Db;
use think\facade\Event;
use app\common\model\UserProductCollect;
use app\common\model\ProductBuyLog;
use app\common\model\ProductOrder;
class ProductRepository extends BaseRepository
{
    //Product验证器
    protected $ProductValidate;
    //Product的model
    protected $productModel;

    public function __construct()
    {
        $this->ProductValidate = sunnier_validate('app\common\validate\Product');
        $this->productModel = new Product();
    }

    /**
     * @param $data
     * @param $user
     * @return array
     * Description:保存数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 17:16
     */
    public function save($data,$user){
        //处理数据
        $ProductLogic=app('app\common\logic\Product');
        $logicResult=$ProductLogic->saveData($data,$user);
        if($logicResult['status']!=1){
            return ['status'=>0,'msg'=>$logicResult['msg']];
        }
        $productData=$logicResult['data']['product_data'];
        if(!$this->ProductValidate->check($productData)){
            return ['status'=>0,'msg'=>$this->ProductValidate->getError()];
        }
        $productDetailData=$logicResult['data']['product_detail_data'];
        $productSkuData=$logicResult['data']['product_sku_data'];
        $productAttrData=$logicResult['data']['product_attr_data'];
        Db::startTrans();
        try {
            if (!empty($productData['id'])) {
                if($this->productModel->update($productData)===false){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'修改商品信息失败！'];
                }
                $productId=$productData['id'];
                //SKU数据
                $skuTypeIds=[];
                $leftProductIds=[];
                $skuTypeValueIds=[];
                $productSkuFilterData=[];
                $productSkuRelationData=[];
                if(!empty($productSkuData)){
                    foreach ($productSkuData as $psk=>$psv)
                    {
                        //将sku_property拆分
                        $skuPropertyVal=$psv['sku_property'];
                        $skuPropertyValArr=explode(',',$skuPropertyVal);
                        foreach ($skuPropertyValArr as $skuIdKey=>$skuIdsStr){
                            $skuIdsArr=explode(':',$skuIdsStr);
                            $skuTypeIds[]=$skuIdsArr[0];
                            $skuTypeValueIds[]=$skuIdsArr[1];
                        }
                        if(!empty($psv['id'])) {
                            $leftProductIds[] = $psv['id'];
                        }
                        //如果存在时间字段去掉
                        if(isset($psv['create_time'])){
                            unset($psv['create_time']);
                        }
                        if(isset($psv['update_time'])){
                            unset($psv['update_time']);
                        }
                        $psv['product_id']=$productId;
                        $productSkuData[$psk]=$psv;
                    }
                    $productSkuFilterData[]=[
                        'product_id'=>$productId,
                        'sku_ids'=>implode(',',$skuTypeIds),
                        'sku_value_ids'=>implode(',',$skuTypeValueIds)
                    ];
                    foreach($skuTypeIds as $skuIdValKey=>$skuIdVal){
                        $productSkuRelationData[]=[
                            'product_id'=>$productId,
                            'sku_type_id'=>$skuIdVal,
                            'sku_value_id'=>$skuTypeValueIds[$skuIdValKey],
                        ];
                    }
                    $skuProductModel=new SkuProduct();
                    //先查询已有的SKU商品
                    $exitSkuProductIds=[];
                    $skuExitProductData=$skuProductModel->field('id')
                        ->where('product_id',$productId)->select();
                    if(!$skuExitProductData->isEmpty()){
                        foreach ($skuExitProductData as $skuExitProductDataVal) {
                            $exitSkuProductIds[] =$skuExitProductDataVal['id'];
                        }
                    }
                    $needDelIds=array_values(array_diff($exitSkuProductIds,$leftProductIds));
                    if(!empty($needDelIds)) {
                        if (!$skuProductModel->destroy($needDelIds)) {
                            Db::rollBack();
                            return ['status' => 0, 'msg' => '删除子商品表失败！'];
                        }
                    }
                    if(!$skuProductModel->saveAll($productSkuData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'新增子商品信息失败！'];
                    }
                }
                //商品参数
                if(!empty($productAttrData)){
                    $leftProductAttrIds=[];
                    foreach ($productAttrData as $pak=>$pav)
                    {
                        if(!empty($pv['id'])) {
                            $leftProductAttrIds[] = $pav['id'];
                        }
                        $pav['product_id']=$productId;
                        $productAttrData[$pak]=$pav;
                    }
                    $productAttributeModel=new ProductAttribute();
                    //先查询已有的商品参数
                    $exitProductAttrIds=[];
                    $productAttributeData=$productAttributeModel->field('id')
                        ->where('product_id',$productId)->select();
                    if(!$productAttributeData->isEmpty()){
                        foreach ($productAttributeData as $exitProductAttributeVal) {
                            $exitProductAttrIds[] =$exitProductAttributeVal['id'];
                        }
                    }
                    $needDelAttrIds=array_diff($exitProductAttrIds,$leftProductAttrIds);
                    if(!empty($needDelAttrIds)) {
                        if (!$productAttributeModel->destroy($needDelAttrIds)) {
                            Db::rollBack();
                            return ['status' => 0, 'msg' => '删除商品属性表失败！'];
                        }
                    }
                    if(!$productAttributeModel->saveAll($productAttrData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'修改商品参数信息失败！'];
                    }
                }
                $productSkuFilterModel=new ProductSkuFilter();
                if($productSkuFilterModel->where('product_id',$productId)->delete()===false){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'删除子商品子表filter失败！'];
                }
                if(!empty($productSkuFilterData)){
                    if(!$productSkuFilterModel->insertAll($productSkuFilterData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'修改子商品信息失败！'];
                    }
                }
                $productSkuRelationModel=new ProductSkuRelation();
                if($productSkuRelationModel->where('product_id',$productId)->delete()===false){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'删除子商品子表relation失败！'];
                }
                if(!empty($productSkuRelationData)){
                    if(!$productSkuRelationModel->insertAll($productSkuRelationData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'修改子商品信息失败！'];
                    }
                }
                $productDetailModel=new ProductDetail();
                if($productDetailModel->update($productDetailData)===false){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'修改商品详情信息失败！'];
                }
            } else {
                $nowTime=time();
                $productData['create_time']=$nowTime;
                if(!$productId=$this->productModel->insertGetId($productData)){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'保存商品信息失败！'];
                }
                $productDetailData['product_id']=$productId;
                //SKU数据
                $skuTypeIds=[];
                $skuTypeValueIds=[];
                $productSkuFilterData=[];
                $productSkuRelationData=[];
                if(!empty($productSkuData)){
                    foreach ($productSkuData as $psk=>$psv)
                    {
                        //将sku_property拆分
                        $skuPropertyVal=$psv['sku_property'];
                        $skuPropertyValArr=explode(',',$skuPropertyVal);
                        foreach ($skuPropertyValArr as $skuIdKey=>$skuIdsStr){
                            $skuIdsArr=explode(':',$skuIdsStr);
                            $skuTypeIds[]=$skuIdsArr[0];
                            $skuTypeValueIds[]=$skuIdsArr[1];
                        }
                        //如果存在时间字段去掉
                        if(isset($psv['create_time'])){
                            unset($psv['create_time']);
                        }
                        if(isset($psv['update_time'])){
                            unset($psv['update_time']);
                        }
                        $psv['product_id']=$productId;
                        $productSkuData[$psk]=$psv;
                    }
                    $productSkuFilterData[]=[
                        'product_id'=>$productId,
                        'sku_ids'=>implode(',',$skuTypeIds),
                        'sku_value_ids'=>implode(',',$skuTypeValueIds)
                    ];
                    foreach($skuTypeIds as $skuIdValKey=>$skuIdVal){
                        $productSkuRelationData[]=[
                            'product_id'=>$productId,
                            'sku_type_id'=>$skuIdVal,
                            'sku_value_id'=>$skuTypeValueIds[$skuIdValKey],
                        ];
                    }
                    $skuProductModel=new SkuProduct();
                    if(!$skuProductModel->insertAll($productSkuData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'新增子商品信息失败！'];
                    }
                }
                if(!empty($productAttrData)){
                    foreach ($productAttrData as $pak=>$pav)
                    {
                        $pav['product_id']=$productId;
                        $productAttrData[$pak]=$pav;
                    }
                    $productAttrModel=new ProductAttribute();
                    if(!$productAttrModel->insertAll($productAttrData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'新增商品参数信息失败！'];
                    }
                }
                if(!empty($productSkuFilterData)){
                    $productSkuFilterModel=new ProductSkuFilter();
                    if(!$productSkuFilterModel->insertAll($productSkuFilterData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'修改子商品信息失败！'];
                    }
                }
                if(!empty($productSkuRelationData)){
                    $productSkuRelationModel=new ProductSkuRelation();
                    if(!$productSkuRelationModel->insertAll($productSkuRelationData)){
                        Db::rollBack();
                        return ['status'=>0,'msg'=>'新增子商品信息失败！'];
                    }
                }
                $productDetailModel=new ProductDetail();
                if(!$productDetailModel->save($productDetailData)){
                    Db::rollBack();
                    return ['status'=>0,'msg'=>'保存商品详情信息失败！'];
                }
            }
            Db::commit();
            return ['status'=>1,'msg'=>'保存成功！'];
        }catch (\Exception $e){
            Db::rollBack();
            return ['status'=>0,'msg'=>$e->getMessage().$e->getLine().$e->getFile()];
        }
    }

    /**
     * @param $productSkuData
     * @return array
     * @throws \Exception
     * Description:修改SKU商品信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-28 10:52
     */
    public function updateSkuInfo($productSkuData){
        foreach ($productSkuData as $psk=>$psv)
        {
            //如果存在时间字段去掉
            if(isset($psv['create_time'])){
                unset($psv['create_time']);
            }
            if(isset($psv['update_time'])){
                unset($psv['update_time']);
            }
            $productSkuData[$psk]=$psv;
        }
        $skuProductModel=new SkuProduct();
        if(!$skuProductModel->saveAll($productSkuData)){
            return ['status'=>0,'msg'=>'新增子商品信息失败！'];
        }
        return ['status'=>1,'msg'=>'保存成功！'];
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 23:00
     */
    public function getList(){
        $products=$this->productModel
            ->field('id,img,title,price,sale_num')
            ->where('publish_status',1)->order('sort_num DESC')
            ->limit(5)->select();
        if(!$products->isEmpty()){
            $productData=$products->toArray();
            $ProductIds=[];
            $seasonType=config('Product.season_type');
            foreach ($productData as $bk=>$bv){
                $ProductIds[]=$bv['id'];
                $bv['img']=get_real_url($bv['img']);
                $productData[$bk]=$bv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$productData,
            ];
        }else{
            return [
                'status'=>0,
                'msg'=>'没有数据！',
                'data'=>[],
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
     * Description:商品详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 17:34
     */
    public function detail($data,$userInfo=[]){
        if(empty($data['id'])){
            return [
                'status'=>-10000,
                'msg'=>'ID没传'
            ];
        }
        $ProductId=$data['id'];
        $productData=$this->productModel
            ->where('id',$ProductId)
            ->where('publish_status',1)
            ->with(['detail','sku_stock_list'])
            ->find();
        if(empty($productData)){
            return [
                'status'=>-10000,
                'msg'=>'未找到商品信息'
            ];
        }
        //将信息加上完整图片链接
        $productData['img']=deal_pics_url($productData['img']);
        $productData['album_pic']=deal_pics_url($productData['album_pic']);
        Event::trigger('ProductDetail', ['user'=>$userInfo,'product'=>$productData]);
        $productData['is_collect']=0;
        $productData['detail_mobile_html']=html_entity_decode($productData['detail_mobile_html']);
        //根据sku数据找到对应的SKU的属性和值分割
        $skuPropertyIdDataArr=[];
        $skuPropertyValDataArr=[];
        $skuProduct=$productData['sku_stock_list'];
        foreach ($skuProduct as $skuData){
            $skuPropertyIdDataVal=$skuData['sku_property'];
            $skuPropertyIdDataValArr=explode(',',$skuPropertyIdDataVal);
            $skuPropertyValDataVal=$skuData['property_value'];
            $skuPropertyValDataValArr=explode(',',$skuPropertyValDataVal);
            foreach ($skuPropertyIdDataValArr as $sk=>$sv){
                $svArr=explode(':',$sv);
                $svValVal=$skuPropertyValDataValArr[$sk];
                $svValArr=explode(':',$svValVal);
                $skuPropertyIdDataArr[$svArr[0]]=[
                    'id'=>$svArr[0],
                    'name'=>$svValArr[0]
                ];
                $skuPropertyValDataArr[$svArr[1]]=[
                    'id'=>$svArr[1],
                    'pid'=>$svArr[0],
                    'name'=>$svValArr[1]
                ];
            }
        }
        $skuPropertyValDataArrPidArr=array_column($skuPropertyValDataArr,'pid');
        array_multisort($skuPropertyValDataArrPidArr,SORT_ASC,$skuPropertyValDataArr);
        $productData['sku_property']=array_values($skuPropertyIdDataArr);
        $productData['sku_value']=array_values($skuPropertyValDataArr);
        if(!empty($userInfo)) {
            //如果存在用户查询是否存在收藏，如果有则收藏字段为1
            $ProductCollectModel=new UserProductCollect();
            $collectWhere=[
                'uid'=>$userInfo['id'],
                'product_id'=>$productData['id']
            ];
            if($collectDataRes=$ProductCollectModel->field('id,status')
                ->where($collectWhere)->find()){
                if($collectDataRes['status']==1) {
                    $productData['is_collect'] = 1;
                }
            }
        }
        return [
            'status'=>1,
            'msg'=>'获取成功',
            'data'=>$productData
        ];
    }

    /**
     * @return array
     * Description:获取商品分类
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 15:05
     */
    public function getCategory(){
        $ProductType=config('Product.Product_type');
        $resultData=[];
        foreach ($ProductType as $ck=>$cv){
            $resultData[]=[
                'cate_id'=>$ck,
                'title'=>$cv
            ];
        }
        return [
            'status'=>1,
            'msg'=>'获取成功',
            'data'=>$resultData
        ];
    }

    /**
     * @param array $data
     * @param int $page
     * @param int $listRow
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:根据分类获取商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 16:04
     */
    public function getCategoryList($data=[],$page=1,$listRow=10,$order='sort_num DESC,id DESC'){
        $where=[];
        if(!empty($data['cate_id'])){
            $where['product_category_id']=intval($data['cate_id']);
        }
        $allCount=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)->where('publish_status',1)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'没有数据！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        $products=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)
            ->where('publish_status',1)->order($order)
            ->page($page,$listRow)->select();
        if(!$products->isEmpty()) {
            $productData = $products->toArray();
            foreach ($productData as $ck=>$cv){
                $cv['img']=get_real_url($cv['img']);
                $productData[$ck]=$cv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>$productData,
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
    }

    /**
     * @param array $data
     * @param $page
     * @param int $listRow
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取专题的商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 15:33
     */
    public function getSubjectList($data=[],$page=1,$listRow=10,$order='sort_num DESC,id DESC'){
        $where=[];
        if(!empty($data['subject_id'])){
            $where['subject_id']=intval($data['subject_id']);
        }
        $allCount=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)->where('publish_status',1)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'没有数据！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        $products=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)
            ->where('publish_status',1)->order($order)
            ->page($page,$listRow)->select();
        if(!$products->isEmpty()) {
            $productData = $products->toArray();
            foreach ($productData as $ck=>$cv){
                $cv['img']=get_real_url($cv['img']);
                $productData[$ck]=$cv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>$productData,
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }else{
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
    }

    /**
     * @param array $data
     * @param int $page
     * @param int $listRow
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取推荐专题商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 15:43
     */
    public function getSubjectRecommendList($data=[],$page=1,$listRow=10,$order='sort_num DESC,id DESC'){
        $where=[];
        if(!empty($data['subject_id'])){
            $where['subject_id']=intval($data['subject_id']);
        }
        $products=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)
            ->where('publish_status',1)->order($order)
            ->page($page,$listRow)->select();
        if(!$products->isEmpty()) {
            $productData = $products->toArray();
            foreach ($productData as $ck=>$cv){
                $cv['img']=get_real_url($cv['img']);
                $productData[$ck]=$cv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$productData
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param array $data
     * @param int $page
     * @param int $listRow
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:首页推荐商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 16:07
     */
    public function getHomeRecommendList($data=[],$page=1,$listRow=10,$order='sort_num DESC,id DESC'){
        $where=[];
        $products=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)
            ->where('publish_status',1)->order($order)
            ->page($page,$listRow)->select();
        if(!$products->isEmpty()) {
            $productData = $products->toArray();
            foreach ($productData as $ck=>$cv){
                $cv['img']=get_real_url($cv['img']);
                $productData[$ck]=$cv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$productData
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param array $data
     * @param int $page
     * @param int $listRow
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:推荐列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 16:18
     */
    public function getRecommendList($data=[],$page=1,$listRow=10,$order='sort_num DESC,id DESC'){
        $where=[];
        $allCount=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)->where('publish_status',1)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'没有数据！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        $products=$this->productModel->field('id,title,img,price,sale_num')
            ->where($where)
            ->where('publish_status',1)->order($order)
            ->page($page,$listRow)->select();
        if(!$products->isEmpty()) {
            $productData = $products->toArray();
            foreach ($productData as $ck=>$cv){
                $cv['img']=get_real_url($cv['img']);
                $productData[$ck]=$cv;
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>$productData,
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
    }

    /**
     * @param $data
     * @return array
     * Description:保存购买日志数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-21 22:35
     */
    public function saveData($data){
        try {
            if(empty($data)){
                return [
                    'status'=>0,
                    'msg'=>'没有数据！'
                ];
            }
            $orderIds=[];
            foreach ($data as $val){
                $orderIds[]=$val['order_id'];
            }
            if(empty($orderIds)){
                return [
                    'status'=>0,
                    'msg'=>'没有数据！'
                ];
            }
            //查询出对应的子商品ID
            $productOrderModel=new ProductOrder();
            $productOrderData=$productOrderModel->field('id,sku_product_id')
                ->where('order_id','IN',$orderIds)->select();
            if($productOrderData->isEmpty()){
                return [
                    'status'=>0,
                    'msg'=>'没有数据！'
                ];
            }
            $productIds=[];
            foreach ($productOrderData as $val){
                $productIds[]=$val['sku_product_id'];
            }
            if(!empty($productIds)) {
                $arrayCountIds=array_count_values($productIds);
                $arrayCountValues=array_unique(array_values($arrayCountIds));
                $needSaveNum=[];
                foreach ($arrayCountValues as $count){
                    foreach ($arrayCountIds as $id=>$countVal){
                        if($count==$countVal){
                            $needSaveNum[$count][]=$id;
                        }
                    }
                }
                foreach ($needSaveNum as $num=>$saveIds){
                    if(!Db::table(env('database.prefix','xt_').'sku_product')
                        ->where('id', 'IN', $saveIds)
                        ->inc('sale_num',intval($num))->update()){
                        return [
                            'status'=>0,
                            'msg'=>'更新数据失败！'
                        ];
                    }
                }
            }
            $ProductBuyLog=new ProductBuyLog();
            if ($ProductBuyLog->insertAll($data)) {
                return [
                    'status' => 1,
                    'msg' => '保存成功'
                ];
            } else {
                return [
                    'status' => 0,
                    'msg' => '保存失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => '保存失败' . $e->getMessage()
            ];
        }
    }

    /**
     * @param $where
     * @param $page
     * @param $listRow
     * @return array
     * Description:后台商品列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 11:03
     */
    public function getBackList($where,$page,$listRow){
        $count=$this->productModel->where($where)
            ->where('delete_status',0)->count();
        if($count>0) {
            $list = $this->productModel
                ->field('id,title,delete_status,new_status,publish_status,
                recommend_status,verify_status,product_sn,img,price,sort_num,sale_num,create_time')
                ->where($where)
                ->where('delete_status',0)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                $list=$list->toArray();
                foreach ($list as $lk=>$lv)
                {
                    if(!empty($lv['img'])){
                        $lv['img']=get_real_url($lv['img']);
                        $list[$lk]=$lv;
                    }
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => [
                        'list' => $list,
                        'page' => $page,
                        'list_row' => $listRow,
                        'total' => $count,
                        'totalPage' => ceil($count / $listRow)
                    ],
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[
                        'list'=>[],
                        'page'=>$page,
                        'list_row'=>$listRow,
                        'total'=>$count,
                        'totalPage'=>ceil($count/$listRow)
                    ],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'page'=>$page,
                    'list_row'=>$listRow,
                    'total'=>$count,
                    'totalPage'=>ceil($count/$listRow)
                ],
            ];
        }
    }
}