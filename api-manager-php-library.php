<?php

/**
 * Plugin Name: WooCommerce API Manager PHP Library for Plugins and Themes
 * Plugin URI: https://www.toddlahman.com/shop/woocommerce-api-manager-php-library-for-plugins-and-themes/
 * Description: Drop the wc-am-client.php library into a plugin or theme, and use the example code below after line 26.
 * Version: 2.7
 * Author: Todd Lahman LLC
 * Author URI: https://www.toddlahman.com/
 * License: Copyright Todd Lahman LLC
 *
 *    Intellectual Property rights, and copyright, reserved by Todd Lahman, LLC as allowed by law include,
 *    but are not limited to, the working concept, function, and behavior of this library,
 *    the logical code structure and expression as written.
 *
 * @package     WooCommerce API Manager Library for plugin or theme
 * @author      Todd Lahman LLC
 * @category    Plugin/Theme/library
 * @copyright   Copyright (c) Todd Lahman LLC
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IMPORTANT
 *
 * Use only the code below at the top of a plugin file below the plugin header, or at the top of a theme functions file.
 */

// Load WC_AM_Client class if it exists.
if ( ! class_exists( 'WC_AM_Client_2_7' ) ) {
	// Uncomment next line if this is a plugin
	require_once( plugin_dir_path( __FILE__ ) . 'wc-am-client.php' );

	// Uncomment next line if this is a theme
	// require_once( get_stylesheet_directory() . '/wc-am-client.php' );
}

// Instantiate WC_AM_Client class object if the WC_AM_Client class is loaded.
if ( class_exists( 'WC_AM_Client_2_7' ) ) {
	/**
	 * This file is only an example that includes a plugin header, and this code used to instantiate the client object. The variable $wcam_lib
	 * can be used to access the public properties from the WC_AM_Client class, but $wcam_lib must have a unique name. To find data saved by
	 * the WC_AM_Client in the options table, search for wc_am_client_{product_id}, so in this example it would be wc_am_client_132967.
	 *
	 * All data here is sent to the WooCommerce API Manager API, except for the $software_title, which is used as a title, and menu label, for
	 * the API Key activation form the client will see.
	 *
	 * ****
	 * NOTE
	 * ****
	 * If $product_id is empty, the customer can manually enter the product_id into a form field on the activation screen.
	 *
	 * @param string $file             Must be __FILE__ from the root plugin file, or theme functions, file locations.
	 * @param int    $product_id       Must match the Product ID number (integer) in the product.
	 * @param string $software_version This product's current software version.
	 * @param string $plugin_or_theme  'plugin' or 'theme'
	 * @param string $api_url          The URL to the site that is running the API Manager. Example: https://www.toddlahman.com/
	 * @param string $software_title   The name, or title, of the product. The title is not sent to the API Manager APIs, but is used for menu titles.
	 *
	 * Example:
	 *
	 * $wcam_lib = new WC_AM_Client_2_7( $file, $product_id, $software_version, $plugin_or_theme, $api_url, $software_title );
	 */

	// Example of empty string product_id.
	//$wcam_lib = new WC_AM_Client_2_7( __FILE__, '', '1.0', 'plugin', 'http://wc/', 'Simple Comments - Simple' );

	// Preferred positive integer product_id.
	$wcam_lib = new WC_AM_Client_2_7( __FILE__, 139932, '2.7', 'plugin', 'https://www.toddlahman.com/', 'WooCommerce API Manager PHP Library for Plugins and Themes' );
}