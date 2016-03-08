<?php
	header('charset=utf-8');

	/*
	 * Filtr. Settings
	 */
	define('__FILTR_ID',		27);
	define('__FILTR_STAT',		'');
	define('__FILTR_APPTOKEN',	'');

	/*
	 * Directories
	 */
	define('__BASEDIR',		'/home/sandros/www/awe/');
	define('__INCLUDES',	__BASEDIR.'includes/');
	define('__FILES',		__BASEDIR.'files/');
	define('__TMP',			__BASEDIR.'tmp/');

	/*
	 * URLs
	 */
	define('__ANNOUNCE',	'http://awetune.tk/announce.php');
	define('__FILES_URL',	'/?download=');
	define('__FILTR_URL',	'https://filtr.sandros.hu/');
	define('__LOGIN_URL',	__FILTR_URL.'app_login/'.__FILTR_ID);
	define('__REG_URL',		__FILTR_URL.'?regform');
	define('__LOGOUT_URL',	'/?token=');
	define('__SALT',		'idliketofuckwithanita');

	/*
	 * Database connection settings
	 */
	define('__DB_SERVER',	'localhost');
	define('__DB_USERNAME',	'');
	define('__DB_PASSWORD',	'');
	define('__DB_DATABASE',	'');

	/*
	 * General settings
	 */
	// Peer announce interval (Seconds)
	define('__INTERVAL', 60);
	// Time out if peer is this late to re-announce (Seconds)
	define('__TIMEOUT', 120);
	// Minimum announce interval (Seconds) - most clients obey this, but not all
	define('__INTERVAL_MIN', 60);
	// By default, never encode more than this number of peers in a single request
	define('__MAX_PPR', 20);
	// where to store the log file if any. must exist and writeable to be used
	define('__LOGFILE', __BASEDIR.'mylog.log');

	/*
	 * Whitelisting
	 */
	define('__WHITELIST_ENABLED', false);
	// Each element is an infohash of the torrent, in hexadecimal format
	$_whiteList = array();

	/*
	 * PHP error reporting (for debugging)
	 */
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	/** @var mysqli $_sql */
	$_sql = null;

	function dbConnect() {
		global $_sql;
		if ($_sql) return true;
		$_sql = new mysqli(__DB_SERVER, __DB_USERNAME, __DB_PASSWORD, __DB_DATABASE);
		if($_sql->connect_errno) {
			actionReport('db_conn_failed');
			die(trackError('Database connection failed'));
		}
		$_sql->query("SET CHARSET 'utf8'");
		$_sql->query("SET NAMES 'utf8'");
	}

	function dbSettings()
	{
		global $_sql, $_set;
		$query = $_sql->query("SELECT * FROM settings");
		while ($data = $query->fetch_assoc())
			$_set[$data['var']] = $data['val'];
	}
	
	// FILTR
	require_once __INCLUDES.'__filtr.class.php';
	require_once __INCLUDES.'__filtr.php';
	require_once __INCLUDES.'_list.php';
	require_once __INCLUDES.'_essentials.php';


	function human_filesize($bytes, $dec = 0) 
	{
		$size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$factor = floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . ' '.$size[$factor];
	}