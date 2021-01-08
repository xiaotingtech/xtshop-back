<?php
namespace app\common\repository;

use app\common\model\ProductSkuTypeValue;
class ProductSkuTypeValueRepository extends BaseRepository
{
    //productSkuTypeValue验证器
    protected $productSkuTypeValueValidate;
    //productSkuTypeValue的model
    protected $productSkuTypeValueModel;

    public function __construct()
    {
        $this->productSkuTypeValueValidate = sunnier_validate('app\common\validate\ProductSkuTypeValue');
        $this->productSkuTypeValueModel = new ProductSkuTypeValue();
    }

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * Description:获取列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 16:35
     */
    public function getList($where,$page=1,$listRow=10){
        $count=$this->productSkuTypeValueModel->where($where)
            ->count();
        if($count>0) {
            $list = $this->productSkuTypeValueModel
                ->field('id,name,sku_type_id,cate_id,sort_num')
                ->where($where)
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
     * 2020-08-28 9:36
     */
    public function save($data){
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if (!$this->productSkuTypeValueValidate->scene('edit')->check($data)) {
                return ['status'=>0, 'msg'=>$this->productSkuTypeValueValidate->getError()];
            }
            if(!$this->productSkuTypeValueModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if (!$this->productSkuTypeValueValidate->scene('add')->check($data)) {
                return ['status'=>0, 'msg'=>$this->productSkuTypeValueValidate->getError()];
            }
            if(!$this->productSkuTypeValueModel->save($data)){
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