<?php

/*
This is for registering the website in CDB, sending WP FP's settings and sending and updating Privacy policy
WP FP's settings are sent via the function in class-fupi-get-gdpr-status.php
*/
class Fupi_PROOFREC_send {
    private $purpose;

    private $clean_data;

    // HELPERS
    private function get_privacy_policy_data() {
        $cook = get_option( 'fupi_cook' );
        if ( !empty( $cook['pp_id'] ) ) {
            $pp_id = (int) $cook['pp_id'];
            $pp_post = get_post( $pp_id );
            if ( !empty( $pp_post ) && isset( $pp_post->post_status ) && $pp_post->post_status === 'publish' ) {
                $modified_date = get_post_field( 'post_modified', $pp_id );
                $pp_content = $pp_post->post_content;
                $pp_content = apply_filters( 'the_content', $pp_content );
                $pp_content = do_shortcode( $pp_content );
                return [$pp_content, $modified_date];
            }
        }
        return false;
    }

    private function get_pp_md5( $pp_content ) {
        $pp_md5 = md5( json_encode( $pp_content ) );
        // sent settings data if settings with this MD5 have not been sent yet
        $versions_opts = get_option( 'fupi_versions' );
        if ( empty( $versions_opts['pp_md5'] ) || $versions_opts['pp_md5'] !== $pp_md5 ) {
            // Update MD5
            $versions_opts['pp_md5'] = $pp_md5;
            update_option( 'fupi_versions', $versions_opts );
            return [true, $pp_md5];
            // true = has changed
        }
        return [false, $pp_md5];
        // false = has not changed
    }

    //
    // CDB SENDERS
    //
    private function send_data_to_cdb(
        $method,
        $url,
        $payload,
        $cdb_key = false
    ) {
        // Get CDB Key
        if ( empty( $cdb_key ) ) {
            $proofrec = get_option( 'fupi_proofrec' );
            $cdb_key = ( isset( $proofrec['cdb_key'] ) ? esc_attr( $proofrec['cdb_key'] ) : false );
        }
        if ( empty( $cdb_key ) ) {
            return false;
        }
        $payload['installID'] = 999999;
        $header_arr = ['Content-Type: application/json', 'x-api-key: ' . $cdb_key];
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        if ( $method == 'PUT' ) {
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
        } else {
            curl_setopt( $ch, CURLOPT_POST, true );
        }
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec( $ch );
        curl_close( $ch );
        $serverReponseObject = json_decode( $server_output );
        trigger_error( '[FP] Tracking configuration data sent to CDB' );
        return $serverReponseObject;
    }

    public function register_website_in_cdb( $cdb_key ) {
        $server_response = $this->send_data_to_cdb(
            'PUT',
            'https://prod-fr.consentsdb.com/domain/activate',
            [],
            $cdb_key
        );
        return $server_response;
    }

    public function send_config_to_cdb( $payload, $cdb_key = false ) {
        $payload = [
            'wpfpSettings' => $payload,
        ];
        $server_response = $this->send_data_to_cdb(
            'POST',
            'https://prod-fr.consentsdb.com/api/configuration/new',
            $payload,
            $cdb_key
        );
        return $server_response;
    }

    public function send_privacy_policy_to_cdb( $cdb_key = false, $return = false, $only_send_when_pp_changed = true ) {
        $privacy_policy_data = $this->get_privacy_policy_data();
        if ( empty( $privacy_policy_data ) ) {
            return false;
        }
        $pp_md5_data = $this->get_pp_md5( $privacy_policy_data[0] );
        $pp_md5_changed = $pp_md5_data[0];
        //  Check if MD5 has changed (this is not checked if the PP is being sent for the first time)
        if ( $only_send_when_pp_changed && !$pp_md5_changed ) {
            return false;
        }
        $pp_content = $privacy_policy_data[0];
        // $privacy_policy_data[1] is not used for CDB (contains modified_date)
        $response = $this->send_data_to_cdb(
            'POST',
            'https://prod-fr.consentsdb.com/api/privacy/new',
            [
                'privacyPolicy' => $pp_content,
            ],
            $cdb_key
        );
        trigger_error( '[FP] Privacy policy sent to CDB' );
        if ( $return ) {
            return $response;
        }
    }

