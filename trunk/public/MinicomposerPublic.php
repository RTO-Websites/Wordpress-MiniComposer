<?php namespace Pub;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       MiniComposer
 * @since      1.0.0
 *
 * @package    Minicomposer
 * @subpackage Minicomposer/public
 */
use MagicAdminPage\MagicAdminPage;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Minicomposer
 * @subpackage Minicomposer/public
 * @author     Sascha Hennemann <s.hennemann@rto.de>
 */
class MinicomposerPublic {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $columnCount = 0;

    private $columnStyle = '';


    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {

        $this->pluginName = $pluginName;
        $this->version = $version;
        $this->options = MagicAdminPage::getOption( 'minicomposer' );

        $this->addPxToGlobalOptions();

        add_filter( 'the_content', array( $this, 'appendColumns' ) );
        add_action( 'wp_footer', array( $this, 'addFooterStyle' ) );
    }

    /**
     * Add pixel to numeric values
     */
    public function addPxToGlobalOptions() {
        if ( isset( $this->options['globalGutter'] ) && is_numeric( $this->options['globalGutter'] ) ) {
            $this->options['globalGutter'] .= 'px';
        }
        if ( isset( $this->options['globalPadding'] ) && is_numeric( $this->options['globalPadding'] ) ) {
            $this->options['globalPadding'] .= 'px';
        }
        if ( isset( $this->options['globalMinHeight'] ) && is_numeric( $this->options['globalMinHeight'] ) ) {
            $this->options['globalMinHeight'] .= 'px';
        }
        if ( isset( $this->options['globalColumnMargin'] ) && is_numeric( $this->options['globalColumnMargin'] ) ) {
            $this->options['globalColumnMargin'] .= 'px';
        }
        if ( isset( $this->options['globalRowMargin'] ) && is_numeric( $this->options['globalRowMargin'] ) ) {
            $this->options['globalRowMargin'] .= 'px';
        }
    }

    /**
     * Add px to numeric value
     *
     * @param $value
     * @return string
     */
    public function addPxToValue( $value ) {
        if ( is_numeric( $value ) ) {
            $value .= 'px';
        }
        return $value;
    }

    /**
     * Append columns to the_content()
     *
     * @param $content
     * @return string
     */
    public function appendColumns( $content ) {
        global $post;
        $gridOutput = '';

        $grid = get_post_meta( $post->ID, 'minicomposerColumns', true );

        if ( empty( $grid ) ) {
            return $content;
        }

        $gridOutput .= $this->createRows( $grid );

        return $content . $gridOutput;
    }

    /**
     * Create HTML for rows and columns
     *
     * @param $rows
     * @return string
     */
    private function createRows( $rows ) {
        $gridOutput = '';
        foreach ( $rows as $rowIndex => $row ) {
            $gridOutput .= '<div class="row  mc-row">';
            foreach ( $row as $columnIndex => $column ) {
                $this->columnCount += 1;
                // set classes for grid
                $columnClasses = $this->createColumnClasses( $column );
                $columnInnerStyle = $this->createColumnStyle( $column );

                $columnStyle = isset( $column->gutter ) && $column->gutter !== '' ? '.mc-column-' . $this->columnCount
                    . '{padding:' . $this->addPxToValue( $column->gutter ) . '}' : '';

                $this->columnStyle .= $columnStyle . '.mc-column-' . $this->columnCount . ' > .inner-column{' . $columnInnerStyle . '}';

                $gridOutput .= '<div class="mc-column-' . $this->columnCount . ' mc-column  columns ' . $columnClasses . '">';
                $gridOutput .= '<div class="inner-column">';
                $gridOutput .= trim( $column->content );
                if ( !empty( $column->rows ) ) {
                    $gridOutput .= $this->createRows( $column->rows );
                }

                $gridOutput .= '</div>';
                $gridOutput .= '</div>';
            }
            $gridOutput .= '</div>';
        }

        return $gridOutput;
    }

