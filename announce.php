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

	require('includes/_config.php');
	require(__INCLUDES.'_util.php');
	require(__INCLUDES.'_data.php');

	header("Content-Type: text/plain");
	dbConnect();
	dbSettings();

	if (
		$_set['download_only_user']	&& 
		(
			!isset($_GET['usrid']) 
			|| !isset($_GET['dltkn']) 
			|| !isset($_GET['trid']) 
			|| !is_numeric($_GET['usrid']) 
			|| !$_sql->query("SELECT 1 FROM users WHERE id = $_GET[usrid]")->num_rows
			|| $_GET['dltkn'] != tokenGenerator($_GET['usrid'].$_GET['trid'])
		)
	)
		die('d14:failure reason29:Invalid usere');

	$peerId = validateFixedLengthString('peer_id');
	$port = validateConstrainedInt('port', 1, 65535);
	$infoHash = validateFixedLengthString('info_hash');
	$key = validateString('key', true);
	$downloaded = validateInt('downloaded', true);
	$uploaded = validateInt('uploaded', true);
	$left = validateInt('left', true);
	$numWant = validateInt('numwant', true);
	$noPeerId = isset($_GET['no_peer_id']);

	if($numWant <= 0 || $numWant > __MAX_PPR) {
		$numWant = __MAX_PPR;
	}

	if(__WHITELIST_ENABLED && !isWhitelisted($infoHash)) {
		actionReport('not_allowed_torrent');
		die(trackError('Torrent not allowed on this tracker'));
	}

	$peerPk = dbUpdatePeer($peerId, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'], $key, $port, $_GET['usrid']);
	$torrentPk = dbUpdateTorrent($infoHash);
	$peerTorrentPk = dbUpdatePeerTorrent($peerPk, $uploaded, $downloaded, $left, $infoHash);

	if(isset($_GET['event']) && $_GET['event'] === 'stopped') {
		actionReport('stopped');
		dbStoppedPeer($peerTorrentPk);
		// The RFC says its OK to return an empty string when stopping a torrent however some clients will whine about it so we return an empty dictionary
		die(trackPeers(array(), 0, 0, $noPeerId));
	}

	actionReport('run');

	$reply = dbGetPeers($torrentPk, $peerPk, $_SERVER['REMOTE_ADDR'], $numWant);
	list($seeders, $leechers) = dbGetCounts($torrentPk);
	$result = trackPeers($reply, $seeders, $leechers, $noPeerId);
	mydebug($result);
	die($result);
