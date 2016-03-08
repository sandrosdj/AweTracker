<?php

	header('Content-type: application/json');

	if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die(json_encode(array('status'	=>	'invalid_id')));

	require('../includes/_config.php');
	dbConnect();

	$sql = "SELECT "
		."bit_torrent.id AS torrentId, "
		."bit_torrent.hash AS torrentHash, "
		."torrents.name AS name, "
		."torrents.size AS size, "
		."SUM(CASE WHEN bit_peer_torrent.remain = 0 THEN 1 ELSE 0 END) AS seeds, "
		."SUM(CASE WHEN bit_peer_torrent.stopped = 0 THEN 1 ELSE 0 END) AS peers, "
		."torrents.time AS `time`, "
		."torrents.comment AS `comment`, "
		."torrents.description AS `description` "
	. "FROM bit_torrent "
	."LEFT JOIN bit_peer_torrent ON (bit_peer_torrent.torrent_id = bit_torrent.id AND bit_peer_torrent.stopped = 0) "
	."INNER JOIN torrents ON torrents.id = bit_torrent.id "
	."WHERE bit_torrent.id = $_GET[id] "
	."GROUP BY bit_torrent.id";

	$query = $_sql->query($sql) or die(mysqli_error($_sql));

	if ($query->num_rows)
		while ($data = $query->fetch_assoc())
			die(json_encode(array_merge(
				array('status'	=> 'ok'),
				array(
					'id'	=> $data['torrentId'],
					'name'	=> $data['name'],
					'size'	=> $data['size'],
					'seeds'	=> $data['seeds'],
					'peers'	=> $data['peers'],
					'time'	=> $data['time'],
					'size2'	=> human_filesize($data['size']),
					'dl'	=> __FILES_URL.$data['torrentId'],

					'comment'		=> htmlentities($data['comment']),
					'description'	=> ($data['description'] ? "<p>".str_replace('<br/><br/>', "</p><p>", nl2br(htmlentities($data['description'])))."</p>" : false)
				))));
	else
		die(json_encode(array('status'	=>	'not_found')));