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

    <div class="minicomposer-add-column-2 button">+ 2 <?php _e( 'Columns', $this->textdomain ); ?></div>
    <div class="minicomposer-add-column-3 button">+ 3 <?php _e( 'Columns', $this->textdomain ); ?></div>
    <div class="minicomposer-add-column-4 button">+ 4 <?php _e( 'Columns', $this->textdomain ); ?></div>

    <div class="minicomposer-sortable-rows">
        <?php getRows( $composerRows ); ?>
    </div>


    <div class="global-wp-editor composer-overlay">
        <?php
        wp_editor( '', 'composer_global_editor', array('wpautop' => true, 'forced_root_block' => false) );
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


    <script>
        window.columnMinHeight = '<?php echo intval( $this->options['globalMinHeight'] ); ?>';
    </script>
    <style>
        .minicomposer-column {
            min-height: <?php echo intval($this->options['globalMinHeight']) - 10 . 'px'; ?>;
        }
        .minicomposer-column,
        .minicomposer-column .content,
        .minicomposer-column .content * {
            <?php
            if (!empty($this->options['columnAdminStyle'])) {
                echo $this->options['columnAdminStyle'];
            }
            ?>
        }
    </style>

    <?php
    if (!empty($this->options['columnAdminFont'])) {
        echo $this->options['columnAdminFont'];
    }
    ?>
    <datalist id="datalist-bg-size">
        <option>cover</option>
        <option>contain</option>
    </datalist>
    <datalist id="datalist-bg-position">
        <option>top center</option>
        <option>top left</option>
        <option>top right</option>
        <option>center</option>
        <option>bottom center</option>
        <option>bottom left</option>
        <option>bottom right</option>
    </datalist>
    <datalist id="datalist-bg-repeat">
        <option>repeat</option>
        <option>repeat-x</option>
        <option>repeat-y</option>
        <option>no-repeat</option>
    </datalist>

<?php
function getRows( $rows ) {
    foreach ( $rows as $row ): ?>
        <div class="minicomposer-row" draggable="true">
            <?php foreach ( $row as $col ): ?>
                <div class="minicomposer-column" draggable="true"
                    <?php
                    foreach ( $col as $key => $value ) {
                        if ( $key == 'content' )
                            continue;

                        if ( is_array( $value ) || is_object( $value ) ) {
                            //echo ' data-' . $key . '="' . json_encode($value) . '" ';
                        } else {
                            echo ' data-' . $key . '="' . $value . '" ';
                        }
                    }
                    ?>
                >
                    <span class="content"><?php echo( !empty( $col->content ) ? $col->content : '' ); ?></span>
                    <span class="options">
                        <span class="minicomposer-style-settings"></span>
                        <span class="minicomposer-responsive-settings"></span>
                        <span class="minicomposer-delete"></span>
                    </span>
                    <span class="column-bg"></span>
                    <span class="column-count"><?php echo $col->medium; ?></span>
                    <?php
                    if ( !empty( $col->rows ) ) {
                        getRows( $col->rows );
                    }
                    ?>
                </div>
            <?php endforeach; ?>

            <span class="options">
                <span class="minicomposer-delete"></span>
            </span>
            <span class="row-bg"></span>
        </div>
    <?php endforeach;
}
