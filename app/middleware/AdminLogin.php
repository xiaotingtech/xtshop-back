<?php
/**
 * Created by xtshop.
 * Description:后台登陆中间件
 * Author: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020/7/22
 * Time: 10:59
 */
namespace app\middleware;

class AdminLogin extends BaseMiddle
{
    public function __construct()
    {
        parent::__construct();
    }

    public function handle($request, \Closure $next)
    {
        $user=session('user');
        if(empty($user)){
            return redirect('/admin/user/login');
        }
        return $next($request);
    }
}