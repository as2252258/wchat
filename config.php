<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/3/26 0026
 * Time: 10:23
 */

defined('YII_ENV') or define('YII_ENV', 'dev');

spl_autoload_register(function ($className) {
	foreach (glob(__DIR__ . '/*') as $val) {
		if (!is_file($val)) {
			continue;
		}
		if (!in_array($val, get_included_files())) {
			require_once $val;
		}
	}
	return;
}, TRUE);

//
//if(strpos($_SERVER['SERVER_NAME'],'trade-test') != false){
//	return [
//		'app_id' => 'wx0a825f86f531c5d4',
//		'app_secret' => '79e644e1510da224a94ec7f61bf62565',
//		'key' => 'tZbiiLJG6ZKUo1jhy09M7ha4KIJiwskO',
//	];
//}else{
return [
	'app_id' => 'wxb4c6efd3cca6385b',
	'app_secret' => '9b1ed69033c4e33a928daeab31945184',
	'key' => 'tZbiiLJG6ZKUo1jhy09M7ha4KIJiwskO',
	'mch_id' => '1502867261',
	'encodingAesKey' => '3ZcJvXrncPjbUucxxmlqQRXbmazdklECBEdmLqYt0PG',
	'token' => '837ee5101638f7321553fc2809cffdb0',
	'notify_url' => 'https://game-slave-trade-api.zhuangb123.com/friend_geet/NewPay/actionNotify',
];
//}

//function myAutoload($className)
//{
//	$files = glob(__DIR__ . '/packet');
//	$explode = end(explode('\\', $className));
//	if (!empty($files)) {
//		foreach ($files as $key => $val) {
//			if (strpos($val, $explode) !== false) {
//				include $val;
//				break;
//			}
//		}
//	}
//}

//spl_autoload_register('autoload');
//return [
//	'app_id' => 'wx0a825f86f531c5d4',
//	'app_secret' => '79e644e1510da224a94ec7f61bf62565',
//	'key' => 'tZbiiLJG6ZKUo1jhy09M7ha4KIJiwskO',
//];
