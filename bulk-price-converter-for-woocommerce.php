<?php
/*
Plugin Name: Price Update: Bulk Pricing Editor for WooCommerce
Plugin URI: https://wpfactory.com/item/bulk-price-converter-for-woocommerce-plugin/
Description: Save your time with all-in-one bulk price converter for all your WooCommerce store products, change your prices, add a fixed amount or multiply prices for all your products with a couple of clicks.
Version: 2.0.0
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 4.4
Text Domain: bulk-price-converter-for-woocommerce
Domain Path: /langs
WC tested up to: 9.9
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Bulk_Price_Converter' ) ) :

/**
 * Main Alg_WC_Bulk_Price_Converter Class
 *
 * @version 2.0.0
 * @since   1.0.0
 */
final class Alg_WC_Bulk_Price_Converter {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.2.0
	 */
	public $version = '2.0.0';

	/**
	 * @var Alg_WC_Bulk_Price_Converter The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * core.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	public $core;

	/**
	 * Main Alg_WC_Bulk_Price_Converter Instance.
	 *
	 * Ensures only one instance of Alg_WC_Bulk_Price_Converter is loaded or can be loaded.
	 *
	 * @static
	 * @return Alg_WC_Bulk_Price_Converter - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Bulk_Price_Converter Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active plugins
		if (
			! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ||
			(
				'bulk-price-converter-for-woocommerce.php' === basename( __FILE__ ) &&
				$this->is_plugin_active( 'bulk-price-converter-for-woocommerce-pro/bulk-price-converter-for-woocommerce-pro.php' )
			)
		) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Pro
		if ( 'bulk-price-converter-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-bulk-price-converter-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * localize.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function localize() {
		load_plugin_textdomain(
			'bulk-price-converter-for-woocommerce',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/langs/'
		);
	}

	/**
	 * is_plugin_active.
	 *
	 * @version 1.5.1
	 * @since   1.5.1
	 */
	function is_plugin_active( $plugin ) {
		return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array(
					$plugin,
					apply_filters( 'active_plugins', ( array ) get_option( 'active_plugins', array() ) )
				) ||
				(
					is_multisite() &&
					array_key_exists(
						$plugin,
						( array ) get_site_option( 'active_sitewide_plugins', array() )
					)
				)
			)
		);
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.4.1
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-alg-wc-bulk-price-converter-core.php' );
		// Tool
		require_once( 'includes/class-alg-wc-bulk-price-converter-tool.php' );
	}

	/**
	 * admin.
	 *
	 * @version 2.0.0
	 * @since   1.4.3
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

		// Adds cross-selling library.
		add_action( 'init', array( $this, 'add_cross_selling_library' ) );

		// Move WC Settings tab to WPFactory menu.
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );

		// Version update
		if ( get_option( 'alg_wc_bulk_price_converter_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @return  void
	 */
	function add_cross_selling_library(){
		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		// Cross-selling library.
		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => __FILE__ ) );
		$cross_selling->init();
	}

	/**
	 * move_wc_settings_tab_to_wpfactory_submenu.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @return  void
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {
		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpf_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();
		if ( method_exists( $wpf_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			$wpf_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
				'wc_settings_tab_id' => 'alg_wc_bulk_price_converter',
				'menu_title'         => __( 'Bulk Price Converter', 'bulk-price-converter-for-woocommerce' ),
				'page_title'         => __( 'Price Update: Bulk Pricing Editor for WooCommerce', 'bulk-price-converter-for-woocommerce' ),
				'plugin_icon'        => array(
					'get_url_method'    => 'wporg_plugins_api',
					'wporg_plugin_slug' => 'bulk-price-converter-for-woocommerce',
				),
			) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 2.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_bulk_price_converter' ) . '">' .
			__( 'Settings', 'bulk-price-converter-for-woocommerce' ) .
		'</a>';

		$custom_links[] = '<a style="font-weight: bold;" target="_blank" href="' . esc_url( 'https://wordpress.org/support/plugin/bulk-price-converter-for-woocommerce/reviews/#new-post' ) . '">' .
			__( 'Review Us', 'bulk-price-converter-for-woocommerce' ) .
		'</a>';

		if ( 'bulk-price-converter-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a style="color: green; font-weight: bold;" target="_blank" href="' . esc_url( 'https://wpfactory.com/item/bulk-price-converter-for-woocommerce-plugin/' ) . '">' .
				__( 'Go Pro', 'bulk-price-converter-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Bulk Price Converter settings tab to WooCommerce settings.
	 *
	 * @version 1.4.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-wc-settings-bulk-price-converter.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.4.3
	 * @since   1.4.0
	 */
	function version_updated() {
		update_option( 'alg_wc_bulk_price_converter_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_bulk_price_converter' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Bulk_Price_Converter to prevent the need to use globals.
	 *
	 * @return Alg_WC_Bulk_Price_Converter
	 */
	function alg_wc_bulk_price_converter() {
		return Alg_WC_Bulk_Price_Converter::instance();
	}
}

/**
 * init.
 *
 * @todo load on `plugins_loaded`
 */
alg_wc_bulk_price_converter();

/**
 * before_woocommerce_init.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			dirname(__FILE__),
			true
		);
	}
} );