    //
    // EMAIL SENDERS
    //
    public function send_config_to_email( $email_address, $payload, $MD5 ) {
        include_once FUPI_PATH . '/admin/common/generate-files.php';
        $file_generator = new Fupi_Generate_Files();
        $file_path = $file_generator->make_file( array(
            'folder'       => 'conf',
            'file_name'    => 'config',
            'file_format'  => 'txt',
            'file_content' => json_encode( $payload ),
        ) );
        if ( $file_path !== 'error' ) {
            $site_name = get_bloginfo( 'name' );
            // Content
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            $subject = sprintf( esc_html__( '[Do not delete] Tracking setup from %1$s', 'full-picture-analytics-cookie-notice' ), $site_name );
            $body = sprintf( esc_html__( "This is an automatic message, sent by WP Full Picture plugin version %1\$s. You are getting this message because some privacy-related settings of your tracking tools have changed.\n\nDo not delete this email.\n\nIt holds a copy of your privacy-related tracking configuration that was current at the time of collecting tracking consents.\n\nSettings version: %2\$s", 'full-picture-analytics-cookie-notice' ), FUPI_VERSION, $MD5 );
            $body .= sprintf( esc_html__( "\n\nLearn how to find consents and combine them with privacy policy and tracking configuration data: %s.", 'full-picture-analytics-cookie-notice' ), 'https://wpfullpicture.com/support/documentation/how-to-find-visitors-consents-stored-in-emails/' );
            $attachments = array($file_path);
            // Send email
            $mail_sent = wp_mail(
                $email_address,
                $subject,
                $body,
                $headers,
                $attachments
            );
            // Delete file
            unlink( $file_path );
            if ( !$mail_sent ) {
                trigger_error( "[FP] There was an error sending email with configuration backup" );
                return false;
            } else {
                trigger_error( '[FP] Tracking configuration data sent to email address' );
            }
            return true;
        }
        return false;
    }

    public function send_privacy_policy_to_email( $email_address, $only_send_when_pp_changed = true ) {
        // Get data
        $pp_data = $this->get_privacy_policy_data();
        if ( empty( $pp_data ) ) {
            return false;
        }
        $pp_content = $pp_data[0];
        $modified_date = $pp_data[1];
        $pp_md5_data = $this->get_pp_md5( $pp_content );
        $pp_changed = $pp_md5_data[0];
        $pp_md5 = $pp_md5_data[1];
        // Check if MD5 has changed (this is not checked if the PP is being sent for the first time)
        if ( $only_send_when_pp_changed && !$pp_changed ) {
            return false;
        }
        // Generate file
        include_once FUPI_PATH . '/admin/common/generate-files.php';
        $file_generator = new Fupi_Generate_Files();
        $file_path = $file_generator->make_file( array(
            'folder'       => 'pp',
            'file_name'    => 'privacy_policy',
            'file_format'  => 'txt',
            'file_content' => $pp_content,
        ) );
        // Send email
        if ( $file_path != 'error' ) {
            $site_name = get_bloginfo( 'name' );
            // Content
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            $subject = sprintf( esc_html__( '[Do not delete] Privacy policy from %1$s', 'full-picture-analytics-cookie-notice' ), $site_name );
            $body = sprintf( esc_html__( "This is an automatic message, sent by WP Full Picture plugin version %1\$s. You are getting this message because privacy policy on your site has been updated or you started collecting consents.\n\nDo not delete this email. It holds a copy of your policy that was current at the time of collecting tracking consents.\n\nPrivacy Policy ID: %2\$s", 'full-picture-analytics-cookie-notice' ), FUPI_VERSION, $pp_md5 );
            $body .= sprintf( esc_html__( "\n\nLearn how to find consents and combine them with privacy policy and tracking configuration data: %s.", 'full-picture-analytics-cookie-notice' ), 'https://wpfullpicture.com/support/documentation/how-to-find-visitors-consents-stored-in-emails/' );
            $attachments = array($file_path);
            // Send email
            $mail_sent = wp_mail(
                $email_address,
                $subject,
                $body,
                $headers,
                $attachments
            );
            // Delete file
            unlink( $file_path );
            if ( !$mail_sent ) {
                trigger_error( "[FP] There was an error sending email with privacy policy backup" );
                return false;
            } else {
                trigger_error( '[FP] Privacy policy sent to email address' );
            }
            return true;
        }
        return false;
    }

}
