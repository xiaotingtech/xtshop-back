<?php
/**
 * Created by xtshop
 * Class Miss
 * Description:未找到控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 22:16
 */
namespace app\admin\controller;

class Miss extends Base
{
    /**
     * @return \think\response\Json
     * Description:未找到路由的公共位置
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:16
     */
    public function index(){
        return api_res(-10000,'未找到路由');
    }
}