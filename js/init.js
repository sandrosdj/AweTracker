var lToptions = {
	elem:		'#list_results',
	order:		0,
	orderby:	0,
	search:		false,
	mytorrents:	false,
	category:	false
};

$(document).ready(function(){

	/*
		DESIGN SPECIFIC
		Things to look cool.
	*/
	$(document).on('click', '.awe-activate > *', function(){
		if (!$(this).is('.awe-exclude'))
			$(this).addClass('active').siblings().removeClass('active');
	});


	/*
		SITE SPECIFIC
		Things to operate the sites stuffs.
	*/

	// List torrents
	listTorrentsDefault();


	// ==== EVENTS ==== //

		// Change options by clicking on things
		$(document).on('click', '[data-lToptions]', function(){
			var options = $(this).attr('data-lToptions').split(',');
			for (var i = 0; i < options.length; i++)
			{
				var option = options[i].split('=');
				switch (option[0])
				{
					case 'order':		lToptions.order			= option[1];									break;
					case 'orderby':		lToptions.orderby		= option[1];									break;
					case 'search':		lToptions.search		= (option[1] == 'false' ? false : option[1]);	break;
					case 'mytorrents':	lToptions.mytorrents	= (option[1] == 'false' ? false : option[1]);	break;
					case 'category':	lToptions.category		= (option[1] == 'false' ? false : option[1]);	break;
				}
			}
			listTorrentsDefault();
		});

		// Modal close
		$('#tdetails').on('hidden.bs.modal', function(){
			window.location.hash = '';
		});

		// Tooltips
		$(document).on('mouseover', '[title]', function(){
			$(this).tooltip('show', { placement: 'auto' });
		});

		// Search
		$('#search').submit(function(){
			window.location.hash = '#search/'+$(this).find('input[name=search]').val();

			return false;
		});

		// Upload form
		$('#upload input:file').change(function(){
			if ($(this).val())
				$('#upload_details').slideDown();
		});


	// List categories
	$.each(lTcategories, function(i, item){
		$('#list_categories').append('<li><a href="#" data-lToptions="category='+item.id+'">'+item.category+'</a></li>');
		$('#select_category').append('<option value="'+item.id+'">'+item.category+'</option>');
	});
	$('#select_category option:first-child').remove();
	$('#list_categories li:first-child').addClass('active');

	/*
		HASH Change
		Handle every "subatomic" stuff.
	*/
	$(window).bind('hashchange', function(e){
		hashHandler(window.location.hash);
	});
	if (window.location.hash)
		hashHandler(window.location.hash);
});