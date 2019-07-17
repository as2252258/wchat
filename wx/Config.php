<?php


namespace wchat;


class Config
{

	/**
	 * @var string
	 *
	 * 小程序ID
	 */
	private $appid = '';


	/**
	 * @var string
	 *
	 * 商户号ID
	 */
	private $mch_id = '';

	/**
	 * @var string
	 *
	 * 设备号
	 */
	private $device_info = 'WEB';

	/**
	 * @var string
	 *
	 * 随机字符串
	 */
	private $nonce_str = '';

	/**
	 * @var string
	 *
	 * 商品简单描述
	 */
	private $body = '好友默契Pk充值!';

	/**
	 * @var string
	 *
	 * 商户订单号
	 */
	private $out_trade_no = "";

	/**
	 * @var int
	 *
	 * 金额
	 */
	private $total_fee = 0;

	/**
	 * @var string
	 *
	 * 终端IP
	 */
	private $spbill_create_ip = "";

	/**
	 * @var string
	 *
	 * 异步回调地址
	 */
	private $notify_url = "";

	/**
	 * @var string
	 *
	 * 交易类型
	 */
	private $trade_type = 'JSAPI';

	/**
	 * @var string
	 *
	 * 签名方式
	 */
	private $sign_type = 'MD5';

	/**
	 * @var string
	 *
	 * 商户接口地址
	 */
	private $mch_host = 'https://api.mch.weixin.qq.com';

	/**
	 * @var string
	 */
	private $appsecret = '';

	private $remote_addr = '127.0.0.1';

	private $ssl_cert = '';
	private $ssl_key = '';

	/**
	 * @var string
	 */
	private $key = '';
	private $access_token = '';

	/**
	 * @return string
	 */
	public function getAccessToken(): string
	{
		return $this->access_token;
	}

	/**
	 * @param string $access_token
	 */
	public function setAccessToken(string $access_token)
	{
		$this->access_token = $access_token;
	}

	/**
	 * @param string $remote_addr
	 */
	public function setRemoteAddr(string $remote_addr)
	{
		$this->remote_addr = $remote_addr;
	}

	/**
	 * @param string $appid
	 */
	public function setAppid(string $appid)
	{
		$this->appid = $appid;
	}

	/**
	 * @param string $mch_id
	 */
	public function setMchId(string $mch_id)
	{
		$this->mch_id = $mch_id;
	}

	/**
	 * @param string $device_info
	 */
	public function setDeviceInfo(string $device_info)
	{
		$this->device_info = $device_info;
	}

	/**
	 * @param string $nonce_str
	 */
	public function setNonceStr(string $nonce_str)
	{
		$this->nonce_str = $nonce_str;
	}

	/**
	 * @param string $body
	 */
	public function setBody(string $body)
	{
		$this->body = $body;
	}

	/**
	 * @param string $out_trade_no
	 */
	public function setOutTradeNo(string $out_trade_no)
	{
		$this->out_trade_no = $out_trade_no;
	}

	/**
	 * @param int $total_fee
	 */
	public function setTotalFee(int $total_fee)
	{
		$this->total_fee = $total_fee;
	}

	/**
	 * @param string $spbill_create_ip
	 */
	public function setSpbillCreateIp(string $spbill_create_ip)
	{
		$this->spbill_create_ip = $spbill_create_ip;
	}

	/**
	 * @param string $notify_url
	 */
	public function setNotifyUrl(string $notify_url)
	{
		$this->notify_url = $notify_url;
	}

	/**
	 * @param string $trade_type
	 */
	public function setTradeType(string $trade_type)
	{
		$this->trade_type = $trade_type;
	}

	/**
	 * @param string $sign_type
	 */
	public function setSignType(string $sign_type)
	{
		$this->sign_type = $sign_type;
	}

	/**
	 * @param string $mch_host
	 */
	public function setMchHost(string $mch_host)
	{
		$this->mch_host = $mch_host;
	}

	/**
	 * @param string $appsecret
	 */
	public function setAppsecret(string $appsecret)
	{
		$this->appsecret = $appsecret;
	}

	/**
	 * @param string $ssl_cert
	 */
	public function setSslCert(string $ssl_cert)
	{
		$this->ssl_cert = $ssl_cert;
	}

	/**
	 * @param string $ssl_key
	 */
	public function setSslKey(string $ssl_key)
	{
		$this->ssl_key = $ssl_key;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key)
	{
		$this->key = $key;
	}

	/**
	 * @return string
	 */
	public function getAppid(): string
	{
		return $this->appid;
	}

	/**
	 * @return string
	 */
	public function getMchId(): string
	{
		return $this->mch_id;
	}

	/**
	 * @return string
	 */
	public function getDeviceInfo(): string
	{
		return $this->device_info;
	}

	/**
	 * @return string
	 */
	public function getNonceStr(): string
	{
		return $this->nonce_str;
	}

	/**
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * @return string
	 */
	public function getOutTradeNo(): string
	{
		return $this->out_trade_no;
	}

	/**
	 * @return int
	 */
	public function getTotalFee(): int
	{
		return $this->total_fee;
	}

	/**
	 * @return string
	 */
	public function getSpbillCreateIp(): string
	{
		return $this->spbill_create_ip;
	}

	/**
	 * @return string
	 */
	public function getNotifyUrl(): string
	{
		return $this->notify_url;
	}

	/**
	 * @return string
	 */
	public function getTradeType(): string
	{
		return $this->trade_type;
	}

	/**
	 * @return string
	 */
	public function getSignType(): string
	{
		return $this->sign_type;
	}

	/**
	 * @return string
	 */
	public function getMchHost(): string
	{
		return $this->mch_host;
	}

	/**
	 * @return string
	 */
	public function getAppsecret(): string
	{
		return $this->appsecret;
	}

	/**
	 * @return string
	 */
	public function getRemoteAddr(): string
	{
		return $this->remote_addr;
	}

	/**
	 * @return string
	 */
	public function getSslCert(): string
	{
		return $this->ssl_cert;
	}

	/**
	 * @return string
	 */
	public function getSslKey(): string
	{
		return $this->ssl_key;
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

}
