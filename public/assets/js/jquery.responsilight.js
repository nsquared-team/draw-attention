;(function ($, window, document, undefined) {

	/* ------------------------------------------- */
	/* Browser support checking */
	/* ------------------------------------------- */
	var hasCanvas,
		hasPointerEvents,
		doHighlights = false;

	/* test if browser supports canvas */
	hasCanvas = (function() {
			return !!document.createElement('canvas').getContext;
		})();

	/* test if browser supports non-SVG pointer-events */
	hasPointerEvents = (function () {
		var supports = false,
			a = document.createElement("x");
			a.style.cssText = "pointer-events:auto;";

		if (window.PointerEvent) {
			supports = true;
		} else if (a.style.pointerEvents === 'auto') {
			supports = true;
		}
		return supports;
	})();

	if (hasCanvas && hasPointerEvents) {
		doHighlights = true;
	}

	/* ------------------------------------------- */
	/* Helpers */
	/* ------------------------------------------- */

	var opts, /* Define this here so the options are available to all functions */
		convertHexToDecimal,
		convertToRgba;

	convertHexToDecimal = function(hex) {
		return Math.max(0, Math.min(parseInt(hex, 16), 255));
	}

	convertToRgba = function(color, opacity) {
		color = color.replace('#', '');
		return 'rgba(' + convertHexToDecimal(color.substr(0, 2)) + ',' + convertHexToDecimal(color.substr(2, 2)) + ',' + convertHexToDecimal(color.substr(4, 2)) + ',' + opacity + ')';
	}

	/* ------------------------------------------- */
	/* Canvas magic */
	/* ------------------------------------------- */

	var drawIt,
		cleanUp,
		prepImage,
		drawShape,
		drawPoly,
		drawCircle,
		drawRect,
		drawOptions;

	drawIt = function(img, map) {
		if(doHighlights) {
			prepImage(img, map);
			linkToHotspot(img, map);
			cleanUp(img,map);
		} else {
			simpleMap(map);
		}
	};

	cleanUp = function(img,map){
		map.find('area').each(function(){
			var $this = $(this);
			if($this.data('stickyCanvas') && $this.data('stickyCanvas' == true)) {
				var id = $this.attr('id'),
					stickyCanvas = $('#canvas-' + id);

				drawShape(stickyCanvas,$this,img);
			}
		});
	};

	prepImage = function(img, map) {
		var w = img.width(),
			h = img.height(),
			mapName = map.attr('name'),
			wrapped,
			$wrap,
			index = 0;

		wrapped = jQuery('<div id="wrap-' + mapName + '"></div>');

		if(img.parent('#wrap-' + mapName).length < 1) {
			img.wrap(wrapped);
		}

		$wrap = $('#wrap-' + mapName);

		if($wrap.parent().width() < img.get(0).naturalWidth) {
			$wrap.css({
				'position': 'relative',
				'width': 'auto',
				'margin': '0 auto',
				'line-height': 0
			});
		} else {
			$wrap.css({
				'position': 'relative',
				'line-height': '0',
				'width': w
			});
		}

		map.find('area').each(function(){
			var $this = $(this);
			index++;
			$this.attr('id', mapName + '-area-' + index);
		});
	};

	drawShape = function($canvas, area, img){
		var canvas = $canvas.get(0),
			context = canvas.getContext('2d'),
			shape = area.attr('shape');

		context.clearRect(0,0,canvas.width,canvas.height);

		var coords = area.attr('coords').split(','),
			xCoords = [],
			yCoords = [];

		for(var i=0; i<coords.length; i++) {
			if(i%2 == 0) {
				xCoords.push(coords[i]);
			} else {
				yCoords.push(coords[i]);
			}
		}

		if(shape == 'poly') {
			drawPoly(context, xCoords, yCoords, img);
		} else if(shape == 'circle') {
			drawCircle(context, xCoords, yCoords, img);
		} else if(shape == 'rect') {
			drawRect(context, xCoords, yCoords, img);
		}
	};

	drawPoly = function(context, xCoords, yCoords, img) {
		drawOptions(img);
		context.beginPath();
		context.moveTo(xCoords[0], yCoords[0]);
		for(var j=1; j<xCoords.length; j++) {
			context.lineTo(xCoords[j], yCoords[j]);
		}
		context.closePath();
		context.fillStyle = convertToRgba(opts.highlightColor, opts.highlightOpacity);
		context.fill();
		context.lineWidth = opts.highlightBorderWidth;
		context.strokeStyle = convertToRgba(opts.highlightBorderColor, opts.highlightBorderOpacity);
		context.stroke();
	};

	drawCircle = function(context, xCoords, yCoords) {
		drawOptions(img);
		context.beginPath();
		context.arc(xCoords[0], yCoords[0], xCoords[1], 0, Math.PI*2, true);
		context.fillStyle = convertToRgba(opts.highlightColor, opts.highlightOpacity);
		context.fill();
		context.lineWidth = opts.highlightBorderWidth;;
		context.strokeStyle = convertToRgba(opts.highlightBorderColor, opts.highlightBorderOpacity);
		context.stroke();
	}

	drawRect = function(context, xCoords, yCoords) {
		drawOptions(img);
		context.fillStyle = convertToRgba(opts.highlightColor, opts.highlightOpacity);
		context.lineWidth = opts.highlightBorderWidth;;
		context.strokeStyle = convertToRgba(opts.highlightBorderColor, opts.highlightBorderOpacity);
		context.fillRect(xCoords[0], yCoords[0], xCoords[1]-xCoords[0], yCoords[1]-yCoords[0]);
		context.strokeRect(xCoords[0], yCoords[0], xCoords[1]-xCoords[0], yCoords[1]-yCoords[0]);
	}

	drawOptions = function(img) {
		var dataOpts = {};

		if (img.data('highlight-color') !== '') {
			Object.defineProperty(dataOpts, 'highlightColor', {value : img.data('highlight-color')});
		}

		if (img.data('highlight-opacity') !== '') {
			Object.defineProperty(dataOpts, 'highlightOpacity', {value : img.data('highlight-opacity')});
		}

		if (img.data('highlight-border-color') !== '') {
			Object.defineProperty(dataOpts, 'highlightBorderColor', {value : img.data('highlight-border-color')});
		}

		if (img.data('highlight-border-width') !== '') {
			Object.defineProperty(dataOpts, 'highlightBorderWidth', {value : img.data('highlight-border-width')});
		}

		if (img.data('highlight-border-opacity') !== '') {
			Object.defineProperty(dataOpts, 'highlightBorderOpacity', {value : img.data('highlight-border-opacity')});
		}

		if (img.data('event-trigger') !== '') {
			Object.defineProperty(dataOpts, 'eventTrigger', {value : img.data('event-trigger')});
		}

		opts = $.extend(dataOpts, $.fn.responsilight.defaults);
	}

	/* ------------------------------------------- */
	/* Event handling */
	/* ------------------------------------------- */

	var resizeImageMap,
		imageEvents,
		linkToHotspot,
		mapOver,
		mapOut,
		mapClick,
		resizeDelay,
		simpleMap,
		lastMapClick;

	resizeImageMap = function(img, map) {
		if (typeof(img.attr('usemap')) == 'undefined')
			return;

		var image = img.get(0),
			$image = img;

		if(!$image.data('responsilight')) {
			/* This is the initial page load */
			$image.data('responsilight', 'initialized');
			$('<img />').load(function() {
				recalcCoords();
			}).attr('src', $image.attr('src'));
		} else {
			/* We're resizing the page */
			recalcCoords();
		}

		function recalcCoords(){
			var w = image.naturalWidth,
				h = image.naturalHeight,
				wPercent = $image.width()/100,
				hPercent = $image.height()/100,
				c = 'coords';

			map.find('area').each(function(index){
				var $this = $(this);

				if(!$this.data(c))
					$this.data(c, $this.attr(c));

				var coords = $this.data(c).split(','),
					coordsPercent = new Array(coords.length);

				for (var i=0; i<coordsPercent.length; ++i) {
					if (i%2 === 0)
						coordsPercent[i] = parseInt(((coords[i]/w)*100)*wPercent);
					else
						coordsPercent[i] = parseInt(((coords[i]/h)*100)*hPercent);
				}

				$this.attr(c, coordsPercent.toString());

			});
			drawIt(img, map);
		}

	};

	imageEvents = function(img, map) {
		var moved = false;

		map.find('area').each(function(){
			var $this = $(this),
				action = $this.data('action'),
				trigger = opts.eventTrigger;

			$this.on('touchstart touchend touchmove mouseover mouseout mouseup mousedown blur focus keypress click', function(e){
				var type = e.type;

				switch(type) {
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
						e.preventDefault();
						mapOver($this, img, type);
						mapClick($this, img);
						break;
					case 'mousedown':
						e.preventDefault();
						mapClick($this, img);
					case 'focus':
						mapOver($this, img);
						break;
					case 'mouseover':
						e.preventDefault();
						mapOver($this, img);
						break;
					case 'blur':
						e.preventDefault();
						mapOut($this,img);
						break;
					case 'mouseout':
						e.preventDefault();
						mapOut($this,img);
						break;
					case 'keypress':
						if(trigger == 'click') {
							e.preventDefault();
							mapClick($this, img);
						}
						if (e.which === 13) {
							e.preventDefault();
							mapClick($this, img);
						}
						break;
					case 'click':
						e.preventDefault();
				}
			});
		});
	};

	linkToHotspot = function(img, map) {
		var hash = window.location.hash;

		if (hash) {
			var area = map.find('area[href="' + hash + '"]');

			if ( area.length ) {
				area = area.first();
				var imgTop = img.offset().top,
					coords = area.attr('coords').split(','),
					yCoords = [];

				for(var i=0; i<coords.length; i++) {
					if(i%2 != 0) {
						yCoords.push(coords[i]);
					}
				}

				var areaImgTop = Math.min.apply(Math, yCoords),
					areaImgBottom = Math.max.apply(Math, yCoords),
					windowHeight = $(window).height(),
					windowBottom = imgTop + windowHeight,
					areaBottom = imgTop + areaImgBottom,
					areaTop = imgTop + areaImgTop,
					padding = 50,
					scrollCoord;

				if (areaBottom > windowBottom) {
					scrollCoord = imgTop + (areaBottom - windowBottom) + padding;
					if ((areaBottom-areaTop) > windowHeight) {
						scrollCoord = areaTop - padding;
					}
				} else {
					scrollCoord = imgTop - padding;
				}

				mapOver(area, img, null);
				mapClick(area, img);
				setTimeout(function() {
    			window.scrollTo(0, scrollCoord);
  			}, 1);
			}
		}
	};

	mapOver = function(area, img, type) {
		type = type || '';
		if (doHighlights) {
			var w = img.width(),
				h = img.height(),
				shape = area.attr('shape'),
				id = area.attr('id'),
				$canvas = $('#canvas-' + id);

			if ($canvas.length) {
				return;
			}

			var makeCanvas = $('<canvas id="canvas-' + id + '" width="' + w + '" height="' + h + '"></canvas>');

			img.parent().append(makeCanvas);

			makeCanvas.css({
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'pointer-events': 'none',
				'display': 'none'
			});

			$canvas = $('#canvas-' + id);

			/* Fix for bug on iOS where touch event doesn't register on the area */
			$canvas.on('touchstart', function(e){
				e.preventDefault();
			});

			drawShape($canvas, area, img);

			$canvas.stop(true, true).fadeIn('fast');
		}
		area.trigger('showHighlight');
		if(opts.eventTrigger == 'hover' && type != 'touchstart') {
			area.data('stickyCanvas', true);
			area.trigger('stickyHighlight');
		}
	};

	mapOut = function(area, img) {
		if(opts.eventTrigger == 'hover') {
			area.data('stickyCanvas', false);
			area.trigger('unstickyHighlight');
		}
		if(doHighlights) {
			var id = area.attr('id'),
				canvas = $('#canvas-' + id);

			if (!area.data('stickyCanvas')) {
				canvas.stop(true, true).fadeOut('fast', function(){
					canvas.remove();
				});
			}
		}
		area.trigger('removeHighlight');
	};

	mapClick = function(area, img) {
		now = Date.now();
		if (lastMapClick && lastMapClick > (now-100)) {
			lastMapClick = now;
			return;
		}
		lastMapClick = now;

		if(doHighlights) {
			var id = area.attr('id'),
				stickyCanvas = $('#canvas-' + id),
				isSticky = area.data('stickyCanvas');

			if (stickyCanvas.length == 0) {
				mapOver(area, img);
				stickyCanvas = $('#canvas-' + id);
			}

			if (isSticky) {
				stickyCanvas.stop(true, true).fadeOut('fast', function(){
					$(this).remove();
				});
			} else {
				area.data('stickyCanvas', true);
				stickyCanvas.addClass('sticky-canvas');
				stickyCanvas.siblings('canvas').stop(true, true).fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}

		if (isSticky) {
			area.data('stickyCanvas', false);
			area.trigger('unstickyHighlight');
			area.trigger('removeHighlight');
		} else {
			area.data('stickyCanvas', true);
			area.trigger('stickyHighlight');
			area.siblings('area').data('stickyCanvas', false).trigger('removeHighlight');
		}
	};

	resizeDelay = (function() {
		var timers = {};
		return function (callback, ms, uniqueId) {
			if (!uniqueId) {
				uniqueId = Math.random() * 100;
			}
			if (timers[uniqueId]) {
				clearTimeout (timers[uniqueId]);
			}
			timers[uniqueId] = setTimeout(callback, ms);
		};
	})();

	simpleMap = function(map) {
		map.find('area').each(function(index){
			var $this = $(this),
				mapName = map.attr('name');
			$this.attr('id', mapName + '-area-' + index);
		});
	}

	/* Responsilighter plugin */
	$.fn.responsilight = function () {

		var $img = this;

		function responsilight_init(){
			$img.each(function(){
				var $this = $(this),
					$mapName = $this.attr('usemap').replace('#', ''),
					$map = $('map[name="' + $mapName + '"]');
				drawOptions($this);
				imageEvents($this, $map);
				resizeImageMap($this, $map);
			});
		}

		$(window).on('resize orientationchange', function() {
			resizeDelay(function() {
				responsilight_init();
			}, 300, 'responsilight');
		}).trigger('resize');

		return this;
	};

	$.fn.responsilight.defaults = {
		highlightColor: '#000000',
		highlightOpacity: '0.5',
		highlightBorderColor: '#000000',
		highlightBorderWidth: 1,
		highlightBorderOpacity: '1',
		eventTrigger: 'click'
	}

}(jQuery, window, document));