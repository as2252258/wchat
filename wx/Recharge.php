<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/3/26 0026
 * Time: 10:22
 */

namespace wchat;

class Recharge extends Base
{

	/** @var Recharge */
	private static $recharge;

	private $money = 0;

	private $orderNo;

	private $data = [];

	/**
	 * @param int $money
	 * @param string $orderNo
	 * @return bool|Result
	 */
	public function payment(int $money, string $orderNo, $openId = NULL)
	{
		if ($money < 0) {
			return new Result(['code' => 500, 'message' => '充值金额不能小于0.']);
		}
		$this->money = $money;
		$this->orderNo = $orderNo;
		$this->data['openid'] = $openId;

		$params = [
			$this->mch_host . '/pay/unifiedorder',
			$this->builder(),
			[$this, 'payCallback'],
			['Content-Type' => 'text/xml']
		];

		return WxClient::post(...$params);
	}


	/**
	 * @param $result
	 * @param $body
	 * @return array
	 */
	public function payCallback($result, $body)
	{
		$data = Help::toArray($result);
		if (isset($data['sign'])) {
			$sign = $data['sign'];
			unset($data['sign']);
			$_sign = Help::sign($data, $this->key, $this->sign_type);
		}
		$return = [];
		if (!isset($sign) || $sign != $_sign) {
			$return['code'] = -1;
			$return['message'] = $data['return_msg'] ?? '返回数据签名验证失败';
		} else {
			if ($data['return_code'] == 'FAIL') {
				$return['code'] = -1;
				$return['message'] = $data['return_msg'];
			} else {
				$return['code'] = 0;
				$return['data'] = $data;
				$return['data']['postBody'] = $body;
			}
		}
		return $return;
	}


	/**
	 * @return string
	 */
	protected function builder()
	{
		$data = [
			'appid' => $this->appid,
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->random(32),
			'body' => $this->body,
			'out_trade_no' => $this->orderNo,
			'total_fee' => $this->money,
			'sign_type' => $this->sign_type,
			'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
			'notify_url' => $this->notify_url,
			'trade_type' => $this->trade_type,
		];

		$data = array_merge($data, $this->data);

		$data['sign'] = Help::sign($data, $this->key, $this->sign_type);

		return Help::toXml($data);
	}

	/**
	 * @param $money
	 * @param $openid
	 * @param $order
	 * @param $REMOTE_ADDR
	 * @return Result
	 * @throws
	 *
	 * 提现
	 */
	public function tx($money, $openid, $order, $REMOTE_ADDR, $desc = NULL)
	{
		$array = [
			'nonce_str' => $this->random(32),
			'partner_trade_no' => $order,
			'mchid' => $this->mch_id,
			'mch_appid' => $this->appid,
			'openid' => $openid,
			'check_name' => 'NO_CHECK',
			'amount' => $money * 100,
			'spbill_create_ip' => $REMOTE_ADDR,
			'desc' => $desc ?? '有大佬给你发红包啦 . ',
		];

		$array['sign'] = Help::sign($array, $this->key, $this->sign_type);
		$prams = [
			$this->mch_host . '/mmpaymkttransfers/promotion/transfers',
			Help::toXml($array),
			[$this, 'txCallback'],
			NULL,
			[$this->ssl_cert, $this->ssl_key]
		];

		return WxClient::post(...$prams);
	}


	/**
	 * @param $data
	 * @return Result
	 * 提现回调
	 */
	public function txCallback($data)
	{
		$array = Help::toArray($data);
		if ($array['result_code'] != 'SUCCESS') {
			$data = ['code' => $array['err_code'], 'message' => $array['err_code_des']];
		} else {
			$data = ['code' => 0, 'message' => '支付成功'];
		}
		return new Result($data);
	}

}
