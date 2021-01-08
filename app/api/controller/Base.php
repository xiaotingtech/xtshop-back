<?php
/**
 * Created by xtshop
 * Class Base
 * Description:API控制器基类
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 21:48
 */
namespace app\api\controller;

use think\App;
use think\facade\Request;
use util\Aes;
use app\BaseController;
class Base extends BaseController
{
    //请求对象
    protected $request=null;

    //用户数据
    protected $user=[];

    //传输数据
    protected $postData=[];

    //公共参数中数据
    protected $headerData=[];

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    protected function initialize()
    {
        parent::initialize();
        $this->request=Request::instance();
        if(!$this->request->isPost()){
            header('Content-Type:application/json');
            header('X-Powered-By:sunnier');
            echo $this->apiRes(-10023,'请求方式不正确',[]);die;
        }
        $data=$this->request->post('code','','string');
        if(!empty($data)) {
            $postDataResult =Aes::getInstance()->aes128cbcHexDecrypt($data);
            if(!empty($postDataResult)){
                $postDataArr=json_decode($postDataResult,true);
                $this->postData=$postDataArr;
            }else{
                header('Content-Type:application/json');
                header('X-Powered-By:sunnier');
                echo $this->apiRes(-10000,'解密传参失败');die;
            }
        }
        $headerCodeData=$this->request->header('globalinfo');
        if(!empty($headerCodeData)){
            $headerDataResult =Aes::getInstance()->aes128cbcHexDecrypt($headerCodeData);
            if(!empty($headerDataResult)){
                $this->headerData=json_decode($headerDataResult,true);
            }else{
                header('Content-Type:application/json');
                header('X-Powered-By:sunnier');
                echo $this->apiRes(-10000,'解密header传参失败');die;
            }
        }
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * Description:返回json
     * User: 孙春晓
     * Date: 2019-03-01
     * Time: 10:17
     */
    protected function apiRes($code=1, $msg='', $data=[]){
        $resultArray = array('status' => $code, 'msg' => $msg, 'data' => $data, 'server_time' => time());
        $aes = Aes::getInstance();
        $paramArray = [];
        $isPrint = $this->request->param('is_print', 1); //是否打印请求和结果集数据
        $code = $this->request->param('code');
        //构建请求和返回结果集的明文数据
        if ($isPrint == config('response.is_print')) {
            $paramArray = array(
                'request' => array(
                    'request_url' => (!empty($_SERVER['REQUEST_SCHEME']) && !empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) ? ($_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : '',
                    'request_code' => $code,
                    'request_param' => !empty($this->postData) ? $this->postData : $_REQUEST,
                ),
                'response' => $resultArray
            );
        }
        $aesResultData=$aes->aes128cbcEncrypt(json_encode($resultArray));
        if(!empty($aesResultData)){
            //构建返回的加密串
            $tempArray = array(
                'code' => $aesResultData,
                'val' => $paramArray
            );
            echo json_encode($tempArray);
        }else{
            //失败的话返回空字符串
            $tempArray = array(
                'code' => '',
                'val' => $paramArray
            );
            echo json_encode($tempArray);
        }
    }

    /**
     * @return array
     * Description:获取用户方法
     * Author: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019/8/2
     * Time: 10:39
     */
    public function getUser()
    {
        if(!empty($this->headerData['token'])) {
            $userService=app('app\common\repository\UserRepository');
            $result=$userService->getUserByToken($this->headerData['token']);
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