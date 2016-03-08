<?php

	function actionReport($action)
	{
		global $user;
		return @file_get_contents('http://filtr.sandros.hu/statistics/'.__FILTR_ID.'/?ait='.__FILTR_STAT.'&action='.urlencode($action).(LOGGEDIN ? '&filtrUser='.$user['id'] : null));
	}

	if (isset($_GET['token']))
	{
		setcookie('filtr_user_token', $_GET['token'], time()+3600*31, '/');
		header('Location: /');
		exit;
	}

	$user = array(
		'id'	=> 0,
		'name'	=> 'Guest'
	);

	if (isset($_COOKIE['filtr_user_token']))
	{
		$filtr = new filtrLogin();

		$filtr -> setAppid(__FILTR_ID);
		$filtr -> setApptoken(__FILTR_APPTOKEN);
		$filtr -> setToken($_COOKIE['filtr_user_token']);

		$filtr -> cache = __TMP;
		$filtr -> Login();

		if ($filtr -> status())
		{
			$user = $filtr -> getData();
			dbConnect();
			if (!$_sql->query("SELECT 1 FROM users WHERE id = $user[id]")->num_rows && !$_sql->query("INSERT INTO users (id, time) VALUES ($user[id], ".time().")"))
				die('System can not create user.');

			define('LOGGEDIN', true);
		}

		unset($filtr);
	}
	if (!defined('LOGGEDIN'))
		define('LOGGEDIN', false);