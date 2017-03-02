<?php
/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */

if ( \is_user_logged_in() && \current_user_can( 'edit_post' ) ) {
    // is logged in
    global $mcPluginUrl;

    ?>
    <div class="inline-edit-menu">
        <a class="no-ajax edit-mode-button" href="#"
                onclick="jQuery('body').toggleClass('edit-mode');return false;">Edit
            Mode</a><br/>
        <a href="<?php echo admin_url( 'post.php' ); ?>?post=<?php echo $postid; ?>&action=edit"
                target="_blank"
                class="no-ajax">Zur
            Admin</a>
    </div>

    <div class="inline-edit-box">
        <textarea id="inline-edit-field"></textarea><br/>
        <input type="button" class="inline-edit-save-button" value="Speichern"/>
        <input type="button" class="inline-edit-cancel-button" value="Abbrechen"/>
        <div class="inline-edit-box-title"></div>
        <div class="close-button"></div>
    </div>

    <script>
      var adminAjax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    </script>
    <script type="text/javascript"
            src="<?php echo $mcPluginUrl; ?>/public/js/inline-edit-columns.js"></script>
    <script type="text/javascript"
            src="<?php echo $mcPluginUrl; ?>/public/js/inline-edit.js"></script>


    <link rel="stylesheet" href="<?php echo $mcPluginUrl; ?>/public/css/inline-edit.css"/>
    <style>
        .edit-mode .mc-column {
            cursor: url("<?php echo $mcPluginUrl; ?>/public/img/edit-pen.png"), default;
        }
    </style>
    <?php
}
