// script for loading the portfolio image into the page to show the user that it has been uploaded 
jQuery(document).ready(function($) {
	var data = {
		action: 'portfolio_meta',
		'post_id' : MyScriptParams.post_id
	};
	
	// call wordpress function to get the new portfolio image URL
	$.post('/wp-admin/admin-ajax.php', data, function(response) {
		$('img#portfolio_media_thumbnail', window.parent.document).css("display", "block");
		$('div#portfolio_media div#portfolio_media_wrap', window.parent.document).empty();
		$('div#portfolio_media div#portfolio_media_wrap', window.parent.document).html( response );
		parent.tb_remove();
	});
});