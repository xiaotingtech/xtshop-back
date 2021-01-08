<?php
/**
 * Created by xtshop
 * Class ApiAuthWeapp
 * Description:用户认证中间件
 * @package app\middleware
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-04 10:55
 */
namespace app\middleware;

use think\facade\Request;
use util\Aes;
class ApiAuthWeapp extends BaseMiddle
{
    public function __construct()
    {
        parent::__construct();
    }

    public function handle($request, \Closure $next)
    {
        $result=$this->getUser();
        if ($result['code']==1){
            return $next($request);
        }else {
            $arr=[
                'code'=>$result['code'],
                'msg'=>$result['msg'],
                'data'=>$result['data']
            ];
            return json($arr);
        }
    }
    /**
     * @return array
     * Description:获取用户
     * User: 孙春晓
     * Date: 2019-03-01
     * Time: 18:06
     */
    private function getUser(){
        $headerData=[];
        $headerCodeData=Request::header('globalinfo');
        if(!empty($headerCodeData)){
            $headerDataResult =Aes::getInstance()->aes128cbcHexDecrypt($headerCodeData);
            if(!empty($headerDataResult)){
                $headerData=json_decode($headerDataResult,true);
            }else{
                return [
                    'code'=>-10000,
                    'msg'=>'解密header传参失败！',
                    'data'=>new class{},
                ];
            }
        }
        if(!empty($headerData['token'])) {
            $userService=app('app\common\repository\UserRepository');
            $result=$userService->getUserByToken($headerData['token']);
            return [
                'code'=>$result['status'],
                'msg'=>$result['msg'],
                'data'=>$result['data'],
            ];
        }else{
            return [
                'code'=>-10020,
                'msg'=>'请您登录！',
                'data'=>new class{},
            ];
        }
    }
}