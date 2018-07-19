<?php
/**
 * Created by PhpStorm.
 * User: qv
 * Date: 2018/7/19 0019
 * Time: 18:38
 */


spl_autoload_register(function ($className) {
    include __DIR__ . '/wx/' . str_replace('wchat\\', '', $className) . '.php';
});

$data = \wchat\Recharge::call( __DIR__.'/config.php');
var_dump($data);