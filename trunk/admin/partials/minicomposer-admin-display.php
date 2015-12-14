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
                <li class="minicomposer-column"
                    data-small="<?php echo (isset($col->small) ? $col->small : ''); ?>"
                    data-medium="<?php echo (isset($col->medium) ? $col->medium : ''); ?>"
                    data-large="<?php echo (isset($col->large) ? $col->large : ''); ?>"
                    data-padding="<?php echo (isset($col->padding) ? $col->padding : ''); ?>"
                    data-cssclass="<?php echo (isset($col->cssclass) ? $col->cssclass : ''); ?>"

                    data-backgroundimage="<?php echo (isset($col->backgroundImage) ? $col->backgroundImage : ''); ?>"
                    data-backgroundcolor="<?php echo (isset($col->backgroundColor) ? $col->backgroundColor : ''); ?>"
                    data-backgroundsize="<?php echo (isset($col->backgroundSize) ? $col->backgroundSize : ''); ?>"
                    data-backgroundrepeat="<?php echo (isset($col->backgroundRepeat) ? $col->backgroundRepeat : ''); ?>"
                    data-backgroundposition="<?php echo (isset($col->backgroundPosition) ? $col->backgroundPosition : ''); ?>"
                >
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

<div class="hidden-responsive-settings">
    <?php $this->createFields($post, $this->responsiveFields); ?>
    <div class="button button-cancel minicomposer-cancel-responsive"><?php _e('Cancel'); ?></div>
    <div class="button button-save minicomposer-save-responsive"><?php _e('Save'); ?></div>
</div>


<div class="hidden-style-settings">
    <?php $this->createFields($post, $this->styleFields); ?>
    <div class="button button-cancel minicomposer-cancel-style"><?php _e('Cancel'); ?></div>
    <div class="button button-save minicomposer-save-style"><?php _e('Save'); ?></div>
</div>