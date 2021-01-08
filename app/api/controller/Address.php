<?php
namespace app\api\controller;

class Address extends Base
{
    protected $addressRepository;

    public function initialize()
    {
        parent::initialize();

        $this->addressRepository = app('app\common\repository\UserAddressRepository');
    }

    /**
     * @return \think\response\Json
     * Description:保存地址
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 18:00
     */
    public function saveAddress(){
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        $data=$this->postData;
        $result = $this->addressRepository->save($data,$userInfo);
        return api_res($result['status'], $result['msg']);
    }
    /**
     * @return \think\response\Json
     * Description:地址列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 17:44
     */
    public function listAddress(){
        $where=[];
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        if(!empty($userInfo)){
            $where[]=['uid','=',$userInfo['id']];
        }
        $result = $this->addressRepository->getList($where);
        return api_res($result['status'], $result['msg'], $result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:获取默认地址
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-22 11:06
     */
    public function getDefaultAddress(){
        $where=[];
        $userRes=$this->getUser();
        if($userRes['code']!=1) {
            return api_res($userRes['code'], $userRes['msg'], $userRes['data']);
        }
        $userInfo=$userRes['data'];
        if(!empty($userInfo)){
            $where[]=['uid','=',$userInfo['id']];
        }
        $result = $this->addressRepository->getDefaultAddress($where);
        return api_res($result['status'], $result['msg'], $result['data']);
    }
}