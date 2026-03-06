<?php

class Fupi_compliance_status_checker {

    private $modules_info = [];
    private $tools = [];
    private $track = [];
    private $data = [];
    private $consent_status = 'alert';
    private $url_pass_enabled = false;
    private $proofrec;
    private $cook;
    private $main;
    private $gtag;
    private $format;
    private $cdb_key;
    private $is_first_reg;
    private $modules_names;
    private $woo_enabled;
    private $clean_val_id;
    private $clean_val;
    private $send_to;
    private $pp_ok = false;
    
    public function __construct( $clean_val_id = false, $clean_val = false, $opts = array() ) {

        $this->clean_val_id     = $clean_val_id;
        $this->clean_val        = $clean_val;
        $this->proofrec         = $this->clean_val_id == 'proofrec' && ! empty( $this->clean_val ) ? $this->clean_val : get_option('fupi_proofrec');

        $this->cook             = $this->clean_val_id == 'cook' && $this->clean_val !== false ? $this->clean_val : get_option('fupi_cook');
        $this->pp_ok            = $this->pp_ok(); // ! $this->cook has to be set before

        $this->is_first_reg     = empty( $opts['is_first_reg'] ) ? false : true;
        $this->cdb_key          = ! empty( $opts['cdb_key'] ) ? esc_attr( $opts['cdb_key'] ) : ( empty ( $this->proofrec['cdb_key'] ) ? false : esc_attr( $this->proofrec['cdb_key'] ) );

        $this->tools            = $this->clean_val_id == 'tools' && $this->clean_val !== false ? $this->clean_val : get_option('fupi_tools'); 
        $this->main             = $this->clean_val_id == 'main' && $this->clean_val !== false ? $this->clean_val : get_option('fupi_main'); 
        $this->track            = $this->clean_val_id == 'track' && $this->clean_val !== false ? $this->clean_val : get_option('fupi_track');
        $this->gtag             = $this->clean_val_id == 'gtag' && $this->clean_val !== false ? $this->clean_val : get_option('fupi_gtag');
        

        $this->get_modules_data();

        // Check if WooCommerce is enabled
        $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
        $this->woo_enabled = false;
        
        if ( ! empty ( $this->tools['woo'] ) ) {
            if ( function_exists( 'wp_get_active_and_valid_plugins' ) && in_array( $plugin_path, wp_get_active_and_valid_plugins() ) ) {
                $this->woo_enabled = true;
            } else if ( function_exists( 'wp_get_active_network_plugins' ) && in_array( $plugin_path, wp_get_active_network_plugins() ) ) {
                $this->woo_enabled = true;
            }
        }
    }

    private function pp_ok(){
        
        if ( ! empty( $this->cook['pp_id'] ) ) {
            $pp_id = (int) $this->cook['pp_id'];
            return get_post_status( $pp_id ) == 'publish';
        }

        return false;
    }

    private function process_data(){
        
        $this->check_cook();
        $this->check_shortcodes();
        $this->check_proofrec();
        $this->check_cscr();
        $this->check_safefonts();
        $this->check_woo();

        // these modules show sections only if they are enabled
        $this->check_other_modules();
        
        if ( $this->format !== 'cdb' ) $this->add_extra_info_section(); // adds a section about the reCaptcha
    }

