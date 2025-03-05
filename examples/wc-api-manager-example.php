<?php

/**
 * Plugin Name: WooCommerce API Manager PHP Library for Plugins and Themes Example
 * Plugin URI: https://kestrelwp.com/product/woocommerce-api-manager-php-library-for-plugins-and-themes/
 * Description: Drop the wc-am-client.php library into a plugin or theme, and use the example code below after line 26.
 * Version: 2.10.0
 * Author: Kestrel
 * Author URI: https://kestrelwp.com
 *
 * Copyright: (c) 2023-2025 Kestrel [hey@kestrelwp.com]
 *
 * @package     WooCommerce API Manager Library for plugin or theme
 * @author      Kestrel
 * @category    Plugin/Theme/library
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * @IMPORTANT
 *
 * Use the code below at the top of a plugin file below the plugin header, or at the top of a theme functions file.
 */

// Load the WC_AM_Client client class if it exists.
if ( ! class_exists( 'WC_AM_Client_2_10_0' ) ) {
	/**
	 * @IMPORTANT This must be exactly the same for both plugins and themes.
	 *
	 * Adjust this depending on the location of the wc-am-client.php file. Ensure the file name is correct.
	 */
	require_once plugin_dir_path(__FILE__ ) . 'wc-am-client.php';
}

// Instantiate the WC_AM_Client class object if the WC_AM_Client class is loaded.
if ( class_exists( 'WC_AM_Client_2_10_0' ) ) {
	/**
	 * This file is only an example that includes a plugin header, and this code is used to instantiate the client object.
	 *
	 * You can assign the object to a variable, such as `$wcam_lib`, or not, depending on your needs.
	 * Such variable `$wcam_lib` can be used to access the public properties from the WC_AM_Client class, but `$wcam_lib` must have a unique name.
	 * To find data saved by the WC_AM_Client in the options table, search for `wc_am_client_{product_id}`, so in this example it would be `wc_am_client_132967`.
	 *
	 * All data passed to the constructor is sent to the WooCommerce API Manager API, except for the $software_title, which is used as a title, and menu label, for the API Key activation form the client will see.
	 *
	 * @IMPORTANT The second argument passed to the client constructor should be the Product ID number. If left empty the customer will need to enter it in the activation form.
	 * This is important to prevent ambiguity that could result in activation mismatch errors.
	 *
	 * @param string   $file             Must be __FILE__ from the root plugin file, or theme functions, file locations.
	 * @param int|null $product_id       Should match the Product ID number (integer) in the product.
	 * @param int|null $parent_id        The parent ID of the product. This is used to check if the product is a variable product.
	 * @param string   $software_version This product's current software version.
	 * @param string   $plugin_or_theme  One of 'plugin' or 'theme'.
	 * @param string   $api_url          The URL to the site that is running the API Manager. Example: https://kestrelwp.com/. Must be the root URL.
	 * @param string   $software_title   The name, or title, of the product. The title is not sent to the API Manager APIs, but is used for menu titles.
	 * @param string   $text             Text Domain used by your plugin.
	 * @param array    $custom_menu      Custom menu settings (optional, can be omitted).
	 * @param bool     $inactive_notice  Display the not activated yet admin notice (optional, can be omitted -- default true).
	 */

	/**
	 * Theme example
	 *
	 * The following is an example assuming your product is a WordPress theme.
	 *
	 * @NOTE Replace the dummy values with your own.
	 */
	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, 123, null, '1.0', 'theme', 'https://example.org/example-theme', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain' );

	/**
	 * Plugin example (default menu).
	 *
	 * These examples will generate a default menu item in the WordPress admin menu to enable your customers to enter their license key (and, when not set in the constructor, the product ID).
	 *
	 * @NOTE Replace the dummy values with your own.
	 */

	// If the Product ID is not set the customer will see a form field when activating the API Key that requires the Product ID along with a form field for the API Key.
	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, '', null, '1.2.3', 'plugin', 'https://example.org/example-plugin', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain' );

	// Setting the Product ID below will eliminate the required form field for the customer to enter the Product ID, so the customer will only be required to enter the API Key.
	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, 456, null, '1.2.3', 'plugin', 'https://example.org/example-plugin', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain' );

	// If you offer the product as a variable product where the customer can switch to another product with a different Product ID, then do not set the Product ID here, but you may pass a product parent ID instead (not recommended).
	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, null, 789, '1.2.3', 'plugin', 'https://example.org/example-plugin', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain' );

	/**
	 * Custom top level or top level submenu.
	 *
	 * If you don't want to rely on the built-in menu to handle the product's activation, then you can supply your own menu.
	 *
	 * Custom WordPress menus allowed:
	 *
	 * @see add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null );
	 * @see add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null );
	 * @see add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null );
	 *
	 * Then pass the custom menu settings as an array to the WC_AM_Client_2_10_0 class as shown in the example below.
	 */

	$wcam_lib_custom_menu = array(
		'menu_type'   => 'add_submenu_page', // note this could be any of the allowed menu types as shown above
		'parent_slug' => 'my-plugin.php',
		'page_title'  => __( 'Example Plugin License Activation', 'example-textdomain' ),
		'menu_title'  => __( 'Example API Key', 'example-textdomain' )
	);

	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, 123, null, '1.2.3', 'plugin', 'https://example.org/example-plugin', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain', $wcam_lib_custom_menu );

	/**
	 * Suppress the inactive notice.
	 *
	 * When your plugin or theme is activated, the WC_AM_Client_2_10_0 class will display a notice in the admin area if the plugin or theme has not been activated.
	 * You can disable this by setting the last argument passed to the client constructor to false.
	 */
	$wcam_lib = new WC_AM_Client_2_10_0( __FILE__, 123, null, '1.2.3', 'plugin', 'https://example.org/example-plugin', __( 'Example WordPress Plugin', 'example-textdomain' ), 'example-textdomain', null, false );
}
