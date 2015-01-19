jQuery(document).ready(function($){
	
	// Load iOS Switch script:
	$('input.iOSToggle').iToggle();
	
	// Info panel blob jump:
	$('div.blobContainer img').hover(function(){
		$(this).stop(true, false).fadeTo(200, 1.0);
	}, function(){
		$(this).stop(true, false).fadeTo(200, 0.7);
	});
	
});