    public function get_html(){
        
        $this->format = 'html';
        $this->process_data();
        $output = '';

        foreach ( $this->data as $module_id => $checked_module_data ) {

            // TITLE
            $output .= '<section>
                <h3>' . $checked_module_data['module_name'] . '</h3>';

                // TOP COMMENT
                if ( isset( $checked_module_data['top comments'] ) ) {
                    foreach ( $checked_module_data['top comments'] as $str ) {
                        $output .= '<p style="font-size: 17px;">' . $str . '</p>';
                    };
                }

                if ( ! empty( $checked_module_data['setup'] ) || ! empty( $checked_module_data['tracked_extra_data'] ) || ! empty( $checked_module_data['pp comments'] ) || isset( $checked_module_data['opt-setup'] ) || isset( $checked_module_data['pre-setup'] ) ) {

                    // TABLE START

                    $output .= '<table class="fupi_classic_table">
                        <tbody>';

                        // PRE SETUP
                        if ( isset( $checked_module_data['pre-setup'] ) ) {
                            foreach ( $checked_module_data['pre-setup'] as $arr ) { 
                                $output .= '<tr>
                                    <td class="fupi_module_status_ico"><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span></td>
                                    <td>' . $arr[0] . '</td>
                                </tr>';
                            };
                        }

                        // SETUP INFO

                        if ( ! empty ( $checked_module_data['setup']) ) {
                            foreach ( $checked_module_data['setup'] as $setup_a ) {

                                $descr = '';
                                $icon = '';

                                if ( ! empty( $setup_a[0] ) ) {
                                    switch ( $setup_a[0] ) {
                                        case 'info':
                                            $icon = '<span class="dashicons dashicons-star-filled" style="color:#369; font-size: 20px;"></span>';
                                        break;
                                        case 'warning':
                                            $icon = '<span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span>';
                                        break;
                                        case 'alert':
                                            $icon = '<span class="dashicons dashicons-warning" style="color:red; font-size: 20px;"></span>';
                                        break;
                                        default:
                                            $icon = '<span class="dashicons dashicons-lightbulb" style="color:#a7a7a7; font-size: 20px;"></span>';
                                            // $icon = '<span class="dashicons dashicons-yes-alt" style="color:green; font-size: 20px;"></span>';
                                        break;
                                    }
                                }

                                $recommendation = empty( $setup_a[2] ) ? '' : '<p class="fupi_module_extra_descr">' . $setup_a[2] . '</p>'; // class="fupi_module_status_recommend"

                                $output .= '<tr>
                                    <td class="fupi_module_status_ico">' . $icon . '</td>
                                    <td>' . $setup_a[1] . $recommendation . '</td>
                                </tr>';
                            }
                            
                        }

                        // OPTIONAL SETUP INFO
                        if ( isset( $checked_module_data['opt-setup'] ) ) {
                            foreach ( $checked_module_data['opt-setup'] as $arr ) {

                                // previously <p> had class="fupi_module_status_recommend"
                                $output .= '<tr>
                                    <td class="fupi_module_status_ico"><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span></td>
                                    <td>' . $arr[0] . '</td>
                                </tr>';
                            };
                        }

                        // PP INFO
                        
                        if ( ! empty( $checked_module_data['tracked_extra_data'] ) || ! empty( $checked_module_data['pp comments'] ) ) {
                            
                            if ( ! empty( $checked_module_data['tracked_extra_data'] ) ) {
                                
                                $output .= '<tr>
                                <td class="fupi_module_status_ico">
                                    <span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>
                                </td>
                                <td>
                                    ' . esc_html__('Add to your privacy policy information about additional information tracked and sent to the tool:', 'full-picture-analytics-cookie-notice') .'
                                    <ul style="padding-left: 30px; list-style-type: circle;">';

                                        foreach ( $checked_module_data['tracked_extra_data'] as $pp_text ) {
                                            $output .= '<li>' . $pp_text . '</li>';
                                        }

                                $output .= '</ul>
                                    </td>   
                                </tr>';
                            };

                            if ( ! empty( $checked_module_data['pp comments'] ) ) { 
                                foreach ( $checked_module_data['pp comments'] as $comment ) {
                                    $output .= '<tr>
                                        <td class="fupi_module_status_ico">
                                            <span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>
                                        </td>
                                        <td>';

                                            if ( gettype( $comment ) == 'array' ) {
                                                $output .= $comment[0];
                                                if ( ! empty( $comment[1] ) && gettype( $comment[1] ) == 'array' ) {
                                                    $output .= '<ul style="padding-left: 30px; list-style-type: circle;">';
                                                        foreach ( $comment[1] as $li ) {
                                                            $output .= '<li>' . $li . '</li>';
                                                    }
                                                }
                                                $output .= '</ul>';
                                            } else {
                                                $output .= $comment;
                                            }

                                            // /**/

                                            // if ( gettype( $comment ) == 'array' ) {
                                            //     $output .= '<ul style="padding-left: 30px; list-style-type: circle;">';
                                            //     foreach ( $comment as $li ) {
                                            //         $output .= '<li>' . $li . '</li>';
                                            //     }
                                            //     $output .= '</ul>';
                                            // } else {
                                            //     $output .= '<p>' . $comment . '</p>';
                                            // }
                                        $output .= '</ul>
                                    </td>   
                                </tr>';
                                }
                            };
                        }

                        // TABLE END
                        $output .= '</tbody>
                    </table>';
                }

                // BOTTOM COMMENTS

                if ( isset( $checked_module_data['bottom comments'] ) ) {
                    foreach ( $checked_module_data['bottom comments'] as $str ) {
                        $output .= '<p>' . $str . '</p>';
                    };
                }

            $output .='</section>';
        }

        return $output;
    }

