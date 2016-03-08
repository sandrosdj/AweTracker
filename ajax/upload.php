<?php

	header('Content-type: application/json');

	require('../includes/_config.php');
	dbConnect();
	dbSettings();

	sleep($_set['sleep_before_upload']);

	require(__INCLUDES.'_torrent.class.php');

	if ($_set['upload_only_user'] && !LOGGEDIN) die(json_encode(array(	'status'	=> 'login'	)));

	if (isset($_POST['category']) && is_numeric($_POST['category']) && isset($_POST['visibility']) && is_numeric($_POST['visibility']) && isset($_POST['description']) && isset($_FILES['torrent']))
	{
		// Open torrent for mods
		$torrent	= new Torrent($_FILES['torrent']['tmp_name']);

		$name		= $_sql->real_escape_string(trim($torrent->name()));
		$size		= $_sql->real_escape_string($torrent->size());
		$comment	= $_sql->real_escape_string(trim($torrent->comment()));			if (!strlen($comment))		$comment = false;
		$hash_org	= $_sql->real_escape_string($torrent->hash_info());

		$torrent -> announce(false);
		$torrent -> announce(__ANNOUNCE);
		$torrent -> is_private($_set['is_private'] > 0 ? true : false);

		// Save mods
		$torrent -> save(__TMP.$hash_org);
		unset($torrent);

		// Make sure everything is up to date
		$torrent	= new Torrent(__TMP.$hash_org);
		$hash		= $_sql->real_escape_string($torrent->hash_info());
		unset($torrent);

		// Details
		$description	= $_sql->real_escape_string(trim($_POST['description']));	if (!strlen($description))	$description = false;

		// Add stuff "manually"
		if (!$_sql->query("SELECT 1 FROM bit_torrent WHERE hash = '$hash'")->num_rows && !$_sql->query("INSERT INTO bit_torrent (hash, awe) VALUES ('$hash', 1)"))
			die(json_encode(array(	'status'	=> 'db_failed'	)));
		unset($maxid);

		// Save torrent info
		$query = $_sql->query("SELECT id FROM bit_torrent WHERE hash = '$hash' LIMIT 1");
		if ($query->num_rows)
			while ($data = $query->fetch_assoc())
			{
				if (!$_sql->query("SELECT 1 FROM torrents WHERE id = $data[id]")->num_rows)
					if ($_sql->query("INSERT INTO torrents (id, user, name, comment, description, size, category, visibility, time) VALUES ($data[id], ".($user['id'] ? $user['id'] : 'NULL').", '$name', ".($comment ? "'$comment'" : 'NULL').", ".($description ? "'$description'" : 'NULL').", $size, $_POST[category], ".($_POST['visibility'] > 0 ? $_POST['visibility'] : 'NULL').", ".time().")"))
						if (rename(__TMP.$hash_org, __FILES.$data['id'].'.torrent'))
							die(json_encode(array(	'status'	=> 'ok'	)));
						else
							die(json_encode(array(	'status'	=> 'save_failed'	)));
					else
						die(json_encode(array(	'status'	=> 'db_failed'	)));
				else
					die(json_encode(array(	'status'	=> 'exists'	)));
			}
		else
			die(json_encode(array(	'status'	=> 'no_hash'	)));
	} else
		die(json_encode(array(	'status'	=> 'no_data'	)));