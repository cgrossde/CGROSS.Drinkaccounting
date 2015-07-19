$(document).ready(function() {
	// Hide flashmessages
	$('.flashmessages').hide();
	$('#content').append('<div class="notifications top-right"></div>');
	$('.flashmessages li').each(function(index) {
		var alertType = getNotifyClass($(this));
		var alertText = $(this).text();
		$('.notifications').notify({
			type: alertType,
			message: { text: alertText },
			fadeOut: {
				enabled: false,
				delay: 5000
			}
		}).show();
	});
	
	function getNotifyClass(flashmessage) {
		if(flashmessage.hasClass("flashmessages-ok")) {
			console.log("Has class");
			return "info";
		}
		
		return "info";
	}
	
	// Bad, quick fix for title with html attributes (due to bad template and ajax)
	$('title').html($('title').text().replace(/(<([^>]+)>)/ig,""));

});
