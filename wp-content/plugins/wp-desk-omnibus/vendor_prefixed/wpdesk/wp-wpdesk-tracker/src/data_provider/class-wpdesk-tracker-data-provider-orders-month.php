<?php

namespace OmnibusProVendor;

/**
 * WP Desk Tracker
 *
 * @class        WPDESK_Tracker
 * @version        1.3.2
 * @package        WPDESK/Helper
 * @category    Class
 * @author        WP Desk
 */
if (!\defined('ABSPATH')) {
    exit;
}
if (!\class_exists('OmnibusProVendor\WPDesk_Tracker_Data_Provider_Orders_Month')) {
    /**
     * Class WPDesk_Tracker_Data_Provider_Orders_Month
     */
    class WPDesk_Tracker_Data_Provider_Orders_Month implements \WPDesk_Tracker_Data_Provider
    {
        /**
         * Info about orders per month.
         *
         * @return array Data provided to tracker.
         */
        public function get_data()
        {
            global $wpdb;
            if (\class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                $query = $wpdb->get_results("\nSELECT min(date_created_gmt) min, max(date_created_gmt) max, TIMESTAMPDIFF(MONTH, min(date_created_gmt), max(date_created_gmt) )+1 months\nFROM {$wpdb->prefix}wc_orders\nWHERE type = 'shop_order'\nAND status = 'wc-completed'\n");
            } else {
                $query = $wpdb->get_results("\n            \tSELECT min(post_date) min, max(post_date) max, TIMESTAMPDIFF(MONTH, min(post_date), max(post_date) )+1 months\n            \tFROM {$wpdb->posts} p\n            \tWHERE p.post_type = 'shop_order'\n            \tAND p.post_status = 'wc-completed'\n            \t");
            }
            $data['orders_per_month'] = [];
            if ($query) {
                foreach ($query as $row) {
                    $data['orders_per_month']['first'] = $row->min;
                    $data['orders_per_month']['last'] = $row->max;
                    $data['orders_per_month']['months'] = $row->months;
                    if ($row->months != 0) {
                        if (isset($data['orders']) && isset($data['orders']['wc-completed'])) {
                            $data['orders_per_month']['per_month'] = \floatval($data['orders']['wc-completed']) / \floatval($row->months);
                        }
                    }
                }
            }
            return $data;
        }
    }
}
