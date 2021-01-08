<?php
/**
 * Created by xtshop
 * Class User
 * Description:后台用户登录相关
 * @package app\index\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 16:29
 */
namespace app\admin\controller;

class User extends Base
{
    protected $useRepository;
    public function initialize()
    {
        parent::initialize();
        $this->useRepository=app('app\common\repository\UserRepository');
    }

    /**
     * @return mixed
     * Description:登录
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     */
    public function login(){
        if($this->request->isPost()) {
            $data=$this->request->post();
            $result=$this->useRepository->login($data);
            if($result['status']==1){
                return show(1,$result['msg'],$result['data']);
            }else{
                return show(0,$result['msg'],$result['data']);
            }
        }else{
            return show(0,'请求方式不正确！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:41
     */
    public function info(){
        if($this->request->isPost()) {
            $result=$this->useRepository->info($this->headerData['authorization']);
            if($result['status']==1){
                return show($result['status'],$result['msg'],$result['data']);
            }else{
                return show($result['status'],$result['msg'],$result['data']);
            }
        }else{
            return show(-10000,'请求方式不正确！');
        }
    }

    /**
     * Description:登出操作
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     */
    public function logout()
    {
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $result=$this->useRepository->logout($userInfo);
        return show($result['status'],$result['msg']);
    }
}