    public function send_and_return_status(){

        if ( ! $this->pp_ok ) {
            trigger_error('[FP] Privacy policy is not published. Recording consents is disabled.');
            return;
        }
        
        $this->format = 'cdb';
        $send_to = $this->get_sending_location(); // either "email" or "cdb'
        
        if ( $send_to === false ) return false;

        // get email address if it is not available in the options
        if ( $send_to == 'email' ) {
            if ( ! empty( $opts['email_to'] ) ) {
                $email_to = $opts['email_to'];
            } else {
                if ( ! empty ( $this->proofrec['local_backup_email'] ) ) {
                    $email_to = $this->proofrec['local_backup_email'];
                } else {
                    $email_to = get_option( 'admin_email' );
                }
            }
        }

        $this->process_data();

        foreach ( $this->data as $module_id => $module_data ) {

            // remove pre-setup
            if ( ! empty( $module_data['pre-setup'] ) ) {
                // unset empty extra data
                unset( $this->data[$module_id]['pre-setup'] );
            }

            // remove opt-setup
            if ( ! empty( $module_data['opt-setup'] ) ) {
                // unset empty extra data
                unset( $this->data[$module_id]['opt-setup'] );
            }

            // unset empty extra data
            if ( empty( $module_data['tracked_extra_data'] ) ) {
                unset( $this->data[$module_id]['tracked_extra_data'] );
            }

            // Remove the whole module if it has no data other then 'name'
            if ( empty( $module_data['setup'] ) ) {
                unset( $this->data[$module_id]['setup'] );
            }

            // check if module has content
            $has_content = false;

            // go over all elements of $module_data array
            foreach ( $module_data as $key => $value ) {
                // check if it's not the name
                if ( $key != 'name' ) {
                    // check if is empty
                    if ( ! empty( $value ) ) {
                        $has_content = true;
                    }
                }
            }

            // unset if it has no content
            if ( ! $has_content ) {
                unset( $this->data[$module_id] );
            }

            // get only the main text from "setup"

            if ( ! empty( $module_data['setup'] ) ) {
                
                $new_setup_data = [];
                
                foreach ( $module_data['setup'] as $arr ) {
                    $new_setup_data[] = $arr[1];
                }

                $this->data[$module_id]['setup'] = $new_setup_data;
            }

            // remove pp comments

            if ( ! empty( $module_data['pp comments'] ) ) {
                unset( $this->data[$module_id]['pp comments'] );
            }

            // remove top comments

            if ( ! empty( $module_data['top comments'] ) ) {
                unset( $this->data[$module_id]['top comments'] );
            }
        }

        // generate md5
        $encoded_data = json_encode( $this->data );
        $md5 = md5( $encoded_data );

        // trigger_error(' Sending config - encoded data: ' . $encoded_data );

        // sent settings data if settings with this MD5 have not been sent yet
        $versions_opts = get_option('fupi_versions');

        if ( $this->is_first_reg || ( ! empty( $versions_opts ) && ( empty( $versions_opts['md5'] ) || $versions_opts['md5'] !== $md5 ) ) ) {

            // Update MD5
            $versions_opts['md5'] = $md5;
            update_option( 'fupi_versions', $versions_opts );
            
            // add MD5 && WP FP version number

            include_once FUPI_PATH . '/admin/modules/proofrec/proofrec-sender.php';
            $proofrec_sender = new Fupi_PROOFREC_send();
            
            if ( $send_to == 'email' ) { 
                $sending_status = $proofrec_sender->send_config_to_email( $email_to, $this->data, $md5 );
                return $sending_status;
            } else {
                $this->data['md5'] = $md5;
                $this->data['wpfpVersion'] = FUPI_VERSION;
                $sending_status = $proofrec_sender->send_config_to_cdb( $this->data, $this->cdb_key );

                return $sending_status;
            }
        }
    }

