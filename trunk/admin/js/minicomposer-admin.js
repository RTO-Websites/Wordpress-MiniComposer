(function ($) {
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
    resizeArgs = null,
    sortableRowArgs = null,
    sortableColArgs = null;

  /**
   * DOM-Ready
   */
  $(function () {
    // make columns sortable
    if (jQuery.fn.sortable) {
      initSortable();

      initEvents();

      // set container width
      $('.minicomposer-sortable-rows').css({
        width: ($('.minicomposer-sortable-rows').width() + 'px')
      });

      updateComposer();
    }

    /**
     * Add new column
     */
    $('.minicomposer-add-column').on('click', function () {
      addColumn(1);
    });

    $('.minicomposer-add-column-2').on('click', function () {
      addColumn(2);
    });

    $('.minicomposer-add-column-3').on('click', function () {
      addColumn(3);
    });

    /**
     * Add new row
     */
    $('.minicomposer-add-row').on('click', addRow);


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


    // Event for style button
    $(document).on('click', '.minicomposer-style-settings', openStyleFields);
    // Save and close style-settings
    $('.minicomposer-save-style').on('click', saveStyleFields);
    // Cancel&Close style-settings
    $('.minicomposer-cancel-style').on('click', closeStyleFields);

  });


  function initEvents() {
    // make resizable
    $('.minicomposer-column').resizable(resizeArgs);

    // make cols sortable
    $('.minicomposer-row').sortable(sortableColArgs);

    // make rows sortable
    $('.minicomposer-sortable-rows').sortable(sortableRowArgs);

    // set startSize of columns
    $('.minicomposer-column').each(function (index, element) {
      $(element).css({
        width: (window.columnWidth * $(element).data('medium')) + 'px'
      });
    });

    // add upload
    addUpload();
  }

  /**
   * Init sortable rows and columns
   */
  function initSortable() {
    var container = $('.minicomposer-sortable-rows'),
      containerWidth = container.width() - 30,
      maxWidth = containerWidth;

    window.columnWidth = Math.floor(containerWidth / 12);

    resizeArgs = {
      grid: [window.columnWidth, 1],
      minWidth: window.columnWidth,
      maxWidth: maxWidth,
      handles: 'e, s',
      stop: resizeColumnEnd,
      resize: resizeColumn,
    };

    sortableRowArgs = {
      items: '.minicomposer-row',
      placeholder: "ui-state-highlight-row",
      update: updateComposer,
    };

    sortableColArgs = {
      connectWith: '.minicomposer-row',
      placeholder: "ui-state-highlight",
      update: updateComposer,
    };
  }

  /**
   * Adds a row
   */
  function addRow() {
    var newRow = $('<ul class="minicomposer-row">' +
      '<span class="options">' +
      '<span class="minicomposer-delete">⌫</span>' +
      '</span>' +
      '</ul>');

    $('.minicomposer-sortable-rows').last().append(newRow);

    //$('.minicomposer-row').sortable('destroy');
    //$('.minicomposer-row').sortable(sortableColArgs);
    newRow.sortable(sortableColArgs);

    updateComposer();
  }


  /**
   * Adds a column
   */
  function addColumn(amount) {
    if (typeof(amount) === 'undefined') {
      amount = 1;
    }

    // create row if no row exists
    if (!$('.minicomposer-row').length) {
      addRow();
    }

    var size = Math.round(12 / amount);

    for (var index = 0; index < amount; index += 1) {
      var column = $('<li class="minicomposer-column" data-medium="' + size + '">' +
        '<span class="content"></span>' +
        '<span class="options">' +
        '<span class="minicomposer-style-settings"></span>' +
        '<span class="minicomposer-responsive-settings"></span>' +
        '<span class="minicomposer-delete"></span>' +
        '</span>' +
        '<span class="column-count">' + size + '</span>' +
        '</li>');

      column.css({width: (size * window.columnWidth) + 'px'});

      $('.minicomposer-row').last().append(column);

      column.resizable(resizeArgs);
      updateComposer();
    }
  }

  /**
   * Add upload for background-image
   */
  function addUpload() {
    // backup original-function to work with wp-editor
    window.oldSendToEditor = window.send_to_editor;

    /**
     * Upload.
     */
    $('.upload-button').click(function (e) {
      /**
       * Insert URL to input
       */
      window.send_to_editor = function (html) {
        var imgurl = $('img', html).attr('src'),
          imgElement = window.composerUploadButton.parent().find('.upload-preview-image'),
          uploadField = window.composerUploadButton.parent().find('.upload-field');

        // fill preview image
        if (imgElement.length) {
          imgElement.prop('src', imgurl);
        }

        // fill upload-fields
        if (uploadField.length) {
          uploadField.val(imgurl);
        }
        tb_remove();

        window.composerUploadButton = null;


        // write back original-function
        window.send_to_editor = window.oldSendToEditor;
      };

      window.composerUploadButton = $(e.target);
      tb_show('', 'media-upload.php?type=image&TB_iframe=true');

      return false;
    });
  }

  /**
   * Trigger at end of resize
   *
   * @param e
   */
  function resizeColumnEnd(e) {
    // set new min-height
    var newMinHeight = $(e.target).height() - 10;

    // set only if a minheight exists and newMinheight is not default
    if (newMinHeight !== window.columnMinHeight || $(e.target).data('minheight')) {
      $(e.target).data('minheight', newMinHeight + 'px');
      $(e.target).css({'height': ''});
      $(e.target).find('> .content').css({'min-height': newMinHeight + 'px'});
    }

    updateComposer();
  }

  /**
   * Trigger on resize
   * Sets column-count
   *
   * @param e
   */
  function resizeColumn(e) {
    $('.minicomposer-row').each(function (rowIndex, row) {
      $(row).find('> li').each(function (index, element) {
        var size = Math.floor($(element).outerWidth() / window.columnWidth);
        $(element).find('.column-count').html(size);
      });
    });
  }

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

    $('.minicomposer-row').each(function (rowIndex, row) {
      rowConfig[rowCount] = [];

      $(row).find('> li').each(function (index, element) {
        rowConfig[rowCount][colCount] = getDataset(element);
        rowConfig[rowCount][colCount].content = $(element).find('.content').html();

        rowConfig[rowCount][colCount].medium = Math.floor($(element).outerWidth() / window.columnWidth);

        $(element).find('.column-count').html(rowConfig[rowCount][colCount].medium);

        setStyle(element);
        colCount += 1;
      });

      colCount = 0;
      rowCount += 1;
    });

    $('.minicomposer-sortable-rows').sortable('refresh');

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


  function setOverlayPosition(target, overlay) {
    var container = $('.minicomposer-sortable-rows'),
      containerOffset = container.offset(),
      top = target.offset().top - containerOffset.top + 54,
      left = target.offset().left - containerOffset.left + 10;

    if (left > container.width() - overlay.width()) {
      left = container.width() - overlay.width() - 30;
    }

    overlay.css({
      top: top + 'px',
      left: left + 'px',
    })
  }

  /**
   * Make Wp-Editor visible and fills with content from column
   *
   * @param e
   */
  function openWpEditor(e) {
    closeResponsiveFields();
    closeStyleFields();

    if (!$(e.target).is('.minicomposer-column')) {
      return;
    }
    var content = $(e.target).find('.content').html();

    switchEditors.go('composer_global_editor', 'tinymce');

    composerEditor = tinyMCE.get('composer_global_editor');
    if (!composerEditor) {
      console.info('WP-Editor not found');
      return;
    }

    currentColumn = $(e.target);
    $('.global-wp-editor').addClass('visible');
    composerEditor.setContent(content);

    setOverlayPosition($(e.target), $('.global-wp-editor'));
  }

  /**
   * Cancel and close WP-Editor
   * @param e
   */
  function cancelWpEditor(e) {
    switchEditors.go('composer_global_editor', 'tinymce');
    composerEditor = tinyMCE.get('composer_global_editor');
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

    switchEditors.go('composer_global_editor', 'tinymce');

    composerEditor = tinyMCE.get('composer_global_editor');
    currentColumn.find('.content').html(composerEditor.getContent());

    closeWpEditor();

    updateComposer();
  }

  /**
   * Make WP-Editor hidden
   */
  function closeWpEditor() {
    currentColumn = null;
    $('.global-wp-editor').removeClass('visible');
  }

  /**
   * Display responsive-fields and fill with values from data-attribute
   *
   * @param e
   */
  function openResponsiveFields(e) {
    closeStyleFields();
    closeWpEditor();

    $('.global-responsive-settings').addClass('visible');
    setOverlayPosition($(e.target), $('.global-responsive-settings'));

    currentColumn = $(e.target).closest('.minicomposer-column');

    $('#responsiveClass').val(currentColumn.data('cssclass'));
    $('#responsiveSmall').val(currentColumn.data('small'));
    $('#responsiveMedium').val(currentColumn.data('medium'));
    $('#responsiveLarge').val(currentColumn.data('large'));

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
    $('.global-responsive-settings').removeClass('visible');
  }

  /**
   * Display style-fields and fill with values from data-attribute
   *
   * @param e
   */
  function openStyleFields(e) {
    closeResponsiveFields();
    closeWpEditor();
    $('.global-style-settings').addClass('visible');
    setOverlayPosition($(e.target), $('.global-style-settings'));

    currentColumn = $(e.target).closest('.minicomposer-column');

    $('#columnBackground-image').val(currentColumn.data('backgroundimage'));
    $('#columnBackground-color').val(currentColumn.data('backgroundcolor'));
    $('#columnBackground-repeat').val(currentColumn.data('backgroundrepeat'));
    $('#columnBackground-position').val(currentColumn.data('backgroundposition'));
    $('#columnBackground-size').val(currentColumn.data('backgroundsize'));
    $('#columnPadding').val(currentColumn.data('padding'));
    $('#columnGutter').val(currentColumn.data('gutter'));
    $('#minHeight').val(currentColumn.data('minheight'));

    if (typeof(currentColumn.data('backgroundimage')) !== 'undefined') {
      $('#columnBackground-image-img').prop('src', currentColumn.data('backgroundimage'));
    } else {
      $('#columnBackground-image-img').prop('src', '');
    }
  }

  /**
   * Fill data-attribute with values from fields
   * and hide fields
   */
  function saveStyleFields() {
    if (!currentColumn) {
      console.info('saveFail', currentColumn);
      closeStyleFields();
      return;
    }

    // set data-attributes
    currentColumn.data('backgroundimage', $('#columnBackground-image').val());
    currentColumn.data('backgroundcolor', $('#columnBackground-color').val());
    currentColumn.data('backgroundrepeat', $('#columnBackground-repeat').val());
    currentColumn.data('backgroundposition', $('#columnBackground-position').val());
    currentColumn.data('backgroundsize', $('#columnBackground-size').val());
    currentColumn.data('padding', $('#columnPadding').val());
    currentColumn.data('gutter', $('#columnGutter').val());
    currentColumn.data('minheight', $('#minHeight').val());

    closeStyleFields();

    updateComposer();
  }


  /**
   * Cancel style fields
   */
  function closeStyleFields() {
    currentColumn = null;
    $('.global-style-settings').removeClass('visible');
  }

  /**
   * Set style from data-attributes to specific element
   *
   * @param element
   */
  function setStyle(element) {
    element = $(element);
    var contentElement = element.find('.content');
    contentElement.css({
      backgroundColor: element.data('backgroundcolor'),
      backgroundRepeat: element.data('backgroundrepeat'),
      backgroundPosition: element.data('backgroundposition'),
      backgroundSize: element.data('backgroundsize'),
    });

    // set bg-image
    if (typeof(element.data('backgroundimage')) !== 'undefined' &&
      element.data('backgroundimage').length
    ) {
      // set bg-image
      contentElement.css({
        backgroundImage: 'url(' + element.data('backgroundimage') + ')'
      });
    } else {
      // remove bg-image
      contentElement.css({
        backgroundImage: ''
      });
    }

    // set min-height
    if (typeof(element.data('minheight')) !== 'undefined' &&
      element.data('minheight').length
    ) {
      contentElement.css({minHeight: element.data('minheight')});
    } else {
      contentElement.css({minHeight: ''});
    }
  }

  function getDataset(element) {
    var allowedTypes = ['string', 'number'],
      data = $(element).data(),
      filteredData = {};

    for (var index in data) {
      if ($.inArray(typeof(data[index]), allowedTypes) !== -1) {
        filteredData[index] = data[index];
      }
    }

    return filteredData;
  }

})(jQuery);

