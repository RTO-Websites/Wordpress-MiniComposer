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
use MagicAdminPage\MagicAdminPage;

include_once( 'MinicomposerAdminBase.php' );

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
class MinicomposerAdmin extends \MinicomposerAdminBase {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    private $textdomain;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        parent::__construct();

        $this->pluginName = $pluginName;
        $this->textdomain = $pluginName;
        $this->version = $version;

        $defaultMinHeight = '40';
        $this->options = MagicAdminPage::getOption( 'minicomposer' );
        if ( empty( $this->options['globalMinHeight'] ) ) {
            $this->options['globalMinHeight'] = $defaultMinHeight;
        }

        load_plugin_textdomain( $this->textdomain, false, '/' . $this->pluginName . '/languages' );


        $this->translateFields();

        $composerPage = new MagicAdminPage(
            'minicomposer',
            'MiniComposer',
            'MiniComposer',
            null,
            'dashicons-editor-table'
        );

        $composerPage->addFields( array(
            'globalPadding' => array(
                'type' => 'text',
                'title' => __( 'Column-Padding', $this->textdomain ),
            ),
            'globalGutter' => array(
                'type' => 'text',
                'title' => __( 'Column-Gutter', $this->textdomain ),
            ),
            'globalMinHeight' => array(
                'type' => 'text',
                'title' => __( 'Column-Min-Height', $this->textdomain ),
            ),
            'globalColumnMargin' => array(
                'type' => 'text',
                'title' => __( 'Column-Margin-Bottom', $this->textdomain ),
            ),
            'globalRowMargin' => array(
                'type' => 'text',
                'title' => __( 'Row-Margin-Bottom', $this->textdomain ),
            ),

            'headlineExpert' => array(
                'type' => 'headline',
                'title' => __( 'Expert-Settings', $this->textdomain ),
            ),
            'useBootstrap' => array(
                'type' => 'checkbox',
                'title' => __( 'Use bootstrap instead of foundation', $this->textdomain ),
            ),
            'embedFromCDN' => array(
                'type' => 'checkbox',
                'title' => __( 'Load Foundation/Bootstrap from CDN (only use if your theme doesn\'t already include it)', $this->textdomain ),
            ),

            'headlineAdmin' => array(
                'type' => 'headline',
                'title' => __( 'Admin-Style', $this->textdomain ),
            ),
            'columnAdminStyle' => array(
                'type' => 'textarea',
                'title' => __( 'Column-Style for admin', $this->textdomain ),
                'description' => __( 'Only for admin-view', $this->textdomain ),
            ),
            'columnAdminFont' => array(
                'type' => 'textarea',
                'title' => __( 'Column-Font for admin', $this->textdomain ),
                'description' => __( 'Only for admin-view', $this->textdomain ),
            ),
        ) );

