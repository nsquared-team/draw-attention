;(function ($, hotspots, undefined) {
	"use strict";

	var ua = window.navigator.userAgent,
		isiOS = !!ua.match(/iPad/i) || !!ua.match(/iPhone/i),
		isWebkit = !!ua.match(/WebKit/i),
		isWebkitiOS = isiOS && isWebkit,
		isMobileSafari = isiOS && isWebkit && !ua.match(/CriOS/i),
		isBrowserVersion12Up = function(){
			var temp,
				M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];

			M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];
    	if ((temp = ua.match(/version\/(\d+)/i)) != null) {
    		M.splice(1, 1, temp[1]);
    	}
    	return parseInt(M[1]) >= 12;
		};

	// Store all the leaflets on the page in an array for access later
	var leaflets = [];

	// Store all the more info area hotspots in an object for access later
	hotspots.infoSpots = {};

	var mapSetup = function(){
		$('.da-error').hide();
		$('.hotspot-info').addClass('da-hidden');

		$('img.hotspots-image').each(function(){
			var img = $(this);

			var tester = new Image();

			tester.onload = function(){
				markLoaded(img);
				moreInfoSetup(img);
				leafletSetup(img);
			};
			tester.src = img.attr('src');
		});

		if (isMobileSafari) {
			window.onpageshow = function(event){
				if (event.persisted) {
					window.location.reload();
				}
			};
		}
	};

	var markLoaded = function(img) {
		img.data('status', 'loaded');
		var container = img.parents('.hotspots-container').addClass('loaded');
	};

	var moreInfoSetup = function(img) {
		var container = img.parents('.hotspots-container');

		if (container.data('layout') == 'tooltip') {
			return;
		}

		if (container.data('layout') == 'lightbox') {
			lightboxSetup(container, img);
			return;
		}

		infoboxSetup(container, img);
	};

	var infoboxSetup = function(container, img) {
		var initial = container.find('.hotspot-initial');
		var content = container.find('.hotspots-placeholder');
		initial.addClass('visible');
		container.on('active.responsilight inactive.responsilight', function(e){
			var data = $(e.target).data('areaData');
			var info;
			if (e.type === 'active') {
				info = $(data.href);
			} else {
				info = initial;
			}

			content.children('.visible').removeClass('visible');
			info.removeClass('da-hidden').addClass('visible').appendTo(content);

			// Check for an embedded video player
			var video = info.find('.wp-video');
			if (video.length) {
				if (!info.data('video-resized')) {
					info.data('video-resized', true);
					window.dispatchEvent(new Event('resize'));
				}
			}
		});
	};

	var lightboxSetup = function(container, img) {
		container.on('active.responsilight', function(e){
			var data = $(e.target).data('areaData'),
				info = $(data.href),
				target = $(e.target),
				currentLightbox = $('.featherlight');

			if (e.type === 'active' && currentLightbox.length === 0) {
				$.featherlight(info, {
					afterContent: function(){
						var content = $('.hotspot-info.featherlight-inner'),
							lb = $('.featherlight-content'),
							mapId = container.find('map').attr('name'),
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

								var naturalHeight = img.prop('naturalHeight');

								if (newHeight < naturalHeight) {
									img.css({'width': 'auto'});
									img.animate({
										'height': newHeight
									}, 200);
								}
							}
						}, 100);

					},
					afterClose: function(){
						target.removeClass('hotspot-active');
					}
				});
			}
		});
	};

	var showTooltip = function(shape, areaData) {
		var container = $(shape._map._container);
		var content = $(areaData.href).html();

		var tip = L.responsivePopup({
			autoPan: false,
			closeButton: areaData.trigger == 'click',
			hasTip: container.width() > 840,
			maxHeight: container.height() * .9,
			offset: new L.Point(0,0)
		});

		tip.setContent(content);
		shape.bindPopup(tip);

		if (areaData.trigger === 'click') {
			shape.on('click', function(e){
				if (shape._path.classList.contains('hotspot-active')) {
					shape.closePopup()
				} else {
					shape.openPopup();
				}
			});
		} else {
			shape.on('mouseover', function(){
				shape.openPopup();
			});
			shape.on('mouseout', function(){
				shape.closePopup();
			});
		}

		container.on('click', function(e){
			e.stopPropagation();
		});
		$(document).on('click', function(e){
			shape.closePopup();
			shape._path.classList.remove('hotspot-active');
		});
	};

	var leafletSetup = function(img) {
		var id = img.data('id');
		var container = $('<div id="hotspots-map-container-' + id + '" class="hotspots-map-container"></div>');
		var imgWidth = img.width();
		var imgHeight = img.height();

		container.css({
			'width': imgWidth + 'px',
			'height': imgHeight + 'px'
		});
		img.after(container);

		var map = L.map('hotspots-map-container-' + id, {
			attributionControl: false,
			boxZoom: false,
			crs: L.CRS.Simple,
			doubleClickZoom: false,
			dragging: false,
			keyboard: false,
			minZoom: -20,
			scrollWheelZoom: false,
			tap: !isWebkitiOS,
			touchZoom: false,
			zoomControl: false,
			zoomSnap: 0,
		});

		var domImg = img.get(0);
		var natHeight = domImg.naturalHeight;
		var natWidth = domImg.naturalWidth;
		img.data('natW', natWidth);
		img.data('natH', natHeight);
		var bounds = [[0,0], [natHeight, natWidth]];
		var imageLayer = L.imageOverlay(img.attr('src'), bounds).addTo(map);
		map.fitBounds(bounds);

		leaflets.push({
			map: map,
			img: img
		});

		drawSpots(img, map);
	};

	var showContextMenu = function(shape, areaData, e) {
		var hash = areaData.href;
		var container = $(shape._map._container);

		if (!hash) {
			return;
		}

		$('.da-address-wrapper').remove();

		var windowAddress = window.location.href.split('#')[0];
		var shapeAddress = windowAddress + hash;
    var div = $('<div class="da-address-wrapper"></div>')
        .css({
            'left': e.originalEvent.pageX + 'px',
            'top': e.originalEvent.pageY + 'px'
        })
        .append('<p>' + shapeAddress + '</p>')
        .append('<span class="da-address-close">&times;</span>')
        .appendTo(document.body);

    div.find('.da-address-close').on('click', function(){
    	div.remove();
    });
	};

	var drawSpots = function(img, map) {
		var id = img.data('id');
		var mapName = img.attr('usemap').replace('#', '');
		var imageMap = $('map[name="' + mapName + '"]');
		var areas = imageMap.find('area');
		var container = img.parents('.hotspots-container');

		areas.each(function(){
			var area = $(this);
			var shape = area.attr('shape');
			var coords = area.attr('coords').split(',');
			var areaData = {
				style: area.data('color-scheme') ? area.data('color-scheme') : 'default',
				title: area.attr('title'),
				href: area.attr('href'),
				target: area.attr('target'),
				action: area.data('action'),
				layout: container.data('layout'),
				trigger: container.data('trigger')
			};
			switch(shape) {
				case 'circle':
					renderCircle(coords, map, img, areaData);
					break;
				case 'rect':
					renderPoly(coords, map, img, areaData);
					break
				case 'poly':
					renderPoly(coords, map, img, areaData);
					break;
			}
		});
		// Link to area after all the spots are drawn
		linkToArea();
	};

	var renderCircle = function(coords, map, img, areaData) {
		var x = coords[0];
		var y = img.data('natH') - coords[1];
		var rad = coords[2];
		var circle = L.circle([y,x], {
			radius: rad,
			className: 'hotspot-' + areaData.style,
			title: areaData.title
		}).addTo(map)
		shapeEvents(circle, areaData);
	};

	var renderPoly = function (coords, map, img, areaData) {
		var xCoords = [];
		var yCoords = [];
		for (var i = 0; i < coords.length; i++) {
			if (i % 2 == 0) {
				xCoords.push(coords[i]);
			} else {
				yCoords.push(coords[i]);
			}
		}

		var polyCoords = yCoords.map(function(coord, index) {
			return [img.data('natH') - coord, xCoords[index]];
		});

		var poly = L.polygon(polyCoords, {
			className: 'hotspot-' + areaData.style,
			title: areaData.title
		}).addTo(map)

		// If this is a more info hotspot, add it to the infoSpots object
		if (areaData.href.charAt(0) === '#') {
			var spotName = areaData.href.replace('#', '');
			hotspots.infoSpots[spotName] = poly;
		}

		shapeEvents(poly, areaData);
	};

	var shapeOver = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.data('areaData', areaData);
		$shape.trigger('over.responsilight');
		if (areaData.trigger === 'hover' && e.type !== 'touchstart') {
			$shape.addClass('hotspot-active');
			$shape.trigger('active.responsilight');
		}
	};

	var shapeOut = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.data('areaData', areaData);
		$shape.trigger('out.responsilight');
		if (areaData.trigger === 'hover') {
			$shape.removeClass('hotspot-active');
			$shape.trigger('inactive.responsilight');
		}
	};

	var shapeClick = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.data('areaData', areaData);
		$shape.trigger('areaClick.responsilight');
		if (areaData.trigger === 'hover' && e.type !== 'touchstart' && !isMobileSafari) {
			return;
		}
		$shape.toggleClass('hotspot-active');
		if ($shape.hasClass('hotspot-active')) {
			$shape.trigger('active.responsilight');
		} else {
			$shape.trigger('inactive.responsilight');
		}
		var oldActive = $shape.siblings('.hotspot-active');
		if (oldActive.length) {
			oldActive.removeClass('hotspot-active');
		}
	};

	var shapeEvents = function(shape, areaData) {
		// Handle URL spots
		if (areaData.action == 'url') {
			if (areaData.title) {
				shape.bindTooltip(areaData.title);
			}
			shape.on('click', function(e) {
				if (areaData.target == '_new' && !isMobileSafari) { // new window
					window.open(areaData.href, '_blank');
				} else { // same window
					var first = areaData.href.charAt(0);
					var targetElem;
					try {
						targetElem = first === '#' ? $(areaData.href) : null;
					} catch (error) {
						targetElem = null
					}
					if (targetElem && targetElem.length) { // hash link to existing target
						$('html, body').animate({
							scrollTop: targetElem.offset().top - 50
						}, 750, function(){ // callback after scrolling
							history.pushState({}, '', areaData.href);
						})
					} else {
						window.location = areaData.href;
					}
				}
			});
			return;
		}

		// Show right-click context menu for logged-in admins only
		if (drawattentionData.isLoggedIn && drawattentionData.isAdmin) {
			shape.on('contextmenu', function(e){
				showContextMenu(shape, areaData, e);
			});
		}

		// Handle tooltip spots
		if (areaData.layout === 'tooltip') {
			showTooltip(shape, areaData);
		}

		// Add styled tooltip to all non-hover areas
		if (areaData.action === 'url' || areaData.trigger === 'click' && areaData.layout !== 'tooltip') {
			if (areaData.title) {
				shape.bindTooltip(areaData.title);
			}
		}

		// Handle all other spots
		var moved = false;

		shape.on('touchstart touchmove touchend click mouseover mouseout', function(e){
			switch(e.type) {
				case 'touchstart':
					moved = false;
					break;
				case 'touchmove':
					moved = true;
					break;
				case 'touchend':
					if (moved) {
						return;
					}
					shapeOver(shape, areaData, e);
					shapeClick(shape, areaData, e);
					break;
				case 'click':
					shapeClick(shape, areaData, e);
					break;
				case 'mouseover':
					shapeOver(shape, areaData, e);
					if (isMobileSafari && !isBrowserVersion12Up()) {
						shapeClick(shape, areaData, e);
					}
					break;
				case 'mouseout':
					shapeOut(shape, areaData, e);
					break;
			}
		});
	};

	var linkToArea = function(){ // Called after the shapes are drawn
		var hash = window.location.hash,
			area = null;

		if (!hash) return;

		area = $('area[href="' + hash + '"]');

		if (!area.length) return;

		var spotName = hash.replace('#', '');
		hotspots.infoSpots[spotName].fire('click')
	};

	hotspots.setup = function(){
		mapSetup();
	};

	hotspots.resizeTimer = null;
	hotspots.resizing = false;

	hotspots.init = function(){ // For backward compatibility - resets the size of the leaflet on demand
		leaflets.forEach(function(item){
			var isLoaded = item.img.data('status') === 'loaded';

			if (!isLoaded) {
				return;
			}

			item.img.next('.hotspots-map-container').css({
				'width': item.img.width() + 'px',
				'height': item.img.height() + 'px'
			});
			item.map.invalidateSize(true);
			item.map.fitBounds([[0,0], [item.img.data('natH'), item.img.data('natW')]]);
		});
	};

	hotspots.compatibilityFixes = function(){
		if (window.Foundation) { /* Fix for Foundation firing tag change event indiscriminately when some items are clicked on the page */
			$(window).on('change.zf.tabs', function(e){
				if (e.target.tagName !== 'INPUT') {
					hotspots.init();
				}
			});
		}
		$(window).on('pageloaded', function(){
			hotspots.init();
		});
		$(window).on('load', function(){
			hotspots.init();
		});
		$('a[data-vc-accordion], .et_pb_tabs .et_pb_tabs_controls li, .et_pb_toggle_title').on('click', function() {
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
		$('.elementor-tabs').on('click', '.elementor-tab-title', function(){
			hotspots.init();
		});
	};

}(jQuery, window.hotspots = window.hotspots || {}));

jQuery(function(){
	hotspots.setup();
	hotspots.compatibilityFixes();
});

jQuery(window).on('resize orientationchange', function(e){
	var $window = jQuery(this);
	if (!hotspots.resizing) {
		$window.trigger('resizeStart.responsilight');
		hotspots.resizing = true;
	}
	clearTimeout(hotspots.resizeTimer);
	hotspots.resizeTimer = setTimeout(function(){
		hotspots.init();
		$window.trigger('resizeComplete.responsilight');
		hotspots.resizing = false;
	}, 250);
});

window.onerror = function(errorMsg, url, lineNumber) { // This should be a fun little experiement!
	var errorBox = jQuery('.da-error').show();
	if (errorBox.length) {
		var contents = errorBox.html();
		errorBox.html(contents + '<br/><br/><strong>Error:</strong> ' + errorMsg + '<br/>Line ' + lineNumber + ': ' + url);
	}
	return false;
}