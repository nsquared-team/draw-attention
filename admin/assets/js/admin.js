;(function ( $, hotspotAdmin, undefined ) {
	"use strict";

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

	var layoutSelect = function() {
		$('input[name="_da_map_layout"]').on('change', function() {
			showHideEventTriggerMetabox();
		});
	}
	var showHideEventTriggerMetabox = function() {
		var selectedLayout = $('input[name="_da_map_layout"]:checked').val();
		if (selectedLayout==='lightbox') {
			$('#_da_event_trigger').val('click');
			$('.cmb2-id--da-event-trigger').hide();
		} else {
			$('.cmb2-id--da-event-trigger').show();
		}
	}

	/* Fix the weird-o cloning behavior when adding a new row */
	var hotspotCloning = function() {
		var repeatGroup = $('.cmb-repeatable-group');
		repeatGroup.on('cmb2_add_row', function(){
			// var lastRow = $(this).find('.cmb-row.cmb-repeatable-grouping').last(),
			// 	fields = lastRow.find(':input').not(':button');

			// fields.val('');
			hotspotAdmin.reset();
		});
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

	/* Apply the selected theme */
	var themeApply = function(themeSlug) {
		var themeProperties = Object.keys(daThemes.themes[themeSlug].values);
		$.each( themeProperties, function() {
			var cfName = this;
			$('input[name="'+daThemes.cfPrefix+cfName+'"]').val(daThemes.themes[themeSlug].values[cfName]).trigger('change');
		} );
	}

	/* Fix weird opacity value bug */
	var opacityLabelSync = function() {
		$('.cmb-type-opacity input').on('change', function() {
			var displayedValue = ($(this).val()-0.01)*100;
			$(this).parent().find('.opacity-percentage-value').html(displayedValue);
		});
	}

	/* Confirm before deleting a hotspot */
	var confirmDelete = function(){
		$('.cmb2-wrap > .cmb2-metabox').on('click', '.cmb-remove-group-row', function(e){
			var confirmed = confirm('You\'re deleting a hotspot. There is no undo');
			if (confirmed) {
				return true;
			} else {
				e.stopImmediatePropagation();
				e.preventDefault();
			}
		});
	};

	var saveAlert = function(){
		var isDirty = false;
		$('.cmb2-wrap > .cmb2-metabox').on('change', ':input', function(){
			isDirty = true;
		});

		$(window).on( 'beforeunload.edit-post', function(e) {
			/* Show message only when editing our post type */
			if ($('body').hasClass('post-type-da_image')) {
				var confirmationMessage = 'You\'ve made some changes to your Draw Attention data.';
				confirmationMessage += 'If you leave before saving, your changes will be lost.';

				if (!isDirty) {
					return undefined;
				} else {
					(e || window.event).returnValue = confirmationMessage;
					return confirmationMessage;
				}
			}
		})
	};

	var hideNotice = function() {
		$('.da-disable-third-party-js').hide();
	}

	var sortHotspots = function() {
		$('#_da_hotspots_repeat').sortable({
			items: '.cmb-repeatable-grouping',
			handle: '.cmbhandle-title'
		});
	}

	/* Stuff to fire off on page load */
	hotspotAdmin.init = function() {
		canvasDraw();
		accordion();
		layoutSelect();
		showHideEventTriggerMetabox();
		hotspotNames();
		hotspotCloning();
		hotspotActions();
		themeSelect();
		opacityLabelSync();
		confirmDelete();
		saveAlert();
		hideNotice();
		sortHotspots();
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