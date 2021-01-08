<?php
//支付通知处理
use think\facade\Route;
Route::group('pay', function () {
    //微信异步通知
    Route::any('wechatnotify', '\app\api\controller\Notify@wechatNotify');
});