WooCommerce API Manager Compatibility
==========================

WooCommerce API Manager version >= 2.1 is recommended.

Requirements
============

 WooCommerce API Manager 2.x.

WC API Manager PHP Library version 2.7
==========================

This library can be dropped into a WordPress plugin or theme to activate/deactivate API Keys, to check for Software updates, and check an API Key's status with the **WooCommerce API Manager**.

The ```wc-am-client.php``` file should be placed in the plugin's root folder, or the same level as the ```functions.php``` file if it is a theme. The code below goes in the root plugin file, or a theme's functions.php file, and should always be the first code in the file.

The Class Name ```WC_AM_Client``` in the code example below, and in the wc-am-client.php on lines 22 and 23, can be changed to something unique to your project, if there is a Class Name conflict, however this should not happen if the ```wc-am-client.php``` file is used without alteration, and the example code below is used to load the ```wc-am-client.php``` file.

In the code example below, uncomment the require_once() line for either a plugin or a theme.

The variable ```$wcam_lib``` should have a unique name since it is a reference to a unique object that can be used to access public methods and properties from the ```WC_AM_Client``` class. The class name ```WC_AM_Client``` will have a number at the end of the name to specify the library version.

If $product_id value is an empty string, the customer can manually enter the product_id into a form field on the activation screen as shown in this screenshot https://user-images.githubusercontent.com/3452849/54350479-aac27a00-460a-11e9-926d-2cde952110a4.png.

**IMPORTANT**: The code block below should be added just below your plugin header, or the top of your functions file in a theme. The code block below should always be loaded first, so it will always work, and won't break in case something else in your plugin or theme breaks.

Example Code to add to Plugins and Themes
==========================

```php
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
	 * the WC_AM_Client in the options table, search for wc_am_client_{product_id}, so in this example it would be wc_am_client_13.
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
	 * $wcam_lib = new WC_AM_Client( $file, $product_id, $software_version, $plugin_or_theme, $api_url, $software_title );
	 */
	 
	 // Example of empty string product_id.
	//$wcam_lib = new WC_AM_Client_2_7( __FILE__, '', '1.0', 'plugin', 'http://wc/', 'Simple Comments - Simple' );
	
	// Preferred positive integer product_id.
	$wcam_lib = new WC_AM_Client_2_7( __FILE__, 132967, '1.0', 'plugin', 'http://wc/', 'Simple Comments - Simple' );
}
```

How to Activate and Update in Multisite Environment
==========================

The first Sub-domain or Sub-directory in the network, aka site1, must have the plugin or theme activated with the API Key for software updates to work. There is only one copy of the plugin or theme, and that copy is always updated from site 1. If the client purchase a single API Key activation, but uses it on a WordPress Multisite installation, the client will need two API Key activations, one for the default site 1, and another for the site number the software will be used on. The plugin or theme cannot be network activated, but must have the API Key activated per site.

Preventing Unauthorized Plugin or Theme Use Before API Key Activation
==========================

To prevent a customer from using your software until after activating it with the API Key you can surround the code that loads your software with the following if condition:

```
/**
 * Above this line should be the code that loads the WC API Manager PHP Library, which must be placed in the root plugin file.
 * Do not set to true if using to activate software. True is for real-time status checks after activation.
 * When argument is set to false, the result comes from saved value in options table.
 * When argument is set to true, the result comes from the API Manager on the live store.
 * Default is false.
 */
if ( is_object( $wcam_lib ) && $wcam_lib->get_api_key_status( false ) ) {
    // Code to load your plugin or theme here.
    // This code will not run until the API Key is activated.
}
```

Client Errors
============

Have the client follow these steps if they are unable to activate due an error such as "Cannot activate API Key. The API Key has already been activated with the same unique instance ID sent with this request."

Solution:

* Go to the My Account dashboard, and delete the activation for the object, or the client will end up with more than one activation for the same object. The activation error was returned because the client used an instance ID that was incorrect, which is unique, and was for another activation.
* The store manager can also delete an activation on the order screen for the client.
* Go to Plugins and deactivate then activate the plugin. If is a theme, change the theme then change it back. (This erases the local data sent to the API Manager, and resets the unique Instance ID.
* Go to the plugin/theme settings page, and enter the API Key to reactivate the plugin/theme.

If the client reports an error such as "Connection failed to the License Key API server. Try again later." the problem is most likely one of these possibilities:

* There may be a problem with the client server preventing outgoing requests.
* The store the client is connecting to is blocking the client's request to activate the plugin/theme.
* Your site may be under attack, such as a DDOS (distributed denial of service).

Possible Solutions:

* Have the client disable CloudFlare, or other CDNs, all firewall and caching software.
* The API Manager store should also disable CloudFlare, or other CDNs, all firewall and caching software. Any of these can mangle HTTP/S requests. Also make sure the client software is not trying to connect using HTTP, and being forwarded to HTTPS, as this can cause breakage in some cases. The rule of thumb is to start working with the API Manager without a firewall or CDN, then add these services if desired, but always test to make sure everything is still working.
* In the case of a DDOS, you may need to carefully add CloudFlare, or some other firewall service. This could make things worse if you're not sure how to implement a firewall, so get help if some things are not working as expected.

Screenshots
============

The library uses the **Software Title** to create a unique settings menu item, and a page that can be used to activate and deactivate the product using the **API Key**. Below is a series of screenshots to demostrate the customer experience. Note: The API Email is not long used for authentication, which makes the screenshots out-of-date but still a close example to the current experience.

https://www.toddlahman.com/shop/woocommerce-api-manager-php-library-for-plugins-and-themes/

![](https://cloud.githubusercontent.com/assets/3452849/15640356/183bd64a-25ec-11e6-9989-9311a87a78ad.png)

![](https://cloud.githubusercontent.com/assets/3452849/15640360/183e6d4c-25ec-11e6-92b2-f0368261bb90.png)

![plugin-library-before-activation](https://user-images.githubusercontent.com/3452849/54350479-aac27a00-460a-11e9-926d-2cde952110a4.png)

![plugin-library-after-activation](https://user-images.githubusercontent.com/3452849/54350500-b615a580-460a-11e9-9e4b-dd68db02f14a.png)

![](https://cloud.githubusercontent.com/assets/3452849/15640358/183df222-25ec-11e6-8c04-f5a80a4e62e7.png)

![](https://cloud.githubusercontent.com/assets/3452849/15640359/183e0eec-25ec-11e6-9770-757defde3c8e.png)

![](https://cloud.githubusercontent.com/assets/3452849/15640411/872681e0-25ec-11e6-97fa-13d01070924a.png)