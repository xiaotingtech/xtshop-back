<?php
if (!function_exists('get_client_os')) {
    /**
     * @return string
     * Description:检测来源操作系统（默认Android、iPhone、iPad）
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 15:46
     */
    function get_client_os()
    {
        $os = 'android';
        $ua = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ""; //这里只进行IOS和Android两个操作系统的判断，其他操作系统原理一样
        if (strpos($ua, 'android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0
            $os = 'android'; //preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $version); echo 'Platform:Android OS_Version:' . $version[0];
        } elseif (strpos($ua, 'iPhone') !== false) {
            $os = 'iPhone';
        } elseif (strpos($ua, 'iPad') !== false) {
            $os = 'iPad';
        }
        return $os;
    }
}
if (!function_exists('get_real_url')) {
    /**
     * @param $url
     * @param bool $isOss
     * @return string
     * Description:完整OSS链接获取
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 16:27
     */
    function get_real_url($url)
    {
        $isOss=config('file.is_oss');
        if($isOss) {
            $url = ltrim($url,'/');
            return config('oss.OSS_URL') . $url;
        }else{
            return \think\facade\Request::domain().'/'.config('filesystem.disks.public.url').$url;
        }
    }
}
if (!function_exists('api_res')) {
    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param string[] $header
     * @param array $option
     * @param string $ik
     * @param string $iv
     * @return \think\response\Json
     * Description:接口返回数据方法
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-19 11:32
     */
    function api_res($code=1, $msg='', $data=[],$header=['Content-Type'=>'application/json','X-Powered-By'=>'sunnier'],$option=[],$ik='shop2020101front',$iv='sunnier202010198') {
        $request = \think\facade\Request::instance();
        $resultArray = array('status' => $code, 'msg' => $msg, 'data' => $data, 'server_time' => time());
        $aes = \util\Aes::getInstance();
        $paramArray = [];
        $isPrint = $request->param('is_print', 1); //是否打印请求和结果集数据
        $code = $request->param('code');
        //构建请求和返回结果集的明文数据
        if ($isPrint == config('response.is_print')) {
            $postData=[];
            if(!empty($code)) {
                $postDataResult = $aes->aes128cbcHexDecrypt($code,$iv,$ik);
                if (!empty($postDataResult)) {
                    $aesArray = json_decode($postDataResult, true);
                    $postData = $aesArray;
                }
            }
            $paramArray = array(
                'request' => array(
                    'request_url' => (!empty($_SERVER['REQUEST_SCHEME']) && !empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) ? ($_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : '',
                    'request_code' => $code,
                    'request_param' => !empty($code) ? $postData : $_REQUEST,
                ),
                'response' => $resultArray
            );
        }
        $aesResultData=$aes->aes128cbcEncrypt(json_encode($resultArray),$iv,$ik);
        if(!empty($aesResultData)){
            //构建返回的加密串
            $tempArray = array(
                'code' => $aesResultData,
                'val' => $paramArray
            );
            return json($tempArray, 200, $header, $option);
        }else{
            //失败的话返回空字符串
            $tempArray = array(
                'code' => '',
                'val' => $paramArray
            );
            return json($tempArray, 200, $header, $option);
        }
    }
}
if (!function_exists('sunnier_validate')) {
    /**
     * @param string $validateClass
     * @return \think\Validate
     * Description:自定义验证类
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 10:20
     */
    function sunnier_validate($validateClass='')
    {
        return validate($validateClass,[],false,false);
    }
}
if (!function_exists('show')) {
    /**
     * @param $status
     * @param $msg
     * @param array $data
     * Description:格式化数据，主要用于后台ajax返回
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:26
     */
    function show($status, $msg, $data = [])
    {
        return api_res($status,$msg,$data,['Access-Control-Allow-Origin'=>'*'],[],'shop20209086user','sunnier202098866');
    }
}
if (!function_exists('date_special_str')) {
    /**
     * @param string $str
     * @return string
     * Description:处理日期字符串
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:59
     */
    function date_special_str($str='')
    {
        if(empty($str)){
            return $str;
        }
        //拆出年月日然后按照固定组合返回
        $dataArr=explode('-',$str);
        $year=$dataArr[0];
        $month=$dataArr[1];
        $day=$dataArr[2];
        $result=$day.'/'.$month;
        return $result;
    }
}

if (!function_exists('is_coupon_code')) {
    /**
     * @param string $str
     * @return string
     * Description:判断是否是标准格式优惠券
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 19:08
     */
    function is_coupon_code($str='')
    {
        if(empty($str)){
            return false;
        }
        $str=strval($str);
        if(strlen($str)!=32){
            return false;
        }
        return true;
    }
}
if (!function_exists('password_check')) {
    /**
     * @param $password
     * @param $realVal
     * @return bool
     * Description:检查后台密码
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:26
     */
    function password_check($password,$realVal) {
        if(md5(sha1($password.config('auth.PASSWORD_SALT')))==$realVal){
            return true;
        }
        return false;
    }
}
if (!function_exists('get_menu_all')) {

    /**
     * @param array $user
     * @param bool $is_super
     * @return array|mixed
     * Description:获取所有菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:25
     */
    function get_menu_all($user = [], $is_super = false)
    {
        $nodeMenu = app('app\common\repository\NodeMenu');
        return $nodeMenu->get_by_user_role_menu($user, $is_super);
    }
}
if (!function_exists('deal_pics_url')) {

    /**
     * @param string $albumPics
     * @return array|mixed
     * Description:获取所有菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:25
     */
    function deal_pics_url($albumPics='')
    {
        $albumPicsArr=explode(',',$albumPics);
        $isOss=config('file.is_oss');
        if($isOss) {
            $ossUrl = rtrim(config('oss.OSS_URL'), '/');
        }else{
            $ossUrl = \think\facade\Request::domain().config('filesystem.disks.public.url');
        }
        foreach ($albumPicsArr as $ak=>$av){
            $albumPicsArr[$ak]=$ossUrl.$av;
        }
        return implode(',',$albumPicsArr);
    }
}

if (!function_exists('filter_pics_url')) {

    /**
     * @param string $albumPics
     * @return array|mixed
     * Description:获取所有菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:25
     */
    function filter_pics_url($albumPics='')
    {
        $albumPicsArr=explode(',',$albumPics);
        $isOss=config('file.is_oss');
        if($isOss) {
            $ossUrl = rtrim(config('oss.OSS_URL'), '/');
        }else{
            $ossUrl = \think\facade\Request::domain().config('filesystem.disks.public.url');
        }
        foreach ($albumPicsArr as $ak=>$av){
            $albumPicsArr[$ak]=str_replace($ossUrl,'',$av);
        }
        return implode(',',$albumPicsArr);
    }
}