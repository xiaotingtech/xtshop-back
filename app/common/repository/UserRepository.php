<?php
/**
 * Created by xtshop
 * Class UserService
 * Description:用户数据处理类
 * @package app\common\service
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 16:30
 */
namespace app\common\repository;

use app\common\model\User;
use app\common\model\UserProductCollect;
use app\common\model\UserProductBrowse;
use app\common\model\Product;
use think\facade\Cache;
class UserRepository extends BaseRepository
{
    //use验证器
    protected $userValidate;
    //user的model
    protected $userModel;
    public function __construct()
    {
        $this->userValidate=sunnier_validate('app\common\validate\User');
        $this->userModel=new User();
    }

    /**
     * @param $where
     * @param $page
     * @param $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取用户列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-09 16:36
     */
    public function getList($where,$page,$listRow){
        $count=$this->userModel->where($where)->count();
        if($count>0) {
            $list = $this->userModel->field('id,username,phone,password,vip_time,score_num,status,token_time,create_time')
                ->where($where)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => [
                        'list' => $list,
                        'page' => $page,
                        'list_row' => $listRow,
                        'total' => $count,
                        'totalPage' => ceil($count / $listRow)
                    ],
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[
                        'list'=>[],
                        'page'=>$page,
                        'list_row'=>$listRow,
                        'total'=>$count,
                        'totalPage'=>ceil($count/$listRow)
                    ],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'page'=>$page,
                    'list_row'=>$listRow,
                    'total'=>$count,
                    'totalPage'=>ceil($count/$listRow)
                ],
            ];
        }
    }

    /**
     * @param $data
     * @param $headData
     * @return array
     * Description:微信小程序登录
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 18:34
     */
    public function weAppLogin($data,$headData){
        if(empty($data['code'])){
            return [
                'status'=>-10000,
                'msg'=>'没有code',
                'data'=>new class{}
            ];
        }
        $code=$data['code'];
        $curlObjRes=\util\CurlUtil::getInstance()->getData('https://api.weixin.qq.com/sns/jscode2session',
            [
                'appid'=>config('wechat.weapp_app_id'),
                'secret'=>config('wechat.weapp_app_secret'),
                'js_code'=>$code,
                'grant_type'=>'authorization_code'
            ]);
        if(!empty($curlObjRes)) {
            //解析json为数组
            $curlObj=json_decode($curlObjRes,true);
            $loginInfo=app('app\common\service\WechatUserService')->login($curlObj,$headData);
            if($loginInfo['status']==1) {
                $userInfo=$loginInfo['data'];
                //记录session_key
                Cache::store('redis')->set('session_key_'.$userInfo['user_id'],$curlObj['session_key'],3600);
                return [
                    'status'=>1,
                    'msg'=>$loginInfo['msg'],
                    'data'=>$userInfo
                ];
            }else{
                return [
                    'status'=>-10000,
                    'msg'=>$loginInfo['msg'],
                    'data'=>new class{}
                ];
            }
        }else{
            return [
                'status'=>-10000,
                'msg'=>'未获取到授权信息',
                'data'=>new class{}
            ];
        }
    }

    /**
     * @param $data
     * @param array $headData
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:注册用户
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:06
     */
    public function loginWechat($data,$headData=[]){
        if(empty($data['openid'])){
            return [
                'status'=>-10000,
                'msg'=>'没有openid！',
                'data'=>[],
            ];
        }
        $openId=$data['openid'];
        $nowTime=time();
        //如果不是当前用户则查询看是否存在已有的第三方用户
        $hasUser=$this->userModel->field('id,username,sex,phone,unionid,avatar,vip_time,score_num,level')
            ->where('openid',$openId)->find();
        if(!empty($hasUser)){
            if((!empty($userInfo['openid']))&&(!empty($userInfo['avatar']))) {
                $userInfo = [
                    'user_id' => $hasUser['id'],
                    'username' => $hasUser['username'],
                    'sex' => $hasUser['sex'],
                    'phone' => $hasUser['phone'],
                    'avatar' => $hasUser['avatar'],
                    'token' => $headData['token'],
                    'score_num' => $hasUser['score_num'],
                    'level' => $hasUser['level'],
                    'is_bind'=>1
                ];
            }else{
                $userInfo = [
                    'user_id' => $hasUser['id'],
                    'username' => $hasUser['username'],
                    'sex' => $hasUser['sex'],
                    'phone' => $hasUser['phone'],
                    'avatar' => $hasUser['avatar'],
                    'token' => $headData['token'],
                    'score_num' => $hasUser['score_num'],
                    'level' => $hasUser['level'],
                    'is_bind'=>0
                ];
            }
            return [
                'status' => 1,
                'msg' => '绑定成功！',
                'data' => $userInfo
            ];
        }else{
            //判断数据
            if(empty($data['username'])){
                return [
                    'status'=>-10000,
                    'msg'=>'没有用户名！',
                    'data'=>[],
                ];
            }
            if(empty($data['openid'])){
                return [
                    'status'=>-10000,
                    'msg'=>'没有openid！',
                    'data'=>[],
                ];
            }
            //不存在的话新增一个第三方用户
            $saveData = [
                'username' => $this->filterEmoji($data['username']),
                'phone' => !empty($data['phone']) ? $data['phone'] : '',
                'sex' => !empty($data['sex']) ? $data['sex'] : 0,
                'avatar' => !empty($data['avatar']) ? $data['avatar'] : '',
                'openid' => !empty($data['openid']) ? $data['openid'] : '',
                'unionid' => !empty($data['unionid']) ? $data['unionid'] : '',
                'update_time' => $nowTime,
                'token_time' => $nowTime,
                'create_time' => $nowTime,
            ];
            if(!$userId=$this->userModel->insertGetId($saveData)){
                return [
                    'status'=>-10000,
                    'msg'=>'绑定失败！',
                    'data'=>[],
                ];
            }
        }
        //如果存在则返回已有的这个第三方账号
        $token=\util\Aes::getInstance()->userEncode($userId,$nowTime);
        $userInfo = [
            'user_id' => $userId,
            'username' => $saveData['username'],
            'sex' => $saveData['sex'],
            'phone' =>$saveData['phone'],
            'avatar' => $saveData['avatar'],
            'token' => $token,
            'score_num' => 0,
            'level'=>1,
            'is_bind'=>0
        ];
        return [
            'status'=>1,
            'msg'=>'绑定成功！',
            'data'=>$userInfo
        ];
    }

    /**
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:登录
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:43
     */
    public function login($data){
        if (!$this->userValidate->scene('login')->check($data)) {
            return [
                'status'=>0,
                'msg'=>$this->userValidate->getError()
            ];
        }
        if($user=$this->userModel->field('id,username,phone,password')->with('roles')->where('username',$data['username'])
            ->where('status',1)->find()){
            if(password_check($data['password'],$user['password'])){
                $nowTime=time();
                $userId=$user['id'];
                $saveData=[
                    'id'=>$userId,
                    'token_time'=>$nowTime
                ];
                if(!$this->userModel->update($saveData)){
                    return [
                        'status'=>-10000,
                        'msg'=>"登录失败",
                        'data'=>new class{}
                    ];
                }
                $token=\util\Aes::getInstance()->userEncode($userId,$nowTime);
                return [
                    'status'=>1,
                    'msg'=>'登录成功！',
                    'data'=>[
                        'token'=>$token
                    ]
                ];
            }else{
                return [
                    'status'=>-10000,
                    'msg'=>'密码不正确！',
                    'data'=>new class{}
                ];
            }
        }else{
            return [
                'status'=>-10000,
                'msg'=>'未查找到用户！',
                'data'=>new class{}
            ];
        }
    }

    /**
     * @param $user
     * @return array
     * Description:退出
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:37
     */
    public function logout($user){
        $nowTime=time();
        $userId=$user['id'];
        $saveData=[
            'id'=>$userId,
            'token_time'=>$nowTime
        ];
        if(!$this->userModel->update($saveData)){
            return [
                'status'=>-10000,
                'msg'=>"退出失败！",
                'data'=>new class{}
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>"退出成功！",
                'data'=>new class{}
            ];
        }
    }

    /**
     * @param $data
     * @param array $user
     * @param array $headData
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:保存用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:02
     */
    public function saveInfo($data,$user=[],$headData=[]){
        $uid=$user['id'];
        $info=$this->userModel->field('openid,sex,avatar')->find($uid);
        if(empty($info)){
            return [
                'status'=>-10000,
                'msg'=>'获取失败！',
                'data'=>new class{}
            ];
        }
        $file=\think\facade\Request::file('avatar_file');
        if(!empty($file)){
            $saveFileService=app('app\common\service\SaveFileService');
            $fileRes=$saveFileService->saveAvatarImageFile($file);
            if($fileRes['status']!=1){
                return [
                    'status'=>-10000,
                    'msg'=>$fileRes['msg'],
                    'data'=>new class{}
                ];
            }else{
                $data['avatar']=$fileRes['data']['url'];
            }
        }
        //更新数据
        $saveData=[
            'id'=>$uid,
            'update_time'=>time()
        ];
        $userInfo=[
            'user_id' => (800505000+$uid),
            'username' => $user['username'],
            'sex'=>$info['sex'],
            'phone' => $user['phone'],
            'avatar'=>$info['avatar'],
            'token'=>$headData['token'],
        ];
        if(!empty($data['sex'])){
            $saveData['sex']=$data['sex'];
            $userInfo['sex']=$data['sex'];
        }
        if(!empty($data['username'])){
            $saveData['username']=$data['username'];
            $userInfo['username']=$data['username'];
        }
        if(!empty($data['avatar'])){
            $saveData['avatar']=$data['avatar'];
            $userInfo['avatar']=$data['avatar'];
        }
        if(!$this->userModel->update($saveData)){
            return [
                'status'=>-10000,
                'msg'=>"保存失败",
                'data'=>new class{}
            ];
        }
        return [
            'status'=>1,
            'msg'=>'保存成功！',
            'data'=>$userInfo
        ];
    }

    /**
     * @param $str
     * @return string|string[]|null
     * Description:过滤表情符
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:04
     */
    public function filterEmoji($str)
    {
        try {
            $str = preg_replace_callback('/./u',
                function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                },
                $str);
            return $str;
        }catch (\Exception $e){
            return $str;
        }
    }

    /**
     * @param $token
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:27
     */
    public function getUserByToken($token){
        if(!empty($token)) {
            $aesObj=\util\Aes::getInstance();
            //解出token信息
            if(!$tokenInfo=$aesObj->userDecode($token)){
                return [
                    'status'=>-10021,
                    'msg'=>'token错误',
                    'data'=>new class{},
                ];
            }
            //查询数据库
            if ($user = $this->userModel->field('id,username,phone,openid,unionid,avatar,
            score_num,level,token_time,update_time,status')->where('id', $tokenInfo['uid'])->find()) {
                if($user['status']!=1){
                    return [
                        'status'=>-10011,
                        'msg'=>'封禁帐号禁止登录！',
                        'data'=>new class{},
                    ];
                }
                //如果有时效检测，目前没有有效期
                if($tokenInfo['token_time']!=$user['token_time']){
                    return [
                        'status'=>-10022,
                        'msg'=>'第三方账号已在别的设备绑定，当前设备已退出登录！',
                        'data'=>new class{},
                    ];
                }
                $userArr=$user->toArray();
                $userArr['update_time']=strtotime($userArr['update_time']);
                if((!empty($userArr['openid']))&&(!empty($userArr['avatar']))) {
                    $userArr['is_bind']=1;
                }else{
                    $userArr['is_bind']=0;
                }
                return [
                    'status'=>1,
                    'msg'=>'获取成功',
                    'data'=>$userArr
                ];
            } else {
                return [
                    'status'=>-10021,
                    'msg'=>'token错误,未查找到用户',
                    'data'=>new class{},
                ];
            }
        }else{
            return [
                'status'=>-10020,
                'msg'=>'请您登录！',
                'data'=>new class{},
            ];
        }
    }

    /**
     * @param $token
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:后台获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:43
     */
    public function getBackUserByToken($token){
        if(!empty($token)) {
            $aesObj=\util\Aes::getInstance();
            //解出token信息
            if(!$tokenInfo=$aesObj->userDecode($token)){
                return [
                    'status'=>-10021,
                    'msg'=>'token错误',
                    'data'=>new class{},
                ];
            }
            //查询数据库
            if ($user = $this->userModel->field('id,username,phone,openid,unionid,avatar,
            score_num,level,token_time,update_time,status')->where('id', $tokenInfo['uid'])->find()) {
                if($user['status']!=1){
                    return [
                        'status'=>-10011,
                        'msg'=>'封禁帐号禁止登录！',
                        'data'=>new class{},
                    ];
                }
                //如果有时效检测，目前没有有效期
                if($tokenInfo['token_time']!=$user['token_time']){
                    return [
                        'status'=>-10022,
                        'msg'=>'第三方账号已在别的设备绑定，当前设备已退出登录！',
                        'data'=>new class{},
                    ];
                }
                $userArr=$user->toArray();
                $userArr['update_time']=strtotime($userArr['update_time']);
                if((!empty($userArr['openid']))&&(!empty($userArr['avatar']))) {
                    $userArr['is_bind']=1;
                }else{
                    $userArr['is_bind']=0;
                }
                return [
                    'status'=>1,
                    'msg'=>'获取成功',
                    'data'=>$userArr
                ];
            } else {
                return [
                    'status'=>-10021,
                    'msg'=>'token错误,未查找到用户',
                    'data'=>new class{},
                ];
            }
        }else{
            return [
                'status'=>-10020,
                'msg'=>'请您登录！',
                'data'=>new class{},
            ];
        }
    }

    /**
     * @param $token
     * @return array|array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:54
     */
    public function info($token){
        if(!empty($token)) {
            $aesObj=\util\Aes::getInstance();
            //解出token信息
            if(!$tokenInfo=$aesObj->userDecode($token)){
                return [
                    'status'=>-10021,
                    'msg'=>'token错误',
                    'data'=>new class{},
                ];
            }
            //查询数据库
            if ($user = $this->userModel->field('id,username,phone,openid,unionid,avatar,
            score_num,level,token_time,update_time,status')->where('id', $tokenInfo['uid'])->find()) {
                if($user['status']!=1){
                    return [
                        'status'=>-10011,
                        'msg'=>'封禁帐号禁止登录！',
                        'data'=>new class{},
                    ];
                }
                //如果有时效检测，目前没有有效期
                if($tokenInfo['token_time']!=$user['token_time']){
                    return [
                        'status'=>-10022,
                        'msg'=>'第三方账号已在别的设备绑定，当前设备已退出登录！',
                        'data'=>new class{},
                    ];
                }
                $userArr=$user->toArray();
                $userArr['update_time']=strtotime($userArr['update_time']);
                if((!empty($userArr['openid']))&&(!empty($userArr['avatar']))) {
                    $userArr['is_bind']=1;
                }else{
                    $userArr['is_bind']=0;
                }
                $menu =get_menu_all($userArr);
                return [
                    'status'=>1,
                    'msg'=>'获取成功',
                    'data'=>[
                        'username'=>$userArr['username'],
                        'menus'=>$menu['menus'],
                        'roles'=>['TEST'],
                        'icon'=>$userArr['avatar']
                    ]
                ];
            } else {
                return [
                    'status'=>-10021,
                    'msg'=>'token错误,未查找到用户',
                    'data'=>new class{},
                ];
            }
        }else{
            return [
                'status'=>-10020,
                'msg'=>'请您登录！',
                'data'=>new class{},
            ];
        }
    }

    /**
     * @param $data
     * @param array $user
     * @return array
     * Description:商品收藏(取消)
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:28
     */
    public function collect($data,$user=[]){
        if(empty($data['id'])){
            return [
                'status'=>-10000,
                'msg'=>'没有商品ID',
            ];
        }
        $uid=$user['id'];
        $productId=$data['id'];
        $status=intval($data['status']);
        $nowTime=time();
        $userProductCollect=new UserProductCollect();
        if($status==1){
            //添加
            $saveData=[
                'uid'=>$uid,
                'product_id'=>$productId,
            ];
            if(!$collectDataRes=$userProductCollect->field('id,status')->where($saveData)->find()){
                $saveData['create_time']=$nowTime;
                if(!$userProductCollect->save($saveData)){
                    return [
                        'status'=>-10000,
                        'msg'=>'收藏失败',
                    ];
                }
            }else{
                if($collectDataRes['status']==0) {
                    if (!$userProductCollect->where('id', $collectDataRes['id'])
                        ->update(['status'=>1,'update_time'=>$nowTime])) {
                        return [
                            'status' => -10000,
                            'msg' => '收藏失败',
                        ];
                    }
                }
            }
            return [
                'status'=>1,
                'msg'=>'收藏成功！'
            ];
        }else{
            //删除收藏
            $where=[
                'uid'=>$uid,
                'product_id'=>$productId
            ];
            if(!$userProductCollect->where($where)
                ->update(['status'=>0,'update_time'=>$nowTime])){
                return [
                    'status'=>-10000,
                    'msg'=>'取消收藏失败',
                ];
            }
            return [
                'status'=>1,
                'msg'=>'取消收藏成功！'
            ];
        }
    }

    /**
     * @param array $user
     * @param int $page
     * @param int $listRow
     * @return array
     * Description:收藏列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:40
     */
    public function collectList($user=[],$page=1,$listRow=10){
        $uid=$user['id'];
        $status=1;
        $nowTime=time();
        $userProductCollect=new UserProductCollect();
        $allCount=$userProductCollect->where('uid',$uid)
            ->where('status',$status)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'暂时没有收藏！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        $userCollectData=$userProductCollect
            ->field('product_id,create_time')
            ->where('uid',$uid)
            ->where('status',$status)
            ->page($page,$listRow)
            ->order('id DESC')
            ->select();
        if($userCollectData->isEmpty()){
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
        $productIds=[];
        $productIdCreate=[];
        foreach ($userCollectData as $collectData){
            $productIds[]=$collectData['product_id'];
            $productIdCreate[$collectData['product_id']]=$collectData['create_time'];
        }
        $productModel=new Product();
        //分页查询
        $products=$productModel->field('id,title,img,price,sale_num')
            ->where('id','IN',$productIds)
            ->where('publish_status',1)
            ->select();
        if(!$products->isEmpty()){
            $productData=$products->toArray();
            foreach ($productData as $pk=>$pv){
                $productData[$pk]['img']=get_real_url($pv['img']);
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>[
                    'list'=>$productData,
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据！',
                'data'=>[
                    'list'=>[],
                    'page' => $page,
                    'list_row' => $listRow,
                    'total' => $allCount,
                    'totalPage' =>ceil($allCount/$listRow),
                ],
            ];
        }
    }

    /**
     * @param array $user
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:足迹列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 17:10
     */
    public function browseList($user=[],$page=1,$listRow=10){
        $uid=$user['id'];
        $status=1;
        $userProductBrowse=new UserProductBrowse();
        $allCount=$userProductBrowse->where('uid',$uid)
            ->where('status',$status)->count();
        if($allCount<=0){
            return [
                'status'=>1,
                'msg'=>'暂时没有足迹！',
                'data'=>[],
            ];
        }
        $userBrowseData=$userProductBrowse
            ->field('product_id,create_time')
            ->where('uid',$uid)
            ->where('status',$status)
            ->page($page,$listRow)
            ->order('id DESC')
            ->select();
        if($userBrowseData->isEmpty()){
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
        $productIds=[];
        $productIdCreate=[];
        foreach ($userBrowseData as $collectData){
            $productIds[]=$collectData['product_id'];
            $productIdCreate[$collectData['product_id']]=$collectData['create_time'];
        }
        $productModel=new Product();
        //分页查询
        $products=$productModel->field('id,img')
            ->where('id','IN',$productIds)
            ->select();
        if(!$products->isEmpty()){
            $productData=$products->toArray();
            foreach ($productData as $pk=>$pv){
                if(!empty($pv['img'])){
                    $pv['img']=get_real_url($pv['img']);
                    $productData[$pk]=$pv;
                }
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$productData,
            ];
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param $user
     * @return array
     * Description:清理足迹
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 17:15
     */
    public function browseClear($user){
        $uid=$user['id'];
        $status=0;
        $nowTime=time();
        $userProductBrowse=new UserProductBrowse();
        if($userProductBrowse->where('uid',$uid)
        ->where('status',1)->update(['status'=>$status,'update_time'=>$nowTime])){
            return [
                'status'=>1,
                'msg'=>'清理完成！',
                'data'=>[],
            ];
        }else{
            return [
                'status'=>0,
                'msg'=>'没有数据！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param $data
     * @param $user
     * @param $headData
     * @return array
     * Description:更新用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-18 20:04
     */
    public function updateWechatInfo($data,$user,$headData){
        return app('app\common\service\WechatUserService')->updateWechatInfo($data,$user,$headData);
    }

    /**
     * @param $data
     * @param $user
     * @param $headData
     * @return mixed
     * Description:更新电话
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-20 23:06
     */
    public function updatePhone($data,$user,$headData){
        return app('app\common\service\WechatUserService')->updatePhone($data,$user,$headData);
    }

    /**
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:修改用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 10:34
     */
    public function updateInfo($data){
        if(empty($data['username'])){
            return [
                'status'=>0,
                'msg'=>'没有用户名！',
                'data'=>[],
            ];
        }
        if(empty($data['id'])){
            return [
                'status'=>0,
                'msg'=>'没有用户ID！',
                'data'=>[],
            ];
        }
        if(isset($data['create_time'])) {
            unset($data['create_time']);
        }
        if(isset($data['token_time'])) {
            unset($data['token_time']);
        }
        if(isset($data['vip_time'])) {
            unset($data['vip_time']);
        }
        if(isset($data['score_num'])) {
            unset($data['score_num']);
        }
        $userId=$data['id'];
        unset($data['id']);
        $userInfo=$this->userModel->field('id,username,password')->where('id',$userId)->find();
        if(empty($userInfo)){
            return [
                'status'=>0,
                'msg'=>'未找到用户！',
                'data'=>[],
            ];
        }
        if(!empty($data['password'])){
            if($data['password']==$userInfo['password']){
                unset($data['password']);
            }else {
                $data['password'] = md5(sha1($data['password'] . config('auth.PASSWORD_SALT')));
            }
        }
        $data['update_time']=time();
        if($this->userModel->where('id',$userId)->update($data)){
            return [
                'status'=>1,
                'msg'=>'修改成功！',
                'data'=>[],
            ];
        }else{
            return [
                'status'=>0,
                'msg'=>'修改失败！',
                'data'=>[],
            ];
        }
    }
}