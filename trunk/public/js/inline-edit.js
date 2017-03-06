/**
 * Created by shennemann on 22.02.2017.
 */


var inlineEdit = function () {
  var win = window,
    doc = win.document,
    self = this,

    // private methods
    init,
    initEvents,
    initTinyMce,
    initClasses;

  /**
   * Construct
   */
  init = function () {
    initTinyMce();
    initClasses();
    initEvents();
  };

  initClasses = function () {
    window.columnEdit = new inlineEditColumns();
    window.titleEdit = new inlineEditTitle();
  };

  /**
   * Close edit-box
   */
  self.closeInlineEdit = function () {
    $('.edit-open').removeClass('edit-open');
    $('.inline-edit-box').data({});
    $('.inline-edit-box').removeData();
    $('.inline-edit-box').removeClass('active');
    $('.inline-edit-box textarea').val('');
    $('.inline-edit-box-title').html('');
  };


  /**
   * Init tinymce
   */
  initTinyMce = function () {
    self.resizeTinyMce();
  };

  self.setTinyMceContent = function (content) {
    content = content.replace(/\<\/p\>/g, '</div><br /><br />');
    content = content.replace(/\<p\>/g, '<div>');
    content = content.replace(/\<p\s/g, '<div ');

    switchEditors.go('composer_global_editor', 'tinymce');

    composerEditor = tinyMCE.get('composer_global_editor');
    if (!composerEditor) {
      console.info('WP-Editor not found');
      return;
    }

    composerEditor.setContent(content);
  };

  self.getTinyMceContent = function () {
    switchEditors.go('composer_global_editor', 'tinymce');
    composerEditor = tinyMCE.get('composer_global_editor');

    return composerEditor.getContent();
  };

  self.resizeTinyMce = function () {
    if (typeof(tinyMCE) == 'undefined') {
      return false;
    }
    composerEditor = tinyMCE.get('composer_global_editor');
    if (composerEditor) {
      composerEditor.theme.resizeTo('100%', $('.inline-edit-box').height() - 170, true);
    }
  };

  /**
   * init events
   */
  initEvents = function () {
    /**
     * Onclick cancel button
     */
    jQuery('.inline-edit-cancel-button, .inline-edit-box .close-button').on('click', self.closeInlineEdit);


    /**
     * Save content
     */
    jQuery('.inline-edit-save-button').on('click', function (e) {
      //CKEDITOR.instances['inline-edit-field'].updateElement();
      var data = $('.inline-edit-box').data();
      //data.newContent = $('.inline-edit-box textarea').val();

      data.newContent = self.getTinyMceContent();

      if (data.columnid) {
        // save column
        columnEdit.saveColumn(data);
      } else {
        titleEdit.saveTitle(data);
      }
    });

    /**
     * Prevent link clicking
     */
    jQuery(document).on('click',
      '.edit-mode .mc-column a,' +
      '.edit-mode .inline-edit-title a' +
      '.edit-mode a .inline-edit-title',
      function (e) {
        e.preventDefault();
      });

    /**
     * Hover/mouseenter for editable elements
     *  Must done with js, cause propagation
     */
    jQuery(document).on('mouseover',
      '.edit-mode .div-area, ' +
      '.edit-mode .mc-column, ' +
      '.edit-mode .inline-edit-modul, ' +
      '.edit-mode .inline-edit-title, ' +
      '.edit-mode .inline-edit-admin',
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.inline-edit-hover').removeClass('inline-edit-hover');
        $(e.currentTarget).addClass('inline-edit-hover');
      });

    /**
     * Hover/mouseleave for editable elements
     *  Must done with js, cause propagation
     */
    jQuery(document).on('mouseleave',
      '.edit-mode .div-area, ' +
      '.edit-mode .mc-column, ' +
      '.edit-mode .inline-edit-modul, ' +
      '.edit-mode .inline-edit-title, ' +
      '.edit-mode .inline-edit-admin',
      function (e) {
        $('.inline-edit-hover').removeClass('inline-edit-hover');
      });

    /**
     * Resize cke on resize
     */
    jQuery(window).on('resize', function () {
      self.resizeTinyMce();
    });
  };

  init();
};

window.inlineEditInstance = new inlineEdit();