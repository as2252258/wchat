<?php

namespace wchat;

/**
 * Class Result
 *
 * @package app\components
 *
 * @property $code
 * @property $message
 * @property $count
 * @property $data
 */
class Result
{
	public $code;
	public $message;
	public $count = 0;
	public $data;
	public $header;

	public function __construct(array $data)
	{
		foreach ($data as $key => $val) {
			$this->$key = $val;
		}

		$this->header = $this->reloadResponse($this->header);
	}


	/**
	 * @param $header
	 *
	 * @return array
	 */
	private function reloadResponse($header)
	{
		$data = [];
		$load = explode("\n", $header);
		if (!empty($load) && is_array($load)) {
			foreach ($load as $key => $val) {
				if (empty($val)) continue;
				$ex = explode(': ', $val);
				if (!empty($ex[0]) && !empty($ex[1])) {
					$data[trim($ex[0])] = trim($ex[1]);
				}
			}
		}
		return $data;
	}

	public function __get($name)
	{
		return $this->$name;
	}


	public function __set($name, $value)
	{
		$this->$name = $value;

		return $this;
	}

	public function getTime()
	{
		return [
			'startTime' => $this->startTime,
			'requestTime' => $this->requestTime,
			'runTime' => $this->runTime,
		];
	}

	/**
	 * @param $key
	 * @param $data
	 * @return $this
	 * @throws \Exception
	 */
	public function setAttr($key, $data)
	{
		if (!property_exists($this, $key)) {
			throw new \Exception('未查找到相应对象属性');
		}
		$this->$key = $data;
		return $this;
	}


	public function isResultsOK()
	{
		return $this->code == 0;
	}

	/**
	 * @param array $headers
	 * 批量设置返回头
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $key => $val) {
			$this->setHeader($key, $val);
		}
	}

	/**
	 * @param $key
	 * @param $val
	 * 设置返回头
	 */
	public function setHeader($key, $val)
	{
		header($key . ':' . $val);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getData($name = '')
	{
		if (!$this->isResultsOK()) {
			return '';
		}
		if (!empty($name) && isset($this->data[$name])) {
			return $this->data[$name];
		}
		return $this->data;
	}

	/**
	 * @param $key
	 * @param $data
	 * @return $this
	 */
	public function append($key, $data)
	{
		$this->data[$key] = $data;
		return $this;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getCode()
	{
		return $this->code;
	}
}
