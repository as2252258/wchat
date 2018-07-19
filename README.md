# wchat

#### 项目介绍
微信小程序接口

####配置项
    <?php
    $config = [
        'app_id' => '',
        'mch_id' => '',
        'device_info'' => 'WEB',
        'nonce_str' => '',
        'body'' => '!',
        'out_trade_no' => "",
        'total_fee' => 0,
        'spbill_create_ip' => "",
        'notify_url' => "",
        'trade_type'' => 'JSAPI',
        'sign_type'' => 'MD5',
        'mch_host'' => 'https://api.mch.weixin.qq.com',
        'app_secret' => '',
        'ssl_cert' => '',
        'ssl_key' => '',
        'key' => ''
    ]

$data = \wchat\Recharge::call( __DIR__.'/config.php');

其中$data返回 Recharge实例