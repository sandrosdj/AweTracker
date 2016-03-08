<?php

	function listTorrents($order = 0, $orderby = 0, $limit = 0, $search = false, $category = false, $userid = false)
	{
		global $_sql, $_set, $user;

		if ($len = strlen($search) > 0) $search = $_sql->real_escape_string($len > 100 ? substr($search, 0, 100) : $search);
		if ($userid !== false && !is_numeric($userid)) $userid = false;
		if ($category && !is_numeric($category)) $category = false;

		switch ($order)
		{
			case 1:
				$order = 'torrents.name';
				break;
			case 2:
				$order = 'torrents.size';
				break;
			case 3:
				$order = 'seeds';
				break;
			case 4:
				$order = 'peers';
				break;
			case 0:
			default:
				$order = 'torrents.time';
		}
		$orderby = ($orderby > 0 ? 'ASC' : 'DESC');

		$sql = "SELECT "
				."bit_torrent.id AS torrentId, "
				."bit_torrent.hash AS torrentHash, "
				."torrents.name AS name, "
				."torrents.size AS size, "
				."SUM(CASE WHEN bit_peer_torrent.remain = 0 THEN 1 ELSE 0 END) AS seeds, "
				."SUM(CASE WHEN bit_peer_torrent.stopped = 0 THEN 1 ELSE 0 END) AS peers, "
				."torrents.time AS `time`, "
				."torrents.visibility AS `visibility`, "
				."verifications.verification AS `verification` "
			. "FROM bit_torrent "
			."LEFT OUTER JOIN bit_peer_torrent ON (bit_peer_torrent.torrent_id = bit_torrent.id AND bit_peer_torrent.stopped = 0) "
			."INNER JOIN torrents ON torrents.id = bit_torrent.id "
			."LEFT OUTER JOIN verifications ON verifications.torrent_id = bit_torrent.id "
			."WHERE (torrents.visibility IS NULL".(LOGGEDIN ? " OR (torrents.visibility = 1 AND torrents.user = $user[id]) " : null).") "
			.($search ? "AND (torrents.name LIKE '%$search%' OR torrents.comment LIKE '%$search%') " : null)
			.($userid ? "AND torrents.user = $userid " : null)
			.($category ? " AND torrents.category = $category " : null)
			."GROUP BY bit_torrent.id "
			."ORDER BY $order $orderby, bit_torrent.id DESC "
			.($limit > 0 ? " LIMIT $limit" : null);

		return $_sql->query($sql);
	}
