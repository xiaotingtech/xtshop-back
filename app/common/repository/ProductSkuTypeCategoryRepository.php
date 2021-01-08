<?php
/**
 * Created by xtshop
 * Class ProductSkuTypeCategoryRepository
 * Description:产品SKU的类型仓库
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-09-18 10:39
 */
namespace app\common\repository;

use app\common\model\ProductSkuTypeCategory;
class ProductSkuTypeCategoryRepository extends BaseRepository
{
    //ProductSkuTypeCategory验证器
    protected $productSkuTypeCategoryValidate;
    //ProductSkuType的model
    protected $productSkuTypeCategoryModel;

    public function __construct()
    {
        $this->productSkuTypeCategoryValidate = sunnier_validate('app\common\validate\ProductSkuTypeCategory');
        $this->productSkuTypeCategoryModel = new ProductSkuTypeCategory();
    }

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * Description:获取列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 11:14
     */
    public function getList($where,$page=1,$listRow=10){
        $count=$this->productSkuTypeCategoryModel->where($where)
            ->where('status',1)->count();
        if($count>0) {
            $list = $this->productSkuTypeCategoryModel
                ->field('id,name,sku_type_num,sku_type_value_num,sort_num')
                ->where($where)
                ->where('status',1)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
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

    /**
     * @param $data
     * @return array
     * Description:保存
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 10:40
     */
    public function save($data){
        if (!$this->productSkuTypeCategoryValidate->scene('save')->check($data)) {
            return ['status'=>0, 'msg'=>$this->productSkuTypeCategoryValidate->getError()];
        }
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if(!$this->productSkuTypeCategoryModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if(!$this->productSkuTypeCategoryModel->save($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }
        return [
            'status'=>1,
            'msg'=>'保存成功！'
        ];
    }
}