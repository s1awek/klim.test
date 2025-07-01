<?php

/*

This is for registering the website in CDB, sending WP FP's settings and sending and updating Privacy policy
WP FP's settings are sent via the function in class-fupi-get-gdpr-status.php

*/

class Fupi_send_to_CDB {
    
    private $purpose;
    private $cdb_key = false;
    private $clean_data;

    private function send_request( $method, $url, $payload ){
        
        if ( empty( $this->cdb_key ) ) $this->get_cdb_key();
        if ( empty( $this->cdb_key ) ) return;

        $payload['installID'] = fupi_fs()->get_site()->id;

        $header_arr	= ['Content-Type: application/json', 'x-api-key: ' . $this->cdb_key];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        
        if ( $method == 'PUT' ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        $serverReponseObject = json_decode($server_output);
        return $serverReponseObject;
    }

    public function send_privacy_policy( $return = false ){

        // get privacy policy text
        $priv_policy_id = get_option( 'wp_page_for_privacy_policy' );
        $priv_policy_post = get_post( $priv_policy_id );
        $priv_policy_content = $priv_policy_post->post_content;
        $priv_policy_content = apply_filters( 'the_content', $priv_policy_content );
        $priv_policy_content = do_shortcode( $priv_policy_content );
        // $priv_policy_content = htmlspecialchars( $priv_policy_content, ENT_QUOTES);

        $response = $this->send_request( 'POST', 'https://prod-fr.consentsdb.com/api/privacy/new', [ 'privacyPolicy' => $priv_policy_content ] );

        if ( ! empty( $response ) && $response->status == 'success' ) {
            trigger_error( '[FP] CDB privacy policy has been sent' );
        }

        if ( $return ) return $response;
    }

    public function register_new_site( $clean_data ){

        if ( empty( $clean_data['cdb_key'] ) ) return $clean_data;

        $this->clean_data = $clean_data;
        $this->cdb_key = $clean_data['cdb_key'];

        $priv_policy_url = get_privacy_policy_url();
        
        if ( empty( $priv_policy_url ) ) {
            unset( $clean_data['cdb_key'] ); // CDB MUST HAVE A PP
            add_settings_error( 'fupi_cook', 'settings_updated', esc_attr__( 'ConsentsDB registration failed. To register ConsentsDB you must first publish a privacy policy page and set it in "Settings > Privacy".', 'full-picture-analytics-cookie-notice' ), 'error');
            $this->cdb_key = false;
            return $clean_data;
        }

        // REGISTER SITE
        
        $old_cook_opts = get_option('fupi_cook');

        // init new registration if we did not have cdb_key before
        if ( empty( $old_cook_opts ) || empty( $old_cook_opts['cdb_key'] ) || $old_cook_opts['cdb_key'] !== $this->cdb_key ) {

            // register website
            $response = $this->send_request( 'PUT', 'https://prod-fr.consentsdb.com/domain/activate', [] );
    
            // Send privacy policy
            if ( ! empty( $response ) && $response->status == 'success' ) {
                
                trigger_error( '[FP] CDB website registered' );
    
                $pp_registration_reponse = $this->send_privacy_policy( true );
    
                // Send WP FP configuration
                if ( $pp_registration_reponse->status == 'success' ) {
                    $this->send_plugin_data();
                } else {
                    unset( $clean_data['cdb_key'] );
                    add_settings_error( 'fupi_cook', 'settings_updated', esc_attr__('There was an error registering the site to the ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ), 'error');
                    $this->cdb_key = false;
                    return $clean_data;
                }
            } else {

                unset( $clean_data['cdb_key'] );
                add_settings_error( 'fupi_cook', 'settings_updated', esc_attr__('There was an error registering the site to the ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ), 'error');
                $this->cdb_key = false;
                return $clean_data;
            }

        // otherwise, send updated plugin data
        } else {
            $this->send_plugin_data();
        };

        return  $clean_data; // must return it to save fupi_cook opts
    }

    private function send_plugin_data(){
        // Generate and send new WP FP configuration
        include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
        new Fupi_compliance_status_checker( 'cdb', $this->clean_data, true );
    }

    private function get_cdb_key(){
        $cook_opts = get_option('fupi_cook');
        $this->cdb_key = isset( $cook_opts['cdb_key'] ) ? esc_attr( $cook_opts['cdb_key'] ) : false;
    }
}