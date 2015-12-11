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
	 * @var      string    $pluginName    The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $pluginName       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $pluginName, $version ) {

		$this->pluginName = $pluginName;
		$this->version = $version;

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

		wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/minicomposer-public.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/minicomposer-public.js', array( 'jquery' ), $this->version, false );

	}

}
