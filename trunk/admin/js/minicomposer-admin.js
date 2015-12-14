(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	var composerEditor = null,
		currentColumn = null,
		resizeArgs = null;

	/**
	 * DOM-Ready
	 */
	$(function() {
		// make columns sortable
		if (jQuery.fn.sortable) {
			var container = $('.minicomposer-sortable-rows .minicomposer-row'),
				maxWidth = container.width();

			window.columnWidth = Math.floor(container.width() / 12);

			resizeArgs =  {
				grid: window.columnWidth,
				minWidth: window.columnWidth,
				maxWidth: maxWidth,
				handles: 'e',
				resize: updateComposer
			};

			initEvents();
		}


		function initEvents() {
			// make resizable
			$('.minicomposer-column').resizable(resizeArgs);

			// make cols sortable
			jQuery('.minicomposer-row').sortable({
				connectWith: '.minicomposer-row',
				placeholder: "ui-state-highlight",
				update: updateComposer,
			});

			// make rows sortable
			$('.minicomposer-sortable-rows').sortable({
				items: '.minicomposer-row',
				placeholder: "ui-state-highlight-row",
				update: updateComposer,
			});

			// set startSize of columns
			$('.minicomposer-column').each(function(index, element) {
				$(element).css({
					width: (window.columnWidth * $(element).data('medium')) + 'px'
				});
			});

			// add upload
			addUpload() ;


		}

		function addUpload() {
			/**
			 * Upload.
			 */
			$('.upload-button').click(function(e) {
				window.uploadButton = $(e.target);
				tb_show('', 'media-upload.php?type=image&TB_iframe=true');

				return false;
			});

			/**
			 * Insert URL to input
			 */
			window.send_to_editor = function(html) {
				var imgurl = $('img', html).attr('src'),
					imgElement = window.uploadButton.parent().find('.upload-preview-image'),
					uploadField = window.uploadButton.parent().find('.upload-field');

				if (imgElement.length) {
					imgElement.prop('src', imgurl);
				}

				if (uploadField.length) {
					imgElement.val(imgurl);
				}
				tb_remove();
			}
		}

		/**
		 * Add new column
		 */
		$('.minicomposer-add-column').on('click', function() {
			var column = $('<li class="minicomposer-column"><span class="content"></span></li>');
			$('.minicomposer-row').last().append(column);
			column.resizable(resizeArgs);
			updateComposer();
		});

		/**
		 * Add new row
		 */
		$('.minicomposer-add-row').on('click', function() {
			$('.minicomposer-sortable-rows').last().append('<ul class="minicomposer-row" />');
			updateComposer();
		});



		// Event for delete-button
		$(document).on('click', '.minicomposer-delete', deleteColumnRow);


		// Open WP-Editor
		$(document).on('dblclick', '.minicomposer-column', openWpEditor);
		// Cancel&Close WP-Editor
		$('.minicomposer-cancel-wpeditor').on('click', cancelWpEditor);
		// Save&Close WP-Editor
		$('.minicomposer-save-wpeditor').on('click', saveWpEditor);


		// Event for responsive button
		$(document).on('click', '.minicomposer-responsive-settings', openResponsiveFields);
		// Save and close responsive-settings
		$('.minicomposer-save-responsive').on('click', saveResponsiveFields);
		// Cancel&Close responsive-settings
		$('.minicomposer-cancel-responsive').on('click', closeResponsiveFields);

	});


	/**
	 * Update the composer input field
	 *
	 * Runs on sortable sortupdate and resize
	 */
	function updateComposer() {
		var input = jQuery("#minicomposerColumns"),
			rowConfig = [],
			colCount = 0,
			rowCount = 0;

		$('.minicomposer-row').each(function(rowIndex, row) {
			rowConfig[rowCount] = [];

			$(row).find('> li').each(function(index, element) {
				rowConfig[rowCount][colCount] = {
					small: $(element).data('small'),
					medium: Math.floor($(element).outerWidth() / window.columnWidth),
					large: $(element).data('large'),
					cssclass: $(element).data('cssclass'),
					padding: $(element).data('padding'),
					content: $(element).find('.content').html()
				};

				colCount += 1;
			});

			colCount = 0;
			rowCount += 1;
		});

		$('.minicomposer-row').sortable('refresh');

		input.val(JSON.stringify(rowConfig));
	}


	/**
	 * Deletes a column or row
	 *
	 * @param e
     */
	function deleteColumnRow(e) {
		var target = $(e.target);

		if (target.closest('.minicomposer-column').length) {
			// delete column
			target.closest('.minicomposer-column').remove();
		} else {
			// delete row
			target.closest('.minicomposer-row').remove();
		}

		updateComposer();
	}

	/**
	 * Make Wp-Editor visible and fills with content from column
	 *
	 * @param e
     */
	function openWpEditor(e) {
		var content = $(e.target).find('.content').html();

		composerEditor = tinyMCE.get('composer-global-editor');

		currentColumn = $(e.target);
		$('.hidden-wp-editor').addClass('visible');
		composerEditor.setContent(content);
	}

	/**
	 * Cancel and close WP-Editor
	 * @param e
     */
	function cancelWpEditor(e) {
		composerEditor = tinyMCE.get('composer-global-editor');
		currentColumn = null;
		composerEditor.setContent('');
		closeWpEditor();
	}

	/**
	 * Save WP-Editor and copy content to column
	 *
	 * @param e
     */
	function saveWpEditor(e) {
		if (!currentColumn) {
			cancelWpEditor(e);
			return;
		}

		composerEditor = tinyMCE.get('composer-global-editor');
		currentColumn.find('.content').html(composerEditor.getContent());

		closeWpEditor();

		updateComposer();
	}

	/**
	 * Make WP-Editor hidden
	 */
	function closeWpEditor() {
		currentColumn = null;
		$('.hidden-wp-editor').removeClass('visible');
	}


	/**
	 * Display responsive-fields and fill with values from data-attribute
	 *
	 * @param e
     */
	function openResponsiveFields(e) {
		$('.hidden-responsive-settings').addClass('visible');
		currentColumn = $(e.target).closest('.minicomposer-column');
		$('#responsiveClass').val(currentColumn.data('cssclass'));
		$('#responsiveSmall').val(currentColumn.data('small'));
		$('#responsiveMedium').val(currentColumn.data('medium'));
		$('#responsiveLarge').val(currentColumn.data('large'));
		//$('#columnPadding').val(currentColumn.data('padding'));
	}

	/**
	 * Fill data-attribute with values from fields
	 * and hide fields
	 */
	function saveResponsiveFields() {
		if (!currentColumn) {
			closeResponsiveFields();
			return;
		}

		// set data-attributes
		//currentColumn.data('padding', $('#columnPadding').val());
		currentColumn.data('small', $('#responsiveSmall').val());
		currentColumn.data('medium', $('#responsiveMedium').val());
		currentColumn.data('large', $('#responsiveLarge').val());
		currentColumn.data('cssclass', $('#responsiveClass').val());

		currentColumn.css({width: window.columnWidth * $('#responsiveMedium').val() + 'px'});

		closeResponsiveFields();
		updateComposer();
	}

	/**
	 * Cancel responsive fields
	 */
	function closeResponsiveFields() {
		currentColumn = null;
		$('.hidden-responsive-settings').removeClass('visible');
	}

})( jQuery );
