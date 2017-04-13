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

include_once( 'MinicomposerPublicBase.php' );

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
class MinicomposerPublic extends \MinicomposerPublicBase {

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

    public $pluginUrl;
    private $textdomain;


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
        //$this->options = MagicAdminPage::getOption( 'minicomposer' );

        $this->options = array(
            'globalPadding' => get_theme_mod('globalPadding'),
            'globalGutter' => get_theme_mod('globalGutter'),
            'globalMinHeight' => get_theme_mod('globalMinHeight'),
            'globalColumnMargin' => get_theme_mod('globalColumnMargin'),
            'globalRowMargin' => get_theme_mod('globalRowMargin'),

            'useBootstrap' => get_theme_mod('useBootstrap'),
            'embedFromCDN' => get_theme_mod('embedFromCDN'),
        );

        $this->textdomain = $pluginName;

        load_plugin_textdomain( $this->textdomain, false, '/' . $this->pluginName . '/languages' );

        $this->addPxToGlobalOptions();

        add_filter( 'the_content', array( $this, 'appendColumns' ) );
        add_filter( 'the_title', array( $this, 'wrapTitle' ) );
        add_action( 'wp_head', array( $this, 'addHeaderStyle' ) );
        add_action( 'wp_footer', array( $this, 'addInlineEdit' ) );

        add_shortcode( 'post', array( $this, 'postShortcode' ) );

        parent::__construct();
    }


    /**
     * Add inline-edit from include
     */
    public function addInlineEdit() {
        global $post;
        if ( \is_user_logged_in() && \current_user_can( 'edit_post', $post->ID ) ) {
            include( 'partials/inline-edit.inc.php' );
        }
    }

    /**
     * Wraps title for inline-editing
     *
     * @param $title
     * @return string
     */
    public function wrapTitle( $title, $pid = null ) {
        global $post;

        if ( !\is_user_logged_in() && !\current_user_can( 'edit_post', $post->ID ) || \is_admin() || !in_the_loop() ) {
            return $title;
        }

        $output = '';
        $output .= '<span class="inline-edit-title inline-edit-title-' . $post->ID . '" data-postid="' . $post->ID . '"
            data-posttitle="' . strip_tags( $post->post_title ) . '"
            data-inlineedittooltip="' . __( 'Title from', $this->textdomain ) . ' ' . strip_tags( $post->post_title ) .
            ' (' . $post->ID . ')">';
        $output .= $title;
        $output .= '</span>';

        return $output;
    }

    /**
     * Append columns to the_content()
     *
     * @param $content
     * @return string
     */
    public function appendColumns( $content ) {
        global $post, $mcPost;
        $gridOutput = '';

        $mcPost = $post;
        $grid = get_post_meta( $post->ID, 'minicomposerColumns', true );

        if ( empty( $grid ) ) {
            return $content;
        }

        $gridOutput .= $this->createRows( $grid );
        $gridOutput = $this->wrapColumnsForInlineEdit( $gridOutput, $post->ID );

        return $gridOutput;
    }

    /**
     * Add wrapper around column-html for inline-edit
     * @param $gridOutput
     * @param $postid
     * @return string
     */
    public function wrapColumnsForInlineEdit( $gridOutput, $postid ) {
        if ( \is_user_logged_in() && \current_user_can( 'edit_post', $postid ) ) {
            $gridOutput = '<div data-postid="' . $postid . '" class="mc-wrapper">'
                . $gridOutput
                . '</div>';
        }

        return $gridOutput;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {
        global $post;

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

        if ( \is_user_logged_in() && \current_user_can( 'edit_post', $post->ID ) ) {
            wp_enqueue_style( 'dashicons' );
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


    public function addDataAttributes( $columnCount ) {
        global $post;
        if ( !\is_user_logged_in() || !\current_user_can( 'edit_post', $post->ID ) ) {
            return '';
        }
        return ' data-inlineedittooltip="Column: ' . ( $columnCount + 1 ) . "\n"
            . 'Post: ' . $post->post_name . ' (' . $post->ID . ')" ';
    }

    /**
     * Shortcode to embed column-content from other posts/pages
     *
     * @param $args
     * @param string $content
     * @return string
     */
    public function postShortcode( $args, $content = '' ) {
        global $post;
        if ( empty( $args ) ) {
            return '<!--Post-Shortcode: Args empty -->';
        }

        if ( is_numeric( $args[0] ) ) {
            // get by id
            $incPost = get_post( $args[0] );
        } else {
            // get post by slug
            $incPost = get_posts( array( 'name' => $args[0], 'post_type' => get_post_types() ) );
            $incPost = !empty( $incPost ) ? $incPost[0] : null;
        }

        if ( empty( $incPost ) ) {
            return '<!--Post-Shortcode: No post found for ' . $args[0] . ' -->';
        }
        $orgPost = $post;
        $post = $incPost;
        $orgColumnCount = $this->columnCount;
        $this->columnCount = 0;

        $grid = get_post_meta( $incPost->ID, 'minicomposerColumns', true );
        $gridOutput = $this->createRows( $grid );
        $gridOutput = $this->wrapColumnsForInlineEdit( $gridOutput, $incPost->ID );

        $post = $orgPost;
        $this->columnCount = $orgColumnCount;

        return '<!--postshortcode-->' . $gridOutput;
    }

}
