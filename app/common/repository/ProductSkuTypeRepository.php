<?php
namespace app\common\repository;

use app\common\model\ProductSkuType;
class ProductSkuTypeRepository extends BaseRepository
{
    //ProductSkuType验证器
    protected $ProductSkuTypeValidate;
    //ProductSkuType的model
    protected $ProductSkuTypeModel;

    public function __construct()
    {
        $this->ProductSkuTypeValidate = sunnier_validate('app\common\validate\ProductSkuType');
        $this->ProductSkuTypeModel = new ProductSkuType();
    }

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取SKU名称列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 11:37
     */
    public function getList($where,$page=1,$listRow=10){
        $count=$this->ProductSkuTypeModel->where($where)
            ->where('status',1)->count();
        if($count>0) {
            $list = $this->ProductSkuTypeModel
                ->field('id,type,hand_add_status,name,cate_id,select_type,input_type,sort_num')
                ->where($where)
                ->where('status',1)
                ->with('values')
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                //将values按照,分割组合
                $list=$list->toArray();
                foreach ($list as $lk=>$lv){
                    $lv['input_list_id']=implode(',',array_column($lv['values'],'id'));
                    $lv['input_list']=implode(',',array_column($lv['values'],'name'));
                    unset($lv['values']);
                    $list[$lk]=$lv;
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
    /**
     * @param $data
     * @return array
     * Description:保存
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 9:36
     */
    public function save($data){
        if (!$this->ProductSkuTypeValidate->scene('save')->check($data)) {
            return ['status'=>0, 'msg'=>$this->ProductSkuTypeValidate->getError()];
        }
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if(!$this->ProductSkuTypeModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if(!$this->ProductSkuTypeModel->save($data)){
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