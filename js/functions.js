function hashHandler(wlhash)
{
	var currentHash = wlhash.replace('#', '').split('/');

	switch (currentHash[0])
	{
		case 'search':
			if (!currentHash[1].length)
			{
				window.location.hash = '';
				if (!lToptions.search.length) return false;
			}
			lToptions.search = currentHash[1];
			listTorrentsDefault();			
		break;

		case 'details':
			$('#tdetails').modal('show');
			$('#tdetails .torrentname').html('Loading...');
			$.get('ajax/details.php?id='+currentHash[1], function(data){
				if (data.status != 'ok') { alert('Something went wrong.'); return false; }
				$('#tdetails .torrentname').html(data.name);
				$('#tdetails .torrenturl').attr('href', data.dl);

				if (data.comment.length)		$('#tdetails .torrentcomment').html(data.comment).show();
				else $('#tdetails .torrentcomment').html('').hide();

				if (data.description.length)	$('#tdetails .torrentdescription').html(data.description).show();
				else $('#tdetails .torrentdescription').html('').hide();

				$('#tdetails .torrentsize').html(data.size2);
				$('#tdetails .torrentseedpeer').html(data.seeds+' / '+(data.peers-data.seeds)+' / '+data.peers);
				$('#tdetails .torrentdate').html(new Date(data.time*1000));
			});
		break;
	}
}

function listTorrents(elem, order, orderby, search, category, mytorrents)
{
	$(elem).html('');
	$('.no_results[data-mainelem='+elem+']').remove();
	$.post('ajax/list.php', { order: (order ? order : 3), orderby: (orderby ? orderby : 0), search: (search ? search : 0), category: (category ? category : 0), mytorrents: (mytorrents ? 1 : 0) }, function(data)
	{
		if (data.status == 'ok')
		{
			$.each(data.results, function(i, item)
			{
				$(elem).append('<tr>'
					+'<td class="name"><a href="#details/'+item.id+'">'+item.name+'</a>'+(item.verification ? ' <span class="label label-success" title="Verified">&#10004;</span>' : '')+(loadTimestamp > (item.time-86400) ? ' <span class="label label-info">new</span>' : '')+(item.visibility ? ' <span class="label label-primary">'+(item.visibility == 1 ? 'private' : (item.visibility == 2 ? 'following' : (item.visibility == 3 ? 'followers' : '?')))+'</span>' : '')+'</td>'
					+'<td class="size">'+item.size2+'</td>'
					+'<td>'+item.seeds+'</td>'
					+'<td>'+(item.peers-item.seeds)+'</td>'
					+'<td><a href="'+item.dl+'">Download</a></td>'
				+'</tr>');
			});
		} else if (data.status == 'empty')
			$(elem).parent().after('<h3 data-mainelem="'+elem+'" class="text-center no_results">No results.</h3>');
		else
			alert('Something wrong happened.');
	});
}
function listTorrentsDefault() { listTorrents(lToptions.elem, lToptions.order, lToptions.orderby, lToptions.search, lToptions.category, lToptions.mytorrents); }

function uploadFile(elem, progressbar)
{
	$(elem).find('button').prop('disabled', true);

	var formData = new FormData($(elem).get(0));
	$.ajax({
		url: $(elem).attr('action'),
		type: 'POST',
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload)
				myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
			return myXhr;
		},
		success: completeHandler,
		data: formData,
		cache: false,
		contentType: false,
		processData: false
	});
	function progressHandlingFunction(e){
		if(e.lengthComputable)
			$(progressbar).attr({
				value:	e.loaded,
				max:	e.total
			});
	}
	function completeHandler(e){
		switch (e.status)
		{
			case 'ok':
				alert('Successfully uploaded.');
				$(elem)[0].reset();
				break;
			case 'no_hash':
				alert('Unknown torrent file.');
				break;
			case 'no_data':
				alert('No data received.');
				break;
			case 'db_failed':
			case 'save_failed':
				alert('Internal error.');
				break;
			case 'exists':
				alert('This torrent is already uploaded.');
				break;
			case 'login':
				alert('You have to login to upload.');
				break;
			default:
				alert('Unknown error.');
				break;
		}
		$(elem).find('[disabled]').prop('disabled', false);
		$(progressbar).attr({
			value:	0,
			max:	100
		});
	}

	return false;
}