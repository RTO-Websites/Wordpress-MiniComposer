/**
 * Last change: 28.04.2017 16:26
 */

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
    currentColumnRow = null,
    lastContextMenuTarget = null,
    resizeArgs = null,
    editor = null;

  /**
   * DOM-Ready
   */
  $(function () {
    // make columns resizeable
    initResizeable();
    initEvents();

    jQuery('.composer-overlay').draggable();

    editor = new McEditor();

    // set container width
    $('.minicomposer-sortable-rows').css({
      width: ($('.minicomposer-sortable-rows').width() + 'px')
    });

    updateComposer();

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

    $('.minicomposer-add-column-4').on('click', function () {
      addColumn(4);
    });

    $('.minicomposer-autopublish').on('click', function () {
      $('.minicomposer-autopublish').toggleClass('active');
    });

    /**
     * Add new row
     */
    $('.minicomposer-add-row').on('click', addRow);


    // Event for delete-button
    $(document).on('click', '.global-contextmenu .minicomposer-delete', deleteColumnRow);
    // Event for delete-button
    $(document).on('click', '.global-style-settings .minicomposer-delete', removeBackgroundImage);
    // Event for add-column-in-row-button
    $(document).on('click', '.global-contextmenu .minicomposer-add-column-to-row', addColumnToRow);


    // Open Editor
    $(document).on('dblclick', '.minicomposer-column', openEditor);
    $(document).on('click', '.minicomposer-edit-text', openEditor);
    // Cancel&Close Editor
    $('.minicomposer-cancel-editor').on('click', cancelEditor);
    // Save&Close Editor
    $('.minicomposer-save-editor').on('click', saveEditor);
    // Apply Editor
    $('.minicomposer-apply-editor').on('click', function(e) { saveEditor(e, true) } );


    $('#publish').on('click',  saveEditor);


    // Event for responsive button
    $(document).on('click', '.minicomposer-responsive-settings', openResponsiveFields);
    // Save and close responsive-settings
    $('.minicomposer-save-responsive').on('click', saveResponsiveFields);
    // Cancel&Close responsive-settings
    $('.minicomposer-cancel-responsive').on('click', closeResponsiveFields);

    $(document).on('click', '.minicomposer-clone', cloneColumnRow);

    // Event for style button
    $(document).on('click', '.minicomposer-style-settings', openStyleFields);
    // Save and close style-settings
    $('.minicomposer-save-style').on('click', saveStyleFields);
    // Cancel&Close style-settings
    $('.minicomposer-cancel-style').on('click', closeStyleFields);

    // event for selectable image-list (list of media)
    $(document).on('click', '.selectable-image', selectImage);

    $(document).on('click', closeContextMenu);


    // contextmenu
    $(document).on('contextmenu', '.minicomposer-column, .minicomposer-row', openContextMenu);
  });

  function initEvents() {
    // make resizable
    $('.minicomposer-column').resizable(resizeArgs);

    new McDragNDrop();


    // set startSize of columns
    $('.minicomposer-column').each(function (index, element) {
      var columnWidth = window.getColumnWidth(element);
      $(element).css({
        width: (columnWidth * $(element).data('medium')) + 'px',
        maxWidth: (columnWidth * 12) + 'px'
      });

      window.recalcColumns(element);
    });

    $(window).on('resize', recalcColumns);
  }

  /**
   * Init resizeable column
   */
  function initResizeable() {
    var container = $('.minicomposer-sortable-rows'),
      containerWidth = container.width() - 30,
      maxWidth = containerWidth;

    window.columnWidth = Math.floor(containerWidth / 12);
    maxWidth = window.columnWidth * 12;

    resizeArgs = {
      grid: [window.columnWidth, 1],
      minWidth: window.columnWidth,
      maxWidth: maxWidth,
      handles: 'e, s',
      stop: resizeColumnEnd,
      resize: resizeColumn,
    };
  }

  /**
   * Adds a row
   */
  function addRow() {
    var newRow = $('<div class="minicomposer-row" draggable="true">' +
      '<span class="minicomposer-delete"></span>' +
      '</div>');

    $('.minicomposer-sortable-rows.active-composer').append(newRow);

    updateComposer();
  }


  /**
   * Adds a column
   */
  function addColumn(amount, row) {
    if (typeof(amount) === 'undefined') {
      amount = 1;
    }

    // create row if no row exists
    if (!$('.active-composer .minicomposer-row').length && typeof(row) === 'undefined') {
      addRow();
    }

    if (typeof(row) === 'undefined') {
      row = $('.active-composer .minicomposer-row').last();
    }

    var size = Math.round(12 / amount);

    for (var index = 0; index < amount; index += 1) {
      var column = $('<div class="minicomposer-column" data-medium="' + size + '" draggable="true">' +
        '<span class="content"></span>' +
        '<span class="column-bg"></span>' +
        '<span class="column-count">' + size + '</span>' +
        '</div>');



      var activeColumn = $('.minicomposer-column.has-contextmenu');
      if (activeColumn.length) {
        // after current selected column
        column.insertAfter(activeColumn);
        row = activeColumn.closest('.minicomposer-row');
      } else {
        // add in current row
        row.append(column);
      }
      column.css({width: (size * window.getColumnWidth(column)) + 'px'});

      column.resizable(resizeArgs);
    }

    window.recalcColumns(row);
    updateComposer();
  }

  function addColumnToRow(e) {
    var row = jQuery(e.target).closest('.minicomposer-row');

    addColumn(1, row);
  }


  /**
   * Clone column or row
   * @param e
   */
  function cloneColumnRow(e) {
    var element = $(e.target).closest('.minicomposer-column, .minicomposer-row');

    if (!element.length) {
      return;
    }

    // move contextmenu-element
    $('#minicomposer .inside').append($('.global-contextmenu'));

    var clonedElement = element.clone();

    clonedElement.insertAfter(element);

    updateComposer();
  }

  /**
   * Select an image from a list
   *
   * @param e
   */
  function selectImage(e) {
    var element = $(e.target),
      imageUrl = element.data('url');

    $('.upload-preview-image').prop('src', imageUrl);
    $('.upload-field').val(imageUrl);
  }


  /**
   * Trigger at end of resize
   *
   * @param e
   */
  function resizeColumnEnd(e, ui) {
    // set new min-height
    var newMinHeight = $(e.target).height();

    // set only if a minheight exists and newMinheight is not default
    // only if resize-direction is up
    if ((ui.size.height != ui.originalSize.height) &&
      (newMinHeight !== window.columnMinHeight || $(e.target).data('minheight') &&
      e.type == 'resizestop')
    ) {
      $(e.target).data('minheight', newMinHeight + 'px');
      $(e.target).css({'height': '', 'min-height': newMinHeight + 'px'});
    }

    updateComposer();
  }

  /**
   * Trigger on resize
   * Sets column-count
   *
   * @param e
   */
  function resizeColumn(e, ui) {
    var element = $(e.target),
      size = Math.floor($(element).outerWidth() / window.getColumnWidth(element)),
      elementMinHeight = parseInt($(element).css('min-height'));

    // set height from min-height or element-current-height
    // only if resize-direction is up
    if (elementMinHeight > 0 && ui.size.height != ui.originalSize.height) {
      var newHeight = elementMinHeight > $(element).height()
        ? elementMinHeight
        : $(element).height();

      $(element).css({'height': newHeight + 'px'});
      $(element).css({'min-height': ''});
    }

    // set column-size
    $(element).find('> .column-count').html(size);
    $(element).data('medium', Math.floor($(element).outerWidth() / window.getColumnWidth(element)));
    window.recalcColumns(element);
  }

  /**
   * Update the composer input field
   *
   * Runs on sortable sortupdate and resize
   */
  window.updateComposer = function () {
    var input = jQuery("#minicomposerColumns"),
      rowConfig = [];

    // create json
    rowConfig = getRowArray('.minicomposer-sortable-rows');

    input.val(JSON.stringify(rowConfig));

    if (typeof(updateComposerCallback) == 'function') {
      updateComposerCallback();
    }
  };

  /**
   * Get columns of a row recursive
   *
   * @param container
   * @returns {Array}
   */
  function getRowArray(container) {
    var rowConfig = [],
      colCount = 0,
      rowCount = 0;

    $(container).find('> .minicomposer-row').each(function (rowIndex, row) {
      rowConfig[rowCount] = {};
      rowConfig[rowCount]['options'] = getDataset(row);
      rowConfig[rowCount]['columns'] = [];

      $(row).find('> .minicomposer-column').each(function (index, column) {
        rowConfig[rowCount]['columns'][colCount] = getDataset(column);
        rowConfig[rowCount]['columns'][colCount].content = $(column).find('> .content').html();

        // must be Math.round for working calculation in zoom
        rowConfig[rowCount]['columns'][colCount].medium = Math.round($(column).outerWidth() / window.getColumnWidth(column));

        rowConfig[rowCount]['columns'][colCount].rows = getRowArray(column);

        $(column).find('> .column-count').html(rowConfig[rowCount]['columns'][colCount].medium);

        setStyle(column);
        colCount += 1;
      });

      colCount = 0;
      rowCount += 1;
      setStyle(row);
    });

    return rowConfig;
  }


  /**
   * Recalculate width of columns
   */
  window.recalcColumns = function (row) {
    if (typeof(row) === 'undefined') {
      row = '.minicomposer-sortable-rows';
    }

    $(row).find('.minicomposer-column').each(function (index, element) {
      var columnWidth = window.getColumnWidth(element);
      resizeArgs.grid = [columnWidth, 1];
      resizeArgs.minWidth = columnWidth;
      resizeArgs.maxWidth = columnWidth * 12;

      try {
        $(element).resizable('destroy');
      } catch(e) {

      }
      $(element).resizable(resizeArgs);

      $(element).css({
        width: (columnWidth * $(element).data('medium')) + 'px',
        maxWidth: (columnWidth * 12) + 'px'
      });
    });
  };

  /**
   * Get width of a single column in a row
   *
   * @param column
   * @returns {number}
   */
  window.getColumnWidth = function (column) {
    var row = $(column).closest('.minicomposer-row');

    return Math.floor(row.width() / 12);
  };


  /**
   * Deletes a column or row
   *
   * @param e
   */
  function deleteColumnRow(e) {
    var target = $(e.target),
      columnRow = target.closest('.minicomposer-column, .minicomposer-row');

    // move contextmenu-element
    $('#minicomposer .inside').append($('.global-contextmenu'));

    columnRow.remove();

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
   * Make Editor visible and fills with content from column
   *
   * @param e
   */
  function openEditor(e) {
    closeResponsiveFields();
    closeStyleFields();

    $('.has-editor-open').removeClass('has-editor-open');

    var target = $(e.target);
    if (!target.is('.minicomposer-column')) {
      target = target.closest('.minicomposer-column');
    }

    if (!target.is('.minicomposer-column')) {
      target = $('.minicomposer-column.has-contentmenu');
    }

    if (!target.is('.minicomposer-column')) {
      return;
    }

    closeContextMenu();

    var content = target.find('> .content').html();

    target.addClass('has-editor-open');
    currentColumnRow = target;

    editor.open(content);


    setOverlayPosition(target, $('.global-mc-editor'));
  }

  /**
   * Cancel and close Editor
   * @param e
   */
  function cancelEditor(e) {
    editor.cancel();
    currentColumnRow = null;
    closeEditor();
  }

  /**
   * Save Editor and copy content to column
   *
   * @param e
   */
  function  saveEditor(e, noClose) {
    // TODO: add apply button
    if (!currentColumnRow) {
      cancelEditor(e);
      return;
    }

    var content = editor.save();

    currentColumnRow.find('> .content').html(content);

    if (typeof(noClose) == 'undefined' || !noClose) {
      closeEditor();
    }

    updateComposer();


    if (typeof(saveEditorCallback) == 'function') {
      saveEditorCallback();
    }
  }

  /**
   * Make Editor hidden
   */
  function closeEditor() {
    $('.has-editor-open').removeClass('has-editor-open');
    currentColumnRow = null;
    $('.global-mc-editor').removeClass('visible');
  }

  /**
   * Display responsive-fields and fill with values from data-attribute
   *
   * @param e
   */
  function openResponsiveFields(e) {
    closeStyleFields();
    closeEditor();

    $('.has-responsive-open').removeClass('has-responsive-open');

    $('.global-responsive-settings').addClass('visible');
    setOverlayPosition($(e.target), $('.global-responsive-settings'));

    currentColumnRow = $(e.target).closest('.minicomposer-column, .minicomposer-row');
    currentColumnRow.addClass('has-responsive-open');

    $('#responsiveSmall').val(currentColumnRow.data('small'));
    $('#responsiveMedium').val(currentColumnRow.data('medium'));
    $('#responsiveLarge').val(currentColumnRow.data('large'));
    $('#customAttributes').val(currentColumnRow.data('customattributes'));

  }

  /**
   * Fill data-attribute with values from fields
   * and hide fields
   */
  function saveResponsiveFields() {
    if (!currentColumnRow) {
      closeResponsiveFields();
      return;
    }

    // set data-attributes
    currentColumnRow.data('small', $('#responsiveSmall').val());
    currentColumnRow.data('medium', $('#responsiveMedium').val());
    currentColumnRow.data('large', $('#responsiveLarge').val());
    currentColumnRow.data('customattributes', addSlashes($('#customAttributes').val()));

    currentColumnRow.css({width: window.getColumnWidth(currentColumnRow) * $('#responsiveMedium').val() + 'px'});

    closeResponsiveFields();
    updateComposer();
  }

  function addSlashes(str) {
    str = JSON.stringify(String(str));
    str = str.substring(1, str.length-1);
    return str;
  }

  /**
   * Cancel responsive fields
   */
  function closeResponsiveFields() {
    $('.has-responsive-open').removeClass('has-responsive-open');
    currentColumnRow = null;
    $('.global-responsive-settings').removeClass('visible');
  }

  /**
   * Display style-fields and fill with values from data-attribute
   *
   * @param e
   */
  function openStyleFields(e) {
    $('.has-style-open').removeClass('has-style-open');
    closeResponsiveFields();
    closeEditor();

    $('.global-style-settings').addClass('visible');
    setOverlayPosition($(e.target), $('.global-style-settings'));

    currentColumnRow = $(e.target).closest('.minicomposer-column, .minicomposer-row');
    currentColumnRow.addClass('has-style-open');

    var bgRepeat = typeof(currentColumnRow.data('backgroundrepeat')) !== 'undefined' ? currentColumnRow.data('backgroundrepeat') : 'no-repeat',
      bgPosition = typeof(currentColumnRow.data('backgroundposition')) !== 'undefined' ? currentColumnRow.data('backgroundposition') : 'center',
      bgSize = typeof(currentColumnRow.data('backgroundsize')) !== 'undefined' ? currentColumnRow.data('backgroundsize') : 'contain';

    $('#responsiveClass').val(currentColumnRow.data('cssclass'));
    $('#columnBackground-image').val(currentColumnRow.data('backgroundimage'));
    $('#columnBackground-color').val(currentColumnRow.data('backgroundcolor'));
    $('#columnBackground-repeat').val(bgRepeat);
    $('#columnBackground-position').val(bgPosition);
    $('#columnBackground-size').val(bgSize);
    $('#columnPadding').val(currentColumnRow.data('padding'));
    $('#columnGutter').val(currentColumnRow.data('gutter'));
    $('#minHeight').val(currentColumnRow.data('minheight'));
    $('#htmltag').val(currentColumnRow.data('htmltag'));

    if (typeof(currentColumnRow.data('backgroundimage')) !== 'undefined') {
      $('#columnBackground-image-img').prop('src', currentColumnRow.data('backgroundimage'));
    } else {
      $('#columnBackground-image-img').prop('src', '');
    }
  }

  /**
   * Fill data-attribute with values from fields
   * and hide fields
   */
  function saveStyleFields() {
    if (!currentColumnRow) {
      console.info('saveFail', currentColumnRow);
      closeStyleFields();
      return;
    }

    // set px to every size-value if not empty
    if (!isNaN($('#columnPadding').val()) && $('#columnPadding').val() > 0) {
      $('#columnPadding').val($('#columnPadding').val() + 'px');
    }
    if (!isNaN($('#columnGutter').val()) && $('#columnGutter').val() > 0) {
      $('#columnGutter').val($('#columnGutter').val() + 'px');
    }
    if (!isNaN($('#minheight').val()) && $('#minheight').val() > 0) {
      $('#minheight').val($('#minheight').val() + 'px');
    }

    // set data-attributes
    currentColumnRow.data('cssclass', $('#responsiveClass').val());
    currentColumnRow.data('backgroundimage', $('#columnBackground-image').val());
    currentColumnRow.data('backgroundcolor', $('#columnBackground-color').val());
    currentColumnRow.data('backgroundrepeat', $('#columnBackground-repeat').val());
    currentColumnRow.data('backgroundposition', $('#columnBackground-position').val());
    currentColumnRow.data('backgroundsize', $('#columnBackground-size').val());
    currentColumnRow.data('padding', $('#columnPadding').val());
    currentColumnRow.data('gutter', $('#columnGutter').val());
    currentColumnRow.data('minheight', $('#minHeight').val());
    currentColumnRow.data('htmltag', $('#htmltag').val());

    closeStyleFields();

    updateComposer();
  }


  /**
   * Cancel style fields
   */
  function closeStyleFields() {
    $('.has-style-open').removeClass('has-style-open');
    currentColumnRow = null;
    $('.global-style-settings').removeClass('visible');
  }

  /**
   * Set style from data-attributes to specific element
   *
   * @param element
   */
  function setStyle(element) {
    element = $(element);
    var bgElement = element.find('> .column-bg, > .row-bg');
    bgElement.css({
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
      bgElement.css({
        backgroundImage: 'url(' + element.data('backgroundimage') + ')'
      });
    } else {
      // remove bg-image
      bgElement.css({
        backgroundImage: ''
      });
    }

    // set min-height
    if (typeof(element.data('minheight')) !== 'undefined' &&
      element.data('minheight').length
    ) {
      element.css({minHeight: element.data('minheight')});
    } else {
      element.css({minHeight: ''});
    }
  }

  /**
   * Return an array of all data- attributes
   *
   * @param element
   * @returns {{}}
   */
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

  /**
   * Removes bg-image
   *
   * @param e
   */
  function removeBackgroundImage(e) {
    var target = $(e.target),
      image = target.parent().find('.upload-preview-image'),
      input = target.parent().find('.upload-field');

    input.val('');
    image.attr('src', '');
  }

  function openContextMenu(e) {
    e.preventDefault();
    $('.has-contextmenu').removeClass('has-contextmenu');
    $('.is-contextmenu-parent').removeClass('is-contextmenu-parent');

    var cMenu = $('.global-contextmenu');

    lastContextMenuTarget = $(e.currentTarget);

    lastContextMenuTarget.append(cMenu);
    lastContextMenuTarget.addClass('has-contextmenu');

    lastContextMenuTarget.parents('.minicomposer-column, .minicomposer-row').addClass('is-contextmenu-parent');

    cMenu.addClass('open');
    cMenu.css({
      top: (e.pageY + 10 - lastContextMenuTarget.offset().top) + 'px',
      left: (e.offsetX + 10) + 'px'
    });

    return false;
  }

  function closeContextMenu() {
    var cMenu = $('.global-contextmenu');
    $('.has-contextmenu').removeClass('has-contextmenu');
    $('.is-contextmenu-parent').removeClass('is-contextmenu-parent');
    cMenu.removeClass('open');
  }

})(jQuery);

