;(function ($, hotspots, undefined) {
	"use strict";

	var index = 1;


	/* Get settings and initialize responsilight */
	var mapSetup = function(){
		$('img.hotspots-image').each(function(){
			var img = $(this),
				imgId = img.data('id'),
				colorData = window['daStyles' + imgId];
			img.responsilight({
				colorSchemes: colorData
			});
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
				classes: 'qtip-da-custom tip-title-only qtip-da-hover'
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
					if (area.data('canvasHover') && area.data('canvasHover').length) {
						area.data('canvasHover').removeClass('canvas-show');
					}
					if (area.data('canvasDisplay') && area.data('canvasDisplay').length) {
						area.data('canvasDisplay').addClass('canvas-show');
					}
				}
			}
		};

		/* style hover-only tooltip */
		if (container.hasClass('event-hover')) {
			qtipSettings.style.classes = 'qtip-da-custom qtip-da-hover';
		}

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
		var currentLightbox = $('.featherlight');
		if (e.type === 'active' && currentLightbox.length === 0) {
			$.featherlight(info, {
				afterContent: function(){
					var content = $('.hotspot-info.featherlight-inner'),
						lb = $('.featherlight-content');
					mapId = area.parent('map').attr('name');
					mapNo = mapId.match(/\d+/)[0];

					content.show();
					lb.addClass('lightbox-' + mapNo);

					setTimeout(function(){
						var img = content.find('img'),
							contentHeight = content.get(0).scrollHeight,
							imgHeight = img.height(),
							lbHeight = lb.outerHeight();

						if (contentHeight > lbHeight) {
							var diff = contentHeight - lbHeight + 50,
								newHeight = imgHeight - diff,
								minHeight = $(window).innerHeight()/2;

							newHeight = newHeight < minHeight ? minHeight : newHeight;

							img.height(newHeight);
							img.css({'width': 'auto'});
						}
					}, 100)

				},
				afterClose: function() {
					area.removeClass('active');
					setTimeout(function(){
						area.trigger('blur');
						container.trigger('focus');
					}, 100);
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

		var oldContent = content.children('.visible');

		oldContent.removeClass('visible');
		content.children().removeClass('visible');

		info.removeClass('da-hidden').addClass('visible').appendTo(content);

		// Check for an embedded video player
		var video = info.find('.wp-video');
		if (video.length) {
			if (!info.data('video-resized')) {
				info.data('video-resized', true);
				window.dispatchEvent(new Event('resize'));
			}
		}
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
		areas.off('active.responsilight').on('active.responsilight', function(e){
			showLightbox(container, $(this), e);
		});
	};


	/* Setup Infobox areas */
	var infoboxSetup = function(areas, container) {
		var infoContainer = container.find('.hotspots-placeholder'),
			infoContent = infoContainer.find('.hotspots-content');

		areas.off('active.responsilight inactive.responsilight').on('active.responsilight inactive.responsilight', function(e){
			showInfobox(infoContainer, $(this), e)
		});
	};

	/* Link to an area */
	var linkToArea = function(){
		var hash = window.location.hash,
			area = null;

		if (!hash) return;

		if (hash) {
			area = $('area[href="' + hash + '"]');
		}

		if (!area.length) return;

		area.trigger('focus');
		area.trigger('mousedown');

		/* Calculate where the image and area are on the page */
		var map = area.parents('map'),
			mapRef = map.attr('name'),
			img = $('img[usemap="#' + mapRef + '"]'),
			imgTop = img.offset().top,
			coords = area.attr('coords').split(','),
			yCoords = [];

		for (var i=0; i<coords.length; i++) {
			if (i%2 != 0) {
				yCoords.push(coords[i]);
			}
		}

		var areaImgTop  = Math.min.apply(Math, yCoords),
			areaImgBottom = Math.max.apply(Math, yCoords),
			windowHeight = $(window).height(),
			windowBottom = imgTop + windowHeight,
			areaBottom = imgTop + areaImgBottom,
			areaTop = imgTop + areaImgTop,
			padding = 50,
			scrollCoord;

		// Scroll to the area to be sure it's in the view
		if (areaBottom > windowBottom) {
			scrollCoord = imgTop + (areaBottom - windowBottom) + padding;
			if ((areaBottom - areaTop) > windowHeight) {
				scrollCoord = areaTop - padding;
			}
		} else {
			scrollCoord = imgTop - padding;
		}

		setTimeout(function(){
			window.scrollTo(0, scrollCoord);
		}, 1);
	}


	/* Set up the information update when interacting with the image */
	var daInitialize = function(){
		var ua = window.navigator.userAgent,
			isiOS = !!ua.match(/iPad/i) || !!ua.match(/iPhone/i),
			isWebkit = !!ua.match(/WebKit/i),
			isMobileSafari = isiOS && isWebkit && !ua.match(/CriOS/i);

		$('.da-error').hide();
		$('.hotspot-info').addClass('da-hidden');

		var containers = $('.hotspots-container');

		// Follow URLs for touch screens
		containers.find('area.url-area').off('active.responsilight').on('active.responsilight', function(e){
			var link = $(this),
				href = link.attr('href'),
				target = link.attr('target');



			if (target == '_new' && !isMobileSafari) {
				window.open(href, '_blank');
			} else {
				window.location = href;
			}
		});
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
				container.find('.hotspot-initial').addClass('visible');
				infoboxSetup(container.find('area.more-info-area'), container);
			}
		});
		linkToArea();

		if (isMobileSafari) {
			window.onpageshow = function(event){
				if (event.persisted) {
					window.location.reload();
				}
			};
		}
	};


	/* Fix compatibility with common plugins/addons */
	hotspots.compatibilityFixes = function(){
		$(window).on('pageloaded change.zf.tabs', function(){
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
		$('.responsive-tabs').on('click', '.responsive-tabs__list__item', function(){
			hotspots.init();
		});
		$(window).on('et_hashchange', function() {
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});
		$('.vc_tta-tabs-container').on('click', '.vc_tta-tab', function(){
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