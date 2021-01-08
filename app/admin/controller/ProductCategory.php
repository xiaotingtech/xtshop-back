<?php
namespace app\admin\controller;

use think\facade\Request;
use app\common\model\ProductCategory as ProductCategoryModel;
class ProductCategory extends AuthBase
{
    protected $productCategoryRepository;

    public function initialize()
    {
        parent::initialize();
        $this->productCategoryRepository = app('app\common\repository\ProductCategoryRepository');
    }

    /**
     * @param $parentId
     * @return \think\response\Json
     * Description:分类列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:11
     */
    public function cateList($parentId){
        $where='';
        if(!empty($parentId)){
            $where="pid=".$parentId;
        }else{
            $where='pid=0';
        }
        $page=1;
        $listRow=10;
        $result =$this->productCategoryRepository->getList($where,$page,$listRow);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:保存
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 10:54
     */
    public function cateAdd(){
        if(Request::isPost()){
            $data=input('post.');
            $productCategoryRepository=app('app\common\repository\ProductCategoryRepository');
            $result=$productCategoryRepository->save($data);
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
     * @param $id
     * @return \think\response\Json
     * Description:获取分类信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:06
     */
    public function info($id){
        $productCategoryRepository=app('app\common\repository\ProductCategoryRepository');
        $cateInfo=$productCategoryRepository->info($id);
        if($cateInfo){
            if(!empty($cateInfo['icon'])){
                $cateInfo['icon']=get_real_url($cateInfo['icon']);
            }
            return show(1,'获取成功',$cateInfo);
        }else{
            return show(0,'获取失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:分类的树形结构数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 11:47
     */
    public function cateTreeList(){
        $where='';
        $result =$this->productCategoryRepository->getTreeList($where);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:修改导航显示
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:21
     */
    public function navigation(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $status=input('post.show_navigation',0,'int');
        $productCategoryModel=new ProductCategoryModel();
        if($productCategoryModel->where('id','IN',$ids)->update(['show_navigation'=>$status])){
            return show(1, '修改成功！');
        }else{
            return show(0, '修改失败！');
        }
    }

    /**
     * @return \think\response\Json
     * @throws \Exception
     * Description:修改状态
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:20
     */
    public function changeStatus(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $status=input('post.status',0,'int');
        $productCategoryModel=new ProductCategoryModel();
        if($productCategoryModel->where('id','IN',$ids)->update(['status'=>$status])){
            return show(1, '修改成功！');
        }else{
            return show(0, '修改失败！');
        }
    }

    /**
     * @return \think\response\Json
     * @throws \Exception
     * Description:分类删除
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:19
     */
    public function cateDel(){
        $ids = input('post.ids','','string');
        if (empty($ids)) {
            return show(0, '没有获取ID数据');
        }
        if(!is_array($ids)){
            $ids=explode(',',$ids);
        }
        $productCategoryModel=new ProductCategoryModel();
        if($productCategoryModel->where('id','IN',$ids)->delete()){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }
}