;(function ($, hotspots, undefined) {
	"use strict";

	/* Private: get settings and set up each map */
	var mapSetup  = function() {
		$('img.hotspots-image').responsilight();
	};


	/* Private: set up the information update when clicking on a map area */
	var infoUpdate = function() {
		$('.hotspot-info').hide();

		$('area').on('click', function(e){
			e.preventDefault();

			var $this = $(this),
				newInfo = $($this.attr('href')),
				container = $this.parents('.hotspots-container');

			if (container.hasClass( 'lightbox' ) ) { /* If the lightbox layout is selected */
				$.featherlight(newInfo, {
					afterContent: function(){
						$('.hotspot-info.featherlight-inner').show();
						var mapId = container.attr('id'),
							mapNo = mapId.match(/\d+/)[0];

						$('.featherlight-content').addClass('lightbox' + mapNo);
					}
				});

			} else { /* If some other layout is selected */
				var infoContainer = container.find('.hotspots-placeholder'),
					infoContent = infoContainer.find('.hotspots-content');

				if ( infoContent.length == 0 ) {
					infoContainer.wrapInner('<div class="hotspots-content"></div>');
					infoContent = infoContainer.find('.hotspots-content');
				}

				infoContainer.addClass('loading');
				infoContent.fadeOut('fast', function(){
					infoContent.children().hide().end().append(newInfo);
					newInfo.show();
					infoContainer.removeClass('loading');
					infoContent.fadeIn('fast');
				});
			}

		});
	};

	/* Public: initialize */
	hotspots.init = function() {
	  mapSetup();
	  infoUpdate();
	};

}(jQuery, window.hotspots = window.hotspots || {}));


jQuery(function(){
	hotspots.init();
});