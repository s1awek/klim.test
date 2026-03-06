<?php

class Fupi_PROOFREC_admin {
    private $settings;

    private $tools;

    private $main;

    private $cook;

    private $versions;

    private $table_name = 'fupi_consents';

    private $cron_hook = 'fupi_consents_backup_cron_event';

    public function __construct() {
        $this->settings = get_option( 'fupi_proofrec' );
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->cook = get_option( 'fupi_cook' );
        $this->versions = get_option( 'fupi_versions' );
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters() {
        add_action( 'fupi_register_setting_proofrec', array($this, 'register_module_settings') );
        add_filter(
            'fupi_proofrec_add_fields_settings',
            array($this, 'add_fields_settings'),
            10,
            1
        );
        add_filter(
            'fupi_proofrec_get_page_descr',
            array($this, 'get_page_descr'),
            10,
            2
        );
        // CDB - Privacy page updates listener
        add_action(
            'publish_page',
            array($this, 'fupi_listen_to_pp_page_updates'),
            10,
            2
        );
    }

    //
    // BACKUP PP
    //
    // Check if the page with the provided ID is the Privacy Policy page and it is published
    private function is_pp_ok( $post_id ) {
        if ( !empty( $this->cook['pp_id'] ) ) {
            $pp_id = (int) $this->cook['pp_id'];
            if ( $post_id == $pp_id ) {
                $page_status = get_post_status( $pp_id );
                return $page_status == 'publish';
            }
        }
        return false;
    }

    public function fupi_listen_to_pp_page_updates( $post_id, $post ) {
        // STOP if we don't store proofs
        $send_to_email = !empty( $this->settings['storage_location'] ) && $this->settings['storage_location'] == 'email';
        $send_to_cdb = !$send_to_email && !empty( $this->settings['cdb_key'] );
        if ( !$send_to_email && !$send_to_cdb ) {
            return;
        }
        // STOP if the current page does not pass Privacy Policy checks
        if ( !$this->is_pp_ok( $post_id ) ) {
            return;
        }
        // Send privacy policy
        include_once 'proofrec-sender.php';
        $proofrec_sender = new Fupi_PROOFREC_send();
        if ( $send_to_email ) {
            $email_to = ( !empty( $this->settings['local_backup_email'] ) ? $this->settings['local_backup_email'] : get_option( 'admin_email' ) );
            $email_sent = $proofrec_sender->send_privacy_policy_to_email( $email_to );
        } else {
            $proofrec_sender->send_privacy_policy_to_cdb();
        }
    }

    //
    // EMAIL CONSENTS
    //
    private function create_db_tables() {
        global $wpdb;
        $full_table_name = $wpdb->prefix . $this->table_name;
        // Create table if not already present
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$full_table_name}'" ) != $full_table_name ) {
            $charset_collate = $wpdb->get_charset_collate();
            /*
            id int(9) NOT NULL AUTO_INCREMENT, //!! mediumint -> int ???
            consent_id varchar(30) NOT NULL, //!!
            consent_date bigint NOT NULL, //!! czy jest potrzebne 20, mozna zrobic index bazodanowy
            provided_consents longtext NOT NULL,
            extra_data longtext NOT NULL,
            */
            $sql = "CREATE TABLE {$full_table_name} (\r\n                id int(9) NOT NULL AUTO_INCREMENT,\r\n                consent_id varchar(50) NOT NULL,\r\n                consent_date bigint NOT NULL,\r\n                provided_consents longtext NOT NULL,\r\n                extra_data longtext NOT NULL,\r\n                PRIMARY KEY  (id)\r\n            ) {$charset_collate};";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }

    // Ensure the backup cron is scheduled with the right frequency
    private function schedule_cron( $clean_data = false ) {
        $freq = ( !empty( $clean_data['email_frequency'] ) ? $clean_data['email_frequency'] : 'daily' );
        $this->unschedule_cron();
        // unschedule previous CRON to set up a new one with the correct frequency
        if ( !in_array( $freq, array('hourly', 'twicedaily', 'daily') ) ) {
            $freq = 'daily';
        }
        // make sure that no unexpected value is passed
        if ( !wp_next_scheduled( $this->cron_hook ) ) {
            wp_schedule_event( time() + 60, $freq, $this->cron_hook );
        }
    }

    // Remove backup cron
    private function unschedule_cron() {
        $timestamp = wp_next_scheduled( $this->cron_hook );
        while ( $timestamp ) {
            wp_unschedule_event( $timestamp, $this->cron_hook );
            $timestamp = wp_next_scheduled( $this->cron_hook );
        }
    }

    // Delete all consents
    public function handle_delete_all_consents() {
        if ( !isset( $_POST['fupi_nonce'] ) || !wp_verify_nonce( $_POST['fupi_nonce'], 'fupi_delete_all_consents' ) || !current_user_can( 'manage_options' ) ) {
            wp_die( 'Forbidden' );
        }
        global $wpdb;
        $wpdb->query( "DELETE FROM " . $wpdb->prefix . self::TABLE );
        wp_redirect( admin_url( 'admin.php?page=fupi-consents&deleted=1' ) );
        exit;
    }

    //
    // ADMIN PAGE
    //
    public function add_fields_settings( $sections ) {
        include_once 'proofrec-fields.php';
        return $sections;
    }

    public function register_module_settings() {
        register_setting( 'fupi_proofrec', 'fupi_proofrec', array(
            'sanitize_callback' => array($this, 'sanitize_fields'),
        ) );
    }

