/**
 * Created by shennemann on 23.02.2017.
 */

var inlineEditTitle = function () {
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
    jQuery(document).on('click', '.edit-mode .inline-edit-title', function (e) {
      self.editTitle(e);
    });
  };

  self.editTitle = function (e) {
    e.stopPropagation();
    var titleElement = $(e.currentTarget),
      postid = titleElement.data('postid');


    // destroy current cke
    /*if (CKEDITOR.instances['inline-edit-field']) {
     CKEDITOR.instances['inline-edit-field'].destroy();
     }*/

    // get column-content via ajax
    jQuery.ajax({
      url: adminAjax,
      type: "get",
      xhrFields: {
        withCredentials: true
      },
      data: {
        postid: postid,
        action: 'getTitleContent'
      },
    }).done(function (result) {
      if (result.success) {
        titleElement.addClass('edit-open');
        $('.inline-edit-box').removeData();
        $('.inline-edit-box').addClass('active');
        //$('.inline-edit-box textarea').val(result.content);
        $('.inline-edit-box').data(result);
        $('.inline-edit-box-title').html('Post: ' + result.postslug + ' (' + result.postid + ')');

        inlineEditInstance.resizeTinyMce();
        inlineEditInstance.setTinyMceContent(result.content);

        //CKEDITOR.config.height = $('.inline-edit-box').height() - 170;
        //CKEDITOR.replace('inline-edit-field');
      }
    });
  };

  /**
   * Save new column content via ajax in database
   */
  self.saveTitle = function (data) {
    jQuery.ajax({
      url: adminAjax,
      type: "post",
      xhrFields: {
        withCredentials: true
      },
      data: {
        postid: data.postid,
        newcontent: data.newContent,
        action: 'changeTitleContent'
      }
    }).done(function (result) {
      if (result.success) {
        self.saveInlineEditSuccess(result.postid);
      }
      window.inlineEditInstance.closeInlineEdit();
    });
  };

  /**
   * Callback after save
   *  Replaces old content in website with new content
   *
   * @param postid
   */
  self.saveInlineEditSuccess = function (postid) {
    window.titleSelector = '.inline-edit-title-' + postid;
    window.currentTitle = $(window.titleSelector);

    // load page with new content
    jQuery.get(window.location.href, {}, function (result) {
      var newHtml = jQuery.parseHTML(result, document, true),
        newTitle = $(newHtml).find(window.titleSelector);
      console.info('newtitle', newTitle);

      // replace content in column
      window.currentTitle.html(newTitle.html());
    });
  };

  init();
};