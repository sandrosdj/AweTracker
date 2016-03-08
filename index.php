<?php
	require('includes/_config.php');
	dbConnect();
	dbSettings();

	if (isset($_GET['download']) && is_numeric($_GET['download']))
	{
		require('download.php');
		exit;
	}
?>
<!doctype html>
<html lang="en">
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous" />
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

		<script src="js/functions.js"></script>

		<title><?=$_set['title']?></title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<style>
			body { padding-top: 70px; }
			td.size { text-overflow: ellipsis; white-space: nowrap; overflow: hidden; }
		</style>
		<script>
			var loadTimestamp = <?=time()?>;
			var lTcategories = {
			<?php
				$query = $_sql->query("SELECT * FROM categories ORDER BY category ASC");
				echo "0:{id:0,category:'All'}";
				$i = 0;
				while ($data = $query->fetch_assoc())
				{
					$i++;
					echo ",$i:{id:$data[id],category:'$data[category]'}";
				}
				unset($query);
			?>
			};
		</script>
		
		<script src="js/init.js"></script>
	</head>

	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-default navbar-inverse navbar-fixed-top">
			  <div class="container-fluid">
				<div class="navbar-header">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				  <a class="navbar-brand" href="/"><?=$_set['title']?></a>
				</div>

				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				  <ul class="nav navbar-nav awe-activate">
					<li class="active"><a href="#" data-lToptions="mytorrents=false">Torrents</a></li>
					<li><a href="#" data-lToptions="mytorrents=true">My uploads</a></li>
					<li class="dropdown awe-exclude">
					  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=(LOGGEDIN ? $user['name'] : 'Login')?> <span class="caret"></span></a>
					  <ul class="dropdown-menu">
						<li><a href="<?=__LOGIN_URL?>"><?=(LOGGEDIN ? 'Login as ...' : 'Login')?></a></li>
						<li><a href="<?=(LOGGEDIN ? __LOGOUT_URL : __REG_URL)?>"><?=(LOGGEDIN ? 'Logout' : 'Registration')?></a></li>
						<li role="separator" class="divider"></li>
						<li><a href="#" data-lToptions="mytorrents=true">My uploads</a></li>
						<li><a href="<?=__FILTR_URL?>" target="_blank">Filtr.</a></li>
					  </ul>
					</li>
				  </ul>
				  <form class="navbar-form navbar-right" role="search" id="search" action="/" method="get">
					<div class="form-group">
					  <input type="text" class="form-control" placeholder="Name or keyword" name="search" />
					</div>
					<button type="submit" class="btn btn-default">Search</button>
				  </form>
				</div>
			  </div>
			</nav>

			<header>
				<div class="well well-sm"><?=$_set['tagline']?></div>
			</header>

			<div class="row">
				<div class="col-sm-9">

					<div class="btn-group awe-activate" role="group" aria-label="...">
						<button class="btn btn-default" data-lToptions="orderby=1">Ascend</button>
						<button class="btn btn-default" data-lToptions="orderby=0">Descent</button>
					</div>

					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th><a href="#" data-lToptions="order=1">Name</a></th>
								<th><a href="#" data-lToptions="order=2">Size</a></th>
								<th><a href="#" data-lToptions="order=3">Seed</a></th>
								<th><a href="#" data-lToptions="order=4">Leech</a></th>
								<th>Download</th>
							</tr>
						</thead>
						<tbody id="list_results">
						</tbody>
					</table>
				</div>
				<div class="col-sm-3">
					<div class="panel panel-default" style="overflow: hidden">
						<div class="panel-heading">
							<h3 class="panel-title">Upload a torrent</h3>
						</div>
						<div class="panel-body">
							<form action="ajax/upload.php" method="post" name="upload-torrent" enctype="multipart/form-data" id="upload" role="form" onsubmit="return uploadFile(this, '#uplprogress')">
								<div class="form-group">
									<label for="file">File</label>
									<input type="file" id="file" name="torrent" accept="application/x-bittorrent" />
								</div>
								<div id="upload_details" style="display: none">
									<div class="form-group">
										<label for="category">Category</label>
										<select class="form-control input-sm" name="category" id="select_category">
										</select>
										<p class="help-block">Make sure you select the correct category!</p>
									</div>
									<div class="form-group">
										<label for="description">Description</label>
										<textarea class="form-control" name="description"></textarea>
										<p class="help-block">You won't be able to modify this.</p>
									</div>
									<div class="form-group">
										<label for="visibility">Visibility</label>
										<select class="form-control input-sm" name="visibility">
											<option value="0">Public</option>
											<option value="1">Private</option>
											<option value="2">Following</option>
											<option value="3">Followers</option>
										</select>
									</div>
									<button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure?')">Upload</button>
								</div>
							</form>
						</div>
						<div class="panel-footer">
							<progress id="uplprogress" value="0" max="100" style="width: 100%"></progress>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Categories</h3>
						</div>
						<div class="panel-body">
							<ul class="nav nav-pills nav-stacked nav-inverse awe-activate" id="list_categories">
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade bs-details" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" id="tdetails">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title torrentname">Loading...</h4>
						</div>
						<div class="modal-body">
							<p>
								<a href="/" class="btn btn-lg btn-primary torrenturl">Download</a>
							</p>
							<blockquote class="blockquote torrentcomment"></blockquote>
							<div class="panel panel-default">
								<div class="panel-body torrentdescription">
								</div>
							</div>
							<table class="table table-striped">
								<tr>
									<th>Size</th>
									<td class="torrentsize"></td>
								</tr>
								<tr>
									<th>Seed / Peer</th>
									<td class="torrentseedpeer"></td>
								</tr>
								<tr>
									<th>Uploaded on</th>
									<td class="torrentdate"></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<footer class="footer">
				<p><?=$_set['copyright']?></p>
				<p>Credits: jQuery, Bootstrap, BitStorm. Created by <a href="https://sandros.hu">Sandros</a></p>
			</footer>

			<script>var script = document.createElement("script");script.src = "//filtr.sandros.hu/statistics/<?=__FILTR_ID?>"+(document.referrer ? "?cf="+encodeURIComponent(document.referrer):"");document.getElementsByTagName("head")[0].appendChild(script);</script>
		</div>
	</body>
</html>