    //
    // HELPERS
    //

    private function get_modules_data() {
        include FUPI_PATH . '/includes/fupi_modules_data.php';
        include FUPI_PATH . '/includes/fupi_modules_names.php';
        $this->modules_info = $fupi_modules;
        $this->modules_names = $fupi_modules_names;
    }

    private function get_module_info( $id ) {
        foreach ( $this->modules_info as $module_info ) {
            if ( $module_info['id'] == $id ) {
                return $module_info;
            }
        }

        return false; // module not found
    }

    private function get_module_loading_status( $id, $info, $settings ) {

        // If the tools is force loaded  
        if ( isset( $settings['force_load'] ) ) {

            if ( $this->format == 'cdb' ) {
                $t_force_1 = 'The tool is force-loaded for all visitors.';
            } else {
                $t_force_1 = esc_html__('The tool is set to "force load" and track all visitors. Disable it in the module\'s settings.', 'full-picture-analytics-cookie-notice');
            }

            return [ 'alert', $t_force_1 ];

        // If the tools is NOT force loaded
        } else {

            if ( $this->format == 'cdb' ) {
                $main_text = 'This tool requires consents to:';
                $t_cook_4 = 'It tracks additional data after visitors agree to:';
            } else {
                $main_text = esc_html__('This tool requires consents to:', 'full-picture-analytics-cookie-notice');
                $t_cook_4 = esc_html__('It tracks additional data after visitors agree to:', 'full-picture-analytics-cookie-notice');
            }

            // paste required consents
            if ( isset( $info['consents'] ) ) {
                $main_text .= ' ' . join( ', ', $info['consents'] ) . '. ';
                if ( isset( $info['opt_consents'] ) ) {
                    $main_text .=  $t_cook_4 . ' ' .  join( ', ', $info['opt_consents'] );
                }
                return [ 'ok', $main_text ];
            };

            return false;
        }
    }

    private function check_url_passthrough(){
            
        if ( ! empty( $this->gtag['url_passthrough'] ) ) {

            if ( $this->format == 'cdb' ) {
                $t_warning_1 = 'Link decoration is enabled in the Google Tag settings.';
            } else {
                $t_warning_1 = esc_html__('Link decoration is enabled in the Google Tag settings. This setting is a privacy grey area. Make sure you are not breaking any laws by using it. Otherwise, disable it.', 'full-picture-analytics-cookie-notice');
            }

            return [ 'warning', $t_warning_1 ];
        }

        return false;
    }

    private function fupi_modify_cons_banner_text( $text ){

        $open_tag_pos = strpos($text, '{{');
        $close_tag_pos = strpos($text, '}}');
    
        if ( $open_tag_pos !== false && $close_tag_pos !== false ) {
    
            // get the content between {{ }}
            $regex = '/\{\{(.*?)\}\}/';
    
            // Replace matches with anchor tags using preg_replace
            $text = preg_replace_callback($regex, function($match) {
                
                $innerText = $match[1]; // Capture inner text
                $url = false;

                if ( ! empty( $this->cook['pp_id'] ) ) {

                    $pp_id = (int) $this->cook['pp_id'];
                    $pp_post = get_post( $pp_id );
            
                    if ( ! empty ( $pp_post ) && isset( $pp_post->post_status ) && $pp_post->post_status === 'publish' ) {
                        $url = get_permalink( $pp_post );
                    }
                }

                // get URL and create a link
                if ( strpos( $innerText, '|') !== false ) {
                    $innerText_a = explode( '|', $innerText );
                    if ( ! empty( $innerText_a[1] ) ) {
                        $url = $innerText_a[1];
                        $innerText = $innerText_a[0];
                    } 
                }

                if ( $url === false ) return $innerText;
    
                return "<a href=\"$url\">$innerText</a>";
    
            }, $text);
        }
    
        return do_shortcode( $text );
    }

