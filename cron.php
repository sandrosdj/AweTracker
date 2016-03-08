<?php

	require('includes/_config.php');
	dbConnect();

	if (!$_sql->query("UPDATE bit_peer_torrent SET stopped = 1 WHERE last_updated < DATE_SUB(UTC_TIMESTAMP(), INTERVAL ".__TIMEOUT." SECOND)"))
		actionReport('cron_db_lupstop_failed');

	foreach (glob(__TMP."*") as $file)
		if (is_file($file) && filemtime($file) < time() - 86400)
			unlink($file);