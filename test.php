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


$config = new \wchat\Config();
$config->setAppid('');
$config->setAppsecret('');
$config->setMchId('');
$config->setKey('');
$config->setRemoteAddr('');
$config->


$instance = \wchat\Wx::getMiniProGaRamPage();
$instance->setConfig($config);

$recharge = $instance->getRecharge();
$recharge->cashWithdrawal(1, 'xxx', 'ooo');