    /**
     * Adds style for grid on footer
     */
    public function addFooterStyle() {
        echo '<style class="mc-style">';
        // global style
        echo '.row .inner-column{';
        echo 'position:relative;';
        echo isset( $this->options['globalPadding'] ) ? 'padding:' . $this->options['globalPadding'] . ';' : '';
        echo isset( $this->options['globalMinHeight'] ) ? 'min-height:' . $this->options['globalMinHeight'] . ';' : '';
        echo isset( $this->options['globalColumnMargin'] ) ? 'margin-bottom:' . $this->options['globalColumnMargin'] . ';' : '';
        echo '}';

        if ( isset( $this->options['globalRowMargin'] ) && $this->options['globalRowMargin'] !== '' ) {
            echo '.mc-row{margin-bottom:' . $this->options['globalRowMargin'] . ';}';
        }

        if ( isset( $this->options['globalGutter'] ) && $this->options['globalGutter'] !== '' ) {
            echo '.mc-column{padding-left:' . $this->options['globalGutter'] . ';padding-right:' . $this->options['globalGutter'] . ';}';
        }

        echo '.mc-column.clear-left {';
        echo 'clear: left;';
        echo '}';

        // column style
        echo $this->columnStyle;
        echo '</style>';
    }

    /**
     * Create classes like small-4 or large-5 for grid
     *
     * @param $column
     * @return string
     */
    public function createColumnClasses( $column ) {
        $columnClasses = '';
        if ( empty( $this->options['useBootstrap'] ) ) {
            $columnClasses .= !empty( $column->small ) ? ' small-' . $column->small : '';
            $columnClasses .= !empty( $column->medium ) ? ' medium-' . $column->medium : '';
            $columnClasses .= !empty( $column->large ) ? ' large-' . $column->large : '';
        } else {
            $columnClasses .= !empty( $column->small ) ? ' col-xs-' . $column->small : '';
            $columnClasses .= !empty( $column->medium ) ? ' col-md-' . $column->medium : '';
            $columnClasses .= !empty( $column->large ) ? ' col-lg-' . $column->large : '';
        }

        if ( !empty( $column->cssclass ) ) {
            $columnClasses .= ' ' . $column->cssclass;
        }

        return $columnClasses;
    }


    /**
     * Create style for column (background, padding)
     */
    public function createColumnStyle( $column ) {
        $columnStyle = '';
        $columnStyle .= !empty( $column->backgroundimage ) ? 'background-image:url(' . $column->backgroundimage . ');' : '';
        $columnStyle .= !empty( $column->backgroundcolor ) ? 'background-color:' . $column->backgroundcolor . ';' : '';
        $columnStyle .= !empty( $column->backgroundposition ) ? 'background-position:' . $column->backgroundposition . ';' : '';
        $columnStyle .= !empty( $column->backgroundrepeat ) ? 'background-repeat:' . $column->backgroundrepeat . ';' : '';
        $columnStyle .= !empty( $column->backgroundsize ) ? 'background-size:' . $column->backgroundsize . ';' : '';
        $columnStyle .= isset( $column->padding ) && $column->padding !== '' ? 'padding:' . $this->addPxToValue( $column->padding ) . ';' : '';
        $columnStyle .= isset( $column->minheight ) && $column->minheight !== '' ? 'min-height:' . $this->addPxToValue( $column->minheight ) . ';' : '';

        return $columnStyle;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in MinicomposerLoader as all of the hooks are defined
         * in that particular class.
         *
         * The MinicomposerLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/minicomposer-public.css', array(), $this->version, 'all' );

        wp_enqueue_style( $this->pluginName );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in MinicomposerLoader as all of the hooks are defined
         * in that particular class.
         *
         * The MinicomposerLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/minicomposer-public.js', array( 'jquery' ), $this->version, false );

    }

}