    public function register_site_in_cdb( $clean_data ) {
        include_once 'proofrec-sender.php';
        $proofrec_sender = new Fupi_PROOFREC_send();
        // register website
        $response = $proofrec_sender->register_website_in_cdb( $clean_data['cdb_key'] );
        // If website was registered
        if ( !empty( $response ) && $response->status == 'success' ) {
            trigger_error( '[FP] ConsentsDB registration success' );
            // Send privacy policy
            $pp_registration_reponse = $proofrec_sender->send_privacy_policy_to_cdb( $clean_data['cdb_key'], true, false );
            // If PP was sent
            if ( $pp_registration_reponse !== false && $pp_registration_reponse->status == 'success' ) {
                trigger_error( '[FP] Privacy policy sent to ConsentsDB' );
                // Generate and send tracking setup
                include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
                $gdpr_checker = new Fupi_compliance_status_checker('proofrec', $clean_data, array(
                    'is_first_reg' => true,
                    'cdb_key'      => $clean_data['cdb_key'],
                ));
                $sending_setup_response = $gdpr_checker->send_and_return_status();
                // If setup was not sent
                if ( empty( $sending_setup_response ) || $sending_setup_response->status != 'success' ) {
                    unset($clean_data['cdb_key']);
                    trigger_error( '[FP] Could not send tracking setup to ConsentsDB' );
                    add_settings_error(
                        'fupi_cook',
                        'settings_updated',
                        esc_attr__( 'There was an error sending tracking configuration to the ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ),
                        'error'
                    );
                }
                // PP was not sent
            } else {
                unset($clean_data['cdb_key']);
                trigger_error( '[FP] Could not send privacy policy to ConsentsDB' );
                add_settings_error(
                    'fupi_cook',
                    'settings_updated',
                    esc_attr__( 'There was an error sending privacy policy to the ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ),
                    'error'
                );
                return $clean_data;
            }
            // if site was not registered
        } else {
            unset($clean_data['cdb_key']);
            trigger_error( '[FP] Could not register site to ConsentsDB' );
            add_settings_error(
                'fupi_proofrec',
                'settings_updated',
                esc_attr__( 'There was an error registering the site to the ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ),
                'error'
            );
            return $clean_data;
        }
        return $clean_data;
        // must return it to save fupi_cook opts
    }

    public function sanitize_fields( $input ) {
        include 'proofrec-sanitize.php';
        if ( apply_filters( 'fupi_updating_many_options', false ) ) {
            return $clean_data;
        }
        $pp_ok = false;
        if ( !empty( $this->cook['pp_id'] ) ) {
            $pp_id = (int) $this->cook['pp_id'];
            $pp_ok = get_post_status( $pp_id ) == 'publish';
        }
        $send_to_email = !empty( $clean_data['storage_location'] ) && $clean_data['storage_location'] == 'email';
        $send_to_cdb = !$send_to_email && !empty( $clean_data['cdb_key'] );
        if ( $send_to_email ) {
            $email_to = ( !empty( $clean_data['local_backup_email'] ) ? $clean_data['local_backup_email'] : get_option( 'admin_email' ) );
            // If the email sending has just been turned on (notice that "$settings" has previous values)
            if ( empty( $this->settings['storage_location'] ) || $this->settings['storage_location'] != 'email' ) {
                $this->create_db_tables();
                $this->schedule_cron( $clean_data );
                // Send privacy policy to email
                include_once 'proofrec-sender.php';
                $proofrec_sender = new Fupi_PROOFREC_send();
                $proofrec_sender->send_privacy_policy_to_email( $email_to, false );
                // Send tracking setup to email
                include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
                $gdpr_checker = new Fupi_compliance_status_checker('proofrec', $clean_data, array(
                    'is_first_reg' => true,
                    'email_to'     => $email_to,
                ));
                $gdpr_checker->send_and_return_status();
                // Update config && update CRON frequency
            } else {
                $this->schedule_cron( $clean_data );
                trigger_error( 'Sending config update to email' );
                include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
                $gdpr_checker = new Fupi_compliance_status_checker('proofrec', $clean_data, array(
                    'email_to' => $email_to,
                ));
                $gdpr_checker->send_and_return_status();
            }
        } else {
            if ( $send_to_cdb ) {
                // Turn off CRON for sending emails
                $this->unschedule_cron();
                // CDB - register site or update config data
                // if CDB key is new or has changed
                if ( empty( $this->settings['cdb_key'] ) || $this->settings['cdb_key'] != $clean_data['cdb_key'] ) {
                    if ( !$pp_ok ) {
                        unset($clean_data['cdb_key']);
                        add_settings_error(
                            'fupi_proofrec',
                            'settings_updated',
                            esc_attr__( 'ConsentsDB registration failed. To register ConsentsDB you must first publish a privacy policy page and save its ID in the settings of the Consent Management module.', 'full-picture-analytics-cookie-notice' ),
                            'error'
                        );
                    } else {
                        // Register new site
                        $clean_data = $this->register_site_in_cdb( $clean_data );
                    }
                    // if CDB key has not changed
                } else {
                    // Update config
                    include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
                    $gdpr_checker = new Fupi_compliance_status_checker('proofrec', $clean_data);
                    $gdpr_checker->send_and_return_status();
                }
            }
        }
        include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
        return $clean_data;
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ) {
        include 'proofrec-descr.php';
        return $ret_text;
    }

}
