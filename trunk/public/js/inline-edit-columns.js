/**
 * Created by shennemann on 23.02.2017.
 */

var inlineEditColumns = function () {
  var win = window,
    doc = win.document,
    self = this,

    // private methods
    init,
    initEvents;

  init = function () {
    initEvents();
  };

  initEvents = function () {
    /**
     * Click on column
     */
    jQuery(document).on('click', '.edit-mode .mc-column', function (e) {
      self.editColumn(e);
    });
  };

  self.editColumn = function (e) {
    e.stopPropagation();
    var column = $(e.currentTarget),
      mc = column.closest('.mc-wrapper'),
      postid = mc.data('postid'),
      columnid = column.data('columnkey');


    // destroy current cke
    /*if (CKEDITOR.instances['inline-edit-field']) {
     CKEDITOR.instances['inline-edit-field'].destroy();
     }*/


    column.addClass('edit-open');

    // get column-content via ajax
    jQuery.ajax({
      url: adminAjax,
      type: "get",
      xhrFields: {
        withCredentials: true
      },
      data: {
        columnid: columnid,
        postid: postid,
        action: 'getColumnContent'
      },
    }).done(function (result) {
      if (result.success) {
        $('.inline-edit-box').removeData();
        $('.inline-edit-box').addClass('active');
        $('.inline-edit-box textarea').val(result.content);
        $('.inline-edit-box').data(result);
        $('.inline-edit-box-title').html('Column: ' + (parseInt(result.columnid) + 1)
          + '<br />Post: ' + result.postslug + ' (' + result.postid + ')');

        //CKEDITOR.config.height = $('.inline-edit-box').height() - 170;
        //CKEDITOR.replace('inline-edit-field');
      }
    });
  };

  /**
   * Save new column content via ajax in database
   */
  self.saveColumn = function (data) {
    jQuery.ajax({
      url: adminAjax,
      type: "post",
      xhrFields: {
        withCredentials: true
      },
      data: {
        columnid: data.columnid,
        postid: data.postid,
        newcontent: data.newContent,
        action: 'changeColumnContent'
      }
    }).done(function (result) {
      if (result.success) {
        self.saveInlineEditSuccess(result.columnid, result.postid);
      }
      window.inlineEditInstance.closeInlineEdit();
    });
  };

  /**
   * Callback after save
   *  Replaces old content in website with new content
   *
   * @param columnid
   * @param postid
   */
  self.saveInlineEditSuccess = function (columnid, postid) {
    window.columnSelector = '.mc-wrapper[data-postid=' + postid + '] .mc-column-'
      + (parseInt(columnid) + 1) + ' > .inner-column > .column-content';
    window.currentColumn = $(window.columnSelector);

    // load page with new content
    jQuery.get(window.location.href, {}, function (result) {
      var newHtml = jQuery.parseHTML(result, document, true),
        newColumn = $(newHtml).find(window.columnSelector);

      // replace content in column
      window.currentColumn.html(newColumn.html());
    });
  };

  init();
};