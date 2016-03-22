
function updateComposerCallback() {
  var input = jQuery("#minicomposerColumns"),
    postId = jQuery('#post_ID'),
    autoPublishButton = jQuery('.minicomposer-autopublish');

  if ( !autoPublishButton.hasClass('active')) {
    return;
  }

  $.ajax({
    data: {
      action: 'save_minicomposer',
      postId: postId.val(),
      minicomposerColumns: input.val()
    },
    type: 'post',
    url: ajaxurl,
    success: function (data) {
      // do nothing
    }
  });
}