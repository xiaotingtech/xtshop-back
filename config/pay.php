<?php
//支付配置
return [
    'wechat_pay'=>[
        'appid'=>'',
        'app_id'=>'',
        'miniapp_id'=>'',
        'mch_id'=>'',
        'key'=>'',
        'notify_url'=>'https://api.xiaotingshop.cn/pay/wechatnotify',
        'cert_client'=>'',
        'cert_key'=>'',
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        //'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ],
    'server_ip'=>'120.55.44.235',
];