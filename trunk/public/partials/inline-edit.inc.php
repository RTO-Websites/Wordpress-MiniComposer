<?php
/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */

if ( \is_user_logged_in() && \current_user_can( 'edit_post', $post->ID ) ) {
    // is logged in
    global $mcPluginUrl, $mcPost;

    ?>
    <div class="inline-edit-menu">
        <a class="no-ajax edit-mode-button" href="#"
                onclick="jQuery('body').toggleClass('edit-mode');return false;">Edit
            Mode</a><br/>
        <a href="<?php echo admin_url( 'post.php' ); ?>?post=<?php echo $mcPost->ID; ?>&action=edit"
                target="_blank"
                class="no-ajax"><?php _e('To admin', $this->textdomain); ?>
        </a>
    </div>

    <div class="inline-edit-box">
        <!--textarea id="inline-edit-field"></textarea><br/-->
        <?php wp_editor('', 'composer_global_editor', array('media_buttons' => false)); ?>
        <input type="button" class="inline-edit-save-button" value="<?php _e('Save'); ?>"/>
        <input type="button" class="inline-edit-cancel-button" value="<?php _e('Cancel'); ?> "/>
        <div class="inline-edit-box-title"></div>
        <div class="close-button"></div>
    </div>

    <script>
      var adminAjax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    </script>
    <script type="text/javascript"
            src="<?php echo $mcPluginUrl; ?>/public/js/inline-edit-columns.js"></script>
    <script type="text/javascript"
            src="<?php echo $mcPluginUrl; ?>/public/js/inline-edit-title.js"></script>
    <script type="text/javascript"
            src="<?php echo $mcPluginUrl; ?>/public/js/inline-edit.js"></script>


    <link rel="stylesheet" href="<?php echo $mcPluginUrl; ?>/public/css/inline-edit.css"/>
    <style>
        .edit-mode .inline-edit-title,
        .edit-mode .mc-column {
            cursor: url("<?php echo $mcPluginUrl; ?>/public/img/edit-pen.png"), default;
        }
    </style>
    <?php
}
