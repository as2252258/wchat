<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/3/26 0026
 * Time: 10:23
 */

namespace wchat;

abstract class Miniprogarampage
{

	/** @var Config */
	protected $config;

	/** @var mixed $instance */
	private static $instance = null;

	/** @var WxClient */
	protected $request = null;

	/**
	 * Miniprogarampage constructor.
	 */
	private function __construct()
	{
		if (!($this->request instanceof WxClient)) {
			$this->request = new WxClient();
		}
		$this->request->setIsSSL(true);
	}

	/**
	 * @param Config $config
	 * @return mixed
	 */
	public static function getInstance(Config $config)
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}
		static::$instance->config = $config;
		return static::$instance;
	}


	/**
	 * @return bool|mixed|string
	 * @throws \Exception
	 */
	protected function getAccessToken()
	{
		$access = $this->config->getAccessToken();
		if (!empty($access)) {
			return $access;
		}
		$this->request->setMethod(WxClient::GET);
		$data = $this->request->get('/cgi-bin/token', [
			'grant_type' => 'client_credential',
			'appid' => $this->config->getAppid(),
			'secret' => $this->config->getAppsecret(),
		]);
		if (!$data->isResultsOK()) {
			throw new \Exception($data->getMessage());
		}
		$access = $data->getData('access_token');
		$this->config->setAccessToken($access);
		return $access;
	}

	/**
	 * @param $data
	 * @param $body
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
		$data = Help::toArray($result);

		if (!isset($data['sign'])) {
			return $data;
		}

		$sign = $data['sign'];

		unset($data['sign']);

		$key = $this->config->getKey();
		$sign_type = $this->config->getSignType();

		$_sign = Help::sign($data, $key, $sign_type);
		if ($sign != $_sign) {
			return FALSE;
		}
		return $data;
	}

}
