<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2018/4/8 0008
 * Time: 9:49
 */

namespace wchat;

class Template extends Miniprogarampage
{

	private $keywords = [];
	private $templateId = '';
	private $formId = '';
	private $openId = '';
	private $defaultUrl = '';
	private $page = 'pages/index/index';
	private $emphasis_keyword = '';

	private $sendUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send';


	/**
	 * @param array $keywords
	 */
	public function setKeywords(array $keywords)
	{
		$this->keywords = $keywords;
	}

	/**
	 * @param string $templateId
	 */
	public function setTemplateId(string $templateId)
	{
		$this->templateId = $templateId;
	}

	/**
	 * @param string $formId
	 */
	public function setFormId(string $formId)
	{
		$this->formId = $formId;
	}

	/**
	 * @param string $openId
	 */
	public function setOpenId(string $openId)
	{
		$this->openId = $openId;
	}

	/**
	 * @param string $defaultUrl
	 */
	public function setDefaultUrl(string $defaultUrl)
	{
		$this->defaultUrl = $defaultUrl;
	}

	/**
	 * @param string $page
	 */
	public function setPage(string $page)
	{
		$this->page = $page;
	}

	/**
	 * @param string $emphasis_keyword
	 */
	public function setEmphasisKeyword(string $emphasis_keyword)
	{
		$this->emphasis_keyword = $emphasis_keyword;
	}

	/**
	 * @param $index
	 * @param $context
	 * @param $color
	 */
	public function replaceKeyword($index, $context, $color = '')
	{
		if (empty($color)) {
			$color = '#000';
		}
		$this->keywords['keyword' . $index] = [
			'value' => $context,
			'color' => $color
		];
	}


	/**
	 * @param $color
	 * @param $context
	 */
	public function addKeyword($context, $color = null)
	{
		if (empty($color)) {
			$color = '#000';
		}
		$this->keywords['keyword' . count($this->keywords)] = [
			'value' => $context,
			'color' => $color
		];
	}

	/**
	 * @param string $access
	 * @return Result
	 * @throws \Exception
	 *
	 * 奴隶交易通知
	 */
	public function sendTemplate(string $access)
	{
		$url = $this->sendUrl . '?access_token=' . $access;

		$params = [
			"touser" => $this->openId,
			"template_id" => $this->templateId,
			"page" => $this->page,
			"form_id" => $this->formId,
			"data" => $this->keywords,
		];

		if (!empty($this->emphasis_keyword)) {
			$params['emphasis_keyword'] = $this->emphasis_keyword;
		}

		$this->request->setIsSSL(true);
		$this->request->addHeader('content-type', 'application/json');

		$result = $this->request->post($url, $params);
		$result->append('postBody', $params);

		return $result;
	}
}