        add_action( 'add_meta_boxes', array( $this, 'registerPostSettings' ), 1 );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );

        add_filter( 'tiny_mce_before_init', array( $this, 'switchTinymceEnterMode' ) );

        add_action( 'wp_ajax_save_minicomposer', array( $this, 'saveColumnsAjax' ) );
        add_action( 'wp_ajax_nopriv_save_minicomposer', array( $this, 'saveColumnsAjax' ) );


        // register ajax-tasks for inline-edit
        add_action( 'wp_ajax_getColumnContent', array( $this, 'ajaxGetColumnContent' ) );
        add_action( 'wp_ajax_nopriv_getcolumncontent', array( $this, 'ajaxGetColumnContent' ) );
        add_action( 'wp_ajax_changeColumnContent', array( $this, 'ajaxChangeColumnContent' ) );
        add_action( 'wp_ajax_nopriv_changeColumnContent', array( $this, 'ajaxChangeColumnContent' ) );

        add_action( 'wp_ajax_getTitleContent', array( $this, 'ajaxGetTitleContent' ) );
        add_action( 'wp_ajax_nopriv_getTitleContent', array( $this, 'ajaxGetTitleContent' ) );
        add_action( 'wp_ajax_changeTitleContent', array( $this, 'ajaxChangeTitleContent' ) );
        add_action( 'wp_ajax_nopriv_changeTitleContent', array( $this, 'ajaxChangeTitleContent' ) );
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style( 'jquery-ui-resizable', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/minicomposer-admin.css', array(), $this->version . time(), 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script( 'jquery-ui-resizable' );
        wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/minicomposer-admin.js', array( 'jquery' ), $this->version . time(), false );
        wp_enqueue_script( $this->pluginName . '-dragndrop', plugin_dir_url( __FILE__ ) . 'js/mc-dragndrop.js', array( 'jquery' ), $this->version . time(), false );
        wp_enqueue_script( $this->pluginName . '-editor', plugin_dir_url( __FILE__ ) . 'js/mc-editor.js', array( 'jquery' ), $this->version . time(), false );
        wp_enqueue_script( $this->pluginName . '-ajax', plugin_dir_url( __FILE__ ) . 'js/mc-ajax.js', array( 'jquery' ), $this->version . time(), false );
    }


    /**
     * Switch enter mode in tinymce from p to br
     *
     * @param $settings
     * @return mixed
     */
    public function switchTinymceEnterMode( $settings ) {
        $settings['forced_root_block'] = false;
        $settings["force_br_newlines"] = true;
        $settings["force_p_newlines"] = false;
        $settings["convert_newlines_to_brs"] = true;
        return $settings;
    }


    /**
     * Register the Metaboxes for Gallery-Settings and Images
     *
     * @return boolean
     */
    public function registerPostSettings() {
        $postTypes = get_post_types();
        foreach ( $postTypes as $postType ) {
            if ( !post_type_supports( $postType, 'editor' ) ) {
                continue;
            }
            add_meta_box( 'minicomposer', __( 'MiniComposer', $this->textdomain ), array( $this, 'addComposerWp' ), $postType, 'normal', 'high' );
        }
        return false;
    }

    /**
     * Add minicomposer-rows
     *
     * @param type $post
     */
    public function addComposerWp( $post ) {
        $this->createFields( $post, $this->optionFields );
        $composerRows = get_post_meta( $post->ID, 'minicomposerColumns', true );
        $emptyContent = !empty( $post->post_content ) ? nl2br( $post->post_content ) : '';

        $composerRows = $this->extractRows( $composerRows, $emptyContent );

        include( 'partials/minicomposer-admin-display.php' );
    }

    /**
     * Creates fields from property optionFields
     */
    private function createFields( $post, $fields ) {
        echo '<table class="form-table">';

        if ( !empty( $fields ) ) {
            // Loop Post-Options and generate inputs
            foreach ( $fields as $key => $option ) {
                $trClass = !empty( $option['trClass'] ) ? $option['trClass'] : '';
                $inputClass = !empty( $option['inputClass'] ) ? $option['inputClass'] : '';

                $value = get_post_meta( $post->ID, $key, true );

                if ( !empty( $option['isJson'] ) ) {
                    $value = json_encode( $value );
                }

                echo '<tr valign="top" class="input-type-' . $option['type'] . ' ' . $trClass . '">';
                // Generate Label
                echo '<th scope="row"><label class="field-label" for="' . $key . '">' . $option['label'] . '</label></th>';
                echo '<td>';

                if ( !empty( $option['descTop'] ) ) {
                    echo $option['descTop'] . '<br />';
                }

                switch ( $option['type'] ) {
                    case 'select':
                        // Generate select
                        $multiple = !empty( $option['multiple'] ) ? ' multiple ' : '';
                        $selectKey = !empty( $option['multiple'] ) ? $key . '[]' : $key;
                        echo '<select class="field-input" name="' . $selectKey . '" ' . $inputClass . ' id="' . $key . '" ' . $multiple . '>';
                        if ( !empty( $option['options'] ) && is_array( $option['options'] ) ) {
                            foreach ( $option['options'] as $optionKey => $optionTitle ) {
                                $selected = '';
                                if ( $optionKey == $value ||
                                    is_array( $value ) && in_array( $optionKey, $value )
                                ) {
                                    $selected = ' selected="selected"';
                                }
                                echo '<option value="' . $optionKey . '"' . $selected . '>' . $optionTitle . '</option>';
                            }
                        }
                        echo '</select>';
                        break;

                    case 'input':
                        // Generate text-input
                        echo '<input class="field-input ' . $inputClass . '" type="text" name="' . $key . '" id="' . $key . '" value="'
                            . $value . '" />';
                        break;

                    case 'textarea':
                        // Generate textarea
                        $cols = !empty( $option['cols'] ) ? ' cols="' . $option['cols'] . '"' : '';
                        $rows = !empty( $option['rows'] ) ? ' rows="' . $option['rows'] . '"' : '';
                        echo '<textarea class="field-input ' . $inputClass . '" name="' . $key . '" id="' . $key . '" ' . $rows . $cols . '>'
                            . $value .
                            '</textarea>';
                        break;

                    case 'background':
                        $allImages = get_posts( array(
                            'post_type' => 'attachment',
                            'post_mime_type' => 'image',
                            'post_status' => 'inherit',
                            'posts_per_page' => -1,
                        ) );

                        echo '<div class="input-background-container">';
                        echo '<div class="img-list">';

                        foreach ( $allImages as $image ) {
                            $thumbUrl = wp_get_attachment_thumb_url( $image->ID );
                            $url = wp_get_attachment_url( $image->ID );
                            echo '<div class="selectable-image" data-url="' . $url
                                . '" style="background-image:url(' . $thumbUrl . ');" title="' . $image->post_title . '"></div>';
                        }

                        echo '</div>';

                        echo '<span class="sublabel">' . __( 'Image', $this->textdomain ) . '</span>
                                <input class="field-input ' . $inputClass . ' upload-field" type="hidden" name="' . $key . '-image" id="'
                            . $key . '-image" value=\''
                            . $value . '\' />
                            <input class="field-button ' . $inputClass . ' upload-button" type="button" name="' . $key . '-image-button" id="'
                            . $key . '-image-button" value=\''
                            . __( 'Select image', $this->textdomain ) . '\' /> <span class="minicomposer-delete delete-image"></span>
                            <img src="" alt="" id="' . $key . '-image-img" class=" upload-preview-image" />
                            ';

                        echo '</div>';

                        echo '<span class="sublabel">' . __( 'Color', $this->textdomain ) . '</span>
                                <input class="field-input ' . $inputClass . '" type="text" name="' . $key . '-color" id="'
                            . $key . '-color" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Repeat</span>
                                <input list="datalist-bg-repeat" class="field-input ' . $inputClass . '" type="text" name="' . $key . '-repeat" id="'
                            . $key . '-repeat" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Position</span>
                                <input list="datalist-bg-position" class="field-input ' . $inputClass . '" type="text" name="' . $key . '-position" id="'
                            . $key . '-position" value=\''
                            . $value . '\' /><br />';
                        echo '<span class="sublabel">Size</span>
                                <input list="datalist-bg-size" class="field-input ' . $inputClass . '" type="text" name="' . $key . '-size" id="'
                            . $key . '-size" value=\''
                            . $value . '\' /><br />';
                        break;

                    case 'hidden':
                    case 'number':
                    case 'text':
                        // Generate text-input
                        echo '<input class="field-input ' . $inputClass . '" type="' . $option['type'] . '" name="' . $key . '" id="' . $key . '" value=\''
                            . $value . '\' />';
                        break;
                }
                if ( !empty( $option['desc'] ) ) {
                    echo '<br />' . $option['desc'];
                }

                echo '</td></tr>';
            }
        }

        echo '</table>';
    }

    public function translateFields() {
        foreach ( $this->styleFields as $key => $value ) {
            if ( !empty( $value['label'] ) ) {
                $this->styleFields[$key]['label'] = __( $this->styleFields[$key]['label'], $this->textdomain );
            }
        }
    }


    /**
     * Method to save Post-Meta
     *
     * @global type $post_options
     * @param type $postId
     * @param type $post
     * @return type
     */
    public function savePostMeta( $postId, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !isset( $_POST['post_type'] ) ) {
            return;
        }

        if ( $_POST['post_type'] == 'page' ) {
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
                if ( !filter_has_var( INPUT_POST, $key ) ) {
                    continue;
                }

                if ( isset( $_POST[$key] ) && is_array( $_POST[$key] ) ) {
                    // multiselect
                    $value = array();
                    foreach ( $_POST[$key] as $aKey => $aValue ) {
                        $value[] = filter_var( $aValue );
                    }
                } else {
                    // single field
                    $value = filter_input( INPUT_POST, $key );
                }

                if ( $key == 'minicomposerColumns' ) {
                    remove_action( 'save_post', array( $this, 'savePostMeta' ) );

                    $postContent = $this->getColumnContent( json_decode( $value ) );

                    wp_update_post( array(
                        'ID' => $postId,
                        'post_content' => $postContent,
                    ) );

                    add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );
                }

                if ( !empty( $postOption['isJson'] ) ) {
                    $value = json_decode( $value );
                }
                update_post_meta( $postId, $key, $value );

            }
        }
    }

    /**
     * AutoSave via ajax
     */
    public function saveColumnsAjax() {
        if ( !filter_has_var( INPUT_POST, 'minicomposerColumns' ) ) {
            return;
        }

        $value = filter_input( INPUT_POST, 'minicomposerColumns' );

        if ( empty( $value ) ) {
            return;
        }

        $postId = filter_input( INPUT_POST, 'postId' );

        if ( empty( $postId ) || !$this->checkRights($postId)  ) {
            die();
        }

        $postContent = $this->getColumnContent( json_decode( $value ) );

        wp_update_post( array(
            'ID' => $postId,
            'post_content' => $postContent,
        ) );

        update_post_meta( $postId, 'minicomposerColumns', json_decode( $value ) );

    }


    /**
     * Ajax: Gets title of a post for inline-edit
     */
    public function ajaxGetTitleContent() {
        header( 'Content-Type: application/json' );

        $postid = filter_input( INPUT_GET, 'postid' );

        $output = array(
            'postid' => $postid,
            'success' => false,
        );

        if ( empty( $postid ) || !$this->checkRights($postid)  ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['content'] = $post->post_title;
        $output['postslug'] = $post->post_name;
        $output['success'] = true;

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Change content of a column for inline-edit
     */
    public function ajaxChangeTitleContent() {
        header( 'Content-Type: application/json' );

        /*
         * Params
         *  newContent
         *  postid
         */

        $postid = filter_input( INPUT_POST, 'postid' );
        $newContent = filter_input( INPUT_POST, 'newcontent' );


        $output = array(
            'postid' => $postid,
            'newContent' => $newContent,
            'success' => false,
        );

        if ( empty( $postid )  || !$this->checkRights($postid) ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        wp_update_post( array(
            'ID' => $postid,
            'post_title' => $newContent,
        ) );;

        $output['success'] = true;
        #$output[ 'parsedContent' ] = do_shortcode( $newContent );

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Gets content of a single column for inline-edit
     */
    public function ajaxGetColumnContent() {
        header( 'Content-Type: application/json' );
        /*
         * Params
         *  postid
         *  columnid
         */

        $postid = filter_input( INPUT_GET, 'postid' );
        $columnid = filter_input( INPUT_GET, 'columnid' );

        $output = array(
            'columnid' => $columnid,
            'postid' => $postid,
            'success' => false,
        );

        if ( empty( $postid ) || !isset( $columnid ) || !$this->checkRights($postid) ) {
            die( json_encode( $output ) );
        }

        $composerRows = get_post_meta( $postid, 'minicomposerColumns', true );

        // get row-array
        $rows = ( $composerRows );

        // get list of all columns
        $columnList = $this::getColumnContentList( $rows );

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        // get selected column
        if ( isset( $columnList[$columnid] ) ) {
            $columnContent = $columnList[$columnid];
            $output['success'] = true;
            $output['content'] = $columnContent;
        }

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Change content of a column for inline-edit
     */
    public function ajaxChangeColumnContent() {
        header( 'Content-Type: application/json' );

        /*
         * Params
         *  newContent
         *  postid
         *  columnid
         */

        $postid = filter_input( INPUT_POST, 'postid' );
        $columnid = filter_input( INPUT_POST, 'columnid' );
        $newContent = filter_input( INPUT_POST, 'newcontent' );


        $output = array(
            'columnid' => $columnid,
            'postid' => $postid,
            'newContent' => $newContent,
            'success' => false,
        );

        if ( empty( $postid ) || !isset( $columnid ) || !$this->checkRights($postid)  ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        $composerRows = get_post_meta( $postid, 'minicomposerColumns', true );
        // get row-array
        $rows = ( $composerRows );

        // change content of column
        $rows = $this::changeColumnContent( $rows, $columnid, $newContent );
        $output['newRows'] = $rows;

        $postContent = $this->getColumnContent( $rows );

        wp_update_post( array(
            'ID' => $postid,
            'post_content' => $postContent,
        ) );;

        update_post_meta( $postid, 'minicomposerColumns', $rows );

        $output['success'] = true;
        #$output[ 'parsedContent' ] = do_shortcode( $newContent );

        die( json_encode( $output ) );
    }

    /**
     * Checks if user can edit_page/edit_post
     *
     * @int/object $post
     * @return bool
     */
    public function checkRights( $post ) {
        if ( is_numeric( $post ) ) {
            $post = get_post( $post );
        }

        if ( $post->post_type == 'page' ) {
            if ( current_user_can( 'edit_page', $post->ID ) ) {
                return true;
            }
        } else {
            if ( current_user_can( 'edit_post', $post->ID ) ) {
                return true;
            }
        }

        return false;
    }
}
