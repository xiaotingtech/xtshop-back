<?php
/**
 * Created by xtshop
 * Class NoLoginUserLog
 * Description:未登录情况下记录日志，目前缺少用户
 * @package app\middleware
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 21:45
 */
namespace app\middleware;

use think\facade\Request;
use app\common\model\UserLog;
class NoLoginUserLog extends BaseMiddle
{
    private $postData = [];

    private $headerData = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        //记录日志----start
        try {
            $this->getHeadData();
            $module = 'api';
            $moduleVal = 1;
            switch ($module) {
                case 'api':
                    $moduleVal = 1;
                    break;
                case 'index':
                    $moduleVal = 2;
                    break;
                case 'mobile':
                    $moduleVal = 3;
                    break;
            }
            //从返回数据中获取user_id，如果存在则保存进日志
            $userId=0;
            $responseData=$response->getData();
            if(!empty($responseData['code'])){
                $decodeRes=$this->decodeData($responseData['code']);
                if(!empty($decodeRes['data']['user_id'])){
                    $userId=$decodeRes['data']['user_id'];
                }
            }
            if(!empty($userId)){
                $userId=$userId-800505000;
            }
            $saveData = [
                'user_id' => $userId,
                'module' => $moduleVal,
                'controller' => $request->controller(),
                'action' => $request->action(),
                'request' => json_encode($request->param()),
                'response' => json_encode($responseData)
            ];
            //判断要记录的参数
            if (!empty($this->headerData['platform'])) {
                $saveData['platform'] = $this->headerData['platform'];
            }
            if (!empty($this->headerData['version'])) {
                $saveData['version'] = $this->headerData['version'];
            }
            if (!empty($this->headerData['system_version'])) {
                $saveData['system_version'] = $this->headerData['system_version'];
            }
            if (!empty($this->headerData['channel'])) {
                $saveData['channel'] = $this->headerData['channel'];
            }
            if (!empty($this->headerData['package_name'])) {
                $saveData['package_name'] = $this->headerData['package_name'];
            }
            if (!empty($this->headerData['equipment_id'])) {
                $saveData['equipment_id'] = $this->headerData['equipment_id'];
            }
            if (!empty($this->headerData['client_time'])) {
                $saveData['client_time'] = $this->headerData['client_time'];
            }
            //IP地址
            $saveData['ip_address'] = $request->ip();
            //记录进数据库
            $userLogModel=new UserLog();
            $userLogModel->save($saveData);
        } catch (\Exception $e) {
            //捕捉到错误放行，不作处理，后面可以记录log
        }
        //记录日志----end
        return $response;
    }

    /**
     * @param $str
     * @return array
     * Description:解密返回值
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-10-12 10:22
     */
    private function decodeData($str){
        $decodeRes=\util\Aes::getInstance()->aes128cbcHexDecrypt($str);
        $decodeArr=[];
        if (!empty($decodeRes)) {
            $decodeArr = json_decode($decodeRes, true);
        }
        return $decodeArr;
    }

    /**
     * @return array
     * Description:获取用户
     * User: 孙春晓
     * Date: 2019-03-01
     * Time: 18:06
     */
    private function getUser()
    {
        $data = Request::post('code', '', 'string');
        if (!empty($data)) {
            $postDataResult = \util\Aes::getInstance()->aes128cbcHexDecrypt($data);
            if (!empty($postDataResult)) {
                $postDataArr = json_decode($postDataResult, true);
                $this->postData = $postDataArr;
            } else {
                return [
                    'code' => -10000,
                    'msg' => '解密数据失败！',
                    'data' => [],
                ];
            }
        }
        $headData = Request::header('globalinfo');
        if (!empty($headData)) {
            $this->headerData = json_decode($headData, true);
        }
        if (!empty($this->headerData['token'])) {
            $userService = app('app\common\repository\UserRepository');
            $result = $userService->getUserByToken($this->headerData['token']);
            return [
                'code' => $result['status'],
                'msg' => $result['msg'],
                'data' => $result['data'],
            ];
        } else {
            return [
                'code' => -10020,
                'msg' => '请您登录！',
                'data' => [],
            ];
        }
    }

    /**
     * @return array
     * Description:获取公共参数信息
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-10-15 15:04
     */
    private function getHeadData(){
        $data = Request::post('code', '', 'string');
        if (!empty($data)) {
            $postDataResult = \util\Aes::getInstance()->aes128cbcHexDecrypt($data);
            if (!empty($postDataResult)) {
                $postDataArr = json_decode($postDataResult, true);
                $this->postData = $postDataArr;
            } else {
                return [
                    'code' => -10000,
                    'msg' => '解密数据失败！',
                    'data' => [],
                ];
            }
        }
        $headData = Request::header('global_info');
        if (!empty($headData)) {
            $this->headerData = json_decode($headData, true);
        }
    }
}