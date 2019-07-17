<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/3/26 0026
 * Time: 10:22
 */

namespace wchat;

class Recharge extends Miniprogarampage
{
	private $money = 0;

	private $orderNo;

	private $data = [];

	private $transfers = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
	private $unifiedorder = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

	/**
	 * @param int $money
	 * @param string $orderNo
	 * @param string $openId
	 * @return array|mixed|Result
	 * @throws
	 */
	public function recharge(int $money, string $orderNo, $openId = '')
	{
		if ($money < 0) {
			return new Result(['code' => 500, 'message' => '充值金额不能小于0.']);
		}
		$this->money = $money;
		$this->orderNo = $orderNo;
		$this->data['openid'] = $openId;

		$this->request->setCallback([$this, 'payCallback']);
		return $this->send($this->unifiedorder, $this->builder());
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
		}
		$return = [];
		$_sign = Help::sign($data, $this->config->getKey(), $this->config->getSignType());
		if (!isset($sign) || $sign != $_sign) {
			$return['code'] = -1;
			$return['message'] = $data['return_msg'] ?? '返回数据签名验证失败';
		} else {
			$return['code'] = 0;
			$return['data'] = $data;
			$return['data']['postBody'] = $body;
			if ($data['return_code'] == 'FAIL') {
				$return['code'] = -1;
				$return['message'] = $data['return_msg'];
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
			'appid' => $this->config->getAppid(),
			'mch_id' => $this->config->getMchId(),
			'nonce_str' => Help::random(32),
			'body' => $this->config->getBody(),
			'out_trade_no' => $this->orderNo,
			'total_fee' => $this->money,
			'sign_type' => $this->config->getSignType(),
			'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
			'notify_url' => $this->config->getNotifyUrl(),
			'trade_type' => $this->config->getTradeType(),
		];

		$data = array_merge($data, $this->data);

		$key = $this->config->getKey();
		$sign_type = $this->config->getSignType();

		$data['sign'] = Help::sign($data, $key, $sign_type);
		return Help::toXml($data);
	}

	/**
	 * @param $money
	 * @param $openid
	 * @param $order
	 * @param $desc
	 * @return Result
	 * @throws
	 *
	 * 提现
	 */
	public function cashWithdrawal($money, $openid, $order, $desc = '零钱提现')
	{
		$array = [
			'nonce_str' => Help::random(32),
			'partner_trade_no' => $order,
			'mchid' => $this->config->getMchId(),
			'mch_appid' => $this->config->getAppid(),
			'openid' => $openid,
			'check_name' => 'NO_CHECK',
			'amount' => $money * 100,
			'spbill_create_ip' => $this->config->getRemoteAddr(),
			'desc' => $desc,
		];

		$key = $this->config->getKey();
		$sign_type = $this->config->getSignType();
		$array['sign'] = Help::sign($array, $key, $sign_type);

		$this->request->setCallback([$this, 'txCallback']);
		return $this->send($this->transfers, Help::toXml($array));
	}

	/**
	 * @param $url
	 * @param $data
	 * @return array|mixed|Result
	 * @throws \Exception
	 */
	private function send($url, $data)
	{
		$this->request->setIsSSL(true);
		$this->request->setMethod(WxClient::POST);
		$this->request->addHeader('Content-Type', 'text/xml');
		return $this->request->send($url, $data);
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
