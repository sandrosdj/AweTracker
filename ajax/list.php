<?php

	header('Content-type: application/json');

	require('../includes/_config.php');
	dbConnect();
	dbSettings();

	$query = listTorrents((isset($_POST['order']) ? $_POST['order'] : 3), (isset($_POST['orderby']) ? $_POST['orderby'] : 0), $_set['torrents_per_page'], (isset($_POST['search']) && strlen($_POST['search']) <= 255 ? $_POST['search'] : null), (isset($_POST['category']) && $_POST['category'] > 0 ? $_POST['category'] : null), (isset($_POST['mytorrents']) && $_POST['mytorrents'] > 0 ? $user['id'] : false));
	
	if ($query->num_rows)
	{
		$output = array();
		while ($data = $query->fetch_assoc())
			array_push($output, array(
				'id'			=> $data['torrentId'],
				'name'			=> $data['name'],
				'size'			=> $data['size'],
				'seeds'			=> $data['seeds'],
				'peers'			=> $data['peers'],
				'time'			=> $data['time'],
				'size2'			=> human_filesize($data['size']),
				'dl'			=> __FILES_URL.$data['torrentId'],
				'visibility'	=> $data['visibility'],
				'verification'	=> $data['verification']
			));

		die(json_encode(array(
			'status'	=> 'ok',
			'results'	=> $output
		)));
	} else
		die(json_encode(array('status'	=>	'empty')));
