<?php
/**
 * Bulk Price Converter - Section Settings
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Bulk_Price_Converter_Settings_Section' ) ) :

class Alg_WC_Bulk_Price_Converter_Settings_Section {

	/**
	 * id.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	public $id;

	/**
	 * desc.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	public $desc;

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter(
			'woocommerce_get_sections_alg_wc_bulk_price_converter',
			array( $this, 'settings_section' )
		);
		add_filter(
			'woocommerce_get_settings_alg_wc_bulk_price_converter_' . $this->id,
			array( $this, 'get_settings' ),
			PHP_INT_MAX
		);
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