    private function get_tracked_usermeta( $id, $settings, $priv = false ) {
        
        if ( empty( $this->track['custom_data_ids'] ) ) return false;

        $var_name = $id == 'clar' ? 'tag_cf' : 'track_cf';
        $tracked_usermeta = [];

        // this checks if the tool is set to track any user-type custom fields
        if ( isset( $settings[$var_name] ) && is_array( $settings[$var_name] ) ) {
            foreach ( $settings[$var_name] as $tracked_meta ) {
                if ( substr( $tracked_meta['id'], 0, 5 ) == 'user|' ) {

                    $usermeta_id = substr( $tracked_meta['id'], 5 );
                    
                    // this checks if this meta is still set-up in the "shared tracking settings"
                    foreach( $this->track['custom_data_ids'] as $tracked_meta_arr ) {
                        if ( $tracked_meta_arr['id'] == $usermeta_id ) {
                            $tracked_usermeta[] = $usermeta_id;
                            break;
                        }
                    }
                }
            }
        }

        if ( count( $tracked_usermeta ) > 0 ) {
            
            if ( $this->format == 'cdb' ) {
                $t_meta ='User metadata with ID: ';
            } else {
                $t_meta = esc_html__('User metadata with ID: ', 'full-picture-analytics-cookie-notice');
            }

            return $t_meta . join( ', ', $tracked_usermeta );

        } else {
            return false;
        }

        // if ( $priv && $tracks_meta ) {
        //     $this->data[$id]['pp comments'][] = $priv;
        // }
    }

    private function req_data_is_provided( $module_settings ) {

        $has_req = true;

        if ( ! empty( $this->modules_info['requires'] ) ) {
            foreach ( $this->modules_info['requires'] as $req_field_id ) {
                if ( empty( $module_settings[$req_field_id] ) ) {
                    $has_req = false;
                    break;
                }
            }
        };
        
        return $has_req;
    }

    private function set_basic_module_info( $module_id, $module_info ){
        $this->data[$module_id] = [ 
            'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names[$module_id],
            'setup' => [],
            'tracked_extra_data' => [],
        ];
    }

    //
    // CHECK MODULES
    //

    // Always include info from these modules even if they are not active

    private function check_cook(){
        if ( $this->format == 'cdb' ) {
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check-cdb.php';
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check-cdb-iframeblock.php';
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check-cdb-blockscr.php';
        } else {
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check.php';
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check-iframeblock.php';
            include FUPI_PATH . '/admin/modules/cook/cook-gdpr-check-blockscr.php';
        }
    }

    private function check_proofrec(){
        if ( $this->format != 'cdb' ) {
            include FUPI_PATH . '/admin/modules/proofrec/proofrec-gdpr-check-html.php';
        }
    } 

    private function check_cscr(){
        if ( $this->format == 'cdb' ) {
            include FUPI_PATH . '/admin/modules/cscr/cscr-gdpr-check-cdb.php';
        } else {
            include FUPI_PATH . '/admin/modules/cscr/cscr-gdpr-check.php';
        }
    } 

    private function check_safefonts(){
        if ( $this->format != 'cdb' ) {
            include FUPI_PATH . '/admin/modules/safefonts/safefonts-gdpr-check.php';
        }
    } 

    private function check_shortcodes(){
        // handles CDB and HTML format
        include FUPI_PATH . '/admin/modules/track/track-gdpr-check.php';
    } 

    private function check_woo(){
        // handles CDB and HTML format
        include FUPI_PATH . '/admin/modules/woo/woo-gdpr-check.php';
    } 

