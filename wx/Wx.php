<?php


namespace wchat;


class Wx
{

	/** @var static $instance */
	private static $instance = null;

	/** @var Config $config */
	private $config = null;

	/**
	 * @return static
	 */
	public static function getMiniProGaRamPage()
	{
		if (static::$instance === null) {
			static::$instance = new Wx();
		}
		return static::$instance;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}


	/**
	 * @param Config $config
	 * @return $this
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;
		return $this;
	}

	/**
	 * @return Template
	 */
	public function getTemplate()
	{
		return Template::getInstance($this->config);
	}

	/**
	 * @return Account
	 */
	public function getAccount()
	{
		return Account::getInstance($this->config);
	}

	/**
	 * @return Message
	 */
	public function getMessage()
	{
		return Message::getInstance($this->config);
	}

	/**
	 * @return Recharge
	 */
	public function getRecharge()
	{
		return Recharge::getInstance($this->config);
	}
}
