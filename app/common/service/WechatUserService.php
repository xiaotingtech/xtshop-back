<?php
/**
 * Created by xtshop.
 * Description:微信相关的用户处理
 * Author: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020/5/9
 * Time: 11:00
 */
namespace app\common\service;

use app\common\model\User;
use think\App;
use think\facade\Cache;
use \util\wechat\wxBizDataCrypt;
class WechatUserService extends BaseService
{
    //user的model
    protected $userModel;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userModel = new User();
    }

    /**
     * @param $data
     * @param array $headData
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:登录
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 18:35
     */
    public function login($data,$headData=[]){
        if(empty($data['openid'])){
            return [
                'status'=>-10000,
                'msg'=>'获取OPENID失败！',
                'data'=>new class{}
            ];
        }
        $nowTime=time();
        $openId=$data['openid'];
        //如果不是当前用户则查询看是否存在已有的第三方用户
        $hasUser=$this->userModel
            ->field('id,username,openid,sex,phone,avatar,vip_time,token_time,score_num,level')
            ->where('openid',$openId)->find();
        if(!empty($hasUser)){
                $uid = $hasUser['id'];
                //如果存在则返回已有的这个第三方账号
                $token = \util\Aes::getInstance()->userEncode($uid, $hasUser['token_time']);
                if((!empty($hasUser['openid']))&&(!empty($hasUser['avatar']))) {
                    $userInfo = [
                        'user_id' => $hasUser['id'],
                        'username' => $hasUser['username'],
                        'phone' => $hasUser['phone'],
                        'avatar' => $hasUser['avatar'],
                        'token' => $token,
                        'level' => $hasUser['level'],
                        'score_num' => $hasUser['score_num'],
                        'is_bind'=>1
                    ];
                }else{
                    $userInfo = [
                        'user_id' => $hasUser['id'],
                        'username' => $hasUser['username'],
                        'phone' => $hasUser['phone'],
                        'avatar' => $hasUser['avatar'],
                        'token' => $token,
                        'level' => $hasUser['level'],
                        'score_num' => $hasUser['score_num'],
                        'is_bind'=>0
                    ];
                }
                return [
                    'status' => 1,
                    'msg' => '登录成功！',
                    'data' => $userInfo
                ];
        }else {
            //不存在的话新增一个第三方用户
            $saveData = [
                'username' => !empty($data['username'])?$this->filterEmoji($data['username']):'XTSHOP',
                'sex' => !empty($data['sex']) ? $data['sex'] : 0,
                'avatar' => !empty($data['avatar']) ? $data['avatar'] : '',
                'openid' => !empty($data['openid']) ? $data['openid'] : '',
                'unionid' => !empty($data['unionid']) ? $data['unionid'] : '',
                'update_time' => $nowTime,
                'token_time' => $nowTime,
                'create_time' => $nowTime,
            ];
            if (!$userId = $this->userModel->insertGetId($saveData)) {
                return [
                    'status' => -10000,
                    'msg' => '绑定失败！',
                    'data' => [],
                ];
            }
        }
        //如果存在则返回已有的这个第三方账号
        $token=\util\Aes::getInstance()->userEncode($userId,$nowTime);
        $userInfo = [
            'user_id' => $userId,
            'username' => $saveData['username'],
            'phone' => '',
            'avatar' => $saveData['avatar'],
            'token' => $token,
            'level' => 1,
            'score_num' => 0,
            'is_bind'=>0
        ];
        return [
            'status'=>1,
            'msg'=>'登录成功！',
            'data'=>$userInfo
        ];
    }

    /**
     * @param $data
     * @param $user
     * @param $headData
     * @return array
     * Description:解密获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 20:06
     */
    public function updateWechatInfo($data,$user,$headData){
        if(empty($data['encrypted_data'])){
            return [
                'status'=>-10000,
                'msg'=>'加密数据为空',
                'data'=>new class{}
            ];
        }
        $encryptedData=$data['encrypted_data'];
        if(empty($data['iv'])){
            return [
                'status'=>-10000,
                'msg'=>'iv数据为空',
                'data'=>new class{}
            ];
        }
        $iv=$data['iv'];
        $sessionKey=Cache::store('redis')->get('session_key_'.$user['id']);
        $wxBizDataObj=new wxBizDataCrypt(config('wechat.weapp_app_id'),
            $sessionKey);
        $userInfoDecode=$wxBizDataObj->decryptData($encryptedData,$iv);
        if($userInfoDecode['status']!=1){
            return [
                'status'=>-10000,
                'msg'=>$userInfoDecode['msg'],
                'data'=>new class{}
            ];
        }
        $userInfo=$userInfoDecode['data'];
        $updateInfo=[
            'username'=>$userInfo['nickName'],
            'sex'=>$userInfo['gender'],
            'city_name'=>$userInfo['city'],
            'province_name'=>$userInfo['province'],
            'country_name'=>$userInfo['country'],
            'avatar'=>$userInfo['avatarUrl'],
            'update_time'=>time(),
            //'unionid'=>$userInfo['unionId'],
        ];
        if($this->userModel->where('id',$user['id'])->update($updateInfo)){
            $result=$updateInfo;
            unset($updateInfo['openid']);
            //unset($updateInfo['unionid']);
            $result['user_id']=$user['id'];
            $result['score_num']=$user['score_num'];
            $result['level']=$user['level'];
            $result['is_bind']=1;
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$result
            ];
        }else{
            return [
                'status'=>-10000,
                'msg'=>'更新用户信息失败',
                'data'=>new class{}
            ];
        }
    }

    /**
     * @param $data
     * @param $user
     * @param $headData
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * Description:更新电话
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-20 23:06
     */
    public function updatePhone($data,$user,$headData){
        if(empty($data['encrypted_data'])){
            return [
                'status'=>-10000,
                'msg'=>'加密数据为空',
                'data'=>new class{}
            ];
        }
        $encryptedData=$data['encrypted_data'];
        if(empty($data['iv'])){
            return [
                'status'=>-10000,
                'msg'=>'iv数据为空',
                'data'=>new class{}
            ];
        }
        $iv=$data['iv'];
        $sessionKey=Cache::store('redis')->get('session_key_'.$user['id']);
        $wxBizDataObj=new wxBizDataCrypt(config('wechat.weapp_app_id'),
            $sessionKey);
        $userInfoDecode=$wxBizDataObj->decryptData($encryptedData,$iv);
        if($userInfoDecode['status']!=1){
            return [
                'status'=>-10000,
                'msg'=>$userInfoDecode['msg'],
                'data'=>new class{}
            ];
        }
        $userInfo=$userInfoDecode['data'];
        $updateInfo=[
            'phone'=>$userInfo['purePhoneNumber'],
            'update_time'=>time(),
        ];
        if($this->userModel->where('id',$user['id'])->update($updateInfo)){
            $result=$updateInfo;
            $result['user_id']=$user['id'];
            $result['score_num']=$user['score_num'];
            $result['level']=$user['level'];
            $result['is_bind']=1;
            return [
                'status'=>1,
                'msg'=>'更新成功！',
                'data'=>$result
            ];
        }else{
            return [
                'status'=>-10000,
                'msg'=>'更新用户信息失败',
                'data'=>new class{}
            ];
        }
    }
}