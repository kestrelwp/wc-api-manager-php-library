<?php
/**
 * WooCommerce API Manager PHP Client Library
 *
 * Designed to be used with WooCommerce API Manager 2.x, and dropped into a WordPress plugin or theme.
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this plugin in the file license.txt
 *
 * Please do not modify this file if you want to upgrade the SDK to newer
 * versions in the future. If you want to customize the SDK for your needs,
 * please review our developer documentation at https://kestrelwp.com/docs/woocommerce-api-manager-php-library-for-plugins-and-themes-documentation/
 * and join our developer program at https://kestrelwp.com/developers
 *
 * @version     2.10.0
 * @author      Kestrel
 * @copyright   Copyright (c) 2013-2025 Kestrel
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package     WooCommerce API Manager
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_AM_Client_2_10_0' ) ) {
	/**
	 * WooCommerce API Manager client class.
	 */
	class WC_AM_Client_2_10_0 {

		/** @var string API URL. */
		private $api_url = '';

		/** @var string Data key. */
		private $data_key = '';

		/** @var string File path. */
		private $file = '';

		/** @var string Plugin name. */
		private $plugin_name = '';

		/** @var string<"plugin"|"theme"> Product type. Either "plugin" or "theme". */
		private $plugin_or_theme = '';

		/** @var int|null */
		private $product_id = null;

		/** @var int|null */
		private $product_parent_id = null;

		/** @var string */
		private $slug = '';

		/** @var string */
		private $software_title = '';

		/** @var string */
		private $software_version = '';

		/** @var string */
		private $text_domain = '';

		/** @var array */
		private $data = array();

		/** @var string */
		private $identifier = '';

		/** @var bool */
		private $no_product_id = false;

		/** @var int */
		private $product_id_chosen = 0;

		/** @var string */
		private $wc_am_activated_key = '';

		/** @var string */
		private $wc_am_activation_tab_key = '';

		/** @var string */
		private $wc_am_api_key_key = '';

		/** @var string */
		private $wc_am_deactivate_checkbox_key = '';

		/** @var string */
		private $wc_am_deactivation_tab_key = '';

		/** @var string */
		private $wc_am_auto_update_key = '';

		/** @var string */
		private $wc_am_domain = '';

		/** @var string */
		private $wc_am_instance_id = '';

		/** @var string */
		private $wc_am_instance_key = '';

		/** @var string */
		private $wc_am_menu_tab_activation_title = '';

		/** @var string */
		private $wc_am_menu_tab_deactivation_title = '';

		/** @var string */
		private $wc_am_plugin_name = '';

		/** @var string */
		private $wc_am_product_id = '';

		/** @var string */
		private $wc_am_renew_license_url = '';

		/** @var string */
		private $wc_am_settings_menu_title = '';

		/** @var string */
		private $wc_am_settings_title = '';

		/** @var string */
		private $wc_am_software_version = '';

		/** @var array */
		private $menu = array();

		/** @var bool */
		private $inactive_notice = true;

		/**
		 * Cache for the status result to prevent multiple API calls.
		 *
		 * @var string|null
		 */
		private $status_result = null;

		/**
		 * Client constructor.
		 *
		 * @param string                   $file The main plugin or theme __FILE__ path.
		 * @param int|null                 $product_id The product ID. If null, it should be provided by the customer in the API settings.
		 * @param int|null                 $product_parent_id Optional if the product is a variable product and the product ID is neither specified nor entered by the customer (not advisable).
		 * @param string                   $software_version This should be a semantic version number.
		 * @param string<"plugin"|"theme"> $plugin_or_theme The type of product licensed.
		 * @param string                   $api_url The URL to the site that is running the API Manager - must be the root URL.
		 * @param string                   $software_title The name of the product licensed.
		 * @param string                   $text_domain The textdomain of the product licensed.
		 * @param array<string, mixed>     $custom_menu Optional, default menu will be used when empty or not set.
		 * @param bool                     $inactive_notice Optional, default true - use false to hide the inactive license notice.
		 */
		public function __construct( $file, $product_id, $product_parent_id, $software_version, $plugin_or_theme, $api_url, $software_title = '', $text_domain = '', $custom_menu = array(), $inactive_notice = true ) {

			$this->menu            = $this->clean( $custom_menu );
			$this->inactive_notice = $inactive_notice;

			$this->product_parent_id = is_numeric( $product_parent_id ) ? absint( $product_parent_id ) : null;
			$this->no_product_id     = empty( $product_id );
			$this->plugin_or_theme   = esc_attr( strtolower( $plugin_or_theme ) );

			if ( $this->no_product_id ) {
				$this->identifier        = $this->plugin_or_theme === 'plugin' ? dirname( untrailingslashit( plugin_basename( $file ) ) ) : basename( dirname( plugin_basename( $file ) ) );
				$product_id              = strtolower( str_ireplace( array( ' ', '_', '&', '?', '-' ), '_', $this->identifier ) );
				$this->wc_am_product_id  = 'wc_am_product_id_' . $product_id;
				$this->product_id_chosen = get_option( $this->wc_am_product_id );
			} elseif ( ! empty( $product_id ) && is_numeric( $product_id ) ) {
					$this->product_id = absint( $product_id );
			} elseif ( ! empty( $product_id ) && is_string( $product_id ) ) {
				/**
				 * Preserve the value of $product_id to use for API requests. Pre 2.0 product_id is a string, and >= 2.0 is an integer.
				 */
				$this->product_id = esc_attr( $product_id );
			}

			// If the product_id was not provided, but was saved by the customer, used the saved product_id from the customer.
			if ( empty( $this->product_id ) && ! empty( $this->product_id_chosen ) ) {
				$this->product_id = $this->product_id_chosen;
			}

			$this->file             = $file;
			$this->software_title   = esc_attr( $software_title );
			$this->software_version = esc_attr( $software_version );
			$this->api_url          = esc_url( $api_url );
			$this->text_domain      = esc_attr( $text_domain );

			// If the product_id is a pre 2.0 string, format it to be used as an option key, otherwise it will be an integer if >= 2.0.
			$this->data_key            = 'wc_am_client_' . strtolower( str_ireplace( array( ' ', '_', '&', '?', '-' ), '_', $product_id ) );
			$this->wc_am_activated_key = $this->data_key . '_activated';

			if ( is_admin() ) {
				if ( ! empty( $this->plugin_or_theme ) && $this->plugin_or_theme === 'theme' ) {
					add_action( 'admin_init', array( $this, 'activation' ) );
				}

				if ( ! empty( $this->plugin_or_theme ) && $this->plugin_or_theme === 'plugin' ) {
					if ( did_action( 'plugins_loaded' ) || doing_action( 'plugins_loaded' ) || 'plugins_loaded' === current_action() ) {
						add_action( 'admin_init', array( $this, 'activation' ) );
					} else {
						register_activation_hook( $this->file, array( $this, 'activation' ) );
					}
				}

				add_action( 'admin_menu', array( $this, 'register_menu' ) );
				add_action( 'admin_init', array( $this, 'load_settings' ) );
				// Check for external connection blocking.
				add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

				/**
				 * Set all data defaults here.
				 */
				$this->wc_am_api_key_key  = $this->data_key . '_api_key';
				$this->wc_am_instance_key = $this->data_key . '_instance';

				/**
				 * Set all admin menu data.
				 */
				$this->wc_am_deactivate_checkbox_key     = $this->data_key . '_deactivate_checkbox';
				$this->wc_am_activation_tab_key          = $this->data_key . '_dashboard';
				$this->wc_am_deactivation_tab_key        = $this->data_key . '_deactivation';
				$this->wc_am_auto_update_key             = $this->data_key . '_auto_update';
				$this->wc_am_settings_title              = sprintf( __( '%s', $this->text_domain ), ! empty( $this->menu['page_title'] ) ? $this->menu['page_title'] : $this->software_title . ' API Key Activation', $this->text_domain ); // phpcs:ignore
				$this->wc_am_settings_menu_title         = sprintf( __( '%s', $this->text_domain ), ! empty( $this->menu['menu_title'] ) ? $this->menu['menu_title'] : $this->software_title . ' Activation', $this->text_domain ); // phpcs:ignore
				$this->wc_am_menu_tab_activation_title   = esc_html__( 'API Key Activation', $this->text_domain ); // phpcs:ignore
				$this->wc_am_menu_tab_deactivation_title = esc_html__( 'API Key Deactivation', $this->text_domain ); // phpcs:ignore

				/**
				 * Set all software update data here.
				 */
				$this->data                    = get_option( $this->data_key );
				$this->wc_am_plugin_name       = $this->plugin_or_theme === 'plugin' ? untrailingslashit( plugin_basename( $this->file ) ) : basename( dirname( plugin_basename( $file ) ) ); // Same as plugin slug. If a theme, use a theme name like 'twentytwentyfive'.
				$this->wc_am_renew_license_url = $this->api_url . 'my-account'; // URL to renew an API Key. Trailing slash in the upgrade_url is required.
				$this->wc_am_instance_id       = get_option( $this->wc_am_instance_key ); // Instance ID (unique to each blog activation).
				/**
				 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://, so only the host portion of the URL can be sent.
				 * For example the host portion might be www.example.com or example.com. http://www.example.com includes the scheme http, and the host www.example.com.
				 * Sending only the host also eliminates issues when a client site changes from http to https, but their activation still uses the original scheme.
				 * To send only the host, use a line like the one below:
				 *
				 * $this->wc_am_domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
				 */
				$this->wc_am_domain           = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // Blog domain name.
				$this->wc_am_software_version = $this->software_version; // The software version.

				/**
				 * Check for software updates
				 */
				$this->check_for_update();

				if ( $this->inactive_notice ) {
					if ( ! empty( $this->wc_am_activated_key ) && get_option( $this->wc_am_activated_key ) !== 'Activated' ) {
						add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
					}
				}

				/**
				 * Makes auto updates available if WordPress >= 5.5.
				 */
				$this->try_automatic_updates();

				if ( $this->plugin_or_theme === 'plugin' ) {
					add_filter( 'plugin_auto_update_setting_html', array( $this, 'auto_update_message' ), 10, 3 );
				}
			}

			/**
			 * Deletes all data if plugin deactivated.
			 *
			 * Uncomment if applicable.
			 */
			if ( $this->plugin_or_theme == 'plugin' ) { // phpcs:ignore
				// phpcs:ignore
				// register_deactivation_hook( $this->file, array( $this, 'uninstall' ) );
			} elseif ( $this->plugin_or_theme === 'theme' ) { // phpcs:ignore
				// phpcs:ignore
				// add_action( 'switch_theme', array( $this, 'uninstall' ) );
			}
		}

		/**
		 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
		 *
		 * Non-scalar values are ignored.
		 *
		 * @since 2.9
		 *
		 * @param string|array $item Data to sanitize.
		 * @return string|array
		 */
		private function clean( $item ) {
			if ( is_array( $item ) ) {
				return array_map( array( $this, 'clean' ), $item );
			} else {
				return is_scalar( $item ) ? sanitize_text_field( $item ) : $item;
			}
		}

		/**
		 * Register a menu or submenu specific to this product.
		 *
		 * @updated 2.9
		 */
		public function register_menu() {

			$page_title = $this->wc_am_settings_title;
			$menu_title = $this->wc_am_settings_menu_title;
			$capability = ! empty( $this->menu['capability'] ) ? $this->menu['capability'] : 'manage_options';
			$menu_slug  = ! empty( $this->menu['menu_slug'] ) ? $this->menu['menu_slug'] : $this->wc_am_activation_tab_key;
			$callback   = ! empty( $this->menu['callback'] ) ? $this->menu['callback'] : array( $this, 'config_page' );
			$icon_url   = ! empty( $this->menu['icon_url'] ) ? $this->menu['icon_url'] : '';
			$position   = ! empty( $this->menu['position'] ) ? $this->menu['position'] : null;

			if ( is_array( $this->menu ) && ! empty( $this->menu['menu_type'] ) ) {
				if ( $this->menu['menu_type'] === 'add_submenu_page' ) {
					add_submenu_page( $this->menu['parent_slug'], $page_title, $menu_title, $capability, $menu_slug, $callback, $position );
				} elseif ( $this->menu['menu_type'] === 'add_options_page' ) {
					add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $position );
				} elseif ( $this->menu['menu_type'] === 'add_menu_page' ) {
					add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, $position );
				}
			} else {
				add_options_page( sprintf( __( '%s', $this->text_domain ), $this->wc_am_settings_menu_title ), sprintf( __( '%s', $this->text_domain ), $this->wc_am_settings_menu_title ), 'manage_options', $this->wc_am_activation_tab_key, array( $this, 'config_page' ) ); // phpcs:ignore
			}
		}

		/**
		 *  Tries auto updates.
		 *
		 * @since 2.8
		 */
		public function try_automatic_updates() {
			global $wp_version;

			if ( version_compare( $wp_version, '5.5', '>=' ) ) {
				if ( $this->plugin_or_theme === 'plugin' ) {
					add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 10, 2 );
				} elseif ( $this->plugin_or_theme === 'theme' ) {
					add_filter( 'auto_update_theme', array( $this, 'maybe_auto_update' ), 10, 2 );
				}
			}
		}

		/**
		 * Tries to set auto updates.
		 *
		 * @since 2.8
		 *
		 * @param bool|null $update Whether to update.
		 * @param object    $item   The item to update.
		 * @return bool
		 */
		public function maybe_auto_update( $update, $item ) {

			if ( strpos( $this->wc_am_plugin_name, '.php' ) !== 0 ) {
				$slug = dirname( $this->wc_am_plugin_name );
			} else {
				$slug = $this->wc_am_plugin_name;
			}

			if ( isset( $item->slug ) && $item->slug === $slug ) {
				if ( $this->is_auto_update_disabled() ) {
					return false;
				}

				if ( ! $this->get_api_key_status() || ! $this->get_api_key_status( true ) ) {
					return false;
				}

				return true;
			}

			return $update;
		}

		/**
		 * Checks if auto updates are disabled.
		 *
		 * @since 2.8
		 *
		 * @return bool
		 */
		public function is_auto_update_disabled() {
			/*
			 * WordPress will not offer to update if background updates are disabled.
			 * WordPress background updates are disabled if file changes are not allowed.
			 */
			if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
				return true;
			}

			if ( defined( 'WP_INSTALLING' ) ) {
				return true;
			}

			$wp_updates_disabled = defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED;

			/**
			 * Overrides the WordPress AUTOMATIC_UPDATER_DISABLED constant.
			 *
			 * @param bool $wp_updates_disabled true if disables.  false otherwise.
			 */
			$wp_updates_disabled = apply_filters( 'automatic_updater_disabled', $wp_updates_disabled );

			if ( $wp_updates_disabled ) {
				return true;
			}

			// Return true if this plugin or theme background update is disabled.
			// phpcs:ignore
			// return get_option( $this->wc_am_auto_update_key ) !== 'on';

			return false;
		}

		/**
		 * Filter the auto-update message on the plugins page.
		 *
		 * Plugin updates stored in 'auto_update_plugins' array.
		 *
		 * @see   'wp-admin/includes/class-wp-plugins-list-table.php'
		 *
		 * @since 2.8
		 *
		 * @param string $html        HTML of the auto-update message.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin details.
		 *
		 * @return mixed|string
		 */
		public function auto_update_message( $html, $plugin_file, $plugin_data ) {
			global $status, $page;

			if ( $this->wc_am_plugin_name === $plugin_file ) {

				if ( ! $this->get_api_key_status() || ! $this->get_api_key_status( true ) ) {
					return esc_html__( 'Auto-updates unavailable.', $this->text_domain ); // phpcs:ignore
				}

				$auto_updates = (array) get_site_option( 'auto_update_plugins', array() );
				$html         = array();

				if ( ! empty( $plugin_data['auto-update-forced'] ) ) {
					if ( $plugin_data['auto-update-forced'] ) {
						// Forced on.
						$text = __( 'Auto-updates enabled', $this->text_domain ); // phpcs:ignore
					} else {
						$text = __( 'Auto-updates disabled', $this->text_domain ); // phpcs:ignore
					}
					$action     = 'unavailable';
					$time_class = ' hidden';
				} elseif ( in_array( $plugin_file, $auto_updates, true ) ) {
					$text       = __( 'Disable auto-updates' );
					$action     = 'disable';
					$time_class = '';
				} else {
					$text       = __( 'Enable auto-updates' );
					$action     = 'enable';
					$time_class = ' hidden';
				}

				$query_args = array(
					'action'        => "{$action}-auto-update",
					'plugin'        => $plugin_file,
					'paged'         => $page,
					'plugin_status' => $status,
				);

				$url = add_query_arg( $query_args, 'plugins.php' );

				if ( 'unavailable' === $action ) {
					$html[] = '<span class="label">' . $text . '</span>';
				} else {
					$html[] = sprintf( '<a href="%s" class="toggle-auto-update aria-button-if-js" data-wp-action="%s">', wp_nonce_url( $url, 'updates' ), $action );

					$html[] = '<span class="dashicons dashicons-update spin hidden" aria-hidden="true"></span>';
					$html[] = '<span class="label">' . $text . '</span>';
					$html[] = '</a>';
				}

				if ( ! empty( $plugin_data['update'] ) ) {
					$html[] = sprintf( '<div class="auto-update-time%s">%s</div>', $time_class, wp_get_auto_update_message() );
				}

				$html = implode( '', $html );
			}

			return $html;
		}

		/**
		 * Generate the default data.
		 */
		public function activation() {
			$instance_exists = get_option( $this->wc_am_instance_key );

			if ( get_option( $this->data_key ) === false || $instance_exists === false ) {
				if ( $instance_exists === false ) {
					$this->wc_am_instance_id = wp_generate_password( 12, false );
					update_option( $this->wc_am_instance_key, $this->wc_am_instance_id );
				}

				update_option( $this->wc_am_deactivate_checkbox_key, 'on' );
				update_option( $this->wc_am_activated_key, 'Deactivated' );
			}
		}

		/**
		 * Deletes all data if plugin deactivated.
		 *
		 * @return void
		 */
		public function uninstall() {

			/**
			 * Filter wc_am_client_uninstall_disable.
			 *
			 * @since 2.5.1
			 *
			 * If set to false uninstall() method will be disabled.
			 *
			 * @param bool $uninstall
			 */
			if ( apply_filters( 'wc_am_client_uninstall_disable', true ) ) {

				$this->license_key_deactivation();

				$options = array(
					$this->wc_am_instance_key,
					$this->wc_am_deactivate_checkbox_key,
					$this->wc_am_activated_key,
				);

				// Remove options pre API Manager 2.0.
				if ( is_multisite() ) {
					global $blog_id;

					switch_to_blog( $blog_id );

					foreach ( $options as $option ) {
						delete_option( $option );
					}

					restore_current_blog();
				} else {
					foreach ( $options as $option ) {
						delete_option( $option );
					}
				}
			}
		}

		/**
		 * Deactivates the license on the API server.
		 */
		public function license_key_deactivation() {
			$activation_status = get_option( $this->wc_am_activated_key );
			$api_key           = ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '';

			$args = array(
				'api_key' => $api_key,
			);

			if ( ! empty( $api_key ) && $activation_status === 'Activated' ) {
				if ( empty( $this->deactivate( $args ) ) ) {
					add_settings_error( 'not_deactivated_text', 'not_deactivated_error', esc_html__( 'The API Key could not be deactivated. Use the API Key Deactivation tab to manually deactivate the API Key before activating a new API Key. If all else fails, go to Plugins, then deactivate and reactivate this plugin, or if a theme change themes, then change back to this theme, then go to the Settings for this plugin/theme and enter the API Key information again to activate it. Also check the My Account dashboard to see if the API Key for this site was still active before the error message was displayed.', $this->text_domain ), 'updated' ); // phpcs:ignore
				}
			}
		}

		/**
		 * Displays an inactive notice when the software is inactive.
		 */
		public function inactive_notice() {
			/**
			 * Filters the inactive notice.
			 *
			 * @since 2.5.1
			 *
			 * Filter wc_am_client_inactive_notice_override
			 * If set to false inactive_notice() method will be disabled.
			 *
			 * @param bool $inactive_notice
			 */
			if ( apply_filters( 'wc_am_client_inactive_notice_override', true ) ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				// phpcs:ignore
				if ( isset( $_GET['page'] ) && $this->wc_am_activation_tab_key === $_GET['page'] ) {
					return;
				}
				?>
				<div class="notice notice-error">
					<p><?php printf( __( 'The <strong>%1$s</strong> API Key has not been activated, so the %2$s is inactive! %3$sClick here%4$s to activate <strong>%5$s</strong>.', $this->text_domain ), esc_attr( $this->software_title ), esc_attr( $this->plugin_or_theme ), '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->wc_am_activation_tab_key ) ) . '">', '</a>', esc_attr( $this->software_title ) ); // phpcs:ignore ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Check for external blocking contstant.
		 */
		public function check_external_blocking() {

			// Show a  notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant.
			if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {

				// Check if our API endpoint is in the allowed hosts.
				$host = wp_parse_url( $this->api_url, PHP_URL_HOST );

				if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) :
					?>
					<div class="notice notice-error">
						<p><?php printf( __( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %1$s updates. Please add %2$s to %3$s.', $this->text_domain ), $this->software_title, '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>' ); // phpcs:ignore ?></p>
					</div>
					<?php
				endif;
			}
		}

		/**
		 * Outputs the settings page.
		 *
		 * @return void
		 */
		public function config_page() {

			$settings_tabs = array(
				$this->wc_am_activation_tab_key   => esc_html__( $this->wc_am_menu_tab_activation_title, $this->text_domain ), // phpcs:ignore
				$this->wc_am_deactivation_tab_key => esc_html__( $this->wc_am_menu_tab_deactivation_title, $this->text_domain ), // phpcs:ignore
			);

			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->wc_am_activation_tab_key; // phpcs:ignore
			$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->wc_am_activation_tab_key; // phpcs:ignore

			?>
			<div class='wrap'>
				<h2><?php esc_html_e( $this->wc_am_settings_title, $this->text_domain ); // phpcs:ignore ?></h2>
				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $settings_tabs as $tab_page => $tab_name ) :
						$active_tab = $current_tab === $tab_page ? 'nav-tab-active' : '';
						echo '<a class="nav-tab ' . esc_attr( $active_tab ) . '" href="?page=' . esc_attr( $this->wc_am_activation_tab_key ) . '&tab=' . esc_attr( $tab_page ) . '">' . esc_attr( $tab_name ) . '</a>';
					endforeach;
					?>
				</h2>
				<form action='options.php' method='post'>
					<div class="main">
						<?php
						if ( $tab === $this->wc_am_activation_tab_key ) {
							settings_fields( $this->data_key );
							do_settings_sections( $this->wc_am_activation_tab_key );
							submit_button( esc_html__( 'Save Changes', $this->text_domain ) ); // phpcs:ignore
						} else {
							settings_fields( $this->wc_am_deactivate_checkbox_key );
							do_settings_sections( $this->wc_am_deactivation_tab_key );
							submit_button( esc_html__( 'Save Changes', $this->text_domain ) ); // phpcs:ignore
						}
						?>
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Register settings.
		 *
		 * @return void
		 */
		public function load_settings() {
			global $wp_version;

			register_setting( $this->data_key, $this->data_key, array( $this, 'validate_options' ) );

			// API Key.
			add_settings_section( $this->wc_am_api_key_key, esc_html__( 'API Key Activation', $this->text_domain ), array( $this, 'wc_am_api_key_text' ), $this->wc_am_activation_tab_key ); // phpcs:ignore
			add_settings_field( $this->wc_am_api_key_key, esc_html__( 'API Key', $this->text_domain ), array( $this, 'wc_am_api_key_field' ), $this->wc_am_activation_tab_key, $this->wc_am_api_key_key ); // phpcs:ignore

			if ( $this->no_product_id ) {
				add_settings_field( 'product_id', esc_html__( 'Product ID', $this->text_domain ), array( $this, 'wc_am_product_id_field' ), $this->wc_am_activation_tab_key, $this->wc_am_api_key_key ); // phpcs:ignore
			}

			add_settings_field( 'status', esc_html__( 'API Key Status', $this->text_domain ), array( $this, 'wc_am_api_key_status' ), $this->wc_am_activation_tab_key, $this->wc_am_api_key_key ); // phpcs:ignore
			add_settings_field( 'info', esc_html__( 'Activation Info', $this->text_domain ), array( $this, 'wc_am_activation_info' ), $this->wc_am_activation_tab_key, $this->wc_am_api_key_key ); // phpcs:ignore

			// Activation settings.
			register_setting( $this->wc_am_deactivate_checkbox_key, $this->wc_am_deactivate_checkbox_key, array( $this, 'wc_am_license_key_deactivation' ) );

			add_settings_section( 'deactivate_button', esc_html__( 'API Deactivation', $this->text_domain ), array( $this, 'wc_am_deactivate_text' ), $this->wc_am_deactivation_tab_key ); //  phpcs:ignore
			add_settings_field( 'deactivate_button', esc_html__( 'Deactivate API Key', $this->text_domain ), array( $this, 'wc_am_deactivate_textarea' ), $this->wc_am_deactivation_tab_key, 'deactivate_button' ); // phpcs:ignore
		}

		/**
		 * Provides text for api key section.
		 *
		 * @return void
		 */
		public function wc_am_api_key_text() {
			// Empty text. Override if needed.
		}

		/**
		 * Returns the API Key status from the WooCommerce API Manager on the server.
		 *
		 * @return void
		 */
		public function wc_am_api_key_status() {

			if ( $this->get_api_key_status( true ) ) {

				$license_status_check = esc_html__( 'Activated', $this->text_domain ); // phpcs:ignore

				update_option( $this->wc_am_activated_key, 'Activated' );
				update_option( $this->wc_am_deactivate_checkbox_key, 'off' );

			} else {

				$license_status_check = esc_html__( 'Deactivated', $this->text_domain ); // phpcs:ignore
			}

			echo esc_attr( $license_status_check );
		}

		/**
		 * Returns the API Key status by querying the Status API function from the WooCommerce API Manager on the server.
		 *
		 * @return array|mixed|object
		 */
		public function license_key_status() {
			if ( $this->status_result !== null ) {
				$status_result = $this->status_result;
			} else {
				$status_result = $this->status();
			}
			return ! empty( $status_result ) ? json_decode( $status_result, true ) : $status_result;
		}

		/**
		 * Returns true if the API Key status is Activated.
		 *
		 * @since 2.1
		 *
		 * @param bool $live Do not set to true if using to activate software. True is for live status checks after activation.
		 * @return bool
		 */
		public function get_api_key_status( $live = false ) {

			/**
			 * Real-time result.
			 *
			 * @since 2.5.1
			 */
			if ( $live ) {
				$license_status = $this->license_key_status();

				return ! empty( $license_status ) && ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'];
			}

			/**
			 * If $live === false.
			 *
			 * Stored result when first activating software.
			 */
			return get_option( $this->wc_am_activated_key ) === 'Activated';
		}

		/**
		 * Display activation error returned by shop or local server.
		 *
		 * @since 2.9
		 */
		public function wc_am_activation_info() {

			$result_error = get_option( 'wc_am_' . $this->product_id . '_activate_error' );
			$live_status  = $this->license_key_status();
			$line_break   = wp_kses_post( '<br>' );

			if ( ! empty( $live_status ) && isset( $live_status['success'] ) && $live_status['success'] === false ) {
				echo esc_html( 'Error: ' . $live_status['data']['error'] );
			}

			if ( $this->get_api_key_status() ) {
				$result_success = get_option( 'wc_am_' . $this->product_id . '_activate_success' );

				if ( ! empty( $live_status ) && isset( $live_status['status_check'] ) && $live_status['success'] === 'active' ) {
					echo esc_html( 'Activations purchased: ' . $live_status['data']['total_activations_purchased'], $this->text_domain ); // phpcs:ignore
					echo $line_break; // phpcs:ignore
					echo esc_html( 'Total Activations: ' . $live_status['data']['total_activations'], $this->text_domain ); // phpcs:ignore
					echo $line_break; // phpcs:ignore
					echo esc_html( 'Activations Remaining: ' . $live_status['data']['activations_remaining'], $this->text_domain ); // phpcs:ignore
				} elseif ( ! empty( $result_success ) ) {
					echo esc_html( $result_success );
				} else {
					echo '';
				}
			} elseif ( ! $this->get_api_key_status() && ! empty( $live_status ) && isset( $live_status['status_check'] ) && $live_status['status_check'] === 'inactive' ) {

				echo esc_html( 'Activations purchased: ' . $live_status['data']['total_activations_purchased'], $this->text_domain ); // phpcs:ignore
				echo $line_break; // phpcs:ignore
				echo esc_html( 'Total Activations: ' . $live_status['data']['total_activations'], $this->text_domain ); // phpcs:ignore
				echo $line_break; // phpcs:ignore
				echo esc_html( 'Activations Remaining: ' . $live_status['data']['activations_remaining'], $this->text_domain ); // phpcs:ignore
			} elseif ( ! $this->get_api_key_status() && ! empty( $result_error ) ) {
				echo esc_html__( 'Previous activation attempt errors:', $this->text_domain ); // phpcs:ignore
				echo $line_break; // phpcs:ignore
				wp_kses_post( print_r( $result_error ) ); // phpcs:ignore
			} else {
				echo '';
			}
		}

		/**
		 * Outputs the API Key field.
		 *
		 * @return void
		 */
		public function wc_am_api_key_field() {

			$value = ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '';

			/**
			 * Filters the API Key field value.
			 *
			 * @since 2.9.2
			 *
			 * @param string $value The API Key field value.
			 */
			$value = apply_filters( 'wc_am_api_key_field_value', $value, $this->data_key, $this->data );
			if ( $value ) {
				echo "<input id='api_key' name='" . esc_attr( $this->data_key ) . '[' . esc_attr( $this->wc_am_api_key_key ) . "]' size='25' type='text' value='" . esc_attr( $value ) . "' />";
			} else {
				echo "<input id='api_key' name='" . esc_attr( $this->data_key ) . '[' . esc_attr( $this->wc_am_api_key_key ) . "]' size='25' type='text' value='' />";
			}
		}

		/**
		 * Outputs the product ID field.
		 *
		 * @since 2.3
		 */
		public function wc_am_product_id_field() {

			$product_id = get_option( $this->wc_am_product_id );

			if ( ! empty( $product_id ) ) {
				$this->product_id = $product_id;
			}

			if ( ! empty( $product_id ) ) {
				echo "<input id='product_id' name='" . esc_attr( $this->wc_am_product_id ) . "' size='25' type='text' value='" . absint( $this->product_id ) . "' />";
			} else {
				echo "<input id='product_id' name='" . esc_attr( $this->wc_am_product_id ) . "' size='25' type='text' value='' />";
			}
		}

		/**
		 * Sanitizes and validates all input and output for Dashboard.
		 *
		 * @since 2.0
		 *
		 * @param array $input Input data.
		 * @return mixed|string
		 */
		public function validate_options( $input ) {

			// Load existing options, validate, and update with changes from input before returning.
			$options                             = $this->data;
			$options[ $this->wc_am_api_key_key ] = trim( $input[ $this->wc_am_api_key_key ] );
			$api_key                             = trim( $input[ $this->wc_am_api_key_key ] );
			$activation_status                   = get_option( $this->wc_am_activated_key );
			$checkbox_status                     = get_option( $this->wc_am_deactivate_checkbox_key );
			$current_api_key                     = ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '';

			if ( $this->no_product_id ) {

				$new_product_id = absint( $_REQUEST[ $this->wc_am_product_id ] ); // phpcs:ignore

				if ( ! empty( $new_product_id ) ) {

					update_option( $this->wc_am_product_id, $new_product_id );

					$this->product_id = $new_product_id;
				}
			}

			// Should match the settings_fields() value.
			if ( ! empty( $_REQUEST['option_page'] ) && $_REQUEST['option_page'] !== $this->wc_am_deactivate_checkbox_key ) {  // phpcs:ignore

				if ( $activation_status === 'Deactivated' || $activation_status === '' || $api_key === '' || $checkbox_status === 'on' || $current_api_key !== $api_key ) {
					/**
					 * If this is a new key, and an existing key already exists in the database, try to deactivate the existing key before activating the new key.
					 */
					if ( ! empty( $current_api_key ) && $current_api_key !== $api_key ) {
						$this->replace_license_key( $current_api_key );
					}

					$args = array(
						'api_key' => $api_key,
					);

					$activation_result = $this->activate( $args );

					if ( ! empty( $activation_result ) ) {
						$activate_results = json_decode( $activation_result, true );

						if ( $activate_results['success'] === true && $activate_results['activated'] === true ) {

							add_settings_error( 'activate_text', 'activate_msg', sprintf( __( '%s activated. ', $this->text_domain ), esc_attr( $this->software_title ) ) . esc_attr( "{$activate_results['message']}." ), 'updated' ); // phpcs:ignore

							update_option( 'wc_am_' . $this->product_id . '_activate_success', $activate_results['message'] );
							update_option( $this->wc_am_activated_key, 'Activated' );
							update_option( $this->wc_am_deactivate_checkbox_key, 'off' );
						}

						if ( $activate_results === false && ! empty( $this->data ) && ! empty( $this->wc_am_activated_key ) ) {

							add_settings_error( 'api_key_check_text', 'api_key_check_error', esc_html__( 'Connection failed to the License Key API server. See the Activation Error section below for details. There may be a problem on your server preventing outgoing requests, or the store is blocking your request to activate the plugin/theme.', $this->text_domain ), 'error' ); // phpcs:ignore

							update_option( $this->wc_am_activated_key, 'Deactivated' );
						}

						if ( isset( $activate_results['data']['error_code'] ) && ! empty( $this->data ) && ! empty( $this->wc_am_activated_key ) ) {
							add_settings_error( 'wc_am_client_error_text', 'wc_am_client_error', esc_attr( "{$activate_results['data']['error']}" ), 'error' );
							update_option( $this->wc_am_activated_key, 'Deactivated' );
						}
					} else {
						add_settings_error( 'not_activated_empty_response_text', 'not_activated_empty_response_error', esc_html__( 'The API Key activation could not be completed due to an error on the store server or your server. See the Activation Error section below for details. The activation results were empty.', $this->text_domain ), 'updated' ); // phpcs:ignore
					}
				}
			}

			return $options;
		}

		/**
		 * Allow other actors to activate a new key programmatically.
		 *
		 * @since 2.9.2
		 *
		 * @param string $api_key API Key.
		 * @return void
		 */
		public function activate_new_key( $api_key ) {

			$result = $this->activate( array( 'api_key' => $api_key ) );

			if ( ! empty( $result ) ) {

				$result = json_decode( $result, true );

				if ( $result['success'] === true && $result['activated'] === true ) {
					update_option( 'wc_am_' . $this->product_id . '_activate_success', $result['message'] );
					update_option( $this->wc_am_activated_key, 'Activated' );
					update_option( $this->wc_am_deactivate_checkbox_key, 'off' );
					update_option( $this->data_key, array( "{$this->data_key}_api_key" => $api_key ) );
				} elseif ( function_exists( 'wc_get_logger' ) ) {
					wc_get_logger()->error( print_r( $result, true ), array( 'source' => 'wc_product_sample' ) ); // phpcs:ignore
				} else {
					error_log( print_r( $result, true ) ); // phpcs:ignore
				}
			}
		}

		/**
		 * Deactivates the API Key to allow key to be used on another blog.
		 *
		 * @param string|mixed $input Checkbox value.
		 * @return string
		 */
		public function wc_am_license_key_deactivation( $input ) {

			$activation_status = get_option( $this->wc_am_activated_key );
			$options           = ( $input === 'on' ? 'on' : 'off' );

			$args = array(
				'api_key' => ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '',
			);

			if ( ! empty( $this->data[ $this->wc_am_api_key_key ] ) && $options === 'on' && $activation_status === 'Activated' ) {

				// Deactivates API Key key activation.
				$deactivation_result = $this->deactivate( $args );

				if ( ! empty( $deactivation_result ) ) {

					$activate_results = json_decode( $deactivation_result, true );

					if ( $activate_results['success'] === true && $activate_results['deactivated'] === true ) {

						if ( ! empty( $this->wc_am_activated_key ) ) {

							update_option( $this->wc_am_activated_key, 'Deactivated' );

							add_settings_error( 'wc_am_deactivate_text', 'deactivate_msg', esc_html__( 'API Key deactivated. ', $this->text_domain ) . esc_attr( "{$activate_results['activations_remaining']}." ), 'updated' ); // phpcs:ignore
						}

						return $options;
					}

					if ( isset( $activate_results['data']['error_code'] ) && ! empty( $this->data ) && ! empty( $this->wc_am_activated_key ) ) {

						add_settings_error( 'wc_am_client_error_text', 'wc_am_client_error', esc_attr( "{$activate_results['data']['error']}" ), 'error' );

						update_option( $this->wc_am_activated_key, 'Deactivated' );
					}
				} else {

					add_settings_error( 'not_deactivated_empty_response_text', 'not_deactivated_empty_response_error', esc_html__( 'The API Key activation could not be completed due to an unknown error possibly on the store server The activation results were empty.', $this->text_domain ), 'updated' ); // phpcs:ignore
				}
			}

			return $options;
		}

		/**
		 * Deactivate the current API Key before activating the new API Key.
		 *
		 * @param string|mixed $current_api_key Current API Key.
		 * @return void
		 */
		public function replace_license_key( $current_api_key ) {

			$args = array(
				'api_key' => $current_api_key,
			);

			$this->deactivate( $args );
		}

		/**
		 * Outputs the deactivation text.
		 *
		 * @return void
		 */
		public function wc_am_deactivate_text() {
			// Empty text. Override if needed.
		}

		/**
		 * Outputs the deactivation checkbox.
		 *
		 * @return void
		 */
		public function wc_am_deactivate_textarea() {

			echo '<input type="checkbox" id="' . esc_attr( $this->wc_am_deactivate_checkbox_key ) . '" name="' . esc_attr( $this->wc_am_deactivate_checkbox_key ) . '" value="on"';
			echo checked( get_option( $this->wc_am_deactivate_checkbox_key ), 'on' );
			echo '/>';
			?>
			<span class="description"><?php esc_html_e( 'Deactivates an API Key so it can be used on another blog.', $this->text_domain ); // phpcs:ignore ?></span>
			<?php
		}

		/**
		 * Builds the URL containing the API query string for activation, deactivation, and status requests.
		 *
		 * @param array $args Arguments.
		 * @return string
		 */
		public function create_software_api_url( $args ) {
			return add_query_arg( 'wc-api', 'wc-am-api', $this->api_url ) . '&' . http_build_query( $args );
		}

		/**
		 * Sends the request to activate to the API Manager.
		 *
		 * @param array $args Arguments.
		 * @return string
		 */
		public function activate( $args ) {

			if ( empty( $args ) ) {
				add_settings_error( 'not_activated_text', 'not_activated_error', esc_html__( 'The API Key is missing from the deactivation request.', $this->text_domain ), 'updated' ); // phpcs:ignore

				return '';
			}

			$defaults = array(
				'wc_am_action'      => 'activate',
				'product_id'        => $this->product_id,
				'product_parent_id' => $this->product_parent_id,
				'instance'          => $this->wc_am_instance_id,
				'object'            => $this->wc_am_domain,
				'software_version'  => $this->wc_am_software_version,
			);

			$args       = wp_parse_args( $defaults, $args );
			$target_url = esc_url_raw( $this->create_software_api_url( $args ) );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			// Request failed.
			if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) !== 200 ) {
				update_option( 'wc_am_' . $this->product_id . '_activate_error', $request );

				return '';
			} elseif ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
				update_option( 'wc_am_' . $this->product_id . '_activate_error', 'Error code: ' . $request->get_error_code() . '.<br> Error message: ' . $request->get_error_message() . '.<br> Error data: ' . $request->get_error_data() );

				return '';
			}

			delete_option( 'wc_am_' . $this->product_id . '_activate_error' );

			return wp_remote_retrieve_body( $request );
		}

		/**
		 * Sends the request to deactivate to the API Manager.
		 *
		 * @param array $args Arguments.
		 * @return string
		 */
		public function deactivate( $args ) {

			if ( empty( $args ) ) {
				add_settings_error( 'not_deactivated_text', 'not_deactivated_error', esc_html__( 'The API Key is missing from the deactivation request.', $this->text_domain ), 'updated' ); // phpcs:ignore

				return '';
			}

			$defaults = array(
				'wc_am_action'      => 'deactivate',
				'product_id'        => $this->product_id,
				'product_parent_id' => $this->product_parent_id,
				'instance'          => $this->wc_am_instance_id,
				'object'            => $this->wc_am_domain,
			);

			$args       = wp_parse_args( $defaults, $args );
			$target_url = esc_url_raw( $this->create_software_api_url( $args ) );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
				// Request failed.
				return '';
			}

			return wp_remote_retrieve_body( $request );
		}

		/**
		 * Sends the status check request to the API Manager.
		 *
		 * @return string|null
		 */
		public function status() {
			if ( $this->status_result !== null ) {
				return $this->status_result;
			}

			if ( empty( $this->data[ $this->wc_am_api_key_key ] ) ) {
				$this->status_result = '';
				return $this->status_result;
			}

			$defaults = array(
				'wc_am_action'      => 'status',
				'api_key'           => $this->data[ $this->wc_am_api_key_key ],
				'product_id'        => $this->product_id,
				'product_parent_id' => $this->product_parent_id,
				'instance'          => $this->wc_am_instance_id,
				'object'            => $this->wc_am_domain,
			);

			$target_url = esc_url_raw( $this->create_software_api_url( $defaults ) );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {

				// Request failed.
				$this->status_result = '';

				return $this->status_result;
			}

			$this->status_result = wp_remote_retrieve_body( $request );

			return $this->status_result;
		}

		/**
		 * Check for software updates.
		 */
		public function check_for_update() {
			$this->plugin_name = $this->wc_am_plugin_name;

			// Slug should be the same as the plugin/theme directory name.
			if ( strpos( $this->plugin_name, '.php' ) !== 0 ) {
				$this->slug = dirname( $this->plugin_name );
			} else {
				$this->slug = $this->plugin_name;
			}

			// Uses the flag above to determine if this is a plugin or a theme update request.
			if ( $this->plugin_or_theme === 'plugin' ) {
				add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
				add_filter( 'plugins_api', array( $this, 'information_request' ), 10, 3 );
			} elseif ( $this->plugin_or_theme === 'theme' ) {
				add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_check' ) );
				// phpcs:ignore
				// add_filter( 'themes_api', array( $this, 'information_request' ), 10, 3 );
			}
		}

		/**
		 * Sends and receives data to and from the server API.
		 *
		 * @since  2.0
		 *
		 * @param array $args Arguments.
		 * @return bool|string $response
		 */
		public function send_query( $args ) {

			$target_url = esc_url_raw( add_query_arg( 'wc-api', 'wc-am-api', $this->api_url ) . '&' . http_build_query( $args ) );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return ! empty( $response ) ? $response : false;
		}

		/**
		 * Check for updates against the remote server.
		 *
		 * @since  2.0
		 *
		 * @param object $transient Transient object.
		 * @return object $transient
		 */
		public function update_check( $transient ) {

			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$args = array(
				'wc_am_action'      => 'update',
				'slug'              => $this->slug,
				'plugin_name'       => $this->plugin_name,
				'version'           => $this->wc_am_software_version,
				'product_id'        => $this->product_id,
				'product_parent_id' => $this->product_parent_id,
				'api_key'           => ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '',
				'instance'          => $this->wc_am_instance_id,
			);

			// Check for a plugin update.
			$response = json_decode( $this->send_query( $args ), true );

			if ( isset( $response['data']['error_code'] ) ) {
				if ( isset( $response['data']['error'] ) ) {
					$error_message = $response['data']['error'];
				} elseif ( isset( $response['data']['message'] ) ) {
					$error_message = $response['data']['message'];
				} else {
					$error_message = null;
				}

				if ( $error_message ) {
					add_settings_error( 'wc_am_client_error_text', 'wc_am_client_error', $error_message, 'error' );
				}
			}

			if ( $response !== false && isset( $response['success'] ) && $response['success'] === true ) {
				// New plugin version from the API.
				$new_ver = (string) $response['data']['package']['new_version'];
				// Current installed plugin version.
				$curr_ver = (string) $this->wc_am_software_version;

				$package = array(
					'id'             => $response['data']['package']['id'],
					'slug'           => $response['data']['package']['slug'],
					'plugin'         => $response['data']['package']['plugin'],
					'new_version'    => $response['data']['package']['new_version'],
					'url'            => $response['data']['package']['url'],
					'tested'         => $response['data']['package']['tested'],
					'package'        => $response['data']['package']['package'],
					'upgrade_notice' => $response['data']['package']['upgrade_notice'],
				);

				if ( isset( $new_ver, $curr_ver ) ) {
					if ( version_compare( $new_ver, $curr_ver, '>' ) ) {
						if ( $this->plugin_or_theme === 'plugin' ) {
							$transient->response[ $this->plugin_name ] = (object) $package;
							unset( $transient->no_update[ $this->plugin_name ] );
						} elseif ( $this->plugin_or_theme === 'theme' ) {
							$transient->response[ $this->plugin_name ]['new_version'] = $response['data']['package']['new_version'];
							$transient->response[ $this->plugin_name ]['url']         = $response['data']['package']['url'];
							$transient->response[ $this->plugin_name ]['package']     = $response['data']['package']['package'];
						}
					}
				}
			}

			return $transient;
		}

		/**
		 * API request for information.
		 *
		 * If `$action` is 'query_plugins' or 'plugin_information', an object MUST be passed.
		 * If `$action` is 'hot_tags` or 'hot_categories', an array should be passed.
		 *
		 * @param false|object|array $result The result object or array. Default false.
		 * @param string             $action The type of information being requested from the Plugin Install API.
		 * @param object             $args Arguments.
		 *
		 * @return object
		 */
		public function information_request( $result, $action, $args ) {

			// Check if this plugins API is about this plugin.
			if ( isset( $args->slug ) ) {
				if ( $args->slug !== $this->slug ) {
					return $result;
				}
			} else {
				return $result;
			}

			$args = array(
				'wc_am_action'      => 'plugininformation',
				'plugin_name'       => $this->plugin_name,
				'version'           => $this->wc_am_software_version,
				'product_id'        => $this->product_id,
				'product_parent_id' => $this->product_parent_id,
				'api_key'           => ! empty( $this->data[ $this->wc_am_api_key_key ] ) ? $this->data[ $this->wc_am_api_key_key ] : '',
				'instance'          => $this->wc_am_instance_id,
				'object'            => $this->wc_am_domain,
			);

			$response = unserialize( $this->send_query( $args ) ); // phpcs:ignore

			if ( isset( $response ) && is_object( $response ) && $response !== false ) {
				return $response;
			}

			return $result;
		}
	}
}