    //
    // INTEGRATION MODULES STATUS
    //

    private function check_other_modules() {

        foreach ( $this->tools as $module_id => $module_val ) {

            // STOP if the module has nothing to do with GDPR or is always checked (see fns above)
            $module_info = $this->get_module_info( $module_id );
            if ( $module_info === false || empty( $module_info['check_gdpr'] ) || $module_info['check_gdpr'] === 'always' ) continue;
            
            // STOP if a module has no settings even though has a settings page
            $module_settings = $this->clean_val_id == $module_id && ! empty( $this->clean_val ) ? $this->clean_val : get_option( 'fupi_' . $module_id );
            if ( ! empty( $module_info['has_admin_page'] ) && empty( $module_settings ) ) continue;

            // STOP if required data is not provided
            if ( ! $this->req_data_is_provided( $module_settings ) ) continue;

            // // STOP if module is GA4 #2
            // if ( $module_id == 'ga42' ) continue;

            // Run method
            $method_name = "check_{$module_id}";
            $this->$method_name( $module_info, $module_settings );
        }
    }

    private function check_cegg( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/cegg/cegg-gdpr-check.php';
    }

    private function check_tik( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/tik/tik-gdpr-check.php';
    }

    private function check_linkd( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/linkd/linkd-gdpr-check.php';
    }

    private function check_posthog( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/posthog/posthog-gdpr-check.php';
    }

    private function check_gads( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/gads/gads-gdpr-check.php';
    }

    private function check_ga41( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/ga41/ga41-gdpr-check.php';
    }

    private function check_ga42( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/ga42__premium_only/ga42-gdpr-check.php';
    }

    private function check_hotj( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/hotj/hotj-gdpr-check.php';
    }

    private function check_insp( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/insp/insp-gdpr-check.php';
    }

    private function check_mato( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/mato/mato-gdpr-check.php';
    }

    private function check_fbp1( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/fbp1/fbp1-gdpr-check.php';
    }

    private function check_mads( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/mads/mads-gdpr-check.php';
    }

    private function check_clar( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/clar/clar-gdpr-check.php';
    }

    private function check_pin( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/pin/pin-gdpr-check.php';
    }

    private function check_pla( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/pla/pla-gdpr-check.php';
    }

    private function check_twit( $module_info, $settings ){
        include FUPI_PATH . '/admin/modules/twit/twit-gdpr-check.php';
    }

    private function check_gtm( $module_info, $settings ){
        if ( $this->woo_enabled ) include FUPI_PATH . '/admin/modules/gtm/gtm-gdpr-check.php';
    }

    private function add_extra_info_section(){
        
        $this->data['other'] = [ 
            'module_name' => esc_attr__( 'Other recommendations', 'full-picture-analytics-cookie-notice' ),
            'setup' => [],
            'opt-setup' => [
                [
                    esc_attr__( 'Make sure that you do not use Google reCaptcha. It does not comply with GDPR and there is no known method of making it comply with it. Replace it with a GDPR compliant solution like Cloudflare Turnstile (free and paid) or Friendly Captcha (paid for commercial use). Attention. You may read online, that there are ways to make Google reCaptcha compatible with GDPR. This is not true. The proposed solution of conditionally loading reCaptcha\'s scripts prevents access to content if visitors do not agree to tracking, which is against GDPR.', 'full-picture-analytics-cookie-notice' )
                ],
                [
                    esc_attr__( 'Add a checkbox to forms on your website. It should come with text "I agree to the [Privacy Policy](link-to-your-policy) and consent to [Your Company Name] using the data from this form to [Purpose]." or similar. The checkbox cannot be pre-checked.', 'full-picture-analytics-cookie-notice' )
                ]
            ],
            'tracked_extra_data' => [],
        ];
    }

    private function get_sending_location(){

        if ( ! empty ( $this->proofrec['storage_location'] ) && $this->proofrec['storage_location'] == 'email' ) {
            return 'email';
        } else if ( ! empty ( $this->proofrec['cdb_key'] ) ) {
            return 'cdb';
        };

        return false;
    }
}