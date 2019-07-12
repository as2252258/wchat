<?php

namespace wchat;

use Swoole\Coroutine\Http\Client;

class Http
{
	private $url = 'api.weixin.qq.com';

	private $header = [];

	/**
	 * @param $url
	 * @param string $pushType
	 * @param array $data
	 * @param callable|NULL $callback
	 * @param bool $isSSL
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	private function request($url, $pushType = 'get', $data = [], callable $callback = NULL, $isSSL = FALSE)
	{
		if (
			strpos($url, 'http://') === 0 ||
			strpos($url, 'https://') === 0
		) {
			return $this->curl($url, $pushType, $data, $callback, $isSSL);
		}

		if (function_exists('getIsCli') && getIsCli()) {
			return $this->coroutine($url, $pushType, $data, $callback, $isSSL);
		}

		$url = 'https://' . $this->url . '/' . $url;

		return $this->curl($url, $pushType, $data, $callback, $isSSL);
	}

	/**
	 * @param $url
	 * @param string $type
	 * @param array $data
	 * @param callable|NULL $callback
	 * @param bool $isSSL
	 * @return array|mixed|Result
	 * @throws \Exception
	 *
	 * 使用swoole协程方式请求
	 */
	private function coroutine($url, $type = 'get', $data = [], callable $callback = NULL, $isSSL = FALSE)
	{
		$_data = $this->paramEncode($data);
		if ($type == 'get' && is_array($_data)) {
			$url .= '?' . http_build_query($_data);
		}

		$host = \Co::getAddrInfo($this->url);

		$clientInfo = [array_shift($host), 443, TRUE];

		if ($isSSL && is_array($isSSL)) {
			$this->header['ssl_cert_file'] = $isSSL[0];
			$this->header['ssl_key_file'] = $isSSL[1];
		}

		$cli = new Client(...$clientInfo);
		if (!empty($this->header)) {
			$cli->setHeaders($this->header);
		}
		strtolower($type) == 'get' ? $cli->get($url) : $cli->post($url, $data);

		if ($cli->statusCode < 0) {
			throw new \Exception('连接错误!');
		}
		$body = $cli->body;
		$cli->close();
		return $this->build($body, $callback, $_data);
	}

	/**
	 * @param $url
	 * @param string $type
	 * @param array $data
	 * @param callable|NULL $callback
	 * @param bool $isSSL
	 * @return array|mixed|Result
	 */
	private function curl($url, $type = 'get', $data = [], callable $callback = NULL, $isSSL = FALSE)
	{
		$_data = $this->paramEncode($data);
		if (is_array($_data)) $_data = http_build_query($_data);

		if ($type == 'get') $url .= '?' . $_data;

		$ch = $this->buildCurl($url, $isSSL);
		switch (strtolower($type)) {
			case 'post':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
				break;
			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
				break;
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
				break;
			default:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		}
		$output = curl_exec($ch);
		if ($output === FALSE) {
			return new Result(['code' => 500, 'message' => curl_error($ch)]);
		}
		curl_close($ch);

		return $this->build($output, $callback, $_data);
	}

	/**
	 * @param $url
	 * @param $isSSL
	 * @return resource
	 */
	private function buildCurl($url, $isSSL)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);// 超时设置
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		if (!empty($this->header)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
		}
		if ($isSSL && is_array($isSSL)) {
			curl_setopt($ch, CURLOPT_SSLCERT, $isSSL[0]);
			curl_setopt($ch, CURLOPT_SSLKEY, $isSSL[1]);
		}
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);// 超时设置
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//返回内容
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);// 跟踪重定向
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

		return $ch;
	}

	private function build($body, $callback, $_data)
	{
		$result = [];
		if ($callback !== NULL) {
			return call_user_func($callback, $body, $_data);
		}
		if (is_null($results = json_decode($body, TRUE))) {
			$data = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
			$results = json_decode(json_encode($data), TRUE);
		}
		if (!is_array($results)) {
			return new Result(['code' => 505, 'message' => '服务器返回体错误!']);
		}
		if (isset($results['errcode'])) {
			$result['code'] = $results['errcode'];
			$result['message'] = $results['errmsg'];
		} else {
			$result['code'] = 0;
			$result['message'] = 'system success.';
			$result['data'] = $results;
		}
		if (!is_array($result)) {
			return $result;
		}
		return new Result($result);
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
	 * @param callable|NULL $callback
	 * @param array|NULL $header
	 * @param bool $isSSl
	 * @return array|mixed|Result
	 * @throws
	 */
	public static function post($url, $data = [], callable $callback = NULL, array $header = NULL, $isSSl = FALSE)
	{
		static $_class = NULL;
		if ($_class == NULL) $_class = new Http();
		if (!empty($header)) $_class->setHeaders($header);
		return $_class->request($url, 'post', $data, $callback, $isSSl);
	}


	/**
	 * @param $url
	 * @param array $data
	 * @param callable|NULL $callback
	 * @param array|NULL $header
	 * @param bool $isSSl
	 * @return array|mixed|Result
	 * @throws
	 */
	public static function put($url, $data = [], callable $callback = NULL, array $header = NULL, $isSSl = FALSE)
	{
		static $_class = NULL;
		if ($_class == NULL) $_class = new Http();
		if (!empty($header)) $_class->setHeaders($header);
		return $_class->request($url, 'put', $data, $callback, $isSSl);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @param callable|NULL $callback
	 * @param array $header
	 * @return array|mixed|Result
	 * @throws
	 */
	public static function get($url, $data = [], callable $callback = NULL, $header = [])
	{
		static $_class = NULL;
		if ($_class == NULL) $_class = new Http();
		if (!empty($header)) $_class->setHeaders($header);
		return $_class->request($url, 'get', $data, $callback);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @param array $header
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public static function option($url, $data = [], $header = [])
	{
		static $_class = NULL;
		if ($_class == NULL) $_class = new Http();
		if (!empty($header)) $_class->setHeaders($header);
		return $_class->request($url, 'option', $data);
	}

	/**
	 * @param $url
	 * @param array $data
	 * @param array $header
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public static function delete($url, $data = [], $header = [])
	{
		static $_class = NULL;
		if ($_class == NULL) $_class = new Http();
		if (!empty($header)) $_class->setHeaders($header);
		return $_class->request($url, 'delete', $data);
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
