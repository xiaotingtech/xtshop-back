<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'ProductDetail'=>['app\common\listener\ProductInfo'],
        'ProductBuy'=>['app\common\listener\ProductBuy'],
        'OrderManageLog'=>['app\common\listener\OrderManageRecord'],
    ],

    'subscribe' => [
    ],
];
