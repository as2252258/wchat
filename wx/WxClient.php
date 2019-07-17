<?php

namespace wchat;

use Swoole\Coroutine\Http\Client;

class WxClient
{
	private $host = 'api.weixin.qq.com';

	private $header = [];

	private $callback = null;
	private $method = 'get';

	private $url = '';
	private $isSSL = false;

	const POST = 'post';
	const GET = 'get';
	const PUT = 'put';
	const DELETE = 'delete';
	const OPTIONS = 'option';

	/**
	 * @param string $host
	 */
	public function setHost(string $host)
	{
		$this->host = $host;
	}

	/**
	 * @param array $header
	 */
	public function setHeader(array $header)
	{
		$this->header = $header;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addHeader($key, $value)
	{
		$this->header[$key] = $value;
	}

	/**
	 * @param null $callback
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @param string $method
	 */
	public function setMethod(string $method)
	{
		$this->method = $method;
	}

	/**
	 * @param string $url
	 */
	public function setUrl(string $url)
	{
		$this->url = $url;
	}

	/**
	 * @param bool $isSSL
	 */
	public function setIsSSL(bool $isSSL)
	{
		$this->isSSL = $isSSL;
		if ($this->isSSL) {
			$ssl = Wx::getMiniProGaRamPage()->getConfig();
			$this->header['ssl_cert_file'] = $ssl->getSslCert();
			$this->header['ssl_key_file'] = $ssl->getSslKey();
		}
	}


	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	private function request($url, $data = [])
	{
		if (
			strpos($url, 'http://') === 0 ||
			strpos($url, 'https://') === 0
		) {
			return $this->curl($url, $data);
		}

		if (function_exists('getIsCli') && getIsCli()) {
			return $this->coroutine($url, $data);
		}

		if ($this->isSSL) {
			return $this->curl('https://' . $this->host . '/' . $url, $data);
		} else {
			return $this->curl('http://' . $this->host . '/' . $url, $data);
		}
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 *
	 * 使用swoole协程方式请求
	 */
	private function coroutine($url, $data = [])
	{
		$_data = $this->paramEncode($data);
		if ($this->method == 'get' && is_array($_data)) {
			$url .= '?' . http_build_query($_data);
		}

		$client = $this->getClient($this->getHostPort(), $url, $data);
		if ($client->statusCode < 0) {
			throw new \Exception('连接错误!');
		}
		$body = $client->body;
		$client->close();

		return $this->structure($body, $_data);
	}

	/**
	 * @return mixed
	 */
	private function getHostIp()
	{
		return array_shift(\Co::getAddrInfo($this->host));
	}

	/**
	 * @return int
	 */
	private function getHostPort()
	{
		$port = 80;
		if ($this->isSSL) $port = 443;
		return $port;
	}

	/**
	 * @param $host
	 * @param $port
	 * @param $url
	 * @param $data
	 * @return Client
	 */
	private function getClient($port, $url, $data)
	{
		$host = $this->getHostIp();

		$client = new Client($host, $port, $this->isSSL);
		if (!empty($this->header)) {
			$client->setHeaders($this->header);
		}

		switch (strtolower($this->method)) {
			case self::POST:
				$client->post($url, $data);
				break;
			default:
				$client->get($url);
		}
		return $client;
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 */
	private function curl($url, $data = [])
	{
		$data = $this->paramEncode($data, self::POST);
		$ch = $this->structureCurlRequest($url, $data);

		if ($this->method != self::GET) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else if ($this->method == self::POST) {
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
		$output = curl_exec($ch);
		if ($output === FALSE) {
			return new Result(['code' => 500, 'message' => curl_error($ch)]);
		}
		curl_close($ch);

		return $this->structure($output, $data);
	}

	/**
	 * @param $url
	 * @param $_data
	 * @return resource
	 */
	private function structureCurlRequest($url, $_data)
	{
		$ch = curl_init();
		if ($this->method == self::GET) {
			$url = $url . '?' . $_data;
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);// 超时设置
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		if (!empty($this->header)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
		}

		if ($this->isSSL) {
			curl_setopt($ch, CURLOPT_SSLCERT, $this->header['ssl_cert_file']);
			curl_setopt($ch, CURLOPT_SSLKEY, $this->header['ssl_key_file']);
		}

		curl_setopt($ch, CURLOPT_NOBODY, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);// 超时设置
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//返回内容
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);// 跟踪重定向
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

		return $ch;
	}

	/**
	 * @param $body
	 * @param $_data
	 * @return array|mixed|Result
	 * 构建返回体
	 */
	private function structure($body, $_data)
	{
		$this->setIsSSL(false);
		$this->setHeaders(null);

		if ($this->callback !== NULL) {
			$result = call_user_func($this->callback, $body, $_data);
		} else {
			$result = $this->formatResponseBody($body);
		}

		$this->setCallback(null);
		if (!is_array($result)) {
			return $result;
		}

		return new Result($result);
	}

	/**
	 * @param $body
	 * @return array|Result
	 */
	private function formatResponseBody($body)
	{
		$result = [];
		if (is_null($results = json_decode($body, TRUE))) {
			$data = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
			$results = json_decode(json_encode($data), TRUE);
		}
		if (!is_array($results)) {
			$result = new Result(['code' => 505, 'message' => '服务器返回体错误!']);
		} else if (isset($results['errcode'])) {
			$result['code'] = $results['errcode'];
			$result['message'] = $results['errmsg'];
		} else {
			$result['code'] = 0;
			$result['message'] = 'system success.';
			$result['data'] = $results;
		}
		return $result;
	}

	/**
	 * @param        $arr
	 * @param string $pushType
	 *
	 * @return array|string
	 * 将请求参数进行编码
	 */
	private function paramEncode($arr, $pushType = 'post')
	{
		if (!is_array($arr)) {
			return $arr;
		}
		$_tmp = [];
		foreach ($arr as $Key => $val) {
			$_tmp[$Key] = $val;
		}

		return ($pushType == 'post' ? $_tmp : http_build_query($_tmp));
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws
	 */
	public function post($url, $data = [])
	{
		$this->setMethod(self::POST);
		return $this->request($url, $data);
	}


	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws
	 */
	public function put($url, $data = [])
	{
		$this->setMethod(self::PUT);
		return $this->request($url, $data);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws
	 */
	public function get($url, $data = [])
	{
		$this->setMethod(self::GET);
		return $this->request($url, $data);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function option($url, $data = [])
	{
		$this->setMethod(self::OPTIONS);
		return $this->request($url, $data);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function delete($url, $data = [])
	{
		$this->setMethod(self::DELETE);
		return $this->request($url, $data);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function send($url, $data = [])
	{
		return $this->request($url, $data);
	}

	/**
	 * @param array $headers
	 * @return array
	 */
	public function setHeaders(array $headers)
	{
		if (empty($headers)) {
			return [];
		}
		foreach ($headers as $key => $val) {
			$this->header[] = $key . ':' . $val;
		}
		return $this->header;
	}
}
