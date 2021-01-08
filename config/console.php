<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'cancel_product_order'=> 'app\common\command\CancelProductOrder',//取消订单
        'product_buy_analysis'=> 'app\common\command\ProductBuyAnalysis',//购买商品的统计
    ],
];
