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
	var showTooltip = function(area, newInfo, container, tooSmall) {
		if (tooSmall) {
			area.qtip({
					content: {
						text: newInfo,
						button: true
					},
					show: {
						event: 'stickyHighlight',
						modal: {
							on: true,
							blur: false
						}
					},
					hide: {
						fixed: true,
						delay: 300,
						event: 'unstickyHighlight unfocus'
					},
					position: {
						my: 'center',
						at: 'center',
						target: $(window),
						adjust: {
							scroll: false,
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
						},
						visible: function(event, api) {
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
						},
						hide: function(event, api) {
							var mapId = container.attr('id');
							area.data('stickyCanvas', false);
							$('#' + mapId).find('canvas').fadeOut('slow', function(){
								$(this).remove();
							});
						}
					}
				});
		} else {
			area.qtip({
				content: {
					text: newInfo,
					title: '&nbsp;',
					button: true
				},
				show: {
					solo: true,
					event: 'stickyHighlight',
					effect: function() {
						$(this).fadeTo(300, 1);
					}
				},
				hide: {
					fixed: true,
					delay: 300,
					event: 'unstickyHighlight unfocus'
				},
				position: {
					target: 'mouse',
					viewport: $(window),
					adjust: {
						mouse: false,
						method: 'shift'
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
					},
					hide: function(event, api) {
						var mapId = container.attr('id');
						area.data('stickyCanvas', false);
						$('#' + mapId).find('canvas').fadeOut('slow', function(){
							$(this).remove();
						});
					}
				}
			});
		}
	};

	/* Private: show URL tooltip */
	var showUrlTooltip = function(area, container){
		area.qtip({
			position: {
				target: 'mouse',
				viewport: $(window),
				adjust: {
					x: 10
				}
			},
			style: {
				classes: 'qtip-da-custom tip-title-only'
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
		$('.da-error').hide();
		$('.hotspot-info').hide();

		var container = $('.hotspots-container');

		container.each(function(){
			var container = $(this);
			container.on('touchstart click', 'area.url-area', function(e){
				e.preventDefault();

				var $this = $(this),
					href = $this.attr('href'),
					target = $this.attr('target');

				if (target == '_new') { /* If the link is being opened in a new window */
					if (null == window.open(href) ) {
						window.location.href = href;
					}
				} else if ((this.host == '' || this.pathname == window.location.pathname) && this.hash != '') { /* If the link is on the current page */
					window.location.href = href;
				} else {
					window.location.href = href;
				}
			});

			if (container.hasClass('event-hover')) {
				container.find('area.url-area').each(function(){
					var $this = $(this);

					showUrlTooltip($this, container);
				});
			}

			if (container.hasClass('layout-tooltip')) {
				var screenWidth = $(window).width(),
					daWidth = container.width(),
					tipWidth = 280 * 3,
					tooSmall = false;

				if ((screenWidth < tipWidth) && ((daWidth/screenWidth) > 0.75)) {
					tooSmall = true;
				}

				container.find('area.more-info-area').each(function(){
					var $this = $(this),
						newInfo = $($this.attr('href'));

					if (typeof jQuery.qtip === 'function') {
						showTooltip($this, newInfo, container, tooSmall);
					}
				});
			} else {
				container.on('stickyHighlight unstickyHighlight', 'area.more-info-area', function(e){
					var $this = $(this),
					container = $this.parents('.hotspots-container'),
					isSticky = $this.data('stickyCanvas') || '',
					newInfo = $($this.attr('href'));

					if (container.hasClass('layout-lightbox')) {
						showLightbox(container, isSticky, newInfo, $this);
					} else {
						newInfo = isSticky ? $($this.attr('href')) : container.find('.hotspot-initial');
						showNewInfo(container, isSticky, newInfo);
					}
				});
			}
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
	jQuery(window).on('pageloaded', function() {
		hotspots.init();
	});
	jQuery('.et_pb_tabs .et_pb_tabs_controls li').on('click', function() {
		setTimeout(function() {
			hotspots.init();
		}, 1000);
	});
	jQuery('.ui-tabs-anchor').on('click', function() {
		setTimeout(function() {
			hotspots.init();
		}, 750);
	});
	jQuery('.et_pb_toggle_title').on('click', function() {
		setTimeout(function() {
			hotspots.init();
		}, 1000);
	});
	jQuery(window).on('et_hashchange', function() {
		setTimeout(function() {
			hotspots.init();
		}, 1000);
	});});