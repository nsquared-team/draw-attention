;(function ($, hotspots, undefined) {
	"use strict";

	/* Private: get settings and set up each map */
	var mapSetup  = function() {
		$('img.hotspots-image').responsilight();
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
						img.css({'width': 'auto'});
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

	/* Private: show tooltip */
	var showTooltip = function(area, newInfo, eventTrigger, container){
		var showEvent = eventTrigger == 'hover' ? 'showHighlight' : 'stickyHighlight',
			hideEvent = eventTrigger == 'hover' ? 'removeHighlight' : 'unStickyHighlight';
		$(area).qtip({
			content: {
				text: newInfo
			},
			show: {
				solo: true,
				event: showEvent
			},
			hide: {
				fixed: true,
				delay: 300,
				event: hideEvent
			},
			position: {
				target: 'mouse',
				viewport: container,
				adjust: {
					mouse: false
				}
			},
			style: {
				classes: 'qtip-da-custom'
			},
			events: {
				render: function(event, api) {
					var tooltip = api.elements.tooltip,
						mapId = container.attr('id'),
						mapNo = mapId.match(/\d+/)[0];

					tooltip.addClass('tooltip-'+ mapNo);
				}
			}
		});
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

		/* Tooltip Layout */
		$('.hotspots-container.tooltip').find('area').each(function(){
			var $this = $(this),
				newInfo = $($this.attr('href')),
				container = $this.parents('.hotspots-container'),
				eventTrigger = container.find('img[usemap]').data('event-trigger');

			showTooltip(this, newInfo, eventTrigger, container);
		});


		/* Non-lightbox Layout */
		$('.hotspots-container.event-click').not('.lightbox, .tooltip').on('stickyHighlight', 'area', function(e, isSticky) {
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = isSticky ? $($this.attr('href')) : container.find('.hotspot-initial');

			showNewInfo(container, isSticky, newInfo);
		});

		/* Hover event: Mouseover */
		$('.hotspots-container.event-hover').not('.lightbox, .tooltip').on('showHighlight', 'img.hotspots-image', function(e, href){
			var $this = $(this),
				container = $this.parents('.hotspots-container'),
				newInfo = $(href);

			showNewInfo(container, false, newInfo);
		});

		/* Hover event: Mouseout */
		$('.hotspots-container.event-hover').not('.lightbox, .tooltip').on('removeHighlight', 'img.hotspots-image', function(e){
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