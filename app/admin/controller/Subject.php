<?php
namespace app\admin\controller;

use think\facade\Request;
use app\common\model\Subject as SubjectModel;
class Subject extends AuthBase
{
    protected $subjectRepository;

    public function initialize()
    {
        parent::initialize();
        $this->subjectRepository = app('app\common\repository\SubjectRepository');
    }

    /**
     * @param $parentId
     * @return \think\response\Json
     * Description:专题列表
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
        $result =$this->subjectRepository->getList($where,$page,$listRow);
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
            $subjectRepository=app('app\common\repository\SubjectRepository');
            $result=$subjectRepository->save($data);
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
     * Description:获取专题信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:06
     */
    public function info($id){
        $subjectRepository=app('app\common\repository\SubjectRepository');
        $cateInfo=$subjectRepository->info($id);
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
     * Description:专题的树形结构数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 11:47
     */
    public function cateTreeList(){
        $where='';
        $result =$this->subjectRepository->getTreeList($where);
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
        $subjectModel=new SubjectModel();
        if($subjectModel->where('id','IN',$ids)->update(['show_navigation'=>$status])){
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
        $subjectModel=new SubjectModel();
        if($subjectModel->where('id','IN',$ids)->update(['status'=>$status])){
            return show(1, '修改成功！');
        }else{
            return show(0, '修改失败！');
        }
    }

    /**
     * @return \think\response\Json
     * @throws \Exception
     * Description:专题删除
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
        $subjectModel=new SubjectModel();
        if($subjectModel->where('id','IN',$ids)->delete()){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }
}