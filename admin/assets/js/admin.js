;(function ( $, hotspotAdmin, undefined ) {
	"use strict";

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
		$('.cmb-add-group-row.button').on('click', function(){
			var $this = $(this),
				parent = $this.closest('.cmb-row'),
				areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
				canvasDestroy(areas);
		});

		$('#field_group').on('click', '.cmb-group-title, .cmbhandle', function(event) {
			var $this = $(event.target),
				parent = $this.closest('.cmb-row');

			if (!parent.hasClass('closed')) {
				var areas = parent.siblings('.cmb-repeatable-grouping').addClass('closed');
				canvasDestroy(areas);
				canvasDraw(parent);
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
		var repeatGroup = $('#field_group .cmb-repeatable-group');
		repeatGroup.on('cmb2_add_row', function(){
			var lastRow = $(this).find('.cmb-row.cmb-repeatable-grouping').last();
			canvasDestroy(repeatGroup);
			canvasDraw(lastRow);
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
	};

	var sortHotspots = function() {
		var hotspots = $('#_da_hotspots_repeat');
		if (hotspots.length) {
			hotspots.sortable({
				items: '.cmb-repeatable-grouping',
				handle: '.cmbhandle-title'
			});
		}
	};

	var alwaysVisible = function(){
		var checkbox = $('#_da_always_visible');
		toggleVisible();
		checkbox.on('change', toggleVisible);

		function toggleVisible(){
			var highlightStyles = $('#cmb2-metabox-highlight_styling_metabox'),
				multiStyles = $('#cmb2-metabox-styles');
			if (checkbox.is(':checked')) {
				highlightStyles.addClass('always-visible');
				multiStyles.addClass('always-visible');
			} else {
				highlightStyles.removeClass('always-visible');
				multiStyles.removeClass('always-visible');
			}
		};
	};

	var multiStyles = function(){
		var checkbox = $('#_da_has_multiple_styles');
		toggleStyles();
		checkbox.on('change', toggleStyles);

		function toggleStyles(){
			var styles = $('#styles'),
				hotspots = $('#field_group'),
				styleSelects = hotspots.find('select[id$="_style"]'),
				styleSelectContainers = styleSelects.parents('.cmb-repeat-group-field');
			if (checkbox.is(':checked')) {
				styles.show();
				styleSelectContainers.show();
			} else {
				styles.hide();
				styleSelectContainers.hide();
			}
		}
	};

	var multiThemeSelect = function(){
		var colorSchemeContainer = $('#styles'),
			colorSchemes = colorSchemeContainer.find('.cmb-repeatable-grouping'),
			colorSchemeSelect = $('#da-theme-pack-select');

		colorSchemes.each(function(){
			var colorScheme = $(this),
				newColorSchemeSelect = colorScheme.find('select');

			if (!newColorSchemeSelect.length) {
				var iterator = colorScheme.data('iterator');
				newColorSchemeSelect = colorSchemeSelect.clone().attr('id', 'colorScheme-' + iterator);

				var colorTitle = colorScheme.find('.cmb2-id--da-styles-' + iterator + '-title'),
					wrapper = $('<div></div>', {
						'class': 'cmb-row cmb-type-select premade-color-schemes'
					}),
					schemeHeader = $('<div></div>', {
						'class': 'cmb-th',
						'html': '<label for="colorScheme-' + iterator + '">Color Scheme</label>'
					}).appendTo(wrapper),
					schemeBody = $('<div></div>',{
						'class': 'cmb-td',
						'html': '<p class="cmb2-metabox-description">Quickly apply a color scheme to this style (you can adjust each color afterwards)</p>'
					}).appendTo(wrapper);

				schemeBody.prepend(newColorSchemeSelect);
				colorTitle.after(wrapper);
			}

			newColorSchemeSelect.on('change', function(){
				var confirmed = confirm('Applying a new theme will overwite the current styling you have selected for this hotspot style');
				if (confirmed) {
					multiThemeApply(colorScheme, $(this));
				} else {
					$(this).val('');
				}
			});
		});
	};

	var multiThemeApply = function(colorScheme, select) {
		var themeSlug = select.val(),
			themeProperties = Object.keys(daThemes.themes[themeSlug].values);
		$.each( themeProperties, function(){
			var cfName = this;
			var input = colorScheme.find('input[id$="' + cfName + '"]');
			input.val(daThemes.themes[themeSlug].values[cfName]).trigger('change');
		});
	};

	var styleCloning = function(){
		var repeatGroup = $('#styles .cmb-repeatable-group');
		repeatGroup.on('cmb2_add_row', function(){
			multiThemeSelect();
		});
	};

	/* Stuff to fire off on page load */
	hotspotAdmin.init = function() {
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
		alwaysVisible();
		multiStyles();
		multiThemeSelect();
		styleCloning();
	}

}(jQuery, window.hotspotAdmin = window.hotspotAdmin || {}));

jQuery(function() {
	hotspotAdmin.init();
})