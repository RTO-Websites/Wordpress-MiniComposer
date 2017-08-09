<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       MiniComposer
 * @since      1.0.0
 *
 * @package    Minicomposer
 * @subpackage Minicomposer/admin/partials
 */
?>

    <div class="minicomposer-info">
        <?php _e('Use <b>right-click</b> on the columns/rows for more options.', $this->textdomain); ?><br />
        <br />
    </div>

    <div class="minicomposer-add-column button">+ <?php _e( 'Column', $this->textdomain ); ?></div>
    <div class="minicomposer-add-row button">+ <?php _e( 'Row', $this->textdomain ); ?></div>

    <div class="minicomposer-add-column-2 button">+
        2 <?php _e( 'Columns', $this->textdomain ); ?></div>
    <div class="minicomposer-add-column-3 button">+
        3 <?php _e( 'Columns', $this->textdomain ); ?></div>
    <div class="minicomposer-add-column-4 button">+
        4 <?php _e( 'Columns', $this->textdomain ); ?></div>
    <div class="minicomposer-autopublish button">Auto <?php _e( 'Publish' ); ?></div>
    <div class="minicomposer-change-size-button button" data-size="small"> <?php _e( 'Small' ); ?></div>
    <div class="minicomposer-change-size-button button active" data-size="medium"> <?php _e( 'Medium' ); ?></div>
    <div class="minicomposer-change-size-button button" data-size="large"> <?php _e( 'Large' ); ?></div>

    <div class="minicomposer-sortable-rows active-composer">
        <?php $this->getRows( $composerRows ); ?>
    </div>

    <div class="global-mc-editor composer-overlay">
        <div class="drag-handle"></div>
        <div class="mc-editor-inner">
            <?php
            wp_editor( '', 'composer_global_editor', array( 'wpautop' => true, 'forced_root_block' => false ) );
            ?>
            <div
                    class="button button-cancel button-secondary minicomposer-cancel-editor"><?php _e( 'Cancel' ); ?></div>
            <div
                    class="button button-save button-primary minicomposer-save-editor"><?php _e( 'Save' ); ?></div>
        </div>
    </div>

    <div class="global-responsive-settings composer-overlay">
        <div class="headline">Responsive</div>
        <?php $this->createFields( $post, $this->responsiveFields ); ?>
        <div
                class="button button-cancel minicomposer-cancel-responsive"><?php _e( 'Cancel' ); ?></div>
        <div
                class="button button-save button-primary minicomposer-save-responsive"><?php _e( 'Save' ); ?></div>
    </div>


    <div class="global-style-settings composer-overlay">
        <div class="headline">Style</div>
        <?php $this->createFields( $post, $this->styleFields ); ?>
        <div class="button button-cancel minicomposer-cancel-style"><?php _e( 'Cancel' ); ?></div>
        <div
                class="button button-save button-primary minicomposer-save-style"><?php _e( 'Save' ); ?></div>
    </div>

    <div class="global-contextmenu">
        <span class="minicomposer-style-settings"><?php _e( 'Style', $this->textdomain ); ?></span>
        <span
                class="minicomposer-responsive-settings"><?php _e( 'Responsive', $this->textdomain ); ?></span>
        <span
                class="minicomposer-edit-text"><?php _e( 'Edit Text', $this->textdomain ); ?></span>
        <span
                class="minicomposer-add-column-to-row"><?php _e( 'Add column', $this->textdomain ); ?></span>
        <span class="minicomposer-clone"><?php _e( 'Clone', $this->textdomain ); ?></span>

        <span class="minicomposer-hide-column dashicons dashicons-hidden"><?php _e( 'Hide'); ?></span>
        <br/>
        <span class="minicomposer-delete"><?php _e( 'Delete', $this->textdomain ); ?></span>
    </div>

<?php
$publishText = $post->post_status == 'publish' ? __( 'Update' ) : __( 'Publish' );
?>
    <input name="save"
            type="submit"
            class="button button-primary button-large"
            id="publish-2"
            value="<?php echo $publishText; ?>"/>


<?php include( 'minicomposer-admin-display-base.php' );

