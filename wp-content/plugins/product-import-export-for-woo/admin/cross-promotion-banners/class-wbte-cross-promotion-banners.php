<?php
/**
 * Main class for Cross Promotion Banners.
 *
 * @version 1.0.0
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'Wbte_Cross_Promotion_Banners' ) ) {

	class Wbte_Cross_Promotion_Banners {

		private static $banner_version = '1.0.0'; // Update the same value in plugin specific banner version constant.

		public function __construct() {

			// Current version is not equal to the latest version.
			if ( version_compare(self::$banner_version, get_option( 'wbfte_promotion_banner_version', self::$banner_version ), '==' ) ) {
			    
			    /**
				 * Class includes helper functions for pklist invoice cta banner
				 */
				if ( ! get_option( 'wt_hide_invoice_cta_banner' ) ) {
					require_once plugin_dir_path(__FILE__) . 'class-wt-pklist-cta-banner.php';
				}

				/**
				 * Class includes helper functions for smart coupon cta banner
				 */
				if ( ! get_option( 'wt_hide_smart_coupon_cta_banner' ) ) {
					require_once plugin_dir_path(__FILE__) . 'class-wt-smart-coupon-cta-banner.php';
				}

				/**
				 * Class includes helper functions for pklist invoice cta banner
				 */
				if ( ! get_option( 'wt_hide_product_ie_cta_banner' ) ) {
					require_once plugin_dir_path(__FILE__) . 'class-wt-p-iew-cta-banner.php';
				}
				
			}
		}

		public static function get_banner_version() {
			return self::$banner_version;
		}
	}

	new Wbte_Cross_Promotion_Banners();
}