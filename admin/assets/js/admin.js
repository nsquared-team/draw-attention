;(function ( $, hotspotAdmin, undefined ) {
	"use strict";

	var hotspots = 20;

	/* Enable drawing hotspots on the full-size image */
	var canvasDraw = function() {
		$('input[data-image-url]').canvasAreaDraw();
	}

	/* Only allow one hotspot editing area to be open at one time. Close them all on page load */
	var accordion = function() {
		$('.cmb-add-group-row.button').on('click', function(){
			var $this = $(this),
				parent = $this.closest('.cmb-row'),
				areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
		});

		$('#field_group').on('click', '.cmbhandle', function() {
			var $this = $(event.target),
				parent = $this.closest('.cmb-row');

			if (!parent.hasClass('closed')) {
				var areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
			}
		});

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

	/* Fix the weird-o cloning behavior when adding a new row */
	var hotspotCloning = function() {
		var repeatGroup = $('.cmb-repeatable-group');
		repeatGroup.on('cmb2_add_row', function(){
			var lastRow = $(this).find('.cmb-row.cmb-repeatable-grouping').last(),
				fields = lastRow.find(':input').not(':button');

			fields.val('');
			hotspotAdmin.reset();
		});
	}

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
			$(this).parent().find('.opacity-percentage-value').html(displayedValue);
		});
	}

	var areaLimit = function(){
		var repeatGroup = $('.cmb-repeatable-group'),
			cmb = window.CMB2,
			$metabox = cmb.metabox();

		console.log($metabox);

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
	}

	hotspotAdmin.init = function() {
		canvasDraw();
		accordion();
		hotspotNames();
		hotspotCloning();
		themeSelect();
		opacityLabelSync();
		areaLimit();
	}

	/* Reset the drawable canvas areas */
	hotspotAdmin.reset = function() {
		var coordsInputs = $('input[data-image-url]');

		coordsInputs.off('change').siblings('div, button').remove();
		coordsInputs.canvasAreaDraw();
	}

}(jQuery, window.hotspotAdmin = window.hotspotAdmin || {}));

jQuery(function() {
	hotspotAdmin.init();
})