<?php
/**
 * Created by xtshop
 * Class ProductSkuTypeCategory
 * Description:SKU类型分类控制器
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-11-05 10:59
 */
namespace app\admin\controller;

use app\common\model\ProductSkuTypeCategory as ProductSkuTypeCategoryModel;
use think\facade\Request;
class ProductSkuTypeCategory extends AuthBase
{
    protected $productSkuTypeCategoryModel;

    public function initialize()
    {
        parent::initialize();
        $this->productSkuTypeCategoryModel = new productSkuTypeCategoryModel();
    }

    /**
     * @return string
     * @throws \Exception
     * Description:sku类型列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 9:27
     */
    public function cateList(){
        $page = input('get.page',0,'int');
        $list_row = input('get.list_row',config('paginate.list_rows'),'int');
        $where='';
        $result=app('app\common\repository\ProductSkuTypeCategoryRepository')
            ->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:添加修改商品sku的类型
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 11:24
     */
    public function cateAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productSkuTypeRepository=app('app\common\repository\ProductSkuTypeCategoryRepository');
            $result=$productSkuTypeRepository->save($data,$this->user);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            return show(0, '新增节点失败');
        }
    }

    /**
     * @param $id
     * @return \think\response\Json
     * Description:SKU的类型删除
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 11:25
     */
    public function cateDel($id){
        $row = $this->productSkuTypeCategoryModel->update(['status'=>0,'id'=>$id]);
        if ($row) {
            return show(1, '删除成功');
        }else{
            return show(0, '删除失败');
        }
    }
}