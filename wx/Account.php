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
		return http::get('https://api.weixin.qq.com/sns/jscode2session', [
			'appid' => $this->app_id,
			'secret' => $this->app_secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		], null, ['Content-Type' => 'text/xml']);
	}
	
	
}