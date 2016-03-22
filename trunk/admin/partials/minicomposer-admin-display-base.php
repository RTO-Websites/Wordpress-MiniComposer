
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
if ( !empty( $this->options['columnAdminFont'] ) ) {
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

