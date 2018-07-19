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
