<?php
use think\facade\Route;
Route::group('v1', function () {
    //用户
    Route::group('user', function () {
        Route::post('login', '\app\api\controller\User@login');
        Route::post('update', '\app\api\controller\User@updateWechatInfo');
        Route::post('phone', '\app\api\controller\User@updatePhone');//更新用户手机号
        Route::post('info', '\app\api\controller\User@info')->middleware(['app\middleware\NoLoginUserLog']);
        Route::post('collect', '\app\api\controller\User@collect');
        Route::post('collectlist', '\app\api\controller\User@collectList');
        Route::post('browselist', '\app\api\controller\User@browseList');
        Route::post('browseclear', '\app\api\controller\User@browseClear');
    });
    //商品
    Route::group('product', function () {
        Route::post('home', '\app\api\controller\Product@getHomeRecommendList');
        Route::post('detail', '\app\api\controller\Product@detail');
        Route::post('list', '\app\api\controller\Product@getCategoryList');
        Route::post('subjectrecommend', '\app\api\controller\Product@getSubjectRecommendList');
        Route::post('recommend', '\app\api\controller\Product@getRecommendList');
        Route::post('subject', '\app\api\controller\Product@getSubjectList');
    });
    //购物车
    Route::group('cart', function () {
        Route::post('add', '\app\api\controller\Cart@addCart');
        Route::post('inc', '\app\api\controller\Cart@addCart');
        Route::post('dec', '\app\api\controller\Cart@decCart');
        Route::post('del', '\app\api\controller\Cart@deleteCart');
        Route::post('clear', '\app\api\controller\Cart@clearCart');
        Route::post('list', '\app\api\controller\Cart@listCart');
    });
    //地址
    Route::group('address', function () {
        Route::post('list', '\app\api\controller\Address@listAddress');
        Route::post('save', '\app\api\controller\Address@saveAddress');
        Route::post('default', '\app\api\controller\Address@getDefaultAddress');
    });
    //分类
    Route::group('category', function () {
        Route::post('list', '\app\api\controller\Category@getList');
        Route::post('recommend', '\app\api\controller\Category@getRecommendList');
        Route::post('activity', '\app\api\controller\Category@getActivityList');
    });
    //专题
    Route::group('subject', function () {
        Route::post('recommend', '\app\api\controller\Subject@getRecommendList');
    });
    //轮播
    Route::group('banner', function () {
        Route::post('list', '\app\api\controller\Banner@getList');
    });
    //购买
    Route::group('pay', function () {
        Route::post('buy', '\app\api\controller\Pay@buy');
        Route::post('payback', '\app\api\controller\Pay@vipCallBackV1');
    });
    //订单
    Route::group('order', function () {
        Route::post('list', '\app\api\controller\Order@getList');
        Route::post('cancel', '\app\api\controller\Order@closeOrder');
        Route::post('confirm', '\app\api\controller\Order@confirmOrder');
        Route::post('detail', '\app\api\controller\Order@detail');
        Route::post('num', '\app\api\controller\Order@orderNum');
    });
    //订单
    Route::group('express', function () {
        Route::post('query', '\app\api\controller\Express@query');
    });
    Route::miss('\app\api\controller\Miss@index');
});