<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/4/8 0008
 * Time: 9:49
 */

namespace wchat;

class Template extends Base
{

    /**
     * @param $openId
     * @param $message
     * @param $form_id
     * @return Result
     * @throws \Exception
     *
     * 奴隶交易通知
     */
    public function sendTemplate(string $access,array $postBody)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access;
        $postBody = json_encode($postBody);
        return WxClient::post($url, $postBody, NULL, ['content-type' => 'application/json'])
            ->append('postBody', $postBody);
    }
}
