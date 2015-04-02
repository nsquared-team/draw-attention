;(function ($, hotspots, undefined) {
	"use strict";

	/* Private: get settings and set up each map */
	var mapSetup  = function() {
		$('img.hotspots-image').responsilight();

		$('.hotspots-map area').on('click', function(e){
			e.preventDefault();
		});
	};

	/* Private: show lightbox */
	var showLightbox = function(container, isSticky, info, area) {
		var mapId, mapNo;
		if (isSticky) {
			$.featherlight(info, {
				afterContent: function(){
					var content = $('.hotspot-info.featherlight-inner'),
						lb = $('.featherlight-content');
					mapId = container.attr('id');
					mapNo = mapId.match(/\d+/)[0];

					content.show();
					lb.addClass('lightbox-' + mapNo);

					var img = content.find('img'),
						imgHeight = img.height(),
						lbHeight = lb.height(),
						maxImgHeight = lbHeight * 0.8;

					if ( imgHeight > maxImgHeight ) {
						img.height(maxImgHeight);
					}
				},
				afterClose: function() {
					area.data('stickyCanvas', false);
					$('#' + mapId).find('canvas').fadeOut('slow', function(){
						$(this).remove();
					});
				}
			});
		}
	};

	/* Private: show info area */
	var showNewInfo = function(container, isSticky, info) {
		var infoContainer = container.find('.hotspots-placeholder'),
			infoContent = infoContainer.find('.hotspots-content');

		if ( infoContent.length == 0 ) {
			infoContainer.wrapInner('<div class="hotspots-content"></div>');
			infoContent = infoContainer.find('.hotspots-content');
		}

		infoContainer.addClass('loading');
		infoContent.fadeOut('fast', function(){
			infoContent.children().hide().end().append(info);
			info.show();
			infoContainer.removeClass('loading');
			infoContent.fadeIn('fast');
		});
	}

	/* Private: set up the information update when clicking on a map area */
	var daInitialize = function() {
		$('.hotspot-info').hide();

		/* Lightbox Layout */
		$('.hotspots-container.lightbox').on('stickyHighlight', 'area', function(e, isSticky){
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = isSticky ? $($this.attr('href')) : container.find('.hotspot-initial');

			showLightbox(container, isSticky, newInfo, $this);
		});

		/* Non-lightbox Layout */
		$('.hotspots-container.event-click').not('.lightbox').on('stickyHighlight', 'area', function(e, isSticky) {
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = isSticky ? $($this.attr('href')) : container.find('.hotspot-initial');

			showNewInfo(container, isSticky, newInfo);
		});

		/* Hover event: Mouseover */
		$('.hotspots-container.event-hover').not('.lightbox').on('showHighlight', 'img.hotspots-image', function(e, href){
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = $(href);

			showNewInfo(container, false, newInfo);
		});

		/* Hover event: Mouseout */
		$('.hotspots-container.event-hover').not('.lightbox').on('removeHighlight', 'img.hotspots-image', function(e){
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = container.find('.hotspot-initial');

			showNewInfo(container, false, newInfo);
		});

	};

	/* Public: initialize */
	hotspots.init = function() {
	  mapSetup();
	  daInitialize();
	};

}(jQuery, window.hotspots = window.hotspots || {}));


jQuery(function(){
	hotspots.init();
});