/**
 * Created by shennemann on 21.03.2016.
 */


var McEditor = function(args) {
  if (!$) $ = jQuery;

  var self = this;


  function init() {
    addUpload();
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
   * Open editor and fill with content
   *
   * @param content
   */
  this.open = function(content) {
    // hotfix for fucking <p>
    //content = content.replace(/\<\/p\>/g, '<br /><br />');
    //content = content.replace(/\<p\>/g, '');
    content = content.replace(/\<\/p\>/g, '</div><br /><br />');
    content = content.replace(/\<p\>/g, '<div>');
    content = content.replace(/\<p\s/g, '<div ');

    switchEditors.go('composer_global_editor', 'tinymce');

    composerEditor = tinyMCE.get('composer_global_editor');
    if (!composerEditor) {
      console.info('WP-Editor not found');
      return;
    }

    $('.global-mc-editor').addClass('visible');
    composerEditor.setContent(content);
  };

  /**
   * Cancel editor and clear content
   */
  this.cancel = function() {
    switchEditors.go('composer_global_editor', 'tinymce');
    composerEditor = tinyMCE.get('composer_global_editor');
    composerEditor.setContent('');
  };

  /**
   * Save editor and return content
   *
   * @returns {*}
   */
  this.save = function() {
    switchEditors.go('composer_global_editor', 'tinymce');
    composerEditor = tinyMCE.get('composer_global_editor');

    return composerEditor.getContent();
  };

  init();
};
