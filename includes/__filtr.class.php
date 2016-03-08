<?php

/* ---------
	Filtr. Class 4 your Entertainment
	filtr.sandros.hu
	Sandros Industries
	2015. July 11.

	Version: 2.2.7.15b
--------- */


class filtrLogin
{
	/* User authentication */
	private $token;

	/* Filtr. authentication */
	private $appid;
	private $apptoken;
	private $apiurl = 'http://filtr.sandros.hu/api.php';

	/* This holds the response from Filtr. */
	private $apiResponse;

	// Cache
	public $cache;
	public $cachetimeout = 60;
	private $lessy = false;
	private $apps = false;

	/* Hey! :) */
	public function __construct($apiurl = false, $cache = false) {
		if ($apiurl)
			$this->apiurl = $apiurl; // Override the class-default API url with the given one
	}

	/* Data collectors */
	public function setToken($token = 0)	{ $this->token = $token; }
	public function setAppid($user = 0)		{ $this->appid = $user; }
	public function setApptoken($key = 0)	{ $this->apptoken = $key; }
	public function lessy($lessy = true)	{ $this->lessy = ($lessy ? true : false);  }
	public function apps($apps = true)		{ $this->apps = $apps; }

	/* Data storage */
	private $datastorage = array();
	public function DataStorage($todo, $key = false, $value = false) {
		switch($todo)
		{
			case 'read':
				$this->datastorage = array('no_data'=>true, 'data_storage'=>'read');
				$this->lessy = true;
			break;

			case 'write':
				$this->datastorage = array('no_data'=>true, 'data_storage'=>'write', 'data_storage_key'=>$key, 'data_storage_value'=>$value);
				$this->lessy = true;
			break;

			case 'erase':
				$this->datastorage = array('no_data'=>true, 'data_storage'=>'erase');
				$this->lessy = true;
			break;
		}
		if ($this->status())
		{
			$this->Login();
			return (isset($this->apiResponse->data_storage) ? true : false);
		}
		return true;
	}

	/* Nasty things */
	public function Login($timeout = 6) {

		// Caching
		if (!$this->lessy && $this->cache && file_exists($this->cache.$this->token) && filemtime($this->cache.$this->token) > time()-$this->cachetimeout)
		{
			$this->apiResponse = json_decode(file_get_contents($this->cache.$this->token));

			if (!$this->apps)
				return true;

			elseif ($this->apps && isset($this->apiResponse->apps))
				return true;
		}

		// Collect the auth infos
		// ! This looks pretty bad. In the next release, there will be a JSON encoder.
		$array = array_merge(array(
			'appid'		=> $this->appid,
			'apptoken'	=> $this->apptoken,
			'token'		=> $this->token,
			'apps'		=> ($this->apps ? 'y' : 'n')
		), $this->datastorage);

		// Convert to GET like string
		$fields = '';
		foreach($array as $key=>$value)
			$fields .= $key.'='.$value.'&';
		$fields = rtrim($fields, '&');


		// Connect options and set data
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,		$this->apiurl);
		curl_setopt($ch, CURLOPT_POST,		count($array));
		curl_setopt($ch, CURLOPT_POSTFIELDS,	$fields);
		curl_setopt($ch, CURLOPT_TIMEOUT,	$timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Free up some memory
		unset($fields);
		unset($array);
		$this->datastorage	= false;

		// Do what we need to
		$rawResponse		= curl_exec($ch);
		$this->apiResponse	= json_decode($rawResponse);

		// Basic cache
		if (!$this->lessy && $this->cache)
		{
			$cache = fopen($this->cache.$this->token, 'w');
			fwrite($cache, $rawResponse);
			fclose($cache);
			unset($cache);
		}
		unset($rawResponse);

		// Close the connection to the login server
		curl_close($ch);
		unset($ch);

		// '1' means the response has came from the remote server
		// Not relevant for this script, but you can build an advanced cache control for better performance.
		return 1;
	}

	// Logged in?
	public function status() {
		if (isset($this->apiResponse->status) && $this->apiResponse->status == 'ok')
			return true;
		return false;
	}

	// Return user's data
	// Array mode is the default, because this could cause serious problems if someone auto-updating this script.
	public function getData($array = true) {
		if ($array)
			return (array)$this->apiResponse;
		return $this->apiResponse;
	}

}
