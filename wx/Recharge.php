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
        $_this = $this;
        if ($money < 0) {
            return new Result(['code' => 500, 'message' => '充值金额不能小于0.']);
        }
        $this->money = $money;
        $this->orderNo = $orderNo;
        $this->data['openid'] = $openId;
        return Http::post($this->createPayUrl(), $this->builder(),
            function ($result, $body) use ($_this) {
                $data = $_this->toArray($result);
                if (isset($data['sign'])) {
                    $sign = $data['sign'];
                    unset($data['sign']);
                    $_sign = $_this->sign($data);
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
            }, ['Content-Type' => 'text/xml']
        );
    }


    /**
     * @return string
     */
    protected function builder()
    {
        $data = [
            'appid' => $this->app_id,
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

        $data['sign'] = $this->sign($data);

        return $this->toXml($data);
    }

    private function createPayUrl()
    {
        return $this->mch_host . '/pay/unifiedorder';
    }

    /**
     * @param $money
     * @param $openid
     * @param $order
     * @param $REMOTE_ADDR
     * @return Result
     *
     * 提现
     */
    public function tx($money, $openid, $order, $REMOTE_ADDR, $desc = NULL)
    {
        $array = [
            'nonce_str' => $this->random(32),
            'partner_trade_no' => $order,
            'mchid' => $this->mch_id,
            'mch_appid' => $this->app_id,
            'openid' => $openid,
            'check_name' => 'NO_CHECK',
            'amount' => $money * 100,
            'spbill_create_ip' => $REMOTE_ADDR,
            'desc' => $desc ?? '有大佬给你发红包啦.',
        ];
        
        $transfers = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        $array['sign'] = $this->sign($array);

        return Http::post($transfers, $this->toXml($array), function ($data) {
            $array = $this->toArray($data);
            if ($array['result_code'] != 'SUCCESS') {
                $data = ['code' => $array['err_code'], 'message' => $array['err_code_des']];
            } else {
                $data = ['code' => 0, 'message' => '支付成功'];
            }
            return new Result($data);
        }, NULL, [$this->ssl_cert, $this->ssl_key]);
    }

}