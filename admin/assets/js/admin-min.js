;(function ( $, hotspotAdmin, cmb, undefined ) {
	"use strict";

	var leafletOptions = {
		color: '#ff1414',
		weight: 1,
		fillOpacity: 0.3
	};
	var $metabox = cmb.metabox();
	var $repeatGroup = $metabox.find('.cmb-repeatable-group');
	var container;
	var map;
	var leafletInput;
	var shapeInput;
	var currentId;
	var img;
	var $img;
	var drawControl;
	var editControl;
	var drawnItems;
	var resizeTimer = null;
	var resizing = false;
	var hotspots = 50;

	hotspotAdmin.reRender = function(){
		if (!resizing) {
			resizing = true;
		}
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function(){
			leafletDrawInit();
			resizing = false;
		}, 250);
	};

	var leafletDrawInit = function() {
		if ( ! container ) {
			return;
		}

		// Sanity check - make sure any old drawing tools are gone
		leafletDrawRemove();
		currentId = container.data('iterator');

		// Start with the coordinates input
		leafletInput = container.find('input[data-image-url]');
		shapeInput = $('#_da_hotspots_' + currentId + '_shape');
		var imageSrc = leafletInput.data('image-url');

		// Set up the image
		img = new Image();
		img.onload = function(){
			leafletSetup();
		};
		img.src = imageSrc;
	}

	var leafletSetup = function() {
		$img = $(img);
		var wrapper = $('<div id="hotspots-drawing-wrapper-' + currentId + '" class="hotspots-drawing-wrapper"></div>');
		wrapper.append($img);
		leafletInput.after(wrapper);

		var drawingContainer = $('<div id="hotspots-drawing-container-' + currentId + '" class="hotspots-drawing-container"></div>');
		var imgWidth = $img.width();
		var imgHeight = $img.height();

		drawingContainer.css({
			'width': imgWidth + 'px',
			'height': imgHeight + 'px'
		});
		$img.after(drawingContainer);

		var natHeight = img.naturalHeight;
		var natWidth = img.naturalWidth;
		$img.data('natW', natWidth);
		$img.data('natH', natHeight);
		var bounds = [[0,0], [natHeight, natWidth]];
		var maxBounds = [[-50,-50], [natHeight+50, natWidth+50]]

		map = L.map('hotspots-drawing-container-' + currentId, {
			attributionControl: false,
			boxZoom: false,
			crs: L.CRS.Simple,
			doubleClickZoom: true,
			dragging: true,
			keyboard: false,
			maxBounds: maxBounds,
			maxBoundsViscosity: 1.0,
			maxZoom: 10,
			minZoom: -1,
			scrollWheelZoom: false,
			tap: true,
			touchZoom: true,
			zoomControl: true,
			zoomSnap: 0
		});



		var imageLayer = L.imageOverlay($img.attr('src'), bounds).addTo(map);
		map.fitBounds(bounds);

		/* Add custom drawing toolbar */
		drawnItems = new L.FeatureGroup();
		map.addLayer(drawnItems);
		drawControl = new L.Control.Draw({
			draw: {
				circlemarker: false,
				marker: false,
				polyline: false
			}
		});

		drawControl.setDrawingOptions({
			rectangle: {
				shapeOptions: {
					color: leafletOptions.color,
					weight: leafletOptions.weight,
					fillOpacity: leafletOptions.fillOpacity
				}
			},
			circle: {
				shapeOptions: {
					color: leafletOptions.color,
					weight: leafletOptions.weight,
					fillOpacity: leafletOptions.fillOpacity
				},
				showRadius: false
			},
			polygon: {
				shapeOptions: {
					color: leafletOptions.color,
					weight: leafletOptions.weight,
					fillOpacity: leafletOptions.fillOpacity
				}
			}
		});

		map.addControl(drawControl);

		/* Create an edit only toolbar */
		editControl = new L.Control.Draw({
			edit: {
				featureGroup: drawnItems
			},
			draw: false
		})

		/* Save shapes that are drawn */
		map.on(L.Draw.Event.CREATED, function(e) {
			drawnItems.addLayer(e.layer);
			shapeEvents(e.layer);
			mapToEditMode();
		});

		/* When shapes are deleted, restore drawing bar */
		map.on(L.Draw.Event.DELETED, function(e) {
			if (drawnItems.getLayers().length === 0){
				mapToDrawingMode();
			}
		});

		L.Draw.Polyline.prototype._onTouch = L.Util.falseFn;

		drawSpot();
		mapEvents();
		localizeStrings();
	}

	var localizeStrings = function(){
		L.drawLocal = {
			draw: {
				toolbar: {
					actions: {
						title: 'Cancel drawing',
						text: 'Cancel'
					},
					finish: {
						title: 'Finish drawing',
						text: 'Finish'
					},
					undo: {
						title: 'Delete last point drawn',
						text: 'Delete last point'
					},
					buttons: {
						polyline: 'Draw a polyline',
						polygon: 'Draw a polygon',
						rectangle: 'Draw a rectangle',
						circle: 'Draw a circle',
						marker: 'Draw a marker',
						circlemarker: 'Draw a circlemarker'
					}
				},
				handlers: {
					circle: {
						tooltip: {
							start: 'Click and drag to draw circle.'
						},
						radius: ''
					},
					circlemarker: {
						tooltip: {
							start: 'Click map to place circle marker.'
						}
					},
					marker: {
						tooltip: {
							start: 'Click map to place marker.'
						}
					},
					polygon: {
						tooltip: {
							start: 'Click to start drawing shape.',
							cont: 'Click to continue drawing shape.',
							end: 'Click first point to close this shape.'
						}
					},
					polyline: {
						error: '<strong>Error:</strong> shape edges cannot cross!',
						tooltip: {
							start: 'Click to start drawing line.',
							cont: 'Click to continue drawing line.',
							end: 'Click last point to finish line.'
						}
					},
					rectangle: {
						tooltip: {
							start: 'Click and drag to draw rectangle.'
						}
					},
					simpleshape: {
						tooltip: {
							end: 'Release mouse to finish drawing.'
						}
					}
				}
			},
			edit: {
				toolbar: {
					actions: {
						save: {
							title: 'Save changes',
							text: 'Save'
						},
						cancel: {
							title: 'Cancel editing, discards all changes',
							text: 'Cancel'
						},
						clearAll: {
							title: 'Clear all layers',
							text: 'Clear All'
						}
					},
					buttons: {
						edit: 'Edit layers',
						editDisabled: 'No layers to edit',
						remove: 'Delete layers',
						removeDisabled: 'No layers to delete'
					}
				},
				handlers: {
					edit: {
						tooltip: {
							text: 'Drag handles or markers to edit features.',
							subtext: 'Click cancel to undo changes.'
						}
					},
					remove: {
						tooltip: {
							text: 'Click on a feature to remove.'
						}
					}
				}
			}
		};
 	}

	var mapEvents = function(){
		map.on('draw:created', function (e) {
			var type = e.layerType,
				layer = e.layer;

			writeShape(type);

			switch(type) {
				case 'polygon':
					writePolyCoords(layer.getLatLngs());
					break;
				case 'circle':
					writeCircleCoords(layer);
					break;
				case 'rectangle':
					// Saving polycoords for rectangles to make front-end rendering easier
					writePolyCoords(layer.getLatLngs());
					break;
			}

		});

		map.on('draw:edited', function(e){
			var layers = e.layers;
			var shape = shapeInput.val();
			layers.eachLayer(function(layer){
				switch(shape) {
					case 'polygon':
						writePolyCoords(layer.getLatLngs());
						break;
					case 'circle':
						writeCircleCoords(layer);
						break;
					case 'rectangle':
						// Saving polycoords for rectangles to make front-end rendering easier
						writePolyCoords(layer.getLatLngs());
						break;
				}
			});
		});

		map.on('draw:deleted', function(e){
			leafletInput.val('');
		});
	};

	var shapeEvents = function(shape) {
		shape.on('click', function(){
			let editButton = document.querySelector('.leaflet-draw-edit-edit');
			let deleteButton = document.querySelector('.leaflet-draw-edit-remove');
			let isEditEnabled = editButton.classList.contains('leaflet-draw-toolbar-button-enabled');
			let isDeleteEnabled = deleteButton.classList.contains('leaflet-draw-toolbar-button-enabled');

			if (!isEditEnabled && !isDeleteEnabled) {
				editButton.click()
			}
		});
	};

	var writePolyCoords = function(coords) {
		var inputCoords = new Array();
		coords[0].map(function(set, index){
			inputCoords.push(Math.round(set.lng));
			inputCoords.push($img.data('natH') - Math.round(set.lat));
		});
		leafletInput.val(inputCoords);
	};

	var writeCircleCoords = function(layer) {
		var inputCoords = new Array();
		var center = layer.getLatLng();
		inputCoords.push(Math.round(center.lng));
		inputCoords.push($img.data('natH') - Math.round(center.lat));
		inputCoords.push(Math.round(layer.getRadius()));

		leafletInput.val(inputCoords);
	};

	var writeShape = function(shape) {
		shapeInput.val(shape);
		container.addClass('shape-' + shape);
	}

	var mapToDrawingMode = function(){
		map.addControl(drawControl);
		map.removeControl(editControl);
	};

	var mapToEditMode = function(){
		map.removeControl(drawControl);
		map.addControl(editControl);
	};

	var leafletDrawRemove = function() {
		if (map) {
			map.remove();
			$('.hotspots-drawing-wrapper').remove()
			map = null;
		}
	};

	var drawSpot = function() {
		var inputVal = leafletInput.val();

		if (!inputVal) {
			return;
		}

		mapToEditMode();
		var coords = leafletInput.val().split(',');
		var shape = $('#_da_hotspots_' + currentId + '_shape').val();

		switch(shape) {
			case 'polygon':
				renderPoly(coords);
				break;
			case 'circle':
				renderCircle(coords);
				break;
			case 'rectangle':
				renderRect(coords);
				break;
		}
	};

	var renderPoly = function(coords) {
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
			return [$img.data('natH') - coord, xCoords[index]];
		});

		var poly = L.polygon(polyCoords, {
			className: 'da-spot',
			color: leafletOptions.color,
			weight: leafletOptions.weight,
			fillOpacity: leafletOptions.fillOpacity
		});

		drawnItems.addLayer(poly);
		shapeEvents(poly);
	};

	var renderRect = function(coords) {
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
			return [$img.data('natH') - coord, xCoords[index]];
		});

		var bounds = [polyCoords[0], polyCoords[2]];

		var rect = L.rectangle(bounds, {
			className: 'da-spot',
			color: leafletOptions.color,
			weight: leafletOptions.weight,
			fillOpacity: leafletOptions.fillOpacity
		});

		drawnItems.addLayer(rect);
		shapeEvents(rect);
	};

	var renderCircle = function(coords) {
		var x = coords[0];
		var y = $img.data('natH') - coords[1];
		var rad = coords[2];
		var circle = L.circle([y,x], {
			radius: rad,
			color: leafletOptions.color,
			weight: leafletOptions.weight,
			fillOpacity: leafletOptions.fillOpacity,
			showRadius: false
		})
		drawnItems.addLayer(circle);
		shapeEvents(circle);
	};

	/* Only allow one hotspot editing area to be open at one time. Close them all on page load */
	var accordion = function() {
		// When adding a new area, close all others
		$repeatGroup.on('cmb2_add_row', function(e, $el){
			let areas = $el.siblings('.cmb-repeatable-grouping').addClass('closed');
			leafletDrawRemove();
			container = $el;
			leafletDrawInit();
		});

		// When opening an area, close others, init canvas draw
		$('#field_group').on('click', '.cmb-group-title, .cmbhandle', function(event) {
			var $this = $(event.target),
				parent = $this.closest('.cmb-row');

			if (!parent.hasClass('closed')) {
				var areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
				leafletDrawRemove();
				container = parent;
				leafletDrawInit();
			}
		});

		// Close all on page load
		$('.cmb-repeatable-grouping').addClass('closed');
	};

	/* Events that call hotspotNameUpdate to update the title bar text */
	var hotspotNames = function(){
		$('#field_group').on('keyup click', 'input[name$="[title]"]', function(){
			var $this = $(event.target);
			hotspotNameUpdate($this);
		});

		$('input[name$="[title]"]').each(function(){
			hotspotNameUpdate($(this));
		});
	};

	/* Given an input, update the title bar text to match the value */
	var hotspotNameUpdate = function(input) {
		var $this = $(input),
			value = $this.val();

		if (value) {
			var parent = $this.closest('.cmb-repeatable-grouping'),
				title = parent.find('.cmb-group-title'),
				span = title.find('span'),
				title = (span.length) ? span : title;

			title.text(value);
		}
	}

	var executeConditionalLogic = function( area ) {
		$(area).find('[data-action]').closest('.cmb-row').hide();
		$(area).find('.wp-editor-wrap').closest('.cmb-row').hide();

		var selectedAction = $(area).find('.cmb2_select.action').val();
		if ( !selectedAction ) {
			$(area).find('[data-action="more-info"]').closest('.cmb-row').show();
			$(area).find('.wp-editor-wrap').closest('.cmb-row').show();
		} else {
			$(area).find('[data-action="'+selectedAction+'"]').closest('.cmb-row').show();
		}
	}

	var hotspotActions = function() {
		$('.cmb2-wrap .cmb-repeatable-grouping').each(function() {
			executeConditionalLogic(this);
		});
		$('.cmb2-wrap').on('change', '.cmb2_select.action', function() {
			var area = $(this).closest('.cmb-repeatable-grouping');
			executeConditionalLogic(area);
		});
	}

	/* Select a new color scheme to be applied to the current Draw Attention */
	var themeSelect = function() {
		$('#da-theme-pack-select').on('change', function() {
			var confirmed = confirm('Applying a new theme will overwrite the current styling you have selected');
			if ( confirmed ) {
				themeApply($(this).val());
			} else {
				$('#da-theme-pack-select').val('');
			}
		});
	}

	var themeApply = function(themeSlug) {
		var themeProperties = Object.keys(daThemes.themes[themeSlug].values);
		$.each( themeProperties, function() {
			var cfName = this;
			$('input[name="'+daThemes.cfPrefix+cfName+'"]').val(daThemes.themes[themeSlug].values[cfName]).trigger('change');
		} );
	}

	var opacityLabelSync = function() {
		$('.cmb-type-opacity input').on('change', function() {
			var displayedValue = ($(this).val()-0.01)*100;
			displayedValue = displayedValue.toFixed(0);
			$(this).parent().find('.opacity-percentage-value').html(displayedValue);
		});
	}

	var areaLimit = function(){
		var repeatGroup = $('.cmb-repeatable-group'),
			cmb = window.CMB2,
			$metabox = cmb.metabox();

		/* Adding a row */
		repeatGroup.on('cmb2_add_group_row_start', function(){
			var areaCount = repeatGroup.children('.postbox.cmb-row').length + 1;
			if (areaCount >= hotspots) {
				$metabox.off( 'click', '.cmb-add-group-row', cmb.addGroupRow );
				$metabox.on( 'click', '.cmb-add-group-row', function(e){
					e.preventDefault();
					var confirmed = confirm('Upgrade to Draw Attention Pro to add unlimited clickable areas to your images!');
					if ( confirmed ) {
						window.open('http://wpdrawattention.com', '_blank');
					} else {
						return;
					}
				});
			}
		});

		/* Removing a row */
		repeatGroup.on('cmb2_remove_row', function(){
			var areaCount = repeatGroup.children('.postbox.cmb-row').length;
			if (areaCount < hotspots) {
				$metabox.on( 'click', '.cmb-add-group-row', cmb.addGroupRow );
			}
		});
	};

	var hideNotice = function() {
		$('.da-disable-third-party-js').hide();
	}

	var sortHotspots = function() {
		var hotspots = $('#_da_hotspots_repeat');
		if (hotspots.length) {
			hotspots.sortable({
				items: '.cmb-repeatable-grouping',
				handle: '.cmbhandle-title'
			});
		}
	};

	var removeRowReset = function(){
		// Stop CMB2 from renaming all the areas
		$metabox.off( 'cmb2_remove_row', '.cmb-repeatable-group', cmb.resetTitlesAndIterator )
	};

	hotspotAdmin.init = function() {
		removeRowReset();
		accordion();
		hotspotNames();
		hotspotActions();
		themeSelect();
		opacityLabelSync();
		hideNotice();
		sortHotspots();
	}
})(jQuery, window.hotspotAdmin = window.hotspotAdmin || {}, window.CMB2);

jQuery(document).on('cmb_init', function(){
	hotspotAdmin.init();
});

jQuery(window).on('resize orientationchange', function(e) {
	hotspotAdmin.reRender();
})
