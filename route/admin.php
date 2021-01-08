<?php
use think\facade\Route;
Route::group('back', function () {
    //首页接口
    Route::group('index',function() {
        Route::post('index', '\app\admin\controller\Index@index');
        Route::post('order', '\app\admin\controller\Index@analysisOrder');
    });
    //用户登录获取信息相关
    Route::group('user',function() {
        Route::post('login', '\app\admin\controller\User@login');
        Route::post('info', '\app\admin\controller\User@info');
        Route::post('logout', '\app\admin\controller\User@logout');
    });
    //用户管理相关
    Route::group('member',function() {
        Route::get('list', '\app\admin\controller\Member@memberList');
        Route::post('update', '\app\admin\controller\Member@updateInfo');
        Route::post('add', '\app\admin\controller\Member@memberAdd');
        Route::post('status', '\app\admin\controller\Member@statusChange');
    });
    //订单管理相关
    Route::group('order',function() {
        Route::get('list', '\app\admin\controller\Order@orderList');
        Route::post('detail', '\app\admin\controller\Order@info');
        //修改收货人信息
        Route::post('receiverinfo', '\app\admin\controller\Order@receiverInfo');
        //发货
        Route::post('delivery', '\app\admin\controller\Order@delivery');
        //备注
        Route::post('remark', '\app\admin\controller\Order@remark');
    });
    //订单配置(从配置中单独拿出来方便理解)
    Route::group('ordersetting',function() {
        Route::get('info/:type', '\app\admin\controller\Setting@getInfo')
            ->pattern(['type' => '\d+']);
        Route::post('update', '\app\admin\controller\Setting@saveData');
    });
    //配置
    Route::group('setting',function() {
        Route::get('info/:type', '\app\admin\controller\Setting@getInfo')
            ->pattern(['type' => '\d+']);
        Route::post('update', '\app\admin\controller\Setting@saveData');
    });
    //轮播管理相关
    Route::group('banner',function() {
        Route::get('bannerlist', '\app\admin\controller\Banner@bannerList');
        Route::get('banneredit/:id', '\app\admin\controller\Banner@bannerEdit')->pattern(['id' => '\d+']);
        Route::post('banneredit', '\app\admin\controller\Banner@bannerAdd');
        Route::post('banneradd', '\app\admin\controller\Banner@bannerAdd');
    });
    //角色管理相关
    Route::group('role',function() {
        Route::get('list', '\app\admin\controller\Role@roleList');
        Route::get('listselect', '\app\admin\controller\Role@roleListSelect');
        Route::get('listmenu', '\app\admin\controller\Role@listMenu');
        Route::post('update', '\app\admin\controller\Role@roleAdd');
        Route::post('access', '\app\admin\controller\Role@setAccess');
        Route::post('create', '\app\admin\controller\Role@roleAdd');
        Route::post('status', '\app\admin\controller\Role@roleStatus');
        Route::post('delete', '\app\admin\controller\Role@roleDel');
        Route::get('member', '\app\admin\controller\Role@member');
        Route::post('alloc', '\app\admin\controller\Role@alloc');
    });
    //菜单节点管理相关
    Route::group('node',function() {
        Route::get('list', '\app\admin\controller\Node@nodeList');
        Route::get('tree', '\app\admin\controller\Node@nodeTree');
        Route::post('hidden', '\app\admin\controller\Node@hidden');
        Route::get('update', '\app\admin\controller\Node@nodeEdit');
        Route::post('update', '\app\admin\controller\Node@nodeAdd');
        Route::post('create', '\app\admin\controller\Node@nodeAdd');
        Route::post('delete/:id', '\app\admin\controller\Node@nodeDel')->pattern(['id' => '\d+']);
    });
    //商品管理相关
    Route::group('product',function() {
        Route::post('create', '\app\admin\controller\Product@productAdd');
        Route::post('update', '\app\admin\controller\Product@productAdd');
        Route::get('list', '\app\admin\controller\Product@productList');
        Route::get('sku', '\app\admin\controller\Product@productSku');
        Route::post('skuupdate', '\app\admin\controller\Product@productSkuUpdate');
        Route::get('productinfo/:id', '\app\admin\controller\Product@info')->pattern(['id' => '\d+']);
        Route::post('deletestatus', '\app\admin\controller\Product@deleteStatus');
        Route::post('publishstatus', '\app\admin\controller\Product@publishStatus');
        Route::post('recommendstatus', '\app\admin\controller\Product@recommendStatus');
        Route::post('newstatus', '\app\admin\controller\Product@newStatus');
        Route::get('productattr', '\app\admin\controller\ProductSkuTypeCategory@cateList');
        Route::post('productattradd', '\app\admin\controller\ProductSkuTypeCategory@cateAdd');
        Route::post('productattredit', '\app\admin\controller\ProductSkuTypeCategory@cateAdd');
        Route::get('productattrdel/:id', '\app\admin\controller\ProductSkuTypeCategory@cateDel')->pattern(['id' => '\d+']);
        //SKU管理
        Route::get('productskulist', '\app\admin\controller\ProductSkuType@typeList');
        Route::post('productskuadd', '\app\admin\controller\ProductSkuType@typeAdd');
        Route::post('productskuedit', '\app\admin\controller\ProductSkuType@typeAdd');
        Route::post('productskudel', '\app\admin\controller\ProductSkuType@typeDel');
        //SKU值管理
        Route::get('productskuvalue', '\app\admin\controller\ProductSkuTypeValue@valueList');
        Route::post('productskuvalueedit', '\app\admin\controller\ProductSkuTypeValue@valueAdd');
        Route::post('productskuvalueadd', '\app\admin\controller\ProductSkuTypeValue@valueAdd');
        Route::post('productskuvaluedel', '\app\admin\controller\ProductSkuTypeValue@valueDel');
        //商品分类
        Route::get('productcate/:parentId', '\app\admin\controller\ProductCategory@cateList')->pattern(['parentId' => '\d+']);
        Route::get('productcateinfo/:id', '\app\admin\controller\ProductCategory@info')->pattern(['id' => '\d+']);
        Route::post('productcateadd', '\app\admin\controller\ProductCategory@cateAdd');
        Route::post('updateproductcate', '\app\admin\controller\ProductCategory@cateAdd');
        Route::post('productcatenav', '\app\admin\controller\ProductCategory@navigation');
        Route::post('productcatestatus', '\app\admin\controller\ProductCategory@changeStatus');
        Route::post('productcatedel', '\app\admin\controller\ProductCategory@cateDel');
    });
    Route::group('subject',function() {
        Route::get('list/:parentId', '\app\admin\controller\Subject@cateList')->pattern(['parentId' => '\d+']);
        Route::get('info/:id', '\app\admin\controller\Subject@info')->pattern(['id' => '\d+']);
        Route::post('subjectadd', '\app\admin\controller\Subject@cateAdd');
        Route::post('subjectedit', '\app\admin\controller\Subject@cateAdd');
        Route::post('subjectnav', '\app\admin\controller\Subject@navigation');
        Route::post('subjectstatus', '\app\admin\controller\Subject@changeStatus');
        Route::get('tree', '\app\admin\controller\Subject@cateTreeList');
    });
    //商品分类
    Route::group('productcategory',function() {
        Route::get('tree', '\app\admin\controller\ProductCategory@cateTreeList');
    });
    //图片上传
    Route::group('file',function() {
        Route::post('upload', '\app\admin\controller\File@upload');
    });
    //快递查询接口
    Route::group('express',function() {
        Route::post('query', '\app\admin\controller\Express@query');
    });
    //Miss方法
    Route::miss('\app\admin\controller\Miss@index');
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => '*',
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
]);