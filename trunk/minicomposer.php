<?php

use Inc\Minicomposer;
use Inc\MinicomposerActivator;
use Inc\MinicomposerDeactivator;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              MiniComposer
 * @since             1.0.0
 * @package           Minicomposer
 *
 * @wordpress-plugin
 * Plugin Name:       MiniComposer
 * Plugin URI:        MiniComposer
 * Description:       Layout page builder plugin.
 * Version:           1.0.6
 * Author:            crazypsycho
 * Author URI:        https://github.com/crazypsycho
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       minicomposer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The class responsible for auto loading classes.
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/MinicomposerActivator.php
 */
function activateMinicomposer() {
	MinicomposerActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/MinicomposerDeactivator.php
 */
function deactivateMinicomposer() {
	MinicomposerDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activateMinicomposer' );
register_deactivation_hook( __FILE__, 'deactivateMinicomposer' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function runMinicomposer() {

	$plugin = new Minicomposer();
	$plugin->run();

}
runMinicomposer();
