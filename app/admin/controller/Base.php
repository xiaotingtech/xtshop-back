<?php
namespace app\admin\controller;
/**
 * Created by xtshop.
 * Description:后台基类
 * User: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020-06-14
 * Time: 22:12
 */
use app\BaseController;
use think\App;
use think\facade\Request;
use util\Aes;
class Base extends BaseController
{
    //请求对象
    protected $request=null;

    //用户数据
    protected $user=[];

    //公共参数中数据
    protected $headerData=[];

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    protected function initialize()
    {
        parent::initialize();
        $this->request = Request::instance();
        $this->headerData = $this->request->header();
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * Description:返回数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:40
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
     * Description:获取用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:40
     */
    public function getUser()
    {
        if(!empty($this->headerData['authorization'])) {
            $userService=app('app\common\repository\UserRepository');
            $result=$userService->getBackUserByToken($this->headerData['authorization']);
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