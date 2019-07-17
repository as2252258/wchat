<?php


namespace wchat;


class Help extends Miniprogarampage
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;

	/**
	 * @param $encryptedData
	 * @param $iv
	 * @param $sessionKey
	 * @return object
	 * @throws
	 *
	 *  *    <li>-41001: encodingAesKey 非法</li>
	 *    <li>-41003: aes 解密失败</li>
	 *    <li>-41004: 解密后得到的buffer非法</li>
	 *    <li>-41005: base64加密失败</li>
	 *    <li>-41016: base64解密失败</li>
	 */
	public static function decode($encryptedData, $iv, $sessionKey)
	{
		$config = Wx::getMiniProGaRamPage()->getConfig();
		if (strlen($sessionKey) != 24) {
			throw new \Exception('encodingAesKey 非法', self::$IllegalAesKey);
		}

		$aesKey = base64_decode($sessionKey);
		if (strlen($iv) != 24) {
			throw new \Exception('base64解密失败', self::$IllegalIv);
		}

		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($encryptedData);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, OPENSSL_RAW_DATA, $aesIV);
		if ($result === false) {
			throw new \Exception('aes 解密失败', self::$IllegalBuffer);
		}

		$dataObj = json_decode($result);
		if ($dataObj->watermark->appid != $config->getAppid()) {
			throw new \Exception('aes 解密失败', self::$IllegalBuffer);
		}
		return $dataObj;
	}


	/**
	 * @param array $data
	 * @return string
	 */
	public static function toXml(array $data)
	{
		$xml = "<xml>";
		foreach ($data as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}


	/**
	 * @param $xml
	 * @return mixed
	 */
	public static function toArray($xml)
	{
		if (!is_null($json = json_decode($xml, TRUE))) {
			return $json;
		}
		if (is_array($json)) {
			return $json;
		}
		$data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return json_decode(json_encode($data), TRUE);
	}


	/**
	 * @param int $length
	 * @return string
	 *
	 * 随机字符串
	 */
	public static function random($length = 20)
	{
		$res = [];
		$str = 'abcdefghijklmnopqrstuvwxyz';
		$str .= strtoupper($str) . '1234567890';
		for ($i = 0; $i < $length; $i++) {
			$rand = substr($str, rand(0, strlen($str) - 2), 1);
			if (empty($rand)) {
				$rand = substr($str, strlen($str) - 3, 1);
			}
			array_push($res, $rand);
		}

		return implode($res);
	}

	/**
	 * @param array $array
	 * @param $key
	 * @param $type
	 * @return string
	 */
	public static function sign(array $array, $key, $type)
	{
		ksort($array, SORT_STRING);
		$string = '';
		foreach ($array as $key => $val) {
			if (empty($string)) {
				$string = $key . '=' . $val;
			} else {
				$string .= '&' . $key . '=' . $val;
			}
		}
		$string .= '&key=' . $key;

		if ($type == 'MD5') {
			return strtoupper(md5($string));
		} else {
			return hash('sha256', $string);
		}
	}

}
