# wchat

#### 项目介绍
微信小程序接口

####配置项

```php

$config = new \wchat\Config();
$config->setAppid('');
$config->setAppsecret('');
$config->setMchId('');
$config->setKey('');
$config->setRemoteAddr('');
$config->setDeviceInfo('');
$config->setAccessToken('');


$instance = \wchat\Wx::getMiniProGaRamPage();
$instance->setConfig($config);

$recharge = $instance->getRecharge();
$recharge->cashWithdrawal(1, 'xxx', 'ooo');
$recharge->recharge(1, '', '');

$account = $instance->getAccount();
$account->setSavePath('');
$account->login('');
$account->createwxaqrcode('pages/index/index',200);
$account->getwxacode('pages/index/index',150,true);
$account->getwxacodeunlimit('pages/index/index',150,true);


$message = $instance->getMessage();
$message->setOpenid('');
$message->sendCardNews('');

$template = $instance->getTemplate();
$template->setOpenId('');
$template->setFormId('');
$template->setTemplateId('');
$template->setPage('');
$template->sendTemplate();

```
