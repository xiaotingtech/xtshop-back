<?php
namespace app\admin\controller;

class Setting extends AuthBase
{
    protected $settingRepository;

    public function initialize()
    {
        parent::initialize();
        $this->settingRepository = app('app\common\repository\SettingRepository');
    }

    /**
     * @param int $type
     * @return \think\response\Json
     * Description:获取配置信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 10:31
     */
    public function getInfo($type=0){
        $result =$this->settingRepository->getInfo($type);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:修改配置
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 10:32
     */
    public function saveData(){
        $data=$this->postData;
        if(empty($data['type'])){
            return show(0,'未获取到配置类型');
        }
        $type=$data['type'];
        $result =$this->settingRepository->saveData($data,$type);
        return show($result['status'],$result['msg']);
    }
}