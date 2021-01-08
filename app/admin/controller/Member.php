<?php
/**
 * Created by xtshop
 * Class Member
 * Description:用户管理
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 11:34
 */
namespace app\admin\controller;

use think\facade\Request;
use app\common\model\User;
class Member extends AuthBase
{
    protected $userModel;

    protected $useRepository;

    public function initialize()
    {
        parent::initialize();
        $this->userModel=new User();
        $this->useRepository=app('app\common\repository\UserRepository');
    }
    /**
     * @return mixed
     * Description:用户列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 11:34
     */
    public function memberList(){
        $page = input('get.page',1,'int');
        $list_row = input('get.list_row',0,'int');
        $list_row = isset($list_row) ? $list_row : config('paginate.list_rows');
        $userName=input('get.keyword','','string');
        $where='';
        if(!empty($userName)){
            $where="username like '%".$userName."%' OR phone like '%".$userName."%'";
        }
        $result=app('app\common\repository\UserRepository')->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:更新用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 10:29
     */
    public function updateInfo(){
        if($this->request->isPost()){
            $userData=input('post.');
            $result=$this->useRepository->updateInfo($userData);
            if($result['status']==1){
                return show(1,$result['msg']);
            }else{
                return show(0,$result['msg']);
            }
        }else{
            return show(0,'请求方式不正确！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:添加用户
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-11-05 10:20
     */
    public function memberAdd(){
        if(Request::isPost()){
            $username=input('post.username','','string');
            if(empty($username)){
                return show(0, '请输入用户名！');
            }
            $password=input('post.password','','string');
            if(empty($password)){
                return show(0, '请输入密码！');
            }
            //查询名字是否已经存在
            if($this->userModel->field('id')->where('username',$username)->find()){
                return show(0, '该用户名已经被使用！');
            }
            $phone=input('post.phone','','string');
            $status=input('post.status',1,'int');
            $nowTime=time();
            $saveUserData=[
                'username'=>$username,
                'phone'=>$phone,
                'password'=>md5(sha1($password . config('auth.PASSWORD_SALT'))),
                'status'=>$status,
                'create_time'=>$nowTime,
                'update_time'=>$nowTime
            ];
            if(!$userId=$this->userModel->insertGetId($saveUserData)){
                return show(0, '添加失败！');
            }
            return show(1, '添加成功！');
        }else {
            return show(0, '请求方式不正确！');
        }
    }

    /**
     * Description:禁用用户
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 11:36
     */
    public function statusChange()
    {
        $id = input('post.id',0,'int');
        $status = input('post.status',0,'int');
        $row = $this->userModel->update(['status'=>$status,'id'=>$id]);
        if ($row) {
            return show(1, '修改成功！');
        }
        return show(0,'修改失败');
    }
}