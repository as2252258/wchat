<?php


namespace wchat;


class Message extends Miniprogarampage
{
	const TEXT = 0;
	const IMAGE = 1;
	const VOICE = 2;
	const NEWS = 3;
	const VIDEO = 4;
	const MUSIC = 5;
	const MINIPROGRAMPAGE = 6;
	const WXCARD = 7;

	private $openid = '';
	private $msgData = [];


	/**
	 * @param string $openid
	 */
	public function setOpenid(string $openid)
	{
		$this->openid = $openid;
		$this->msgData['touser'] = $openid;
	}

	/**
	 * @param string $content
	 * @return Result
	 * @throws \Exception
	 */
	public function sendTextNews(string $content)
	{
		$this->msgData['msgtype'] = 'text';
		$this->msgData['text[content]'] = $content;

		return $this->sendKefuMsg();
	}

	/**
	 * @param $media_id
	 * @return Result
	 * @throws \Exception
	 */
	public function sendImageNews(string $media_id)
	{
		$this->msgData['msgtype'] = 'image';
		$this->msgData['image[media_id]'] = $media_id;

		return $this->sendKefuMsg();
	}


	/**
	 * @param $media_id
	 * @return Result
	 * @throws \Exception
	 */
	public function sendVoiceNews(string $media_id)
	{
		$this->msgData['msgtype'] = 'voice';
		$this->msgData['voice[media_id]'] = $media_id;

		return $this->sendKefuMsg();
	}

	/**
	 * @param $media_id
	 * @return Result
	 * @throws \Exception
	 */
	public function sendMpNewsNews(string $media_id)
	{
		$this->msgData['msgtype'] = 'mpnews';
		$this->msgData['mpnews[media_id]'] = $media_id;

		return $this->sendKefuMsg();
	}


	/**
	 * @param string $title
	 * @param string $description
	 * @param string $url
	 * @param string $picurl
	 * @return Result
	 * @throws \Exception
	 */
	public function sendNewsNews(string $title, string $description, string $url, string $picurl)
	{
		$this->msgData['msgtype'] = 'news';
		$this->msgData['news[articles][0][title]'] = $title;
		$this->msgData['news[articles][0][description]'] = $description;
		$this->msgData['news[articles][0][url]'] = $url;
		$this->msgData['news[articles][0][picurl]'] = $picurl;

		return $this->sendKefuMsg();
	}


	/**
	 * @param string $title
	 * @return Result
	 * @throws \Exception
	 */
	public function sendCardNews(string $title)
	{
		$this->msgData['msgtype'] = 'wxcard';
		$this->msgData['wxcard[card_id]'] = $title;

		return $this->sendKefuMsg();
	}


	/**
	 * @param string $head_content
	 * @param string $tail_content
	 * @param array $menus
	 * @return Result
	 * @throws \Exception
	 */
	public function sendMenuNews(string $head_content, string $tail_content, array $menus = [])
	{
		$this->msgData['msgtype'] = 'msgmenu';
		$this->msgData['msgmenu[head_content]'] = $head_content;
		$this->msgData['msgmenu[tail_content]'] = $tail_content;

		if (empty($menus) || !is_array($menus) || count($menus) < 2) {
			throw new \Exception('菜单选项必须有2个');
		}

		foreach ($menus as $key => $val) {
			$this->addNewsMenu($val['id'], $val['name']);
		}

		return $this->sendKefuMsg();
	}

	private $index = 0;

	/**
	 * @param $id
	 * @param $menuName
	 * @return $this
	 */
	public function addNewsMenu($id, $menuName)
	{
		$this->msgData['msgmenu[list][' . $this->index . '][id]'] = $id;
		$this->msgData['msgmenu[list][' . $this->index . '][content]'] = $menuName;

		++$this->index;

		return $this;
	}

	/**
	 * @param $title
	 * @param $appid
	 * @param $pagepath
	 * @param $thumb_media_id
	 * @return Result
	 * @throws \Exception
	 */
	public function sendMiniprogrampageNews(string $title, string $appid, string $pagepath, string $thumb_media_id)
	{
		$this->msgData['msgtype'] = 'msgmenu';
		$this->msgData['miniprogrampage[title]'] = $title;
		$this->msgData['miniprogrampage[appid]'] = $appid;
		$this->msgData['miniprogrampage[pagepath]'] = $pagepath;
		$this->msgData['miniprogrampage[thumb_media_id]'] = $thumb_media_id;

		return $this->sendKefuMsg();
	}

	/**
	 * @param string $filePath
	 * @param string $type
	 * @param bool $isPermanent
	 * @param string $title
	 * @param string $introduction
	 * @return mixed
	 * @throws \Exception
	 */
	public function upload(string $filePath, string $type, $isPermanent = false, string $title = '', string $introduction = '')
	{
		if (!file_exists($filePath)) {
			throw new \Exception('文件不存在');
		}

		if (!in_array($type, ['image', 'voice', 'video', 'thumb'])) {
			throw new \Exception('暂不支持的文件类型');
		}

		$token = $this->getAccessToken();
		if ($isPermanent) {
			$url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$token}&type={$type}";
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		}

		$mime = mime_content_type($filePath);

		$real_path = new \CURLFile(realpath($filePath));

		$data = array("media" => $real_path, 'form-data[filename]' => $filePath, 'form-data[content-type]' => $mime);
		if ($isPermanent && $mime == 'video/mp3') {
			$data = ['media' => $real_path, 'description[title]' => $title, 'description[introduction]' => $introduction];
		}

		$this->request->setMethod(WxClient::POST);

		/** @var Result $body */
		$data = $this->request->post($url, $data);
		if (!$data->isResultsOK()) {
			throw new \Exception($data->getMessage());
		}

		return $data->getData();
	}

	/**
	 * @param $mime
	 * @throws \Exception
	 */
	private function checkExtinfo($mime)
	{
		switch (strtolower($mime)) {
			case 'image/bmp':
			case 'image/png':
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/gif':
				break;
			case 'mp3/wma/wav/amr':
				break;
			case 'mp4';
				break;
			case 'jpg';
				break;
			default:
				throw new \Exception('不支持的文件格式');
		}
	}

	/**
	 * @param $data
	 * @return Result
	 * @throws \Exception
	 */
	private function sendKefuMsg()
	{
		$data = json_encode($this->msgData, JSON_UNESCAPED_UNICODE);

		$url = '/cgi-bin/message/custom/send?access_token=' . $this->getAccessToken();
		$this->request->setMethod(WxClient::POST);

		/** @var Result $body */
		$body = $this->request->post($url, $data);

		if (!$body->isResultsOK()) {
			throw new \Exception($body->getMessage());
		}
		return $body;
	}
}
