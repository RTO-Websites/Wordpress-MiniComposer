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
<div class="minicomposer-add-column button">+ <?php _e( 'Column', $this->textdomain ); ?></div>
<div class="minicomposer-add-row button">+ <?php _e( 'Row', $this->textdomain ); ?></div>

<div class="minicomposer-sortable-rows">
    <?php foreach ( $composerRows as $row ): ?>
        <ul class="minicomposer-row">
            <?php foreach ( $row as $col ): ?>
                <li class="minicomposer-column"
                    <?php
                    foreach ( $col as $key => $value ) {
                        if ( $key == 'content' )
                            continue;
                        echo ' data-' . $key . '="' . $value . '" ';
                    }
                    ?>
                >
                    <span class="content"><?php echo( !empty( $col->content ) ? $col->content : '' ); ?></span>
                    <span class="options">
                        <span class="minicomposer-style-settings">Style</span>
                        <span class="minicomposer-responsive-settings">Responsive</span>
                        <span class="minicomposer-delete"><?php _e( 'Delete', $this->textdomain ); ?></span>
                    </span>
                </li>
            <?php endforeach; ?>

            <span class="options">
                <span class="minicomposer-delete">Delete</span>
            </span>
        </ul>
    <?php endforeach; ?>
</div>


<div class="global-wp-editor composer-overlay">
    <?php
    wp_editor( '', 'composer-global-editor' );
    ?>
    <div class="button button-cancel button-secondary minicomposer-cancel-wpeditor"><?php _e( 'Cancel' ); ?></div>
    <div class="button button-save button-primary minicomposer-save-wpeditor"><?php _e( 'Save' ); ?></div>
</div>

<div class="global-responsive-settings composer-overlay">
    <div class="headline">Responsive</div>
    <?php $this->createFields( $post, $this->responsiveFields ); ?>
    <div class="button button-cancel minicomposer-cancel-responsive"><?php _e( 'Cancel' ); ?></div>
    <div class="button button-save button-primary minicomposer-save-responsive"><?php _e( 'Save' ); ?></div>
</div>


<div class="global-style-settings composer-overlay">
    <div class="headline">Style</div>
    <?php $this->createFields( $post, $this->styleFields ); ?>
    <div class="button button-cancel minicomposer-cancel-style"><?php _e( 'Cancel' ); ?></div>
    <div class="button button-save button-primary minicomposer-save-style"><?php _e( 'Save' ); ?></div>
</div>