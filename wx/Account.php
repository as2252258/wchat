<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/4/19 0019
 * Time: 16:12
 */
namespace wchat;
class Account extends Base
{
	
	/**
	 * @param $code
	 * @return Result
	 */
	public function login($code)
	{
		return WxClient::get('sns/jscode2session', [
			'appid' => $this->appid,
			'secret' => $this->appsecret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		], null, ['Content-Type' => 'text/xml']);
	}
	
	
}
