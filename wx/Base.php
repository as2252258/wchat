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
		return Http::post($url, $data, $callback);
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
	 * @param array $data
	 * @return string
	 */
	public static function toXml(array $data)
	{
		$xml = "<xml>";
		foreach ($data as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}


	/**
	 * @return string
	 */
	public function sign(array $array)
	{
		ksort($array, SORT_STRING);
		$string = '';
		foreach ($array as $key => $val) {
			if (empty($string)) {
				$string = $key . '=' . $val;
			} else {
				$string .= '&' . $key . '=' . $val;
			}
		}
		$string .= '&key=' . $this->key;

//		var_dump($string);

		if ($this->sign_type == 'MD5') {
			return strtoupper(md5($string));
		} else {
			return hash('sha256', $string);
		}
	}

	/**
	 * @param $xml
	 * @return mixed
	 */
	public function toArray($xml)
	{
		if (!is_null($json = json_decode($xml, TRUE))) {
			return $json;
		}
		if (is_array($json)) {
			return $json;
		}
		$data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return json_decode(json_encode($data), TRUE);
	}

	/**
	 * @return bool|mixed|string
	 * @throws \Exception
	 */
	protected function getAccessToken()
	{
		$data = Http::get('https://api.weixin.qq.com/cgi-bin/token', [
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


	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;

	public static function d($code)
	{
		$messages = [
			static::$OK => '',
			static::$IllegalAesKey => '',
			static::$IllegalIv => '',
			static::$IllegalBuffer => '',
			static::$DecodeBase64Error => '',
		];
		return $messages[$code] ?? static::$DecodeBase64Error;
	}

	/**
	 * @param $encryptedData
	 * @param $iv
	 * @param $data
	 * @return int
	 */
	public static function decode($encryptedData, $iv, $sessionKey, &$data, $appId = null)
	{
		if (strlen($sessionKey) != 24) {
			return self::$IllegalAesKey;
		}

		$aesKey = base64_decode($sessionKey);
		if (strlen($iv) != 24) {
			return self::$IllegalIv;
		}

		$aesIV = base64_decode($iv);

		$aesCipher = base64_decode($encryptedData);

		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$dataObj = json_decode($result);

		if ($dataObj == NULL) {
			return self::$IllegalBuffer;
		}
		if (empty($appId)) {
			$appId = static::$appid;
		}
		if ($dataObj->watermark->appid != $appId) {
			return self::$IllegalBuffer;
		}
		$data = $dataObj;
		return self::$OK;
	}
}
