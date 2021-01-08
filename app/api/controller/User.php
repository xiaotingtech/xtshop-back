<?php
/**
 * Created by xtshop
 * Class User
 * Description:用户控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 21:54
 */
namespace app\api\controller;

class User extends Base
{
    protected $userRepository;

    public function initialize()
    {
        parent::initialize();

        $this->userRepository=app('app\common\repository\UserRepository');
    }

    /**
     * @return \think\response\Json
     * Description:获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:01
     */
    public function info(){
        $headData=$this->headerData;
        if(empty($headData['token'])){
            $postData=$this->postData;
            $result=$this->userRepository->loginWechat($postData,$headData);
            if($result['status']!=1){
                $resData=new class{};
            }else{
                $resData=$result['data'];
            }
            return api_res($result['status'],$result['msg'],$resData);
        }
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $user=$userRes['data'];
        $result = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'avatar' => $user['avatar'],
            'phone' => $user['phone'],
            'token'=>$headData['token'],
            'score_num' => $user['score_num'],
            'level' => $user['level'],
            'is_bind' => $user['is_bind'],
        ];
        return api_res(1, '获取成功！', $result);
    }

    /**
     * @return \think\response\Json
     * Description:更新用户数据接口
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:01
     */
    public function updateInfo(){
        $headData=$this->headerData;
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $postData=$this->postData;
        $user=$userRes['data'];
        $result=$this->userRepository->saveInfo($postData,$user,$headData);
        if($result['status']==1){
            return api_res(1,$result['msg'],$result['data']);
        }else{
            return api_res($result['status'],$result['msg'],$result['data']);
        }
    }

    /**
     * @return \think\response\Json
     * Description:收藏接口
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:29
     */
    public function collect(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $postData=$this->postData;
        $result=$this->userRepository->collect($postData,$userInfo);
        if($result['status']==1){
            return api_res(1,$result['msg']);
        }else{
            return api_res($result['status'],$result['msg']);
        }
    }

    /**
     * @return \think\response\Json
     * Description:收藏列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:51
     */
    public function collectList(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $postData=$this->postData;
        $page=!empty($postData['page'])?$postData['page']:1;
        $listRow=!empty($postData['list_row'])?$postData['list_row']:config('extend.list_row');
        $result=$this->userRepository->collectList($userInfo,$page,$listRow);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:足迹列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 17:09
     */
    public function browseList(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $postData=$this->postData;
        $page=!empty($postData['page'])?$postData['page']:1;
        $listRow=!empty($postData['list_row'])?$postData['list_row']:config('extend.list_row');
        $result=$this->userRepository->browseList($userInfo,$page,$listRow);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:清空足迹
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 17:09
     */
    public function browseClear(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $result=$this->userRepository->browseClear($userInfo);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:登录
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 18:28
     */
    public function login(){
        $data=$this->postData;
        $headData=$this->headerData;
        $result=$this->userRepository->weAppLogin($data,$headData);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:更新用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 19:24
     */
    public function updateWechatInfo(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $data=$this->postData;
        $headData=$this->headerData;
        $result=$this->userRepository->updateWechatInfo($data,$userRes['data'],$headData);
        return api_res($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:更新电话
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-20 23:05
     */
    public function updatePhone(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $data=$this->postData;
        $headData=$this->headerData;
        $result=$this->userRepository->updatePhone($data,$userRes['data'],$headData);
        return api_res($result['status'],$result['msg'],$result['data']);
    }
}