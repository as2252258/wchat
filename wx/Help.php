<?php


namespace wchat;


class Help extends Base
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;

	public static function d($code)
	{
		$messages = [
			static::$OK => '',
			static::$IllegalAesKey => '',
			static::$IllegalIv => '',
			static::$IllegalBuffer => '',
			static::$DecodeBase64Error => '',
		];
		return $messages[$code] ?? static::$DecodeBase64Error;
	}

	/**
	 * @param $encryptedData
	 * @param $iv
	 * @param $sessionKey
	 * @param $data
	 * @param null $appId
	 * @return int
	 */
	public static function decode($encryptedData, $iv, $sessionKey, &$data, $appId = null)
	{
		if (strlen($sessionKey) != 24) {
			return self::$IllegalAesKey;
		}

		flush();
		$aesKey = base64_decode($sessionKey);
		if (strlen($iv) != 24) {
			return self::$IllegalIv;
		}

		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($encryptedData);

		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, OPENSSL_RAW_DATA, $aesIV);
		if ($result === false) {
			return self::$IllegalBuffer;
		}

		$dataObj = json_decode($result);
		if ($dataObj->watermark->appid != $appId) {
			return self::$IllegalBuffer;
		}
		$data = $dataObj;
		return self::$OK;
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
