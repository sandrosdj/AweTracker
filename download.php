<?php

	if ($_set['download_only_user'] && !LOGGEDIN)
	{
		actionReport('dl_prevented_guest');
		die('Only users can download torrents.');
	}

	if (!file_exists(__FILES.$_GET['download'].'.torrent'))
	{
		actionReport('dl_file_not_found');
		die('Torrent not found.');
	}

	require(__INCLUDES.'_torrent.class.php');

	$torrent = new Torrent(__FILES.$_GET['download'].'.torrent');
	$torrent -> announce(false);
	$torrent -> announce(__ANNOUNCE.'?usrid='.$user['id'].'&trid='.$_GET['download'].'&dltkn='.tokenGenerator($user['id'].$_GET['download']));

	if ($errors = $torrent->errors())
	{
		header('Content-type: text/plain');
		var_dump($errors);
		exit;
	}

	header('Content-type: application/x-bittorrent');
	$torrent -> send();
