;(function ($, window, document, undefined) {

	/* ------------------------------------------- */
	/* Browser support checking */
	/* ------------------------------------------- */
	var hasCanvas,
		hasPointerEvents;

	/* test if browser supports canvas */
	hasCanvas = !!window.HTMLCanvasElement;

	/* test if browser supports non-SVG pointer-events */
	hasPointerEvents = (function () {
		var element = document.createElement('x'),
			documentElement = document.documentElement,
			getComputedStyle = window.getComputedStyle,
			supports;

		if (!('pointerEvents' in element.style)) {
			return false;
		}

		element.style.pointerEvents = 'auto';
		element.style.pointerEvents = 'x';
		documentElement.appendChild(element);
		supports = getComputedStyle &&
				getComputedStyle(element, '').pointerEvents === 'auto';
		documentElement.removeChild(element);
		return !!supports;
	})();

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
		drawCanvas,
		drawPoly,
		drawCircle,
		drawRect,
		drawOptions;

	drawIt = function(img, map) {
		if(hasCanvas && hasPointerEvents) {
			drawCanvas(img, map);
		}
	};

	drawCanvas = function(img, map) {
		var w = img.width(),
			h = img.height(),
			oldCanvas = img.siblings('canvas'),
			mapName = map.attr('name'),
			wrapped,
			$wrap,
			index = 0;

		if(oldCanvas.length) {
			oldCanvas.remove();
		}

		wrapped = jQuery('<div id="wrap-' + mapName + '"></div>');

		if(img.parent('#wrap-' + mapName).length) {
			img.unwrap();
		}

		img.wrap(wrapped);
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

			$this.off('mouseenter');
			$this.off('mouseleave');

			$this.hover(
				function(){ mapOver($this, img); },
				function(){ mapOut($this, img); }
			);

			if (opts.eventTrigger == 'click') {
				$this.off('click').on('click', function(e){
					e.preventDefault();
					mapClick($(this), img);
				});
			}

			if ($this.data('stickyCanvas')) {
				mapOver($this, img);
				img.siblings('canvas').addClass('sticky-canvas');
			}

		});
	};

	drawPoly = function(context, xCoords, yCoords) {
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
		context.beginPath();
		context.arc(xCoords[0], yCoords[0], xCoords[1], 0, Math.PI*2, true);
		context.fillStyle = convertToRgba(opts.highlightColor, opts.highlightOpacity);
		context.fill();
		context.lineWidth = opts.highlightBorderWidth;;
		context.strokeStyle = convertToRgba(opts.highlightBorderColor, opts.highlightBorderOpacity);
		context.stroke();
	}

	drawRect = function(context, xCoords, yCoords) {
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
		mapOver,
		mapOut,
		mapClick,
		resizeDelay;

	resizeImageMap = function(img, map) {
		if (typeof(img.attr('usemap')) == 'undefined')
			return;

		var image = img.get(0),
			$image = img;

		$('<img />').load(function() {
			var w = image.naturalWidth,
				h = image.naturalHeight,
				wPercent = $image.width()/100,
				hPercent = $image.height()/100,
				c = 'coords';

			map.find('area').each(function(){
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
		}).attr('src', $image.attr('src'));
	};

	mapOver = function(area, img) {
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

		var canvas = $canvas.get(0),
			context = canvas.getContext('2d');

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
			drawPoly(context, xCoords, yCoords);
		} else if(shape == 'circle') {
			drawCircle(context, xCoords, yCoords);
		} else if(shape == 'rect') {
			drawRect(context, xCoords, yCoords);
		}

		$canvas.stop(true, true).fadeIn('fast');
		var href = area.attr('href');
		img.trigger('showHighlight', [href]);
	};

	mapOut = function(area, img) {
		var id = area.attr('id'),
			canvas = $('#canvas-' + id);

		if (!area.data('stickyCanvas')) {
			canvas.stop(true, true).fadeOut('fast', function(){
				canvas.remove();
			});
		}
		img.trigger('removeHighlight');

	};

	mapClick = function(area, img) {
		var id = area.attr('id'),
			stickyCanvas = $('#canvas-' + id),
			isSticky = area.data('stickyCanvas'),
			href = area.attr('href');

		if (stickyCanvas.length == 0) {
			mapOver(area, img);
			stickyCanvas = $('#canvas-' + id);
		}


		if (isSticky) {
			area.data('stickyCanvas', false);
			area.trigger('stickyHighlight', [false]);
			stickyCanvas.stop(true, true).fadeOut('fast', function(){
				$(this).remove();
			});
		} else {
			area.data('stickyCanvas', true);
			stickyCanvas.addClass('sticky-canvas');
			img.trigger('activateHighlight', [href]);
			area.trigger('stickyHighlight', [true]);
			stickyCanvas.siblings('canvas.sticky-canvas').stop(true, true).fadeOut('fast', function(){
				$(this).remove();
			});
			area.siblings('area').data('stickyCanvas', false);
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

	/* Responsilighter plugin */
	$.fn.responsilight = function () {

		var $img = this;

		function responsilight_init(){
			$img.each(function(){
				var $this = $(this),
					$mapName = $this.attr('usemap').replace('#', ''),
					$map = $('map[name="' + $mapName + '"]');
				drawOptions($this);
				resizeImageMap($(this), $map);
			});
		}

		$(window).resize(function() {
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