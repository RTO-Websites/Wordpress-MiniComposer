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


    // Open Editor
    $(document).on('dblclick', '.minicomposer-column', openEditor);
    // Cancel&Close Editor
    $('.minicomposer-cancel-editor').on('click', cancelEditor);
    // Save&Close Editor
    $('.minicomposer-save-editor').on('click', saveEditor);


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
  function addColumn(amount) {
    if (typeof(amount) === 'undefined') {
      amount = 1;
    }

    // create row if no row exists
    if (!$('.active-composer .minicomposer-row').length) {
      addRow();
    }

    var size = Math.round(12 / amount);

    for (var index = 0; index < amount; index += 1) {
      var column = $('<div class="minicomposer-column" data-medium="' + size + '" draggable="true">' +
        '<span class="content"></span>' +
        '<span class="column-bg"></span>' +
        '<span class="column-count">' + size + '</span>' +
        '</div>');


      // TODO: only on active
      $('.active-composer .minicomposer-row').last().append(column);
      column.css({width: (size * window.getColumnWidth(column)) + 'px'});

      column.resizable(resizeArgs);
    }

    window.recalcColumns($('.active-composer .minicomposer-row').last());
    updateComposer();
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
  function resizeColumnEnd(e) {
    // set new min-height
    var newMinHeight = $(e.target).height();

    // set only if a minheight exists and newMinheight is not default
    if (newMinHeight !== window.columnMinHeight || $(e.target).data('minheight') && e.type == 'resizestop') {
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
  function resizeColumn(e) {
    var element = $(e.target),
      size = Math.floor($(element).outerWidth() / window.getColumnWidth(element)),
      elementMinHeight = parseInt($(element).css('min-height'));

    // set height from min-height or element-current-height
    if (elementMinHeight > 0) {
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
      rowConfig[rowCount] = [];

      $(row).find('> .minicomposer-column').each(function (index, element) {
        rowConfig[rowCount][colCount] = getDataset(element);
        rowConfig[rowCount][colCount].content = $(element).find('> .content').html();

        // must be Math.round for working calculation in zoom
        rowConfig[rowCount][colCount].medium = Math.round($(element).outerWidth() / window.getColumnWidth(element));

        rowConfig[rowCount][colCount].rows = getRowArray(element)

        $(element).find('> .column-count').html(rowConfig[rowCount][colCount].medium);

        setStyle(element);
        colCount += 1;
      });

      colCount = 0;
      rowCount += 1;
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

      $(element).resizable('destroy');
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
      column = target.closest('.minicomposer-column'),
      row = target.closest('.minicomposer-row')

    // move contextmenu-element
    $('#minicomposer .inside').append($('.global-contextmenu'));

    if (column.length) {
      // delete column
      column.remove();
    } else {
      // delete row
      row.remove();
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
      return;
    }
    var content = target.find('> .content').html();

    target.addClass('has-editor-open');
    currentColumn = target;

    editor.open(content);


    setOverlayPosition(target, $('.global-mc-editor'));
  }

  /**
   * Cancel and close Editor
   * @param e
   */
  function cancelEditor(e) {
    editor.cancel();
    currentColumn = null;
    closeEditor();
  }

  /**
   * Save Editor and copy content to column
   *
   * @param e
   */
  function  saveEditor(e) {
    if (!currentColumn) {
      cancelEditor(e);
      return;
    }

    var content = editor.save();

    currentColumn.find('> .content').html(content);

    closeEditor();
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
    currentColumn = null;
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

    currentColumn = $(e.target).closest('.minicomposer-column');
    currentColumn.addClass('has-responsive-open');

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

    currentColumn.css({width: window.getColumnWidth(currentColumn) * $('#responsiveMedium').val() + 'px'});

    closeResponsiveFields();
    updateComposer();
  }

  /**
   * Cancel responsive fields
   */
  function closeResponsiveFields() {
    $('.has-responsive-open').removeClass('has-responsive-open');
    currentColumn = null;
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

    currentColumn = $(e.target).closest('.minicomposer-column');
    currentColumn.addClass('has-style-open');

    var bgRepeat = typeof(currentColumn.data('backgroundrepeat')) !== 'undefined' ? currentColumn.data('backgroundrepeat') : 'no-repeat',
      bgPosition = typeof(currentColumn.data('backgroundposition')) !== 'undefined' ? currentColumn.data('backgroundposition') : 'center',
      bgSize = typeof(currentColumn.data('backgroundsize')) !== 'undefined' ? currentColumn.data('backgroundsize') : 'contain';


    $('#columnBackground-image').val(currentColumn.data('backgroundimage'));
    $('#columnBackground-color').val(currentColumn.data('backgroundcolor'));
    $('#columnBackground-repeat').val(bgRepeat);
    $('#columnBackground-position').val(bgPosition);
    $('#columnBackground-size').val(bgSize);
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
    $('.has-style-open').removeClass('has-style-open');
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
    var bgElement = element.find('> .column-bg');
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

    var cMenu = $('.global-contextmenu');

    lastContextMenuTarget = $(e.currentTarget);

    lastContextMenuTarget.append(cMenu);
    lastContextMenuTarget.addClass('has-contextmenu');

    cMenu.addClass('open');
    cMenu.css({
      top: (e.offsetY + 10) + 'px',
      left: (e.offsetX + 10) + 'px'
    });

    return false;
  }

  function closeContextMenu() {
    var cMenu = $('.global-contextmenu');
    $('.has-contextmenu').removeClass('has-contextmenu');
    cMenu.removeClass('open');
  }

})(jQuery);

