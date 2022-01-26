;(function ($, hotspots, undefined) {
	"use strict";

	var ua = window.navigator.userAgent,
		isiOS = !!ua.match(/iPad/i) || !!ua.match(/iPhone/i),
		isWebkit = !!ua.match(/WebKit/i),
		isWebkitiOS = isiOS && isWebkit,
		isMobileSafari = isiOS && isWebkit && !ua.match(/CriOS/i),
		getBrowserVersion = function(){
			var temp,
				M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];

			M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];
    	if ((temp = ua.match(/version\/(\d+)/i)) != null) {
    		M.splice(1, 1, temp[1]);
    	}
    	return parseInt(M[1]);
		}

	// Store all the leaflets on the page in an array for access later
	var leaflets = [];

	// Store all image maps on the page in an object for access later
	var imageMaps = {};

	// Store all the more info area hotspots in an object for access later
	hotspots.infoSpots = {};

	var mapSetup = function(){
		$('.da-error').hide();
		$('.hotspot-info').addClass('da-hidden');

		var images = $('img.hotspots-image, picture.hotspots-image img'); // Select images as well as images inside picture elements

		images.each(function(){
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

	var stopAudioVideo = function(el) {
    var iframe = el.querySelector( 'iframe[src*=youtube]');
    var video = el.querySelector( 'video' );
    var audio = el.querySelector( 'audio' );
    if (iframe) {
    	var iframeSrc = iframe.src;
    	iframe.src = iframeSrc;
    }
    if (video) {
    	video.pause();
    }
    if (audio) {
    	audio.pause();
    }
	}

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

			var visibleContent = content.children('.visible');
			stopAudioVideo(visibleContent.get(0));
			visibleContent.removeClass('visible');
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
				container = info.parent(),
				target = $(e.target),
				currentLightbox = $('.featherlight');

			if (e.type === 'active' && currentLightbox.length === 0) {
				$.featherlight('<div class="hotspot-info"></div>', {
					closeLabel: drawattentionData.closeLabel,
					closeSpeed: 250,
					closeOnEsc: false,
					afterContent: function(){
						var content = $('.featherlight-inner'),
							lb = $('.featherlight-content'),
							mapNo = img.data('id');

						info.appendTo(content).show();
						lb.addClass('lightbox-' + mapNo);

						// Fix accessibility issue - links not focusable by keyboard
						var untabbables = info.find('a[tabindex=-1]');
						untabbables.attr('tabindex', 0);

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

						lightboxAnchorLinks(content, target);
					},
					afterOpen: function() {
						$('body').on('keyup', documentEsc);
					},
					afterClose: function(){
						target.removeClass('hotspot-active');
						$('body').off('keyup', documentEsc);
					},
					beforeClose: function(){
						stopAudioVideo(document.querySelector('.featherlight-content'));
						setTimeout(function(){
							info.hide().appendTo(container);
						}, 500); // delay hiding content and moving it back outside the lightbox
					}
				});
			}
		});
	};

	var documentEsc = function(e) {
		if (e.keyCode === 27) {
			$.featherlight.current().close();
		}
	};

	var lightboxAnchorLinks = function(content, hotspot){
		var links = content.find('.hotspot-content a[href^="#"]');
		links.on('click', function(e){
			e.preventDefault();
			var targetEl = $(e.target.hash);
			var current = $.featherlight.current();
			if (!current) return
			current.afterClose = function(){
				hotspot.removeClass('hotspot-active');
				$('html').removeClass('with-featherlight');
				$('html, body').animate({
					scrollTop: targetEl.offset().top
				}, 500);
			}
			current.close();
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

		// Check to be sure the container is on the page
		// To prevent errors when loaded in Elementor popup
		var containerTest = $('#hotspots-map-container-' + id);
		if (!containerTest.length) {
			return
		}

		var map = L.map('hotspots-map-container-' + id, {
			attributionControl: false,
			boxZoom: false,
			crs: L.CRS.Simple,
			doubleClickZoom: false,
			dragging: false,
			keyboard: false,
			minZoom: -20,
			scrollWheelZoom: false,
			// tap: !isWebkitiOS,
			tap: true,
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

		/* If the image has a usemap attribute, detach the map and store it in the imageMaps object */
		if (img[0].hasAttribute('usemap')) {
			var mapName = img.attr('usemap').replace('#', '');
			var imageMap = $('map[name="' + mapName + '"]');
			imageMaps[id] = imageMap.detach();
			img.removeAttr('usemap');
		} else { /* Else we've already removed the image map, so we just need to get it */
			var imageMap = imageMaps[id];
		}

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
				spot: area.data('id'),
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
		// A11y fixes after all the spots are drawn
		a11yFixes(img, map);

		// Link to area after all the spots are drawn
		hotspots.linkToArea();
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
		});

		// Attach the area data to the path as soon as it's added to the map, a11y attributes
		poly.on('add', function(e) {
			var $poly = $(e.target.getElement());
			$poly.data('areaData', areaData);
			$poly.attr('tabindex', '0');
			$poly.attr('aria-label', areaData.title);
		});

		poly.addTo(map);

		// If this is a more info hotspot, add it to the infoSpots object
		if (areaData.href.charAt(0) === '#') {
			var spotName = areaData.href.replace('#', '');
			hotspots.infoSpots[spotName] = poly;
		}

		shapeEvents(poly, areaData);
	};

	var shapeOver = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.trigger('over.responsilight');
		if (areaData.trigger === 'hover' && e.type !== 'touchstart' & e.type !== 'keypress') {
			$shape.addClass('hotspot-active');
			$shape.trigger('active.responsilight');
		}
	};

	var shapeOut = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.trigger('out.responsilight');
		if (areaData.trigger === 'hover' && e.type !== 'keypress') {
			$shape.removeClass('hotspot-active');
			$shape.trigger('inactive.responsilight');
			// If mouse user, blur hover spots when mouse moves out
			if (e.type === 'mouseout') {
				$shape.trigger('blur');
			}
		}
	};

	var shapeClick = function(shape, areaData, e) {
		var $shape = $(e.target.getElement());
		$shape.trigger('areaClick.responsilight');
		if (areaData.trigger === 'hover' && e.type !== 'touchstart' && e.type !== 'keypress' && !isMobileSafari) {
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

		shape.on('touchstart touchmove touchend click mouseover mouseout keypress focus blur', function(e){
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
				case 'keypress':
					var key = e.originalEvent.which;
					if ( key == 13 || key == 32) {
						e.originalEvent.preventDefault();
						shapeClick(shape, areaData, e);
					}
					break;
				case 'mouseover':
					if (!isMobileSafari || (isMobileSafari && getBrowserVersion < 13)) {
						shapeOver(shape, areaData, e);
					}
					if (isMobileSafari && !(getBrowserVersion() >= 12)) {
						shapeClick(shape, areaData, e);
					}
					break;
				case 'focus':
					shapeOver(shape, areaData, e);
					break;
				case 'blur':
					shapeOut(shape, areaData, e);
					break;
				case 'mouseout':
					shapeOut(shape, areaData, e);
					break;
			}
		});
	};

	var a11yFixes = function(img, map){
		let svg = img.siblings('.leaflet-container').find('.leaflet-overlay-pane svg');
		let id = img.data('id');

		// Add title and description to SVG
		svg.prepend('<description id="img-desc-' + id + '">' + img.data('image-description') + '</description');
		svg.prepend('<title id="img-title-' + id + '">' + img.data('image-title') + '</title>');

		// Use title and desc to describe the svg
		svg.attr('aria-labelledby', 'img-title-' + id);
		svg.attr('aria-describedby', 'img-desc-' + id);

		// Make svg screen reader traversable
		svg.attr('role', 'group');

		// Create a traversable list
		var group = svg.find('g').attr('role', 'list');
		group.children().attr('role', 'listitem');

		// Handle tooltips for the map
		map.on('popupopen',function(popup) {
		  // shift focus to the popup when it opens
		  $(popup.popup._container).find('.leaflet-rrose-content').attr('tabindex','-1').focus();

		  // move the close button to the end of the popup content so screen readers reach it
		  // after the main popup content, not before
		  var close = $(popup.popup._container).find('.leaflet-rrose-close-button').detach();
		  close.attr('aria-label','Close item');
		  $(popup.popup._container).append(close);

			$(document).on('keydown', function(e){
				if (e.which == 27) {
					map.closePopup();
				}
			});

		});

		// return focus to the icon we started from before opening the pop up
		map.on('popupclose',function(popup) {
			$(document).off('keydown');
			$(popup.popup._source._path).focus();
		});

	};

	hotspots.linkToArea = function(){ // Called after the shapes are drawn
		var hash = window.location.hash;
		if (!hash) return;

		var spotName = hash.replace('#', '');
		if (!hotspots.infoSpots.hasOwnProperty(spotName)) return;

		Object.keys(hotspots.infoSpots).forEach(function(key) {
			hotspots.infoSpots[key].closeTooltip()
		})

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
			$(window).on('change.zf.tabs', function(e) {
				if (e.target.tagName !== 'INPUT') {
					hotspots.init();
				}
			});
		}

		$(window).on('pageloaded load', function() { /* Listen for pageloaded and load events on the window */
			hotspots.init();
		});

		$(window).on('et_hashchange', function() { /* Listen for Divi hashchange */
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});

		$('.ult_tabs').on('click', '.ult_tab a', function() { /* Ultimate tabs */
			setTimeout(function() {
				hotspots.init();
			}, 2000);
		});

		$('a[data-vc-accordion], .et_pb_tabs .et_pb_tabs_controls li, .et_pb_toggle_title').on('click', function() { /* Divi tabs, accordion, toggle */
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});
		$('.vc_tta-tabs-container').on('click', '.vc_tta-tab', function() { /* Visual composer tabs */
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});

		$('.ui-tabs-anchor, .nav-tabs > li').on('click', function() { /* UI Tabs */
			setTimeout(function() {
				hotspots.init();
			}, 750);
		});

		$('.fl-accordion-button').on('click', function() { /* Beaver Builder accordion */
			setTimeout(function() {
				hotspots.init();
			}, 500);
		});

		$('.responsive-tabs').on('click', '.responsive-tabs__list__item', function() { /* Responsive tabs */
			hotspots.init();
		});

		$('.elementor-tabs').on('click', '.elementor-tab-title', function() { /* Elementor tabs */
			hotspots.init();
		});

		$('.fl-tabs').on('click', 'a.fl-tabs-label', function() { /* Beaver Builder tabs */
			hotspots.init();
		});

		$('.uabb-adv-accordion-button .uabb-tabs').on('click', function() { /* Ultimate Beaver Builder addons accordion and tabs */
			setTimeout(function() {
				hotspots.init();
			}, 250);
		});
		$('.w-tabs-item').on('click', function() { /* Custom Woocommerce tabs */
			setTimeout(function() {
				hotspots.init();
			}, 1000);
		});
	};

}(jQuery, window.hotspots = window.hotspots || {}));

jQuery(function(){
	hotspots.setup();
	hotspots.compatibilityFixes();
});

jQuery(document).on('elementor/popup/show', function(){
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

jQuery(window).on('hashchange', function(){
	hotspots.linkToArea();
});

window.onerror = function(errorMsg, url, lineNumber) { // This should be a fun little experiement!
	var errorBox = jQuery('.da-error').show();
	if (errorBox.length) {
		var contents = errorBox.html();
		errorBox.html(contents + '<br/><br/><strong>Error:</strong> ' + errorMsg + '<br/>Line ' + lineNumber + ': ' + url);
	}
	return false;
}