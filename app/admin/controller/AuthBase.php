<?php
/**
 * Created by xtshop
 * Class AuthBase
 * Description:后台权限基类
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 16:23
 */
namespace app\admin\controller;

use think\App;
use think\facade\Request;
use think\facade\View;
use util\Aes;
class AuthBase extends Base
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
        $this->postData=$this->request->post();
        $this->headerData=$this->request->header();
    }

    /**
     * @return array
     * Description:获取用户方法
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-07 15:08
     */
    public function getUser()
    {
        if(!empty($this->headerData['authorization'])) {
            $userService=app('app\common\repository\UserRepository');
            $result=$userService->getUserByToken($this->headerData['authorization']);
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

    /**
     * @param $name
     * @param $value
     * Description:为了兼容之前的代码
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 11:39
     */
    public function assign($name,$value){
        View::assign($name,$value);
    }

    /**
     * @param $name
     * @param $data
     * @return string
     * @throws \Exception
     * Description:兼容代码
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 12:06
     */
    public function fetch($name,$data=[]){
        return View::fetch($name,$data);
    }

    /**
     * @param $user
     * @return array
     * Description:根据权限和角色进行权限分隔为菜单设置
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 16:24
     */
    protected function  by_power_menu($user){
        if(empty($user)){
            return ['menu'=>[],'breadcrumbs'=>[],'current'=>[]];
        }
        $url =$this->request->controller().'/'.$this->request->action();
        $menu =get_menu_all($user);
        $bro =[];
        $current =['title'=>'首页'];
        $new_menu =[];
        $url=strtolower($url);
        foreach ($menu as $mk=>$m){
            $m['s'] =0;
            if ($m['is_menu']==1){
                $new_child =[];
                foreach ($m['_child'] as $k=>$value){
                    $value['a']  =0;
                    if (strtolower($value['url'])==$url){
                        $m['s'] =1;
                        $value['a']  =1;
                        $bro=[['title'=>$m['title'],'url'=>''],['title'=>$value['title'],'url'=>$value['url']]];
                        $current =['title'=>$value['title']];
                    }
                    if ($value['is_menu']==1){
                        $new_child[] =$value;
                    }
                }
                $m['_child'] =$new_child;
                $new_menu[]=$m;
            }
        }
        return ['menu'=>$new_menu,'breadcrumbs'=>$bro,'current'=>$current];
    }
}