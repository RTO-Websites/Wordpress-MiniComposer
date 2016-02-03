

var McDragNDrop = function(args) {
  if (!$) $ = jQuery;

  var currentDrag;


  function initEvents() {
    $(document).on('dragstart', '.minicomposer-column, .minicomposer-row', function (e) {
      console.info('dragstart', e);
      $(e.target).addClass('dragging');
      currentDrag = $(e.target);
    });


    $(document).on('dragover', '.minicomposer-column:not(.minicomposer-column .minicomposer-column)', dragOverColumn);
    $(document).on('dragover', '.minicomposer-row', dragOverRow);

    $(document).on('drop', dropElement);

    $(document).on('dragend', '.minicomposer-column, .minicomposer-row', function(e) {
      console.info('dragend', e);
      finishDrag();
    });
  }


  /**
   * Drag row or column over a column
   *
   * @param e
   */
  function dragOverColumn(e) {
    var dropTarget = $(e.target);
    if (!dropTarget.hasClass('minicomposer-column') && !dropTarget.hasClass('minicomposer-row')) {
      return;
    }

    var dragClass = 'dragover';

    removeDragOverClasses();

    if (currentDrag.is('.minicomposer-column')) {
      // column drag
      if (e.originalEvent.offsetX > $(e.target).width() / 2) {
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

  function dragOverRow(e) {
    if (currentDrag.is('.minicomposer-column')) {
      return; // only rows can drogged over rows
    }
    console.info('dragoverrow');
  }

  /**
   * Drop column or row
   *
   * @param e
   */
  function dropElement(e) {
    console.info('drop', currentDrag);

    if (currentDrag.hasClass('minicomposer-row')) {
      // drag is row
      dropRow(e);
    } else {
      // drag is column
      dropColumn(e);
    }

    updateComposer();
    finishDrag();
  }

  function dropRow(e) {
    var dropTarget = $('#minicomposer').find('.dragover');
    console.info('drop row');

    if (dropTarget.find('.content').html().length) {
      // Todo: Create new column, add content and append to row
    }

    dropTarget.find('.content').remove();

    // append row to column
    dropTarget.append(currentDrag);
  }

  function dropColumn(e) {
    var dropTarget = $('#minicomposer').find('.dragover');

    if ($(e.target).hasClass('dragover-right')) {
      currentDrag.insertAfter(dropTarget);
    } else {
      currentDrag.insertBefore(dropTarget);
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