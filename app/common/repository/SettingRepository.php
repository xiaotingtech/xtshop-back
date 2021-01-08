<?php
namespace app\common\repository;

use app\common\model\Setting;
class SettingRepository extends BaseRepository
{
    //配置模型实例
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel=new Setting();
    }

    /**
     * @param int $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取配置内容
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 10:23
     */
    public function getInfo($type=0){
        $result=new Class{};
        if(!empty($type)) {
            $settingRes=$this->settingModel
                ->field('id,type,content')
                ->where('type',$type)
                ->find();
            if(!empty($settingRes)){
                $result=json_decode($settingRes['content'],true);
                $result['type']=$type;
            }
        }
        return [
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>$result
        ];
    }

    /**
     * @param $data
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:保存数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 10:28
     */
    public function saveData($data,$type){
        if(!empty($data)&&!empty($type)){
            $saveData=[];
            $saveData['type']=$type;
            unset($data['type']);
            if(isset($data['update_time'])){
                unset($data['update_time']);
            }
            if(isset($data['create_time'])){
                unset($data['create_time']);
            }
            $saveData['content']=json_encode($data);
            if($infoRes=$this->settingModel
                ->field('id')
                ->where('type',$type)->find()){
                if(!$this->settingModel->where('id',$infoRes['id'])
                    ->update($saveData)){
                    return [
                        'status'=>0,
                        'msg'=>'更新失败!',
                    ];
                }
            }else{
                if(!$this->settingModel
                    ->save($saveData)){
                    return [
                        'status'=>0,
                        'msg'=>'更新失败!',
                    ];
                }
            }
        }else{
            return [
                'status'=>0,
                'msg'=>'更新失败！',
                'result'=>[]
            ];
        }
        return [
            'status'=>1,
            'msg'=>'更新成功!',
        ];
    }
}