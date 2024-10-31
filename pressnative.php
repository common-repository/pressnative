<?php

/**
 * @link              https://pressnative.com
 * @since             1.0.8
 * @package           Pressnative
 *
 * @wordpress-plugin
 * Plugin Name:       Pressnative
 * Plugin URI:        https://wordpress.org/plugins/pressnative
 * Description:       Launch a world class app from your WordPress website in minutes.
 * Version:           1.0.8
 * Author:            Pressnative
 * Author URI:        https://pressnative.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pressnative
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRESSNATIVE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pressnative-activator.php
 */
function activate_pressnative() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pressnative-activator.php';
	Pressnative_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pressnative-deactivator.php
 */
function deactivate_pressnative() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pressnative-deactivator.php';
	Pressnative_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pressnative' );
register_deactivation_hook( __FILE__, 'deactivate_pressnative' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pressnative.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_pressnative() {

	$plugin = new Pressnative();
	$plugin->run();
}
run_pressnative();
