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
        add_action( 'wp_head', array( $this, 'addHeaderStyle' ) );
    }

    /**
     * Add pixel to numeric values
     */
    public function addPxToGlobalOptions() {
        if ( isset( $this->options['globalGutter'] ) ) {
            $this->options['globalGutter'] = $this->addPxToValue( $this->options['globalGutter'] );
        }
        if ( isset( $this->options['globalPadding'] ) ) {
            $this->options['globalPadding'] = $this->addPxToValue( $this->options['globalPadding'] );
        }
        if ( isset( $this->options['globalMinHeight'] ) ) {
            $this->options['globalMinHeight'] = $this->addPxToValue( $this->options['globalMinHeight'] );
        }
        if ( isset( $this->options['globalColumnMargin'] ) ) {
            $this->options['globalColumnMargin'] = $this->addPxToValue( $this->options['globalColumnMargin'] );
        }
        if ( isset( $this->options['globalRowMargin'] ) ) {
            $this->options['globalRowMargin'] = $this->addPxToValue( $this->options['globalRowMargin'] );
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
        } else {
            $split = explode( ' ', $value );
            if ( count( $split ) > 1 ) {
                foreach ( $split as $key => $part ) {
                    if ( is_numeric( $part ) ) {
                        $split[$key] = $part . 'px';
                    }
                }

                $value = implode( ' ', $split );
            }
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

        return $gridOutput;
    }

    /**
     * Create HTML for rows and columns (recursive)
     *
     * @param $rows
     * @return string
     */
    private function createRows( $rows ) {
        $gridOutput = '';

        // loop row
        foreach ( $rows as $rowIndex => $row ) {
            $gridOutput .= '<div class="row  mc-row">';

            // loop columns
            foreach ( $row as $columnIndex => $column ) {
                $this->columnCount += 1;
                // set classes for grid
                $columnClasses = $this->createColumnClasses( $column );
                $columnInnerStyle = $this->createColumnStyle( $column );

                $columnStyle = '';

                // add column-specific gutter
                if ( isset( $column->gutter ) && $column->gutter !== '' ) {
                    $columnStyle .= 'padding-left:' . $this->addPxToValue( $column->gutter ) . ';'
                        . 'padding-right:' . $this->addPxToValue( $column->gutter )
                        . ';';
                }


                // generate html for column
                $gridOutput .= '<div class="mc-column-' . $this->columnCount . ' mc-column  columns ' . $columnClasses . '" style="' . $columnStyle . '">';
                $gridOutput .= '<div class="inner-column" style="' . $columnInnerStyle . '">';
                // remove <p>
                $column->content = str_replace( '</p>', '<br /><br />', $column->content );
                $column->content = str_replace( '<p>', '', $column->content );
                // replace &nbsp;
                $column->content = str_replace( '&nbsp;', ' ', $column->content );
                $gridOutput .= trim( $column->content );

                // column has inner-row -> call recursive createRows
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
     * Adds global style for grid on header
     */
    public function addHeaderStyle() {
        echo '<style class="mc-style">';
        // global style
        echo '.row .inner-column{';
        echo 'position:relative;';
        if ( isset( $this->options['globalPadding'] ) ) {
            echo 'padding:' . $this->options['globalPadding'] . ';';
        }
        echo isset( $this->options['globalMinHeight'] ) ? 'min-height:' . $this->options['globalMinHeight'] . ';' : '';
        echo isset( $this->options['globalColumnMargin'] ) ? 'margin-bottom:' . $this->options['globalColumnMargin'] . ';' : '';
        echo '}';

        if ( isset( $this->options['globalRowMargin'] ) && $this->options['globalRowMargin'] !== '' ) {
            echo '.mc-row{margin-bottom:' . $this->options['globalRowMargin'] . ';}';
        }

        if ( isset( $this->options['globalGutter'] ) && $this->options['globalGutter'] !== '' ) {
            echo '.mc-column{padding-left:' . $this->options['globalGutter'] . ';' . ';padding-right:' . $this->options['globalGutter'] . ';}';
        }

        echo '.mc-column.clear-left{';
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

        if ( isset( $column->padding ) && $column->padding !== '' ) {
            $columnStyle .= 'padding:' . $this->addPxToValue( $column->padding ) . ';';
        }

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

        // load bootstrap or foundation from CDN if activated
        if ( !empty( $this->options['embedFromCDN'] ) && empty( $this->options['useBootstrap'] ) ) {
            wp_enqueue_style( 'foundation', 'https://cdnjs.cloudflare.com/ajax/libs/foundation/6.1.2/foundation.min.css', array(), $this->version, 'all' );
        } else if ( !empty( $this->options['embedFromCDN'] ) ) {
            wp_enqueue_style( 'bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css', array(), $this->version, 'all' );
        }

        //wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/minicomposer-public.css', array(), $this->version, 'all' );
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
