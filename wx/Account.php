<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/4/19 0019
 * Time: 16:12
 */

namespace wchat;
class Account extends Miniprogarampage
{

	private $wxaqr = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=';
	private $getwxacode = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=';
	private $getwxacodeunlimit = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';

	private $savePath = __DIR__ . '/../../../';

	/**
	 * @param $path
	 */
	public function setSavePath($path)
	{
		$this->savePath = $path;
	}

	/**
	 * @param $code
	 * @return Result
	 */
	public function login($code)
	{
		$param['appid'] = $this->config->getAppid();
		$param['secret'] = $this->config->getAppsecret();
		$param['js_code'] = $code;
		$param['grant_type'] = 'authorization_code';

		$this->request->setMethod(WxClient::GET);
		$this->request->addHeader('Content-Type', 'text/xml');

		return $this->request->get('/sns/jscode2session', $param);
	}


	/**
	 * @param $path
	 * @param $width
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function createwxaqrcode($path, $width)
	{
		$url = $this->wxaqr . $this->getAccessToken();

		$sendBody['path'] = $path;
		$sendBody['width'] = $width;

		$this->request->setMethod(WxClient::POST);
		$this->request->setCallback([$this, 'saveByPath']);

		return $this->request->post($url, $sendBody);
	}


	/**
	 * @param $path
	 * @param $width
	 * @param bool $is_hyaline
	 * @param bool $auto_color
	 * @param string $line_color
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function getwxacode($path, $width, $is_hyaline = false, $auto_color = false, $line_color = '')
	{
		$sendBody['path'] = $path;
		$sendBody['width'] = $width;
		$sendBody['auto_color'] = $auto_color;
		$sendBody['is_hyaline'] = $is_hyaline;
		if ($auto_color) {
			$sendBody['line_color'] = $line_color;
		}

		$url = $this->getwxacode . $this->getAccessToken();

		$this->request->setMethod(WxClient::POST);
		$this->request->setCallback([$this, 'saveByPath']);
		return $this->request->post($url, $sendBody);
	}


	/**
	 * @param $path
	 * @param $width
	 * @param bool $is_hyaline
	 * @param bool $auto_color
	 * @param string $line_color
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	public function getwxacodeunlimit($path, $width, $is_hyaline = false, $auto_color = false, $line_color = '')
	{
		$sendBody['path'] = $path;
		$sendBody['width'] = $width;
		$sendBody['auto_color'] = $auto_color;
		$sendBody['is_hyaline'] = $is_hyaline;
		if ($auto_color) {
			$sendBody['line_color'] = $line_color;
		}

		$url = $this->getwxacodeunlimit . $this->getAccessToken();

		$this->request->setMethod(WxClient::POST);
		$this->request->setCallback([$this, 'saveByPath']);
		return $this->request->post($url, $sendBody);
	}

	/**
	 * @param mixed $body
	 * @return string
	 * @throws \Exception
	 */
	public function saveByPath($body)
	{
		if (!is_null($json = json_decode($body))) {
			throw new \Exception($json['errmsg'], $json['errcode']);
		}

		$push = md5_file($body) . '.png';
		file_put_contents($this->savePath . $push, $this->savePath);
		return $this->savePath . $push;
	}
}
