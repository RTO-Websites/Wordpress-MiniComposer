

var McDragNDrop = function(args) {
  if (!$) $ = jQuery;

  var currentDrag,
    lastDragOver = Date.now();


  function initEvents() {
    $(document).on('dragstart', '.minicomposer-column, .minicomposer-row', function (e) {
      $(e.target).addClass('dragging');
      currentDrag = $(e.target);
    });


    $(document).on('dragover', '.minicomposer-column:not(.minicomposer-column .minicomposer-column), .minicomposer-row', dragOver);
    //$(document).on('dragover', '.minicomposer-row', dragOverRow);

    $(document).on('drop', dropElement);

    $(document).on('dragend', '.minicomposer-column, .minicomposer-row', function(e) {
      finishDrag();
    });
  }


  /**
   * Drag row or column over a column
   *
   * @param e
   */
  function dragOver(e) {
    if (lastDragOver+50 > Date.now()) return;
    lastDragOver = Date.now();

    var dropTarget = $(e.target);
    if (!dropTarget.hasClass('minicomposer-column') && !dropTarget.hasClass('minicomposer-row') ) {
      dropTarget = dropTarget.closest('.minicomposer-column, .minicomposer-row');
    }

    removeDragOverClasses();

    if (currentDrag.find(dropTarget).length) {
      return;
    }

    if (dropTarget.hasClass('minicomposer-row')) {
      dragOverRow(e);
    } else {
      dragOverColumn(e);
    }

    return;
  }

  /**
   * Drag over a column
   *
   * @param e
   */
  function dragOverColumn(e) {
    var dropTarget = $(e.target),
      dragClass = 'dragover';

    if (currentDrag.is('.minicomposer-column')) {
      // column drag
      if (e.originalEvent.offsetX > dropTarget.width() / 2) {
        dragClass += ' dragover-right';
      } else {
        dragClass += ' dragover-left';
      }
    } else {
      // row drag
      dragClass += ' dragover-insert';
    }

    dropTarget.addClass(dragClass);
  }

  /**
   * Drag over a row
   *
   * @param e
   */
  function dragOverRow(e) {
    var dropTarget = $(e.target);
    if (!dropTarget.hasClass('minicomposer-row')) {
      dropTarget = dropTarget.closest('.minicomposer-row');
    }
    if (!dropTarget.length) {
      return;
    }

    var dragClass = 'dragover';

    if (currentDrag.is('.minicomposer-column')) {
      // is column
      dragClass += ' dragover-insert';
    } else {
      // is row, check if on top or bottom to drop before or after
      if (e.originalEvent.offsetY > dropTarget.height() / 2) {
        dragClass += ' dragover-bottom';
      } else {
        dragClass += ' dragover-top';
      }
    }
    dropTarget.addClass(dragClass);
  }

  /**
   * Drop column or row
   *
   * @param e
   */
  function dropElement(e) {
    if (currentDrag.hasClass('minicomposer-row')) {
      // drag is row
      dropRow(e);
    } else {
      // drag is column
      dropColumn(e);
    }
    recalcColumns();
    updateComposer();
    finishDrag();
  }

  /**
   * Drop a row
   *
   * @param e
   */
  function dropRow(e) {
    var dropTarget = $('#minicomposer').find('.dragover');

    if (dropTarget.hasClass('minicomposer-column')) {
      // append row to column
      dropTarget.append(currentDrag);
    } else if (dropTarget.hasClass('dragover-bottom')) {
      currentDrag.insertAfter(dropTarget);
    } else {
      currentDrag.insertBefore(dropTarget);
    }
  }

  /**
   * Drop a column
   *
   * @param e
   */
  function dropColumn(e) {
    var dropTarget = $('#minicomposer').find('.dragover');

    try {
      if (dropTarget.hasClass('minicomposer-row')) {
        dropTarget.append(currentDrag);
      } else if (dropTarget.hasClass('dragover-right')) {
        currentDrag.insertAfter(dropTarget);
      } else {
        currentDrag.insertBefore(dropTarget);
      }
    } catch(e) {
      console.info('Fatal drop error');
    }
  }


  /**
   * Remove all classes on all dragover-elements
   */
  function removeDragOverClasses() {
    $('.dragover').removeClass('dragover dragover-left dragover-right dragover-top dragover-bottom dragover-insert');
  }

  /**
   * Finish dragging
   */
  function finishDrag() {
    currentDrag = null;
    $('.dragging').removeClass('dragging');

    removeDragOverClasses();
  }

  initEvents();

};