;(function ( $, hotspotAdmin, cmb, undefined ) {
	"use strict";

	var $metabox = cmb.metabox();
	var $repeatGroup = $metabox.find('.cmb-repeatable-group');
	var hotspots = 50;

	/* Enable drawing hotspots on the full-size image */
	var canvasDraw = function(container) {
		canvasDestroy(container);
		var input = container.find('input[data-image-url]');
		input.canvasAreaDraw();
	}

	/* Destroy canvas drawing hotspots */
	var canvasDestroy = function(container) {
		var input = container.find('input[data-image-url]');
		input.off();
		input.siblings().remove();
	}

	/* Only allow one hotspot editing area to be open at one time. Close them all on page load */
	var accordion = function() {
		// When adding a new area, close all others
		$repeatGroup.on('cmb2_add_row', function(e, $el){
			let areas = $el.siblings('.cmb-repeatable-grouping').addClass('closed');
			canvasDestroy(areas);
			canvasDraw($el);
		});

		// When opening an area, close others, init canvas draw
		$('#field_group').on('click', '.cmb-group-title, .cmbhandle', function(event) {
			var $this = $(event.target),
				parent = $this.closest('.cmb-row');

			if (!parent.hasClass('closed')) {
				var areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
				canvasDestroy(areas);
				canvasDraw(parent);
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

	/* Reset the drawable canvas areas */
	hotspotAdmin.reset = function() {
		var coordsInputs = $('input[data-image-url]');
		coordsInputs.off('change').siblings('div, button, canvas').remove();
		coordsInputs.canvasAreaDraw();
	}

})(jQuery, window.hotspotAdmin = window.hotspotAdmin || {}, window.CMB2);

jQuery(document).on('cmb_init', function(){
	hotspotAdmin.init();
});
