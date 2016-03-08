<?php
	/*
	 * Bitstorm 2 - A small and fast BitTorrent tracker
	 * Copyright 2011 Peter Caprioli
	 * Copyright 2015 Wilhelm Svenselius
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 */

	function dbUpdatePeer($peerId, $userAgent, $ipAddress, $key, $port, $user = 0) {
		global $_sql;
		$insert = $_sql->prepare('INSERT INTO bit_peer ( hash, user_agent, ip_address, key_hash, port, user ) VALUES ( ?, ?, ?, ?, ?, ? ) '
			. 'ON DUPLICATE KEY UPDATE user_agent = VALUES(user_agent), ip_address = VALUES(ip_address), port = VALUES(port), user = VALUES(user), id = LAST_INSERT_ID(id)');

		if(NULL == $userAgent) {
			$userAgent = 'N/A';
		}

		$peerId = bin2hex($peerId);
		$userAgent = substr($userAgent, 0, 80);
		$ipAddress = substr($ipAddress, 0, 40);
		$key = sha1($key);
		$insert->bind_param('ssssii', $peerId, $userAgent, $ipAddress, $key, $port, $user);

		if(!$insert->execute()) {
			actionReport('db_upd_peer_failed');
			die(trackError('Database failed when updating peer: ' . $insert->errno));
		}
		$insert->close();

		return $_sql->insert_id;
	}

	function dbUpdateTorrent($infoHash) {
		global $_sql;
		$insert = $_sql->prepare('INSERT INTO bit_torrent ( hash ) VALUES ( ? ) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');

		$infoHash = bin2hex($infoHash);
		$insert->bind_param('s', $infoHash);

		if(!$insert->execute()) {
			actionReport('db_upd_torr_failed');
			die(trackError('Database failed when updating torrent: ' . $insert->errno));
		}
		$insert->close();

		return $_sql->insert_id;
	}

	function dbUpdatePeerTorrent($peerPk, $uploaded, $downloaded, $remain, $infoHash) {
		global $_sql;
		$insert = $_sql->prepare('INSERT INTO bit_peer_torrent ( peer_id, torrent_id, uploaded, downloaded, remain, last_updated ) '
			. 'SELECT ?, bit_torrent.id, ?, ?, ?, UTC_TIMESTAMP() FROM bit_torrent WHERE bit_torrent.hash = ? '
			. 'ON DUPLICATE KEY UPDATE uploaded = VALUES(uploaded), downloaded = VALUES(downloaded), remain = VALUES(remain), last_updated = VALUES(last_updated), stopped = 0, id = LAST_INSERT_ID(bit_peer_torrent.id)');

		$infoHash = bin2hex($infoHash);
		$insert->bind_param('iiiis', $peerPk, $uploaded, $downloaded, $remain, $infoHash);

		if(!$insert->execute()) {
			actionReport('db_upd_tpeer_failed');
			die(trackError('Database failed when updating peers on torrent: ' . $insert->errno));
		}
		$insert->close();

		return $_sql->insert_id;
	}

	function dbStoppedPeer($peerTorrentPk) {
		global $_sql;
		$update = $_sql->prepare('UPDATE bit_peer_torrent SET stopped = 1 WHERE id = ?');
		$update->bind_param('i', $peerTorrentPk);

		if(!$update->execute()) {
			actionReport('db_upd_speer_failed');
			die(trackError('Database failed when updating peer on torrent: ' . $update->errno));
		}
		$update->close();
	}

	function dbGetPeers($torrentPk, $peerPk, $peerIP, $limit) {
		global $_sql;
		$select = $_sql->prepare('SELECT bit_peer.ip_address, bit_peer.port, bit_peer.hash FROM bit_peer_torrent '
			. 'LEFT JOIN bit_peer ON bit_peer.id = bit_peer_torrent.peer_id '
			. 'WHERE bit_peer_torrent.torrent_id = ? AND bit_peer_torrent.stopped = 0 '
			. 'AND bit_peer_torrent.last_updated >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND) '
			. 'AND bit_peer.id != ? '
			. 'AND bit_peer.ip_address != ? '
			. 'ORDER BY RAND() '
			. 'LIMIT ?');

		$interval = __INTERVAL + __TIMEOUT;
		$select->bind_param('iiisi', $torrentPk, $interval, $peerPk, $peerIP, $limit);

		if(!$select->execute()) {
			actionReport('db_gettpeers_failed');
			die(trackError('Database failed when getting peers: ' . $select->errno));
		}

		if (!method_exists($select, 'get_result')) {
			$select->store_result();
			$result = array();
			while($assoc_array = fetch_stmt_results($select)) {
				$row = array();
				foreach ($assoc_array as $name => $value) {
					$row[] = $value;
				}
				$result[] = $row;
			}
			return $result;
		}

		if(!($result = $select->get_result())) {
			actionReport('db_gettpreult_failed');
			die(trackError('Database failed when getting peers: ' . $select->errno));
		}

		return $result->fetch_all();
	}

	function dbGetCounts($torrentPk) {
		global $_sql;
		$select = $_sql->prepare('SELECT IFNULL(SUM(remain > 0), 0) AS leech, IFNULL(SUM(remain = 0), 0) AS seed FROM bit_peer_torrent '
			. 'WHERE bit_peer_torrent.torrent_id = ? AND bit_peer_torrent.stopped = 0 '
			. 'AND bit_peer_torrent.last_updated >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND) '
			. 'GROUP BY bit_peer_torrent.torrent_id');

		$interval = __INTERVAL + __TIMEOUT;
		$select->bind_param('ii', $torrentPk, $interval);

		if(!$select->execute()) {
			actionReport('db_getcounts_failed');
			die(trackError('Database failed when getting counts: ' . $select->errno));
		}

		$seeders = 0;
		$leechers = 0;
		$select->bind_result($leechers, $seeders);
		$select->fetch();
		return array($seeders, $leechers);
	}
