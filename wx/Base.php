<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/3/26 0026
 * Time: 10:23
 */

namespace wchat;
abstract class Base
{

	/**
	 * @var string
	 *
	 * 小程序ID
	 */
	public $appid = '';


	/**
	 * @var string
	 *
	 * 商户号ID
	 */
	public $mch_id = '';

	/**
	 * @var string
	 *
	 * 设备号
	 */
	public $device_info = 'WEB';

	/**
	 * @var string
	 *
	 * 随机字符串
	 */
	public $nonce_str = '';

	/**
	 * @var string
	 *
	 * 商品简单描述
	 */
	public $body = '好友默契Pk充值!';

	/**
	 * @var string
	 *
	 * 商户订单号
	 */
	public $out_trade_no = "";

	/**
	 * @var int
	 *
	 * 金额
	 */
	public $total_fee = 0;

	/**
	 * @var string
	 *
	 * 终端IP
	 */
	public $spbill_create_ip = "";

	/**
	 * @var string
	 *
	 * 异步回调地址
	 */
	public $notify_url = "https://game-slave-trade-api.zhuangb123.com/recharge/notify";

	/**
	 * @var string
	 *
	 * 交易类型
	 */
	public $trade_type = 'JSAPI';

	/**
	 * @var string
	 *
	 * 签名方式
	 */
	public $sign_type = 'MD5';

	/**
	 * @var string
	 *
	 * 商户接口地址
	 */
	public $mch_host = 'https://api.mch.weixin.qq.com';

	/**
	 * @var string
	 */
	public $appsecret = '';

	public $ssl_cert = '';
	public $ssl_key = '';

	/**
	 * @var string
	 */
	public $key = '';

	/** @var static */
	protected static $base;

	/**
	 * @param $configPath
	 * @return static
	 * @throws \Exception
	 */
	public static function call($configPath)
	{
		if (!static::$base instanceof Base) {
			static::$base = new static();
		}
		$class = static::$base;
		$class->loadConfig($configPath);
		return $class;
	}


	/**
	 *
	 */
	public function loadConfig($config)
	{
		if (empty($config)) {
			return;
		}
		if (is_string($config)) {
			$config = require_once $config;
		}
		foreach ($config as $key => $val) {
			if (!property_exists($this, $key)) {
				continue;
			}
			$this->$key = $val;
		}
	}

	/**
	 * @param $url
	 * @param array $data
	 * @param callable|null $callback
	 * @return Result
	 * @throws
	 */
	public function push($url, $data = [], callable $callback = NULL)
	{
		return WxClient::post($url, $data, $callback);
	}

	/**
	 * @param int $length
	 * @return string
	 *
	 * 随机字符串
	 */
	public function random($length = 20)
	{
		$res = [];
		$str = 'abcdefghijklmnopqrstuvwxyz';
		$str .= strtoupper($str) . '1234567890';
		for ($i = 0; $i < $length; $i++) {
			$rand = substr($str, rand(0, strlen($str) - 2), 1);
			if (empty($rand)) {
				$rand = substr($str, strlen($str) - 3, 1);
			}
			array_push($res, $rand);
		}

		return $this->nonce_str = implode($res);
	}


	/**
	 * @return bool|mixed|string
	 * @throws \Exception
	 */
	protected function getAccessToken()
	{
		$data = WxClient::get('https://api.weixin.qq.com/cgi-bin/token', [
			'grant_type' => 'client_credential',
			'appid' => $this->appid,
			'secret' => $this->appsecret,
		]);

		if (!$data->isResultsOK()) {
			throw new \Exception($data->getMessage());
		}
		return $data->getData('access_token');
	}

	/**
	 * @param $data
	 * @return mixed
	 * @throws \Exception
	 */
	protected function buildResult($data, $body = NULL)
	{
		$data = $this->checkSign($data);
		if (!$data) {
			$return['code'] = -1;
			$return['message'] = '签名错误.';
		} else {
			if (isset($data['return_code'])) {
				if ($data['return_code'] == 'FAIL') {
					$return['code'] = -1;
					$return['message'] = $data['return_msg'];
				} else {
					$return['code'] = 0;
					$return['data'] = $data;
					$return['data']['postBody'] = $body;
				}
			} else {
				if ($data['errcode'] == 'FAIL') {
					$return['code'] = -1;
					$return['message'] = $data['errmsg'];
				} else {
					$return['code'] = 0;
					$return['data'] = $data;
					$return['data']['postBody'] = $body;
				}
			}
		}
		return $return;
	}

	/**
	 * @param $result
	 * @return mixed
	 * @throws \Exception
	 */
	protected function checkSign($result)
	{
		$data = $this->toArray($result);

		if (!isset($data['sign'])) {
			return $data;
		}

		$sign = $data['sign'];

		unset($data['sign']);

		$_sign = $this->sign($data);
		if ($sign != $_sign) {
			return FALSE;
		}
		return $data;
	}

}
