<?php
namespace app\admin\controller;

use app\common\model\ProductSkuType as ProductSkuTypeModel;
use app\common\model\ProductSkuTypeValue as ProductSkuTypeValueModel;
use think\facade\Request;
class ProductSkuTypeValue extends AuthBase
{
    protected $productSkuTypeModel;

    protected $productSkuTypeValueModel;

    public function initialize()
    {
        parent::initialize();
        $this->productSkuTypeModel = new ProductSkuTypeModel();
        $this->productSkuTypeValueModel = new ProductSkuTypeValueModel();
    }

    /**
     * @return string
     * @throws \Exception
     * Description:sku类型列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 9:27
     */
    public function valueList(){
        $page = input('get.page',0,'int');
        $list_row = input('get.list_row',config('paginate.list_rows'),'int');
        $cateId=input('get.cid',0,'int');
        $skuId=input('get.sid',0,'int');
        $where='';
        if(!empty($cateId)){
            $where="cate_id = ".$cateId;
        }
        if(!empty($skuId)){
            $where="sku_type_id = ".$skuId;
        }
        $result=app('app\common\repository\ProductSkuTypeValueRepository')
            ->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:添加修改商品sku的值
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 17:30
     */
    public function valueAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productSkuTypeValueRepository=app('app\common\repository\ProductSkuTypeValueRepository');
            $result=$productSkuTypeValueRepository->save($data,$this->user);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            return show(0,'修改失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:SKU值删除
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 17:31
     */
    public function valueDel(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        if($this->productSkuTypeValueModel->destroy($ids)){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }
}