<?php
/**
 * Created by xtshop
 * Class CartRepository
 * Description:用户地址仓库类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 17:47
 */
namespace app\common\repository;

use app\common\model\UserAddress;
use app\common\validate\UserAddress as UserAddressValidate;
class UserAddressRepository extends BaseRepository
{
    //user_address的model
    protected $userAddressModel;

    protected $userAddressValidate;

    public function __construct()
    {
        $this->userAddressModel = new UserAddress();

        $this->userAddressValidate = new UserAddressValidate();
    }

    /**
     * @param $data
     * @param array $userInfo
     * @return array
     * Description:保存地址
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 18:01
     */
    public function save($data,$userInfo=[]){
        if(empty($userInfo)){
            return [
                'status'=>-10020,
                'msg'=>'请先登录！'
            ];
        }
        $data['uid']=$userInfo['id'];
        if (!$this->userAddressValidate->scene('save')->check($data)) {
            return ['status' => 0, 'msg' => $this->userAddressValidate->getError()];
        }
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if(isset($data['update_time'])){
                unset($data['update_time']);
            }
            if(isset($data['create_time'])){
                unset($data['create_time']);
            }
            $data['is_default']=intval($data['is_default']);
            if(!$this->userAddressModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if(!$this->userAddressModel->save($data)){
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
    /**
     * @param $where
     * @param string $sort
     * @return array
     * Description:地址列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 17:41
     */
    public function getList($where,$sort='id DESC'){
        $count=$this->userAddressModel->where($where)->count();
        if($count>0) {
            $list = $this->userAddressModel
                ->field('id,uid,username,phone,address,address_district,address_door,is_default')
                ->where($where)
                ->order($sort)
                ->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $listData,
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param $where
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取默认地址
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-22 11:08
     */
    public function getDefaultAddress($where,$order='is_default DESC,id DESC'){
        $default=$this->userAddressModel->where($where)->order($order)->find();
        if(!empty($default)){
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$default,
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>new Class{},
            ];
        }
    }
}