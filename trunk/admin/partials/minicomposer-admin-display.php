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
<div class="minicomposer-add-column button">+ Column</div>
<div class="minicomposer-add-row button">+ Row</div>

<div class="minicomposer-sortable-rows">
    <?php foreach ($composerRows as $row): ?>
        <ul class="minicomposer-row">
            <?php foreach ($row as $col): ?>
                <li class="minicomposer-column" data-cols="<?php echo $col->cols; ?>">
                    <span class="content"><?php echo $col->content; ?></span>
                    <span class="options">
                        <span class="minicomposer-style-settings">Style</span>
                        <span class="minicomposer-responsive-settings">Responsive</span>
                        <span class="minicomposer-delete">Delete</span>
                    </span>
                </li>
            <?php endforeach; ?>

            <span class="options">
                <span class="minicomposer-style-settings">Style</span>
                <span class="minicomposer-responsive-settings">Responsive</span>
                <span class="minicomposer-delete">Delete</span>
            </span>
        </ul>
    <?php endforeach; ?>
</div>


<div class="hidden-wp-editor">
    <?php
        wp_editor('', 'composer-global-editor');
    ?>
    <div class="button button-cancel minicomposer-cancel-wpeditor"><?php _e('Cancel'); ?></div>
    <div class="button button-save minicomposer-save-wpeditor"><?php _e('Save'); ?></div>
</div>