jQuery(document).ready(function($){

	// Media Library button hook (WP >= 3.5):
	$('a#dgd_library_button').click(function(e){

		// Prevent default:
		e.preventDefault();

		// Set frame object:
		var frame = wp.media({
			id: 'dgd_featured_image',
			title : dgd_strings.panel.title,
			multiple : true,
			library : { type : 'image'},
			button : { text : dgd_strings.panel.button }
		});

		// On select image:
		frame.on('select', function(){
			var attachment = frame.state().get('selection').first().toJSON();
			doSetFeaturedImage(attachment.id);
		});

		// Display:
		frame.open();

	});


	// Set as featured image hook (WP < 3.5):
	$('a.wp-post-thumbnail').live('click', function(e){
		parent.tb_remove();
		parent.location.reload(1);
	});


	// Set as featured image handler (WP >= 3.5):
	$('a#insert-media-button').live('click', function(){
		if (typeof wp !== 'undefined'){
			var editor_id = $('.wp-media-buttons:eq(0) .add_media').attr('data-editor');
			var frame = wp.media.editor.get(editor_id);
			frame = 'undefined' != typeof(frame) ? frame : wp.media.editor.add(editor_id);
			if (frame){
				frame.on('select', function(){
					var currentState = frame.state();
					if (currentState.id === 'featured-image'){
						doFetchFeaturedImage();
					}
				});
			}
		}
	});


	// Remove featured image:
	$('#remove-post-thumbnail').live('click', function(){
		$('#current-uploaded-image').slideUp('medium');
		updateHotspotImages('');
	});


	// Set featured image:
	function doSetFeaturedImage(attachmentID){
		$.post(ajaxurl, {
			action: 'dgd_set_featured_image',
			postID: dgd_post_id,
			attachmentID: attachmentID
		}, function (response){
			var response = $.parseJSON(response);
			if (response.response_code == 200){

				// Publish post:
				if (dgd_page_reload){
					$('div#publishing-action input#publish').trigger('click');
				}

				// Fetch image:
				doFetchFeaturedImage();

			} else {
				alert(response.response_content);
			}
		});
	}


	// Fetch featured image function:
	function doFetchFeaturedImage(){
		$.post(ajaxurl, {
			action: 'dgd_get_featured_image',
			post_id: dgd_post_id
		}, function (response){

			// Parse response AS JSON:
			var response = $.parseJSON(response);

			// Valid response:
			if (response.response_code == 200){

				// Find current image and continue:
				$('#current-uploaded-image').slideUp('medium', function(){

					// Update image with new info:
					var imageObject = $('#drag_to_upload div.inside img.attachment-full');
					imageObject.attr('src', response.response_content);
					imageObject.removeAttr('width');
					imageObject.removeAttr('height');
					imageObject.removeAttr('title');
					imageObject.removeAttr('alt');

					// Hide container:
					imageObject.load(function(){

						// Display container:
						$('#current-uploaded-image').slideDown('medium');

						// Fade in upload container:
						$('div#plupload-upload-ui').fadeIn('medium');
						$('#uploaderSection .loading').fadeOut('medium');

					});
					updateHotspotImages(imageObject.attr('src'));
				});
			} else {
				alert(response.response_content);
			}

		});
	}


	function updateHotspotImages(src) {
		var fields = $('input[name$="[coordinates]"]');

		fields.each(function(){
			$(this).attr('data-image-url', src);
			hotspotAdmin.reset();
		});
	}

});