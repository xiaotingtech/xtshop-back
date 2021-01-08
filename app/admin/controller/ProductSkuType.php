<?php
namespace app\admin\controller;

use app\common\model\ProductSkuType as ProductSkuTypeModel;
use app\common\model\ProductSkuTypeValue;
use think\facade\Request;
class ProductSkuType extends AuthBase
{
    protected $productSkuTypeModel;

    protected $productSkuTypeValueModel;

    public function initialize()
    {
        parent::initialize();
        $this->productSkuTypeModel = new ProductSkuTypeModel();
        $this->productSkuTypeValueModel = new ProductSkuTypeValue();
    }

    /**
     * @return string
     * @throws \Exception
     * Description:sku类型列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 9:27
     */
    public function typeList(){
        $page = input('get.page',0,'int');
        $list_row = input('get.list_row',config('paginate.list_rows'),'int');
        $cateId=input('get.cid',0,'int');
        $type=input('get.type',0,'int');
        $where=[];
        if(!empty($cateId)){
            $where['cate_id']=$cateId;
        }
        if(!empty($type)){
            $where['type']=$type;
        }else{
            $where['type']=0;
        }
        $result=app('app\common\repository\ProductSkuTypeRepository')
            ->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:添加修改商品sku的类型
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 17:57
     */
    public function typeAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productSkuTypeRepository=app('app\common\repository\ProductSkuTypeRepository');
            $result=$productSkuTypeRepository->save($data,$this->user);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            return show(1,'保存失败！');
        }
    }

    /**
     * Description:SKU属性删除
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 9:51
     */
    public function typeDel(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        if($this->productSkuTypeModel->where('id','IN',$ids)->update(['status'=>0])){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }

    /**
     * @return string
     * @throws \Exception
     * Description:SKU值列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 10:00
     */
    public function valueList(){
        $typeId = input('get.type_id',0,'int');
        $list_row = input('get.list_row');
        $list_row = isset($list_row) ? $list_row : config('paginate.list_rows');
        $list =$this->productSkuTypeValueModel->field('id,name,sort_num,create_time')
            ->paginate($list_row,false,['query'=>[
                'type_id'=>$typeId
            ]]);
        $this->assign('page',$list->render());
        $this->assign('currentPage', $list->currentPage());
        $this->assign('totalPage', $list->lastPage());
        $this->assign('list_row', $list->listRows());
        $this->assign('count', $list->total());
        $this->assign('list',$list);
        $this->assign('type_id',$typeId);
        return $this->fetch('valueList');
    }

    /**
     * @return \think\response\View|void
     * Description:SKU值添加
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 10:11
     */
    public function valueAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productSkuTypeRepository=app('app\common\repository\ProductSkuTypeValueRepository');
            $result=$productSkuTypeRepository->save($data,$this->user);
            if($result['status']!=1){
                return show(0,$result['msg']);
            }else{
                return show(1,$result['msg']);
            }
        }else {
            $typeId = input('get.type_id',0,'int');
            $this->assign('type_id',$typeId);
            return view('valueAdd');
        }
    }

    /**
     * @return string
     * @throws \Exception
     * Description:SKU值修改
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 10:24
     */
    public function valueEdit(){
        $id = input('get.id',0,'int');
        if (!is_numeric($id)) {
            abort(404,'没有获取ID数据！');
        }
        $skuValue = $this->productSkuTypeValueModel->where('id',$id)->find();
        return $this->fetch('valueEdit',[
            'sku_value' => $skuValue,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     * Description:SKU值删除
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-08-28 10:25
     */
    public function valueDel(){
        $id = input('get.id',0,'int');
        if (!is_numeric($id)) {
            return show(0, '没有获取ID数据');
        }
        if($this->productSkuTypeValueModel->destroy($id)){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }
}