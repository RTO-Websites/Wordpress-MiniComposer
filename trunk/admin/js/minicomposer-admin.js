/**
 * Last change: 04.04.2018 16:23
 */

(function($) {
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
    currentSize = 'medium',
    editor = null;

  /**
   * DOM-Ready
   */
  $(function() {
    // make columns resizeable
    initResizeable();
    initEvents();
    $('#minicomposer').addClass('medium-size');

    jQuery('.composer-overlay').each(function(index, element) {
      if (jQuery(element).find('.drag-handle').length) {
        jQuery(element).draggable({
          handle: '.drag-handle',
          containment: '.content_right',
        });
      } else {
        jQuery(element).draggable({containment: '.content_right'});
      }
    });

    editor = new McEditor();

    // set container width
    $('.minicomposer-sortable-rows').css({
      width: ($('.minicomposer-sortable-rows').width() + 'px')
    });

    updateComposer();

    /**
     * Add new column
     */
    $('.minicomposer-add-column').on('click', function() {
      addColumn(1);
    });

    $('.minicomposer-add-column-2').on('click', function() {
      addColumn(2);
    });

    $('.minicomposer-add-column-3').on('click', function() {
      addColumn(3);
    });

    $('.minicomposer-add-column-4').on('click', function() {
      addColumn(4);
    });

    $('.minicomposer-autopublish').on('click', function() {
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
    $('.minicomposer-apply-editor').on('click', function(e) {
      saveEditor(e, true)
    });


    $('#publish').on('click', saveEditor);


    // Event for responsive button
    $(document).on('click', '.minicomposer-responsive-settings', openResponsiveFields);
    // Save and close responsive-settings
    $('.minicomposer-save-responsive').on('click', saveResponsiveFields);
    // Cancel&Close responsive-settings
    $('.minicomposer-cancel-responsive').on('click', closeResponsiveFields);

    $(document).on('click', '.minicomposer-clone', cloneColumnRow);
    $(document).on('click', '.minicomposer-copy', copyColumnRow);
    $(document).on('click', '.minicomposer-insert', insertColumnRow);

    // Event for style button
    $(document).on('click', '.minicomposer-style-settings', openStyleFields);
    // Save and close style-settings
    $('.minicomposer-save-style').on('click', saveStyleFields);
    // Cancel&Close style-settings
    $('.minicomposer-cancel-style').on('click', closeStyleFields);

    // event for selectable image-list (list of media)
    $(document).on('click', '.selectable-image', selectImage);

    $(document).on('click', closeContextMenu);

    $(document).on('click', '.minicomposer-change-size-button', function(e) {
      changeMcColumnSize(e);
    });

    $(document).on('click', '.minicomposer-hide-column', function(e) {
      hideColumn(e);
    });

    // contextmenu
    $(document).on('contextmenu', '.minicomposer-column, .minicomposer-row', openContextMenu);

    recalcColumns();
  });

  window.changeMcColumnSize = function(e) {
    $('.minicomposer-change-size-button').removeClass('active');
    var button = $(e.target);
    button.addClass('active');

    switch(button.data('size')) {
      case 'medium':
        $('#minicomposer').addClass('medium-size');
        $('#minicomposer').removeClass('small-size large-size');
        currentSize = 'medium';
        break;
      case 'small':
        $('#minicomposer').addClass('small-size');
        $('#minicomposer').removeClass('large-size medium-size');
        currentSize = 'small';
        break;
      case 'large':
        $('#minicomposer').addClass('large-size');
        $('#minicomposer').removeClass('small-size medium-size');
        currentSize = 'large';
        break;
    }

    recalcColumns();
    updateComposer();
  };

  function initEvents() {
    // make resizable
    $('.minicomposer-column').resizable(resizeArgs);

    new McDragNDrop();

    // set startSize of columns
    $('.minicomposer-column').each(function(index, element) {
      var columnWidth = window.getColumnWidth(element);
      $(element).css({
        width: (columnWidth * $(element).data(currentSize)) + 'px',
        maxWidth: (columnWidth * 12) + 'px'
      });

      window.recalcColumns(element);
    });

    $(window).on('resize', function(e) {
      if (e.target != window) {
        return;
      }
      recalcColumns();
    });
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

    clonedElement.data(element.data());

    clonedElement.insertAfter(element);

    recalcColumns();
    updateComposer();
  }

  /**
   * Copy a column/row to localStorage
   *
   * @param e
   */
  function copyColumnRow(e) {
    var element = $(e.target).closest('.minicomposer-column, .minicomposer-row'),
      copyElement = null,
      copyData = {};

    if (!element.length) {
      return;
    }

    // move contextmenu-element
    $('#minicomposer .inside').append($('.global-contextmenu'));

    // copy data-attributes
    for (var index in element.data()) {
      if (typeof(element.data()[index]) == 'object') {
        continue;
      }
      copyData[index] = element.data()[index];
    }

    // save in localStorage
    localStorage.mcColumnRow = element.prop('outerHTML');
    localStorage.mcColumnRowData = JSON.stringify(copyData);
  }

  /**
   * Insert a column/row from localStorage
   *
   * @param e
   */
  function insertColumnRow(e) {
    var element = $(e.target).closest('.minicomposer-column, .minicomposer-row');

    if (!element.length) {
      return;
    }

    if (!localStorage.mcColumnRow) {
      return;
    }

    var columnRow = $(localStorage.mcColumnRow), // get column/row from storage
      columnRowData = JSON.parse(localStorage.mcColumnRowData);

    // add data-attributes from localstorage
    columnRow.data(columnRowData);

    if (columnRow.is('.minicomposer-column')) {
      // storage element is column
      if (element.is('.minicomposer-row')) {
        // clicked element is row
        columnRow.appendTo(element);
      } else {
        // clicked element is column
        columnRow.insertAfter(element);
      }
    } else {
      // storage element is row
      if (element.is('.minicomposer-row')) {
        // clicked element is row
        columnRow.insertAfter(element);
      } else {
        // clicked element is column
        columnRow.appendTo(element);
      }
    }

    recalcColumns();
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


    $('.selectable-image').removeClass('active');
    element.addClass('active');
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
    $(element).data(currentSize, Math.floor($(element).outerWidth() / window.getColumnWidth(element)));
    $(element).attr('data-' + currentSize, $(element).data(currentSize));
    window.recalcColumns(element);
  }

  /**
   * Update the composer input field
   *
   * Runs on sortable sortupdate and resize
   */
  window.updateComposer = function() {
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

    $(container).find('> .minicomposer-row').each(function(rowIndex, row) {
      rowConfig[rowCount] = {};
      rowConfig[rowCount]['options'] = getDataset(row);
      rowConfig[rowCount]['columns'] = [];

      $(row).find('> .minicomposer-column').each(function(index, column) {
        rowConfig[rowCount]['columns'][colCount] = getDataset(column);
        rowConfig[rowCount]['columns'][colCount].content = $(column).find('> .content').html();

        // must be Math.round for working calculation in zoom
        //rowConfig[rowCount]['columns'][colCount][currentSize] = Math.round($(column).outerWidth() / window.getColumnWidth(column));
        rowConfig[rowCount]['columns'][colCount][currentSize] = $(column).data(currentSize);
        rowConfig[rowCount]['columns'][colCount].rows = getRowArray(column);

        setColumnSizeInfo(column);

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
   * Sets the info over the columns
   *
   * @param column
   */
  function setColumnSizeInfo(column) {
    var columnSize = $(column).data(currentSize),
      columnSizeMedium = $(column).data('medium');

    if ((typeof(columnSize) == 'undefined' || !columnSize) && currentSize == 'large' && columnSizeMedium) {
      // if on large an size is empty, use medium-size
      $(column).find('> .column-count').html(columnSizeMedium);
    } else if (!columnSize) {
      $(column).find('> .column-count').html('12');
    } else if (columnSize == 13) {
      $(column).find('> .column-count').html('Hidden');
    } else {
      $(column).find('> .column-count').html(columnSize);
    }
  };

  /**
   * Recalculate width of columns
   */
  window.recalcColumns = function(row) {
    if (typeof(row) === 'undefined') {
      row = '.minicomposer-sortable-rows';
    }

    $(row).find('.minicomposer-column').each(function(index, element) {
      var columnWidth = window.getColumnWidth(element);
      resizeArgs.grid = [columnWidth, 1];
      resizeArgs.minWidth = columnWidth;
      resizeArgs.maxWidth = columnWidth * 12;

      try {
        $(element).resizable('destroy');
      } catch(e) {

      }
      $(element).resizable(resizeArgs);

      var columnSize = $(element).data(currentSize);

      if (currentSize == 'large' && !columnSize) {
        // if is large and no large-size is set, get from medium
        columnSize = $(element).data('medium');
      }

      if (!columnSize || columnSize == 13) {
        columnSize = 12;
      }

      $(element).css({
        width: (columnWidth * columnSize) + 'px',
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
  window.getColumnWidth = function(column) {
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
  function saveEditor(e, noClose) {
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
    $('.global-responsive-settings').removeClass('ref-column ref-row');
    setOverlayPosition($(e.target), $('.global-responsive-settings'));

    currentColumnRow = $(e.target).closest('.minicomposer-column, .minicomposer-row');
    currentColumnRow.addClass('has-responsive-open');
    if (currentColumnRow.is('.minicomposer-row')) {
      $('.global-responsive-settings').addClass('ref-row');
    } else {
      $('.global-responsive-settings').addClass('ref-column');
    }

    $('#responsiveSmall').val(currentColumnRow.data('small'));
    $('#responsiveMedium').val(currentColumnRow.data('medium'));
    $('#responsiveLarge').val(currentColumnRow.data('large'));

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
    currentColumnRow.attr('data-small', $('#responsiveSmall').val());
    currentColumnRow.attr('data-medium', $('#responsiveMedium').val());
    currentColumnRow.attr('data-large', $('#responsiveLarge').val());

    switch(currentSize) {
      case 'medium':
        currentColumnRow.css({width: window.getColumnWidth(currentColumnRow) * $('#responsiveMedium').val() + 'px'});
        break;
      case 'small':
        currentColumnRow.css({width: window.getColumnWidth(currentColumnRow) * $('#responsiveSmall').val() + 'px'});
        break;
      case 'large':
        currentColumnRow.css({width: window.getColumnWidth(currentColumnRow) * $('#responsiveLarge').val() + 'px'});
        break;
    }


    closeResponsiveFields();
    updateComposer();
  }

  function addSlashes(str) {
    str = JSON.stringify(String(str));
    str = str.substring(1, str.length - 1);
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
    $('.global-style-settings').removeClass('ref-column ref-row');
    setOverlayPosition($(e.target), $('.global-style-settings'));

    currentColumnRow = $(e.target).closest('.minicomposer-column, .minicomposer-row');
    currentColumnRow.addClass('has-style-open');

    if (currentColumnRow.is('.minicomposer-row')) {
      $('.global-style-settings').addClass('ref-row');
    } else {
      $('.global-style-settings').addClass('ref-column');
    }

    var bgRepeat = typeof(currentColumnRow.data('backgroundrepeat')) !== 'undefined' ? currentColumnRow.data('backgroundrepeat') : 'no-repeat',
      bgPosition = typeof(currentColumnRow.data('backgroundposition')) !== 'undefined' ? currentColumnRow.data('backgroundposition') : 'center',
      bgSize = typeof(currentColumnRow.data('backgroundsize')) !== 'undefined' ? currentColumnRow.data('backgroundsize') : 'contain',
      customAttributes = currentColumnRow.data('customattributes');

    if (!customAttributes) {
      customAttributes = '';
    }

    $('.selectable-image').removeClass('active');
    $('.selectable-image[data-url="' + currentColumnRow.data('backgroundimage') + '"]').addClass('active');

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
    //$('#customAttributes').val(customAttributes.replace(new RegExp("\\\\", "g"), ''));
    $('#customAttributes').val(decodeURIComponent(customAttributes));

    $('#static').prop('checked', currentColumnRow.data('static'));
    $('#fullwidthbg').prop('checked', currentColumnRow.data('fullwidthbg'));

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
    currentColumnRow.data('static', $('#static').is(':checked'));
    currentColumnRow.data('fullwidthbg', $('#fullwidthbg').is(':checked'));
    //currentColumnRow.data('customattributes', addSlashes($('#customAttributes').val()));
    currentColumnRow.data('customattributes', encodeURIComponent($('#customAttributes').val()));

    currentColumnRow.find('.column-class').html($('#responsiveClass').val());

    closeStyleFields();

    updateComposer();
  }

  function hideColumn(e) {
    var column = $(e.target).closest('.minicomposer-column');

    column.data(currentSize, 13);
    column.attr('data-' + currentSize, 13);

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
    var allowedTypes = ['string', 'number', 'boolean'],
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

