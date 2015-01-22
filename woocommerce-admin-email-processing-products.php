<?php
/**
 * @package woocommerce-admin-email-processing-products
 * @version 1.1.2
 * @author Myum
 */
/*
Plugin Name: woocommerce admin email processing products
Plugin URI: https://github.com/Myum/woocommerce-admin-email-processing-products
Description: Sends an email with the pending orders and the amount of each item.
Author: Marc Muixi
Version: 1.1.2
Author URI: http://myum.cat
Text Domain: waepp
Domain Path: /languages
*/

/*  Copyright 2014  Marc Muixi  (email : support@myum.cat)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ). 'includes/class-waepp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin() {
	$plugin = new Waepp();
	$plugin->run();
}
run_plugin();
?>
