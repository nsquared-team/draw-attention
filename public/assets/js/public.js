;(function ($, hotspots, undefined) {
	"use strict";


	/* Get settings and initialize responsilight */
	var mapSetup = function(){
		// TODO: UPDATE THIS CALL SO IT'S CORRECT
		$('img.hotspots-image').responsilight({
			alwaysVisible: true
		});
	};


	/* Display title tooltips for URL areas */
	var showUrlTooltip = function(area) {
		if (typeof jQuery.qtip === 'undefined') {
			return;
		}

		area.qtip({
			position: {
				target: 'mouse',
				viewport: $(window),
				adjust: {
					x: 15,
					y: 15
				}
			},
			style: {
				classes: 'qtip-da-custom tip-title-only'
			},
			events: {
				render: function(event, api) {
					var tooltip = api.elements.tooltip,
						mapId = area.parent('map').attr('name'),
						mapNo = mapId.match(/\d+/)[0];

					tooltip.addClass('tooltip-'+ mapNo);
				}
			}
		});
	};


	/* Display content tooltips for more info areas */
	var showTooltip = function(area, container, tooSmall) {
		if (typeof jQuery.qtip === 'undefined') {
			return;
		}

		var newInfo = $(area.attr('href'));

		var qtipSettings = {
			content: {
				text: newInfo,
				button: true
			},
			show: {
				event: 'active.responsilight'
			},
			hide: {
				fixed: true,
				delay: 300,
				event: 'inactive.responsilight unfocus'
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
				},
				hide: function(event, api) {
					var mapId = container.attr('id');
					area.removeClass('active');
					area.data('canvasHover').removeClass('canvas-show');
					if (area.data('canvasDisplay') && area.data('canvasDisplay').length) {
						area.data('canvasDisplay').addClass('canvas-show');
					}
				}
			}
		};

		/* qtip settings for small screens */
		if (tooSmall) {
			qtipSettings.show.modal = {
				on: true,
				blur: false
			};
			qtipSettings.position = {
				my: 'center',
				at: 'center',
				target: $(window),
				adjust: {
					scroll: false,
					mouse: false
				}
			};
			qtipSettings.events.visible = function(event, api) {
				var tooltip = api.elements.tooltip,
					winHeight = $(window).height(),
					tipHeight = tooltip.height(),
					img = tooltip.find('img'),
					imgHeight = img.height();

				if (tipHeight > winHeight) {
					var textHeight = tipHeight - imgHeight;
					if (textHeight < winHeight) {
						img.css({
							'width': 'auto',
							'maxHeight': winHeight - textHeight + 'px'
						});
						api.reposition();
					}
				}
			}
		/* qtip settings for larger screens */
		} else {
			qtipSettings.content.title = '&nbsp;';
			qtipSettings.show.solo = true;
			qtipSettings.show.effect = function() {
				$(this).fadeTo(300, 1);
			}
			qtipSettings.position = {
				target: 'mouse',
				viewport: $(window),
				adjust: {
					mouse: false,
					method: 'shift'
				}
			}
			/* Make tooltip follow mouse if action is hover */
			if (container.hasClass('event-hover')) {
				qtipSettings.position.adjust.mouse = true;
				qtipSettings.position.adjust.x = 15;
			}
			/* Fix for mobile safari in landscape */
			if ('ontouchstart' in document.documentElement && window.innerWidth > window.innerHeight) {
				qtipSettings.position.target = 'event';
				qtipSettings.position.adjust.mouse = true;
				qtipSettings.position.adjust.method = 'flipinvert';
			}
		}

		area.qtip(qtipSettings);
	};


	/* Display lightbox for more info areas */
	var showLightbox = function(container, area, e) {
		var mapId, mapNo;
		var info = $(area.attr('href'));
		if (e.type === 'active') {
			$.featherlight(info, {
				afterContent: function(){
					var content = $('.hotspot-info.featherlight-inner'),
						lb = $('.featherlight-content');
					mapId = area.parent('map').attr('name');
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
					area.removeClass('active');
					area.data('canvasHover').removeClass('canvas-show');
					if (area.data('canvasDisplay') && area.data('canvasDisplay').length) {
						area.data('canvasDisplay').addClass('canvas-show');
					}
				}
			});
		}
	}


	/* Display new info box for more info areas */
	var showInfobox = function(content, area, e) {
		var info;

		if (e.type === 'active') {
			info = $(area.attr('href'));
		} else {
			info = content.find('.hotspot-initial');
		}

		var oldContent = content.children(':visible');

		oldContent.fadeOut('fast', function(){
			content.children().hide().end().append(info);
			info.show();
			info.fadeIn('fast');
		});
	}


	/* Setup Tooltip areas */
	var tooltipSetup = function(areas, container) {
		var screenWidth = $(window).width(),
			daWidth = container.width(),
			tipWidth = 280,
			tooSmall = false;

		if ( screenWidth<tipWidth*3 || ( screenWidth<tipWidth*4 && daWidth/screenWidth>0.75) ) {
			tooSmall = true;
		}

		areas.each(function(){
			showTooltip($(this), container, tooSmall);
		});
	};


	/* Setup Lightbox areas */
	var lightboxSetup = function(areas, container) {
		areas.on('active.responsilight', function(e){
			showLightbox(container, $(this), e);
		});
	};


	/* Setup Infobox areas */
	var infoboxSetup = function(areas, container) {
		var infoContainer = container.find('.hotspots-placeholder'),
			infoContent = infoContainer.find('.hotspots-content');

		if (!infoContent.length) {
			infoContent = $('<div></div>', {'class': 'hotspots-content'});
			infoContainer.wrapInner(infoContent);
		}

		areas.on('active.responsilight inactive.responsilight', function(e){
			showInfobox(infoContainer, $(this), e)
		});
	};


	/* Set up the information update when interacting with the image */
	var daInitialize = function(){
		$('.da-error').hide();
		$('.hotspot-info').hide();

		var containers = $('.hotspots-container');

		// Show tooltips for URL areas
		containers.find('area.url-area').each(function(){
			showUrlTooltip($(this));
		});

		// Prevent default action when clicking more info areas
		containers.find('area.more-info-area').on('click', function(e){
			e.preventDefault();
		});

		// Sort containers into more info types, call setup
		containers.each(function(index){
			var container = $(this);
			if (container.hasClass('layout-tooltip')) {
				tooltipSetup(container.find('area.more-info-area'), container);
			} else if (container.hasClass('layout-lightbox')) {
				lightboxSetup(container.find('area.more-info-area'), container);
			} else {
				infoboxSetup(container.find('area.more-info-area'), container);
			}
		});
	};


	/* Fix compatibility with common plugins/addons */
	hotspots.compatibilityFixes = function(){
		$(window).on('pageloaded', function(){
			hotspots.init();
		});
		$(window).on('load', function(){
			$('#canvas-undefined').remove();
			hotspots.init();
		});
		$('.et_pb_tabs .et_pb_tabs_controls li, .et_pb_toggle_title').on('click', function() {
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});
		$('.ui-tabs-anchor, .nav-tabs > li').on('click', function() {
			setTimeout(function() {
				hotspots.init();
			}, 750);
		});
		$(window).on('et_hashchange', function() {
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});
	};


	/* Initialize */
	hotspots.init = function() {
		mapSetup();
		daInitialize();
	};


}(jQuery, window.hotspots = window.hotspots || {}));

jQuery(function(){
	hotspots.init();
	hotspots.compatibilityFixes();
});