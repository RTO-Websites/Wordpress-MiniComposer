<?php namespace Admin;

    /**
     * The admin-specific functionality of the plugin.
     *
     * @link       MiniComposer
     * @since      1.0.0
     *
     * @package    Minicomposer
     * @subpackage Minicomposer/admin
     */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Minicomposer
 * @subpackage Minicomposer/admin
 * @author     Sascha Hennemann <s.hennemann@rto.de>
 */
class MinicomposerAdmin
{

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

    private $textdomain;

    private $optionFields = array(
        'minicomposerColumns' => array(
            'type'  => 'textarea',
            'label' => '',
            'trClass' => 'not-hidden',
            'isJson' => true,
        ),
    );

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version )
    {
        $this->pluginName = $pluginName;
        $this->textdomain = $pluginName;
        $this->version = $version;

        add_action( 'add_meta_boxes', array( $this, 'registerPostSettings' ) );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {

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

        wp_enqueue_style( 'jquery-ui-resizable', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/minicomposer-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {

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

        wp_enqueue_script( 'jquery-ui-resizable' );
        wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/minicomposer-admin.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Register the Metaboxes for Gallery-Settings and Images
     *
     * @return boolean
     */
    public function registerPostSettings()
    {
        $postTypes = get_post_types();
        foreach ( $postTypes as $postType ) {
            add_meta_box( 'minicomposer', __( 'MiniComposer', $this->textdomain ), array( $this, 'addComposer' ), $postType );
        }
        return false;
    }

    /**
     * Add a metabox for gallery-settings (position, template)
     *
     * @param type $post
     */
    public function addComposer( $post )
    {
        $this->createFields( $post );

        $composerRows = get_post_meta( $post->ID, 'minicomposerColumns', true );

        include( 'partials/minicomposer-admin-display.php' );
    }

    /**
     * Creates fields from property optionFields
     */
    private function createFields( $post )
    {
        echo '<table class="form-table">';

        if ( !empty( $this->optionFields ) ) {
            // Loop Post-Options and generate inputs
            foreach ( $this->optionFields as $key => $option ) {
                $trClass = !empty( $option[ 'trClass' ] ) ? $option[ 'trClass' ] : '';
                $inputClass = !empty( $option[ 'inputClass' ] ) ? $option[ 'inputClass' ] : '';

                $value = get_post_meta( $post->ID, $key, true );

                if (!empty($option['isJson'])) {
                    $value = json_encode($value);
                }

                echo '<tr valign="top" class="input-type-' . $option[ 'type' ] . ' ' . $trClass . '">';
                // Generate Label
                echo '<th scope="row"><label class="theme-options-label" for="' . $key . '">' . $option[ 'label' ] . '</label></th>';
                echo '<td>';
                switch ( $option[ 'type' ] ) {
                    case 'select':
                        // Generate select
                        echo '<select class="theme-options-input" name="' . $key . ' ' . $inputClass . '" id="' . $key . '">';
                        if ( !empty( $option[ 'value' ] ) && is_array( $option[ 'value' ] ) ) {
                            foreach ( $option[ 'value' ] as $optionKey => $optionTitle ) {
                                $selected = '';
                                //echo '<br/>Key'.$option_key.'-'.get_post_meta($post->ID, $key, true);
                                if ( $optionKey == $value ) {
                                    $selected = ' selected="selected"';
                                }
                                echo '<option value="' . $optionKey . '"' . $selected . '>' . $optionTitle . '</option>';
                            }
                        }
                        echo '</select>';
                        break;

                    case 'input':
                        // Generate text-input
                        echo '<input class="theme-options-input ' . $inputClass . '" type="text" name="' . $key . '" id="' . $key . '" value="'
                            . $value . '" />';
                        break;

                    case 'textarea':
                        // Generate textarea
                        echo '<textarea class="theme-options-input ' . $inputClass . '" name="' . $key . '" id="' . $key . '">'
                            . $value .
                            '</textarea>';
                        break;

                    case 'hidden':
                    case 'number':
                    case 'text':
                        // Generate text-input
                        echo '<input class="theme-options-input ' . $inputClass . '" type="' . $option[ 'type' ] . '" name="' . $key . '" id="' . $key . '" value=\''
                            . $value . '\' />';
                        break;
                }
                echo '</td></tr>';
            }
        }

        echo '</table>';
    }

    /**
     * Method to save Post-Meta
     *
     * @global type $post_options
     * @param type $postId
     * @param type $post
     * @return type
     */
    public function savePostMeta( $postId, $post )
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !isset( $_POST[ 'post_type' ] ) ) {
            return;
        }

        if ( $_POST[ 'post_type' ] == 'page' ) {
            if ( !current_user_can( 'edit_page', $postId ) ) {
                return;
            }
        } else {
            if ( !current_user_can( 'edit_post', $postId ) ) {
                return;
            }
        }
        // Save form-fields
        if ( !empty( $this->optionFields ) ) {
            foreach ( $this->optionFields as $key => $postOption ) {
                $value = filter_input( INPUT_POST, $key );

                if (!empty($postOption['isJson'])) {
                    $value = json_decode($value);
                }
                update_post_meta( $postId, $key, $value );
            }
        }
    }
}
