<?php

class Fupi_compliance_status_checker {
    private $modules_info = [];

    private $tools = [];

    private $data = [];

    private $consent_status = 'alert';

    private $req_consent_banner = 'no';

    private $url_pass_enabled = false;

    private $consb_settings;

    private $format;

    private $cdb_key;

    private $is_first_reg;

    private $modules_names;

    public function __construct(
        $output_format,
        $cook_settings = false,
        $is_first_reg = false,
        $latest_enabled_tools_data = false
    ) {
        $this->is_first_reg = $is_first_reg;
        $this->format = $output_format;
        $this->consb_settings = ( empty( $cook_settings ) ? get_option( 'fupi_cook' ) : $cook_settings );
        $this->cdb_key = ( !empty( $this->consb_settings['cdb_key'] ) ? $this->consb_settings['cdb_key'] : false );
        $this->include_modules_datafile();
        $this->get_enabled_modules( $latest_enabled_tools_data );
        $this->check_cons_banner_module();
        // goes second
        // $this->data = apply_filters( 'fupi_gdpr_status', $this->data, $this->format );
        $this->check_custom_scripts_module();
        $this->check_iframeblock_module();
        $this->check_blockscr_module();
        $this->check_safefonts_module();
        $this->check_woo_module();
        $this->check_other_modules();
        $this->add_extra_info_section();
        $this->is_cons_banner_req();
        $this->output();
    }

    private function is_cons_banner_req() {
        $cook_data = [];
        if ( !in_array( 'cook', $this->tools ) ) {
            $info = $this->get_module_info( 'cook' );
            if ( $this->format == 'cdb' ) {
                $t_alert_1 = 'Consent banner must be enabled for your setup';
                $t_alert_2 = 'Please enable it in either opt-in mode or one of automatic modes';
                $t_warning_1 = 'You may need to use the Consent Banner module on your website.';
                $t_warning_2 = 'Enable it if you use tracking tools on your website, live chat applications, social buttons, social login options or your website loads content from other sites (YouTube video, maps, etc.).';
            } else {
                $t_alert_1 = esc_html__( 'Consent banner must be enabled for your setup', 'full-picture-analytics-cookie-notice' );
                $t_alert_2 = esc_html__( 'Please enable it in either opt-in mode or one of automatic modes', 'full-picture-analytics-cookie-notice' );
                $t_warning_1 = esc_html__( 'You may need to use the Consent Banner module on your website.', 'full-picture-analytics-cookie-notice' );
                $t_warning_2 = esc_html__( 'Enable it if you use tracking tools on your website, live chat applications, social buttons, social login options or your website loads content from other sites (YouTube video, maps, etc.).' );
            }
            $cook_module_name = ( $this->format == 'cdb' ? 'Consent Banner' : $this->modules_names['cook'] );
            switch ( $this->req_consent_banner ) {
                case 'yes':
                    $cook_data = [
                        'module_name' => $cook_module_name,
                        'setup'       => [['alert', $t_alert_1, $t_alert_2]],
                    ];
                    break;
                default:
                    $cook_data = [
                        'module_name' => $cook_module_name,
                        'setup'       => [['warning', $t_warning_1, $t_warning_2]],
                    ];
                    break;
            }
            array_unshift( $this->data, $cook_data );
        }
    }

    private function include_modules_datafile() {
        include FUPI_PATH . '/includes/fupi_modules_data.php';
        include FUPI_PATH . '/includes/fupi_modules_names.php';
        $this->modules_info = $fupi_modules;
        $this->modules_names = $fupi_modules_names;
    }

    private function get_extra_text( $status = false ) {
        // TRANSLATIONS
        if ( $this->format == 'cdb' ) {
            $t_fixerrors_1 = 'Fix errors in consent banner settings.';
            $t_fixerrors_2 = 'Make sure the banner is set up correctly.';
        } else {
            $t_fixerrors_1 = esc_html__( 'Fix errors in consent banner settings.', 'full-picture-analytics-cookie-notice' );
            $t_fixerrors_2 = esc_html__( 'Make sure the banner is set up correctly.', 'full-picture-analytics-cookie-notice' );
        }
        if ( empty( $status ) ) {
            $status = $this->consent_status;
        }
        if ( $status == 'alert' ) {
            return $t_fixerrors_1;
        } else {
            if ( $status == 'warning' ) {
                return $t_fixerrors_2;
            }
        }
        return '';
    }

    private function get_enabled_modules( $latest_enabled_tools_data ) {
        $tools = ( empty( $latest_enabled_tools_data ) ? get_option( 'fupi_tools' ) : $latest_enabled_tools_data );
        if ( !empty( $tools ) ) {
            $this->tools = array_keys( $tools );
        }
    }

    private function get_module_info( $id ) {
        foreach ( $this->modules_info as $module_info ) {
            if ( $module_info['id'] == $id ) {
                return $module_info;
            }
        }
    }

    private function fupi_modify_cons_banner_text( $text ) {
        $open_tag_pos = strpos( $text, '{{' );
        $close_tag_pos = strpos( $text, '}}' );
        if ( $open_tag_pos && $close_tag_pos ) {
            // get the content between {{ }}
            $regex = '/\\{\\{(.*?)\\}\\}/';
            // Replace matches with anchor tags using preg_replace
            $text = preg_replace_callback( $regex, function ( $match ) {
                $innerText = $match[1];
                // Capture inner text
                $url = get_privacy_policy_url();
                // get URL and create a link
                if ( strpos( $innerText, '|' ) > 0 ) {
                    $innerText_a = explode( '|', $innerText );
                    if ( !empty( $innerText_a[1] ) ) {
                        $url = $innerText_a[1];
                        $innerText = $innerText_a[0];
                    }
                }
                return "<a href=\"{$url}\">{$innerText}</a>";
            }, $text );
        }
        return do_shortcode( $text );
    }

    private function output() {
        if ( $this->format == 'cdb' ) {
            foreach ( $this->data as $module_id => $module_data ) {
                // remove pre-setup
                if ( !empty( $module_data['pre-setup'] ) ) {
                    // unset empty extra data
                    unset($this->data[$module_id]['pre-setup']);
                }
                // remove opt-setup
                if ( !empty( $module_data['opt-setup'] ) ) {
                    // unset empty extra data
                    unset($this->data[$module_id]['opt-setup']);
                }
                // join extra data
                if ( !empty( $module_data['tracked_extra_data'] ) ) {
                    $new_extra_data = [];
                    foreach ( $module_data['tracked_extra_data'] as $arr ) {
                        $new_extra_data[] = $arr[0];
                    }
                    $this->data[$module_id]['tracked_extra_data'] = $new_extra_data;
                } else {
                    // unset empty extra data
                    unset($this->data[$module_id]['tracked_extra_data']);
                }
                // Remove the whole module if it has no data other then 'name'
                if ( empty( $module_data['setup'] ) ) {
                    unset($this->data[$module_id]);
                    continue;
                }
                // check if module has content
                $has_content = false;
                // go over all elements of $module_data array
                foreach ( $module_data as $key => $value ) {
                    // check if it's not the name
                    if ( $key != 'name' ) {
                        // check if is empty
                        if ( !empty( $value ) ) {
                            $has_content = true;
                        }
                    }
                }
                // unset if it has no content
                if ( !$has_content ) {
                    unset($this->data[$module_id]);
                }
                // join setup
                if ( !empty( $module_data['setup'] ) ) {
                    $new_setup_data = [];
                    foreach ( $module_data['setup'] as $arr ) {
                        $new_setup_data[] = $arr[1];
                    }
                    $this->data[$module_id]['setup'] = $new_setup_data;
                }
                // remove pp comments
                if ( !empty( $module_data['pp comments'] ) ) {
                    unset($this->data[$module_id]['pp comments']);
                }
                // remove top comments
                if ( !empty( $module_data['top comments'] ) ) {
                    unset($this->data[$module_id]['top comments']);
                }
            }
            // generate md5
            $md5 = md5( json_encode( $this->data ) );
            // sent settings data if settings with this MD5 have not been sent yet
            $versions_opts = get_option( 'fupi_versions' );
            if ( $this->is_first_reg || !empty( $versions_opts ) && (empty( $versions_opts['md5'] ) || $versions_opts['md5'] !== $md5) ) {
                // add MD5 && WP FP version number
                $this->data['md5'] = $md5;
                $this->data['wpfpVersion'] = $versions_opts[0];
                // send request
                $header_arr = ['Content-Type: application/json', 'x-api-key: ' . $this->cdb_key];
                $payload = [
                    'wpfpSettings' => $this->data,
                ];
                $payload['installID'] = fupi_fs()->get_site()->id;
                $json_payload = json_encode( $payload );
                // trigger_error( $json_payload );
                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://prod-fr.consentsdb.com/api/configuration/new' );
                // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_payload );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header_arr );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                $server_output = curl_exec( $ch );
                curl_close( $ch );
                $serverReponseObject = json_decode( $server_output );
                // save in an option
                if ( $serverReponseObject->status == 'success' ) {
                    trigger_error( '[FP] Plugin configuration sent to ConsentsDB' );
                    $versions_opts['md5'] = $md5;
                    update_option( 'fupi_versions', $versions_opts );
                } else {
                    trigger_error( '[FP] There was an error registering the site to the ConsentsDB. Response object: ' . json_encode( $serverReponseObject->status ) );
                    add_settings_error(
                        'fupi_cook',
                        'settings_updated',
                        esc_attr__( 'There was an error registering the site in ConsentsDB. Save the secret key and try again.', 'full-picture-analytics-cookie-notice' ),
                        'error'
                    );
                    if ( !empty( $clean_data ) ) {
                        unset($clean_data['cdb_key']);
                        $this->cdb_key = false;
                        return $clean_data;
                    }
                }
            }
        } else {
            if ( $this->format == 'html' ) {
                $output = '';
                foreach ( $this->data as $module_id => $checked_module_data ) {
                    // TITLE
                    $output .= '<section>
                    <h3>' . $checked_module_data['module_name'] . '</h3>';
                    // TOP COMMENT
                    if ( isset( $checked_module_data['top comments'] ) ) {
                        foreach ( $checked_module_data['top comments'] as $str ) {
                            $output .= '<p style="font-size: 15px;">' . $str . '</p>';
                        }
                    }
                    // PRE SETUP
                    if ( isset( $checked_module_data['pre-setup'] ) ) {
                        // TABLE START
                        $output .= '<table class="fupi_classic_table">
                        <tbody>';
                        foreach ( $checked_module_data['pre-setup'] as $arr ) {
                            $output .= '<tr>
                                <td class="fupi_module_status_ico"><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span></td>
                                <td>' . $arr[0] . '<p class="fupi_module_status_recommend">' . $arr[1] . '</p></td>
                            </tr>';
                        }
                        $output .= '</tbody>
                        </table>';
                    }
                    if ( !empty( $checked_module_data['setup'] ) || !empty( $checked_module_data['tracked_extra_data'] ) || !empty( $checked_module_data['pp comments'] ) || isset( $checked_module_data['opt-setup'] ) ) {
                        // TABLE START
                        $output .= '<table class="fupi_classic_table">
                            <tbody>';
                        // SETUP INFO
                        if ( !empty( $checked_module_data['setup'] ) ) {
                            foreach ( $checked_module_data['setup'] as $setup_a ) {
                                $descr = '';
                                $icon = '';
                                if ( !empty( $setup_a[0] ) ) {
                                    switch ( $setup_a[0] ) {
                                        case 'warning':
                                            $icon = '<span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span>';
                                            break;
                                        case 'alert':
                                            $icon = '<span class="dashicons dashicons-warning" style="color:red; font-size: 20px;"></span>';
                                            break;
                                        default:
                                            $icon = '<span class="dashicons dashicons-yes-alt" style="color:green; font-size: 20px;"></span>';
                                            break;
                                    }
                                }
                                $recommendation = ( empty( $setup_a[2] ) ? '' : '<p class="fupi_module_status_recommend">' . $setup_a[2] . '</p>' );
                                $output .= '<tr>
                                        <td class="fupi_module_status_ico">' . $icon . '</td>
                                        <td>' . $setup_a[1] . $recommendation . '</td>
                                    </tr>';
                            }
                        }
                        // OPTIONAL SETUP INFO
                        if ( isset( $checked_module_data['opt-setup'] ) ) {
                            foreach ( $checked_module_data['opt-setup'] as $arr ) {
                                $output .= '<tr>
                                        <td class="fupi_module_status_ico"><span class="dashicons dashicons-flag" style="color:orange; font-size: 20px;"></span></td>
                                        <td>' . $arr[0] . '<p class="fupi_module_status_recommend">' . $arr[1] . '</p></td>
                                    </tr>';
                            }
                        }
                        // PP INFO
                        if ( !empty( $checked_module_data['tracked_extra_data'] ) || !empty( $checked_module_data['pp comments'] ) ) {
                            if ( !empty( $checked_module_data['tracked_extra_data'] ) ) {
                                $output .= '<tr>
                                    <td class="fupi_module_status_ico">
                                        <span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>
                                    </td>
                                    <td>
                                        ' . esc_html__( 'Add to your privacy policy information about additional, personaly identifiable information tracked and sent to the tool:', 'full-picture-analytics-cookie-notice' ) . '
                                        <ul style="padding-left: 30px; list-style-type: circle;">';
                                foreach ( $checked_module_data['tracked_extra_data'] as $pp_arr ) {
                                    $output .= '<li>' . $pp_arr[0] . '</li>';
                                }
                                $output .= '</ul>
                                        </td>   
                                    </tr>';
                            }
                            if ( !empty( $checked_module_data['pp comments'] ) ) {
                                foreach ( $checked_module_data['pp comments'] as $comment ) {
                                    $output .= '<tr>
                                            <td class="fupi_module_status_ico">
                                                <span class="dashicons dashicons-welcome-write-blog" style="font-size: 20px; color: #6d2974"></span>
                                            </td>
                                            <td>';
                                    if ( gettype( $comment ) == 'array' ) {
                                        $output .= '<p>' . $comment[0] . '</p>';
                                        if ( !empty( $comment[1] ) && gettype( $comment[1] ) == 'array' ) {
                                            $output .= '<ul style="padding-left: 30px; list-style-type: circle;">';
                                            foreach ( $comment[1] as $li ) {
                                                $output .= '<li>' . $li . '</li>';
                                            }
                                        }
                                        $output .= '</ul>';
                                    } else {
                                        $output .= '<p>' . $comment . '</p>';
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
                            }
                        }
                        // TABLE END
                        $output .= '</tbody>
                        </table>';
                    }
                    // BOTTOM COMMENTS
                    if ( isset( $checked_module_data['bottom comments'] ) ) {
                        foreach ( $checked_module_data['bottom comments'] as $str ) {
                            $output .= '<p>' . $str . '</p>';
                        }
                    }
                    $output .= '</section>';
                }
                echo $output;
            }
        }
    }

    private function track_metadata_IDs( $id, $settings, $priv = false ) {
        if ( !in_array( 'trackmeta', $this->tools ) ) {
            return;
        }
        // TRANSLATIONS
        if ( $this->format == 'cdb' ) {
            $t_meta = 'User metadata with ID: ';
        } else {
            $t_meta = esc_html__( 'User metadata with ID: ', 'full-picture-analytics-cookie-notice' );
        }
        $var_name = ( $id == 'clar' ? 'tag_cf' : 'track_cf' );
        $tracks_meta = false;
        if ( isset( $settings[$var_name] ) && is_array( $settings[$var_name] ) ) {
            foreach ( $settings[$var_name] as $tracked_meta ) {
                if ( substr( $tracked_meta['id'], 0, 5 ) == 'user|' ) {
                    $this->data[$id]['tracked_extra_data'][] = [$t_meta . substr( $tracked_meta['id'], 5 )];
                    $tracks_meta = true;
                }
            }
        }
        if ( $priv && $tracks_meta ) {
            $this->data[$id]['pp comments'][] = $priv;
        }
    }

    private function check_url_passthrough( $id ) {
        // TRANSLATIONS
        if ( $this->format == 'cdb' ) {
            $t_warning_1 = 'Link decoration is enabled in the consent banner settings.';
            $t_warning_2 = 'This setting is a privacy grey area. Make sure you are not breaking any laws while using it. Otherwise, disable link decoration in the settings of the consent banner.';
        } else {
            $t_warning_1 = esc_html__( 'Link decoration is enabled in the consent banner settings.', 'full-picture-analytics-cookie-notice' );
            $t_warning_2 = esc_html__( 'This setting is a privacy grey area. Make sure you are not breaking any laws while using it. Otherwise, disable link decoration in the settings of the consent banner.', 'full-picture-analytics-cookie-notice' );
        }
        if ( in_array( 'cook', $this->tools ) ) {
            if ( !empty( $this->consb_settings ) && isset( $this->consb_settings['url_passthrough'] ) ) {
                $this->data[$id]['setup'][] = ['warning', $t_warning_1, $t_warning_2];
            }
        }
    }

    private function req_data_is_provided( $module_settings ) {
        $has_req = true;
        if ( !empty( $this->modules_info['requires'] ) ) {
            foreach ( $this->modules_info['requires'] as $req_field_id ) {
                if ( empty( $module_settings[$req_field_id] ) ) {
                    $has_req = false;
                    break;
                }
            }
        }
        return $has_req;
    }

    private function set_basic_module_info( $module_id, $module_info ) {
        $this->data[$module_id] = [
            'module_name'        => ( $this->format == 'cdb' ? $module_info['name'] : $this->modules_names[$module_id] ),
            'setup'              => [],
            'tracked_extra_data' => [],
        ];
    }

    private function check_other_modules() {
        foreach ( $this->tools as $module_id ) {
            // STOP for consent banner (was checked before)
            if ( $module_id == 'cook' ) {
                continue;
            }
            // STOP if shouldn't be included in status
            $module_info = $this->get_module_info( $module_id );
            if ( !isset( $module_info['check_gdpr'] ) ) {
                continue;
            }
            // STOP if a module has no settings even though has a settings page
            $module_settings = get_option( 'fupi_' . $module_id );
            if ( !empty( $module_settings['has_admin_page'] ) && empty( $module_settings ) ) {
                continue;
            }
            // STOP if required data is not provided
            if ( !$this->req_data_is_provided( $module_settings ) ) {
                continue;
            }
            // Check modules
            switch ( $module_id ) {
                case 'gtm':
                    $this->set_basic_module_info( $module_id, $module_info );
                    $this->get_gtm_status( $module_info, $module_settings );
                    break;
                case 'privex':
                    $this->set_basic_module_info( $module_id, $module_info );
                    $this->get_privex_status( $module_info, $module_settings );
                    break;
                case 'blockscr':
                    break;
                default:
                    if ( $module_info['type'] == 'integr' ) {
                        $this->set_basic_module_info( $module_id, $module_info );
                        $this->get_integr_status( $module_id, $module_info, $module_settings );
                        // remember to include trackmeta data there too!
                    }
                    break;
            }
        }
    }

    private function add_extra_info_section() {
        // do not send to CDB - output only on the settings page
        if ( $this->format !== 'cdb' ) {
            $this->data['other'] = [
                'module_name'        => esc_attr__( 'Other recommendations', 'full-picture-analytics-cookie-notice' ),
                'setup'              => [],
                'opt-setup'          => [[esc_attr__( 'Google reCaptcha warning', 'full-picture-analytics-cookie-notice' ), esc_attr__( 'Google reCaptcha does not comply with GDPR and there is no known method of making it comply with it. Make sure to replace it with a GDPR compliant solution, for example Cloudflare Turnstile (free and paid) or Friendly Captcha (paid for commercial use). Attention. You may read online, that there are ways to make Google reCaptcha compatible with GDPR. This is not true. The proposed solution of conditionally loading reCaptcha\'s scripts prevents access to content if visitors do not agree to tracking, which is against GDPR.', 'full-picture-analytics-cookie-notice' )]],
                'tracked_extra_data' => [],
            ];
        }
    }

    //
    // PRIVACY EXTRAS
    //
    private function get_privex_status( $info, $settings ) {
        if ( !empty( $settings['extra_tools'] ) ) {
            $tools = [];
            foreach ( $settings['extra_tools'] as $tool ) {
                $tools[] = $tool['name'];
            }
            if ( $this->format == 'cdb' ) {
                $t_ok = 'These additional tracking tools are used on the website: ';
                $t_comment = 'Your privacy policy must include information about these tracking tools: ' . join( ', ', $tools ) . ' Inform your visitors what data they track, what do you and these tools use it for and who the providers of these tools share this data with or sell it to.';
            } else {
                $t_ok = esc_html__( 'These additional tracking tools are used on the website: ', 'full-picture-analytics-cookie-notice' );
                $t_comment = sprintf( esc_html__( 'Your privacy policy must include information about these tracking tools: %1$s Inform your visitors what data they track, what do you and these tools use it for and who the providers of these tools share this data with or sell it to.', 'full-picture-analytics-cookie-notice' ), join( ', ', $tools ) );
            }
            $this->data['privex']['setup'][] = ['ok', $t_ok . join( ', ', $tools )];
            $this->data['privex']['pp comments'][] = $t_comment;
        } else {
            if ( $this->format == 'cdb' ) {
                $t_setup = 'No information on additional tracking tools have been provided';
            } else {
                $t_setup = esc_html__( 'No information on additional tracking tools have been provided', 'full-picture-analytics-cookie-notice' );
            }
            $this->data['privex']['setup'][] = ['ok', $t_setup];
        }
    }

    //
    // INTEGRATION MODULES STATUS
    //
    private function get_integr_status( $id, $info, $settings ) {
        $req_consent = false;
        if ( $this->format == 'cdb' ) {
            $t_order_id = 'Order ID';
            $t_all_ok = 'This tool does not track personally identifiable information and does not need consent banner';
        } else {
            $t_order_id = esc_html__( 'Order ID', 'full-picture-analytics-cookie-notice' );
            $t_all_ok = esc_html__( 'This tool does not track personally identifiable information and does not need consent banner', 'full-picture-analytics-cookie-notice' );
        }
        if ( $id == 'cegg' ) {
            $req_consent = true;
            if ( isset( $settings['identif_users'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_cegg = 'ID of a logged in user';
                } else {
                    $t_cegg = esc_html__( 'ID of a logged in user', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_cegg];
            }
        }
        if ( $id == 'tik' ) {
            $req_consent = true;
        }
        if ( $id == 'linkd' ) {
            $req_consent = true;
        }
        if ( $id == 'posthog' ) {
            $req_consent = true;
            if ( $this->format == 'cdb' ) {
                $t_posthog_ok = 'Visitor\'s data is being kept on servers in the EU';
                $t_posthog_alert = 'Visitor\'s data is not being kept on servers in the EU';
            } else {
                $t_posthog_ok = esc_html__( 'Visitor\'s data is being kept on servers in the EU', 'full-picture-analytics-cookie-notice' );
                $t_posthog_alert = esc_html__( 'Visitor\'s data is not being kept on servers in the EU', 'full-picture-analytics-cookie-notice' );
            }
            if ( isset( $settings['data_in_eu'] ) ) {
                $this->data[$id]['setup'][] = ['ok', $t_posthog_ok];
            } else {
                $this->data[$id]['setup'][] = ['alert', $t_posthog_alert];
            }
        }
        if ( $id == 'gads' ) {
            $req_consent = true;
            if ( isset( $settings['enh_conv'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_gads_extra_1 = 'Real name, surname, phone number, email address and physical address of customers and logged-in users (enabled with Enhanced Conversions)';
                } else {
                    $t_gads_extra_1 = esc_html__( 'Real name, surname, phone number, email address and physical address of customers and logged-in users (enabled with Enhanced Conversions)', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_gads_extra_1];
            }
            if ( in_array( 'woo', $this->tools ) ) {
                $this->data[$id]['tracked_extra_data'][] = [$t_order_id];
            }
        }
        if ( $id == 'ga41' || $id == 'ga42' ) {
            $req_consent = true;
            $this->track_metadata_IDs( $id, $settings );
            if ( in_array( 'woo', $this->tools ) ) {
                $this->data[$id]['tracked_extra_data'][] = [$t_order_id];
            }
        }
        if ( $id == 'hotj' ) {
            $req_consent = true;
            if ( isset( $settings['identif_users'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_hotj = 'Unique ID (user email email and/or a user id)';
                } else {
                    $t_hotj = esc_html__( 'Unique ID (user email email and/or a user id)', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_hotj];
            }
            if ( isset( $settings['tag_woo_purchases_data'] ) && in_array( 'id', $settings['tag_woo_purchases_data'] ) ) {
                $this->data[$id]['tracked_extra_data'][] = [$t_order_id];
            }
        }
        if ( $id == 'insp' ) {
            $req_consent = true;
            if ( $this->format == 'cdb' ) {
                $t_insp_1 = 'The script loads an additional script for A/B testing which requires consent to using visitor\'s data for website personalisation.';
                $t_insp_2 = 'Unique ID (user email email, user id or username).';
            } else {
                $t_insp_1 = esc_html__( 'The script loads an additional script for A/B testing which requires consent to using visitor\'s data for website personalisation.', 'full-picture-analytics-cookie-notice' );
                $t_insp_2 = esc_html__( 'Unique ID (user email email, user id or username).', 'full-picture-analytics-cookie-notice' );
            }
            if ( isset( $settings['ab_test_script'] ) ) {
                $this->data[$id]['setup'][] = [$this->consent_status, $t_insp_1];
            }
            if ( isset( $settings['identif_users'] ) ) {
                $this->data[$id]['tracked_extra_data'][] = [$t_insp_2];
            }
        }
        if ( $id == 'mato' ) {
            if ( $this->format == 'cdb' ) {
                $t_mato_1 = 'Matomo works in privacy mode and loads irrespective of user tracking consents. Privacy mode prevents from tracking identifiable information before users agree to tracking in the consent banner (if enabled). Only necessary cookies are loaded, user IDs are not used for cross-browser tracking and order IDs are randomized.';
                $t_mato_2 = 'User ID - used for cross-browser tracking after visitors agree to cookies.';
                $t_mato_3 = 'User ID - for cross-browser tracking.';
                $t_mato_4 = 'Set up a consent banner';
                $t_mato_5 = 'Make sure the banner is set up correctly';
                $t_mato_6 = 'Real order ID is tracked when visitors agree to tracking in a consent banner. Random order ID is sent when they don\'t.';
                $t_mato_7 = 'Privacy mode is enabled. Make sure that this tool doesn\'t track information that can identify users.';
            } else {
                $t_mato_1 = esc_html__( 'Matomo works in privacy mode and loads irrespective of user tracking consents. Privacy mode prevents from tracking identifiable information before users agree to tracking in the consent banner (if enabled). Only necessary cookies are loaded, user IDs are not used for cross-browser tracking and order IDs are randomized.', 'full-picture-analytics-cookie-notice' );
                $t_mato_2 = esc_html__( 'User ID - used for cross-browser tracking after visitors agree to cookies.', 'full-picture-analytics-cookie-notice' );
                $t_mato_3 = esc_html__( 'User ID - for cross-browser tracking.', 'full-picture-analytics-cookie-notice' );
                $t_mato_4 = esc_html__( 'Set up a consent banner', 'full-picture-analytics-cookie-notice' );
                $t_mato_5 = esc_html__( 'Make sure the banner is set up correctly', 'full-picture-analytics-cookie-notice' );
                $t_mato_6 = esc_html__( 'Real order ID is tracked when visitors agree to tracking in a consent banner. Random order ID is sent when they don\'t.', 'full-picture-analytics-cookie-notice' );
                $t_mato_7 = esc_html__( 'Privacy mode is enabled. Make sure that this tool doesn\'t track information that can identify users.', 'full-picture-analytics-cookie-notice' );
            }
            if ( isset( $settings['no_cookies'] ) ) {
                // when privacy mode is enabled, then script loading always follows GDPR
                $this->data[$id]['setup'][0] = ['ok', $t_mato_1];
            } else {
                $req_consent = true;
            }
            if ( isset( $settings['set_user_id'] ) ) {
                if ( isset( $settings['no_cookies'] ) ) {
                    if ( in_array( 'cook', $this->tools ) ) {
                        if ( $this->consent_status !== 'ok' ) {
                            $this->data[$id]['tracked_extra_data'][] = [$t_mato_2];
                        } else {
                            $this->data[$id]['tracked_extra_data'][] = [$t_mato_3];
                        }
                    }
                } else {
                    if ( !in_array( 'cook', $this->tools ) ) {
                        $this->data[$id]['tracked_extra_data'][] = [$t_mato_3, $t_mato_4];
                    } else {
                        $this->data[$id]['tracked_extra_data'][] = [$t_mato_3, $t_mato_5];
                    }
                }
            }
            if ( in_array( 'woo', $this->tools ) ) {
                if ( in_array( 'cook', $this->tools ) ) {
                    if ( isset( $settings['no_cookies'] ) ) {
                        $this->data[$id]['tracked_extra_data'][] = [$t_mato_6];
                    } else {
                        $this->data[$id]['tracked_extra_data'][] = [$t_order_id];
                    }
                } else {
                    if ( !isset( $settings['no_cookies'] ) ) {
                        $this->data[$id]['tracked_extra_data'][] = [$t_order_id];
                    }
                }
            }
            $priv = ( isset( $settings['no_cookies'] ) ? $t_mato_7 : false );
            $this->track_metadata_IDs( $id, $settings, $priv );
        }
        if ( $id == 'fbp1' ) {
            $req_consent = true;
            if ( isset( $settings['adv_match'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_fbp_1 = 'Advanced Match is enabled. Encrypted addresses, email addresses, phone numbers and user identifiers of your visitors and logged in users are sent to Meta (if known).';
                } else {
                    $t_fbp_1 = esc_html__( 'Advanced Match is enabled. Encrypted addresses, email addresses, phone numbers and user identifiers of your visitors and logged in users are sent to Meta (if known).', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_fbp_1];
            }
            if ( isset( $settings['limit_data_use'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_fbp_2 = 'Limited Data Use option is enabled for visitors from the USA.';
                } else {
                    $t_fbp_2 = esc_html__( 'Limited Data Use option is enabled for visitors from the USA.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_fbp_2];
            }
            $this->track_metadata_IDs( $id, $settings );
        }
        if ( $id == 'mads' ) {
            $req_consent = true;
            if ( isset( $settings['enhanced_conv'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_mads_1 = 'Enhanced Conversions is enabled and sends to MS Advertising the email addresses of clients or logged-in users (if known).';
                } else {
                    $t_mads_1 = esc_html__( 'Enhanced Conversions is enabled and sends to MS Advertising the email addresses of clients or logged-in users (if known).', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_mads_1];
            }
        }
        if ( $id == 'clar' ) {
            $extra = false;
            if ( isset( $settings['no_cookie'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_clar_1 = 'MS Clarity works in no-cookie mode and loads irrespective of user tracking consents.This prevents from MS Clarity from tracking identifiable information before users agree to tracking in the consent banner (if enabled). Only necessary cookies are loaded.';
                    $t_clar_2 = 'No-cookie mode is enabled. Make sure that this tool doesn\'t track information that can identify users.';
                } else {
                    $t_clar_1 = esc_html__( 'MS Clarity works in no-cookie mode and loads irrespective of user tracking consents.This prevents from MS Clarity from tracking identifiable information before users agree to tracking in the consent banner (if enabled). Only necessary cookies are loaded.', 'full-picture-analytics-cookie-notice' );
                    $t_clar_2 = esc_html__( 'No-cookie mode is enabled. Make sure that this tool doesn\'t track information that can identify users.', 'full-picture-analytics-cookie-notice' );
                }
                // when privacy mode is enabled, then script loading always follows GDPR
                $this->data[$id]['setup'][0] = ['ok', $t_clar_1];
                $extra = $t_clar_2;
            } else {
                $req_consent = true;
            }
            $this->track_metadata_IDs( $id, $settings, $extra );
        }
        if ( $id == 'pin' ) {
            $req_consent = true;
            if ( isset( $settings['track_user_emails'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_pin_1 = 'Enhanced Match is enabled. Email addresses of clients and logged-in users are sent to Pinterest.';
                } else {
                    $t_pin_1 = esc_html__( 'Enhanced Match is enabled. Email addresses of clients and logged-in users are sent to Pinterest.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_pin_1];
            }
        }
        if ( $id == 'simpl' ) {
            $this->data[$id]['setup'][] = ['ok', $t_all_ok];
        }
        if ( $id == 'pla' ) {
            if ( $this->format == 'cdb' ) {
                $t_pla_1 = 'Due to the nature of Plausible Analytics and its terms and conditions, you cannot sent to it personally identifiable information. Please make sure that no metadata contains it.';
            } else {
                $t_pla_1 = esc_html__( 'Due to the nature of Plausible Analytics and its terms and conditions, you cannot sent to it personally identifiable information. Please make sure that no metadata contains it.', 'full-picture-analytics-cookie-notice' );
            }
            $this->data[$id]['setup'][] = ['ok', $t_all_ok];
            $this->track_metadata_IDs( $id, $settings, $t_pla_1 );
        }
        if ( $id == 'twit' ) {
            $req_consent = true;
            if ( isset( $settings['enhanced_conv'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_twit_1 = 'Enhanced Conversions is enabled. Email addresses of clients and logged-in users are sent to Twitter (if known).';
                } else {
                    $t_twit_1 = esc_html__( 'Enhanced Conversions is enabled. Email addresses of clients and logged-in users are sent to Twitter (if known).', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['tracked_extra_data'][] = [$t_twit_1];
            }
        }
        // add setup information after we checked all the PPs
        if ( $req_consent ) {
            if ( isset( $settings['force_load'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_force_1 = 'The tool is force loaded for all visitors.';
                    $t_force_2 = 'Disable force loading';
                } else {
                    $t_force_1 = esc_html__( 'The tool is force loaded for all visitors.', 'full-picture-analytics-cookie-notice' );
                    $t_force_2 = esc_html__( 'Disable force loading', 'full-picture-analytics-cookie-notice' );
                }
                $this->data[$id]['setup'][] = ['alert', $t_force_1, $t_force_2];
            } else {
                if ( in_array( 'cook', $this->tools ) ) {
                    if ( $this->format == 'cdb' ) {
                        $t_cook_1 = 'The tool is set to disregard consent banner settings and start tracking without waiting for consent';
                        $t_cook_1_2 = 'Disable this option in the module\'s settings';
                        $t_cook_2 = 'This tool loads according to incorrectly configured consent banner.';
                        $t_cook_3 = 'This tool loads according to consent banner settings. When the banner is in opt-in mode, the tool loads after visitors agree to:';
                        $t_cook_4 = 'The tool enables additional functions after visitors agree to using their personal data for:';
                    } else {
                        $t_cook_1 = esc_html__( 'The tool is set to disregard consent banner settings and start tracking without waiting for consent', 'full-picture-analytics-cookie-notice' );
                        $t_cook_1_2 = esc_html__( 'Disable this option in the module\'s settings', 'full-picture-analytics-cookie-notice' );
                        $t_cook_2 = esc_html__( 'This tool loads according to incorrectly configured consent banner.', 'full-picture-analytics-cookie-notice' );
                        $t_cook_3 = esc_html__( 'This tool loads according to consent banner settings. When the banner is in opt-in mode, the tool loads after visitors agree to:', 'full-picture-analytics-cookie-notice' );
                        $t_cook_4 = esc_html__( 'The tool enables additional functions after visitors agree to using their personal data for:', 'full-picture-analytics-cookie-notice' );
                    }
                    if ( isset( $settings['disreg_cookies'] ) ) {
                        $this->data[$id]['setup'][] = ['alert', $t_cook_1, $t_cook_1_2];
                    } else {
                        $extra = $this->get_extra_text();
                        $req_consents = '';
                        // paste required consents
                        if ( isset( $info['consents'] ) ) {
                            $req_consents = join( ', ', $info['consents'] );
                            if ( isset( $info['opt_consents'] ) ) {
                                $req_consents .= '. ' . $t_cook_4 . join( ', ', $info['opt_consents'] );
                            }
                        }
                        $main_text = ( $this->consent_status == 'alert' ? $t_cook_2 : $t_cook_3 . ' ' . $req_consents );
                        $this->data[$id]['setup'][] = [$this->consent_status, $main_text, $extra];
                    }
                } else {
                    if ( $this->format == 'cdb' ) {
                        $t_cons_1 = 'Consent banner is required';
                        $t_cons_2 = 'Enable consent banner';
                    } else {
                        $t_cons_1 = esc_html__( 'Consent banner is required', 'full-picture-analytics-cookie-notice' );
                        $t_cons_2 = esc_html__( 'Enable consent banner', 'full-picture-analytics-cookie-notice' );
                    }
                    $this->data[$id]['setup'][] = ['alert', $t_cons_1, $t_cons_2];
                }
            }
        }
        if ( $this->req_consent_banner != 'yes' ) {
            $this->req_consent_banner = ( $req_consent ? 'yes' : 'no' );
        }
        if ( $id == 'ga41' || $id == 'ga42' || $id == 'gads' ) {
            $this->check_url_passthrough( $id );
        }
    }

    //
    // SAFE FONTS MODULE
    //
    private function check_safefonts_module() {
        $module_info = $this->get_module_info( 'safefonts' );
        $this->set_basic_module_info( 'safefonts', $module_info );
        $is_module_enabled = in_array( 'safefonts', $this->tools );
        if ( $this->format == 'cdb' ) {
            $t_safe_1 = 'Google Fonts are replaced with fonts from Bunny Fonts';
            $t_safe_2 = 'You enabled replacing Google Fonts with safe fonts from Bunny Fonts but your website can still load them dynamically (after the page loads). Scan your website with <a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a> again. If it finds links to Google Fonts, you need to find the plugin or theme that loads them and disable Google Fonts in their settings. Alternatively, you can use an %2$sOMGF%3$s plugin.';
            $t_safe_3 = 'Check if you need to use the Safe Fonts module';
            $t_safe_4 = 'Scan your website with <a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a> and check if your website uses Google Fonts. If it does, then either disable them in the settings of your website, enable the Safe Fonts module to replace them with GDPR-compliant fonts from Bunny Fonts or use a plugin OMGF (free).';
        } else {
            $t_safe_1 = esc_html__( 'Google Fonts are replaced with fonts from Bunny Fonts', 'full-picture-analytics-cookie-notice' );
            $t_safe_2 = sprintf(
                esc_html__( 'You enabled replacing Google Fonts with safe fonts from Bunny Fonts but your website can still load them dynamically (after the page loads). Scan your website with %1$s again. If it finds links to Google Fonts, you need to find the plugin or theme that loads them and disable Google Fonts in their settings. Alternatively, you can use an %2$sOMGF%3$s plugin.', 'full-picture-analytics-cookie-notice' ),
                '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a>',
                '<a href="https://wordpress.org/plugins/host-webfonts-local/" target="_blank">',
                '</a>'
            );
            $t_safe_3 = esc_html__( 'Check if you need to use the Safe Fonts module', 'full-picture-analytics-cookie-notice' );
            $t_safe_4 = sprintf(
                esc_html__( 'Scan your website with %1$s and check if your website uses Google Fonts. If it does, then either disable them in the settings of your website, enable the Safe Fonts module to replace them with GDPR-compliant fonts from Bunny Fonts or use a plugin %2$sOMGF (free)%3$s.', 'full-picture-analytics-cookie-notice' ),
                '<a href="https://fontsplugin.com/google-fonts-checker/" target="_blank">Fonts Checker</a>',
                '<a href="https://wordpress.org/plugins/host-webfonts-local/" target="_blank">',
                '</a>'
            );
        }
        if ( $is_module_enabled ) {
            $this->data['safefonts']['pre-setup'][] = [$t_safe_1, $t_safe_2];
        } else {
            $this->data['safefonts']['pre-setup'][] = [$t_safe_3, $t_safe_4];
        }
    }

    //
    // WOOCOMMERCE MODULE
    //
    private function check_woo_module() {
        if ( in_array( 'woo', $this->tools ) ) {
            if ( $this->format == 'cdb' ) {
                $t_woo_1 = 'Disable Order Attribution or control the SourceBuster script in WooCommerce';
                $t_woo_2 = sprintf( 'Recent versions of WooCommerce come with an order attribution feature that uses cookies to track the last source of a purchase. We recommend you either disable this function in WooCommerce > Settings > Advanced > Features or use the Tracking Tools Manager to load the SourceBuster script only to people who agree to statistical cookies. This script is used by the Order Attribution function. %1$sLearn more%2$s', '<a href="https://wpfullpicture.com/blog/does-order-attribution-feature-in-woocommerce-8-5-1-break-gdpr-and-what-to-do-about-it/">', '</a>' );
                $t_woo_3 = 'SourceBuster script is loaded according to incorrectly set up consent banner';
                $t_woo_4 = 'Correct consent banner settings';
                $t_woo_5 = 'SourceBuster script is loaded according to the consent banner settings';
                $t_woo_6 = 'Enable consent banner to load SourceBuster according to privacy laws';
                $t_woo_7 = 'Enable consent banner';
            } else {
                $t_woo_1 = esc_html__( 'Disable Order Attribution or control the SourceBuster script in WooCommerce', 'full-picture-analytics-cookie-notice' );
                $t_woo_2 = sprintf( esc_html__( 'Recent versions of WooCommerce come with an order attribution feature that uses cookies to track the last source of a purchase. We recommend you either disable this function in WooCommerce > Settings > Advanced > Features or use the Tracking Tools Manager to load the SourceBuster script only to people who agree to statistical cookies. This script is used by the Order Attribution function. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/blog/does-order-attribution-feature-in-woocommerce-8-5-1-break-gdpr-and-what-to-do-about-it/">', '</a>' );
                $t_woo_3 = esc_html__( 'SourceBuster script is loaded according to incorrectly set up consent banner', 'full-picture-analytics-cookie-notice' );
                $t_woo_4 = esc_html__( 'Correct consent banner settings', 'full-picture-analytics-cookie-notice' );
                $t_woo_5 = esc_html__( 'SourceBuster script is loaded according to the consent banner settings', 'full-picture-analytics-cookie-notice' );
                $t_woo_6 = esc_html__( 'Enable consent banner to load SourceBuster according to privacy laws', 'full-picture-analytics-cookie-notice' );
                $t_woo_7 = esc_html__( 'Enable consent banner', 'full-picture-analytics-cookie-notice' );
            }
            $module_info = $this->get_module_info( 'woo' );
            $this->set_basic_module_info( 'woo', $module_info );
            $sourcebuster_warning_text = [$t_woo_1, $t_woo_2];
            if ( in_array( 'blockscr', $this->tools ) ) {
                $blockscr_opts = get_option( 'fupi_blockscr' );
                if ( !empty( $blockscr_opts ) && !empty( $blockscr_opts['auto_rules'] ) && in_array( 'woo_sbjs', $blockscr_opts['auto_rules'] ) ) {
                    if ( in_array( 'cook', $this->tools ) ) {
                        if ( $this->consent_status == 'alert' ) {
                            $this->data['woo']['setup'][] = [$this->consent_status == 'alert', $t_woo_3, $t_woo_4];
                        } else {
                            $this->data['woo']['setup'][] = ['ok', $t_woo_5];
                        }
                    } else {
                        $this->data['woo']['setup'][] = ['alert', $t_woo_6, $t_woo_7];
                    }
                } else {
                    $this->data['woo']['opt-setup'][] = $sourcebuster_warning_text;
                }
            } else {
                $this->data['woo']['opt-setup'][] = $sourcebuster_warning_text;
            }
        }
    }

    //
    // IFRAME MANAGER
    //
    private function check_iframeblock_module() {
        $module_info = $this->get_module_info( 'iframeblock' );
        $this->set_basic_module_info( 'iframeblock', $module_info );
        $is_module_enabled = in_array( 'iframeblock', $this->tools );
        $settings = get_option( 'fupi_iframeblock' );
        // Default
        if ( $this->format == 'cdb' ) {
            $t_iframe_disabl_1 = 'Check if you need to use the Iframes Manager module';
            $t_iframe_disabl_2 = 'If you embed on your site any content from other websites (YouTube videos, Google Maps, X/Twitter twits, etc.), please configure the Consent Banner module and Iframes Manager module. This way WP Full Picture will be able to load this content according to provided consents.';
        } else {
            $t_iframe_disabl_1 = esc_html__( 'Check if you need to use the Iframes Manager module', 'full-picture-analytics-cookie-notice' );
            $t_iframe_disabl_2 = esc_html__( 'If you embed on your site any content from other websites (YouTube videos, Google Maps, X/Twitter twits, etc.), please configure the Consent Banner module and Iframes Manager module. This way WP Full Picture will be able to load this content according to provided consents.', 'full-picture-analytics-cookie-notice' );
        }
        $this->data['iframeblock']['pre-setup'][] = [$t_iframe_disabl_1, $t_iframe_disabl_2];
        if ( $is_module_enabled ) {
            $add_extra_info = false;
            // Automatic iframe rules
            if ( !empty( $settings['auto_rules'] ) && is_array( $settings['auto_rules'] ) ) {
                unset($this->data['iframeblock']['pre-setup']);
                $this->req_consent_banner = 'yes';
                $add_extra_info = true;
                if ( $this->format == 'cdb' ) {
                    $delimiter = ( count( $settings['auto_rules'] ) == 2 ? ' and ' : ', ' );
                    $rules_str = join( $delimiter, $settings['auto_rules'] );
                    $t_iframe_1 = 'Iframes loaded by ' . $rules_str . ' are loaded according to consent banner settings (require consents for statistics and marketing) or when visitors agree to privacy policies of content hosts.';
                } else {
                    $delimiter = ( count( $settings['auto_rules'] ) == 2 ? esc_html__( ' and ', 'full-picture-analytics-cookie-notice' ) : ', ' );
                    $rules_str = join( $delimiter, $settings['auto_rules'] );
                    $t_iframe_1 = sprintf( esc_html__( 'Iframes loaded by %1$s are loaded according to consent banner settings (require consents for statistics and marketing) or when visitors agree to privacy policies of content hosts.', 'full-picture-analytics-cookie-notice' ), $rules_str );
                }
                $extra = $this->get_extra_text();
                $this->data['iframeblock']['setup'][] = [$this->consent_status, $t_iframe_1, $extra];
            }
            // Add automatic and manual rules together
            if ( !empty( $settings['manual_rules'] ) ) {
                $add_extra_info = true;
                if ( $this->format == 'cdb' ) {
                    $t_iframe_2 = 'statistics';
                    $t_iframe_3 = 'marketing';
                    $t_iframe_4 = 'personalisation';
                } else {
                    $t_iframe_2 = esc_html__( 'statistics', 'full-picture-analytics-cookie-notice' );
                    $t_iframe_3 = esc_html__( 'marketing', 'full-picture-analytics-cookie-notice' );
                    $t_iframe_4 = esc_html__( 'personalisation', 'full-picture-analytics-cookie-notice' );
                }
                foreach ( $settings['manual_rules'] as $rules ) {
                    $text = 'Iframes loaded by ' . $rules['iframe_url'];
                    $req_consents = [];
                    $delimiter = ( count( $settings['manual_rules'] ) == 2 ? ' and ' : ', ' );
                    $extra = false;
                    if ( !empty( $rules['stats'] ) ) {
                        $req_consents[] = $t_iframe_2;
                    }
                    if ( !empty( $rules['market'] ) ) {
                        $req_consents[] = $t_iframe_3;
                    }
                    if ( !empty( $rules['pers'] ) ) {
                        $req_consents[] = $t_iframe_4;
                    }
                    if ( count( $req_consents ) > 0 ) {
                        $this->req_consent_banner = 'yes';
                        $entry_status = $this->consent_status;
                        $req_cons_string = join( $delimiter, $req_consents );
                        if ( $this->format == 'cdb' ) {
                            $t_iframe_5 = ' are loaded according to consent banner settings (require consents for ' . $req_cons_string . ') or when visitors agree to privacy policies of content hosts.';
                        } else {
                            $t_iframe_5 = sprintf( esc_html__( ' are loaded according to consent banner settings (require consents for %1$s) or when visitors agree to privacy policies of content hosts.', 'full-picture-analytics-cookie-notice' ), $req_cons_string );
                        }
                        $text .= $t_iframe_5;
                        $extra = $this->get_extra_text();
                    } else {
                        if ( $this->format == 'cdb' ) {
                            $t_iframe_6 = ' are set to load without waiting for consents.';
                        } else {
                            $t_iframe_6 = esc_html__( ' are set to load without waiting for consents.', 'full-picture-analytics-cookie-notice' );
                        }
                        $entry_status = 'warning';
                        $text .= $t_iframe_6;
                    }
                    $this->data['iframeblock']['setup'][] = [$entry_status, $text, $extra];
                }
            }
            if ( $add_extra_info ) {
                if ( $this->format == 'cdb' ) {
                    $t_iframe_7 = 'Add information in your privacy policy that your website loads content from other sources and what happens with their data after they agree. You can link to their privacy policies.';
                } else {
                    $t_iframe_7 = esc_html__( 'Add information in your privacy policy that your website loads content from other sources and what happens with their data after they agree. You can link to their privacy policies.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data['iframeblock']['pp comments'][] = $t_iframe_7;
            }
        }
    }

    //
    // CUSTOM SCRIPTS
    //
    private function check_custom_scripts_module() {
        $module_info = $this->get_module_info( 'cscr' );
        $this->set_basic_module_info( 'cscr', $module_info );
        $is_module_enabled = in_array( 'cscr', $this->tools );
        $settings = get_option( 'fupi_cscr' );
        if ( $is_module_enabled ) {
            if ( $this->req_consent_banner != 'yes' ) {
                $this->req_consent_banner = 'maybe';
            }
            $script_placement = ['fupi_head_scripts', 'fupi_footer_scripts'];
            $adds_scripts = false;
            foreach ( $script_placement as $placement ) {
                if ( !empty( $settings[$placement] ) ) {
                    foreach ( $settings[$placement] as $script_settings ) {
                        if ( !empty( $script_settings['disable'] ) ) {
                            continue;
                        }
                        $adds_scripts = true;
                        $title = ( !empty( $script_settings['title'] ) ? esc_attr( $script_settings['title'] ) : 'Script ' . $script_settings['id'] );
                        if ( $this->format == 'cdb' ) {
                            $t_scr_1 = 'WP Full Picture loads a script';
                        } else {
                            $t_scr_1 = esc_html__( 'WP Full Picture loads a script', 'full-picture-analytics-cookie-notice' );
                        }
                        // write description text
                        $script_info_text = $t_scr_1 . ': "' . $title . '". ';
                        // if we have a consent banner
                        if ( in_array( 'cook', $this->tools ) ) {
                            if ( $this->format == 'cdb' ) {
                                $t_csrc_3 = 'statistics';
                                $t_csrc_4 = 'marketing';
                                $t_csrc_5 = 'personalisation';
                            } else {
                                $t_csrc_3 = esc_html__( 'statistics', 'full-picture-analytics-cookie-notice' );
                                $t_csrc_4 = esc_html__( 'marketing', 'full-picture-analytics-cookie-notice' );
                                $t_csrc_5 = esc_html__( 'personalisation', 'full-picture-analytics-cookie-notice' );
                            }
                            $req_consents = [];
                            if ( !empty( $script_settings['stats'] ) ) {
                                $req_consents[] = $t_csrc_3;
                            }
                            if ( !empty( $script_settings['market'] ) ) {
                                $req_consents[] = $t_csrc_4;
                            }
                            if ( !empty( $script_settings['pers'] ) ) {
                                $req_consents[] = $t_csrc_5;
                            }
                            if ( count( $req_consents ) > 0 ) {
                                $script_status = 'ok';
                                if ( $this->format == 'cdb' ) {
                                    $delimiter = ( count( $req_consents ) == 2 ? ' and ' : ', ' );
                                    $t_csrc_6 = 'It is set to require consents for';
                                    $t_csrc_7 = 'but it is force-loaded before visitors can make their choices.';
                                    $t_csrc_8 = 'Do not force-load this script.';
                                    $t_csrc_9 = 'and loads according to consent banner settings.';
                                } else {
                                    $delimiter = ( count( $req_consents ) == 2 ? esc_html__( ' and ', 'full-picture-analytics-cookie-notice' ) : ', ' );
                                    $t_csrc_6 = esc_html__( 'It is set to require consents for', 'full-picture-analytics-cookie-notice' );
                                    $t_csrc_7 = esc_html__( 'but it is force-loaded before visitors can make their choices.', 'full-picture-analytics-cookie-notice' );
                                    $t_csrc_8 = esc_html__( 'Do not force-load this script.', 'full-picture-analytics-cookie-notice' );
                                    $t_csrc_9 = esc_html__( 'and loads according to consent banner settings.', 'full-picture-analytics-cookie-notice' );
                                }
                                $script_info_text .= $t_csrc_6 . ' ' . join( $delimiter, $req_consents );
                                $extra = $this->get_extra_text();
                                if ( isset( $script_settings['force_load'] ) ) {
                                    $script_status = 'alert';
                                    $script_info_text .= ' ' . $t_csrc_7;
                                    $extra .= ' ' . $t_csrc_8;
                                } else {
                                    $script_info_text .= ' ' . $t_csrc_9;
                                }
                            } else {
                                if ( $this->format == 'cdb' ) {
                                    $t_csrc_10 = 'The script loads without waiting for tracking consents.';
                                    $t_csrc_11 = 'Make sure that this script does not track personaly identifiable information.';
                                } else {
                                    $t_csrc_10 = esc_html__( 'The script loads without waiting for tracking consents.', 'full-picture-analytics-cookie-notice' );
                                    $t_csrc_11 = esc_html__( 'Make sure that this script does not track personaly identifiable information.', 'full-picture-analytics-cookie-notice' );
                                }
                                $script_status = 'warning';
                                $script_info_text .= $t_csrc_10;
                                $extra = $t_csrc_11;
                            }
                            // if we don't have a consent banner
                        } else {
                            if ( $this->format == 'cdb' ) {
                                $t_csrc_12 = 'The script loads without waiting for tracking consents.';
                                $t_csrc_13 = 'Make sure that this script does not track personaly identifiable information.';
                            } else {
                                $t_csrc_12 = esc_html__( 'The script loads without waiting for tracking consents.', 'full-picture-analytics-cookie-notice' );
                                $t_csrc_13 = esc_html__( 'Make sure that this script does not track personaly identifiable information.', 'full-picture-analytics-cookie-notice' );
                            }
                            $script_status = 'warning';
                            $script_info_text .= $t_csrc_12;
                            $extra = $t_csrc_13;
                        }
                        $this->data['cscr']['setup'][] = [$script_status, $script_info_text, $extra];
                    }
                }
            }
            if ( !$adds_scripts ) {
                if ( $this->format == 'cdb' ) {
                    $t_cscr_14 = 'This module does not install any scripts';
                    $t_cscr_15 = 'Make sure that all JavaScript snippets that install tracking tools are loaded with the Custom Scripts module. WP Full Picture will load them according to provided consents.';
                } else {
                    $t_cscr_14 = esc_html__( 'This module does not install any scripts', 'full-picture-analytics-cookie-notice' );
                    $t_cscr_15 = esc_html__( 'Make sure that all JavaScript snippets that install tracking tools are loaded with the Custom Scripts module. WP Full Picture will load them according to provided consents.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data['cscr']['pre-setup'][] = [$t_cscr_14, $t_cscr_15];
            }
            // If module is disabled
        } else {
            if ( $this->format == 'cdb' ) {
                $t_cscr_16 = 'Check if you need to enable the Custom Scripts module';
                $t_cscr_17 = 'If you installed any tracking tools with JavaScript snippets, please move these snippets to the "Custom scripts" module (easy) or Google Tag Manager (advanced). This way, WP Full Picture\'s Consent Banner will be able to load these tools according to provided consents.';
            } else {
                $t_cscr_16 = esc_html__( 'Check if you need to enable the Custom Scripts module', 'full-picture-analytics-cookie-notice' );
                $t_cscr_17 = esc_html__( 'If you installed any tracking tools with JavaScript snippets, please move these snippets to the "Custom scripts" module (easy) or Google Tag Manager (advanced). This way, WP Full Picture\'s Consent Banner will be able to load these tools according to provided consents.', 'full-picture-analytics-cookie-notice' );
            }
            $this->data['cscr']['pre-setup'][] = [$t_cscr_16, $t_cscr_17];
        }
    }

    //
    // TRACKING TOOLS MANAGER
    //
    private function check_blockscr_module() {
        $module_info = $this->get_module_info( 'blockscr' );
        $this->set_basic_module_info( 'blockscr', $module_info );
        $is_module_enabled = in_array( 'blockscr', $this->tools );
        $settings = get_option( 'fupi_blockscr' );
        // defaults
        if ( $this->format == 'cdb' ) {
            $t_block_14 = 'Check if you need to use the Tracking Tools Manager module';
            $t_block_15 = 'Tracking Tools Manager let\'s controls tracking tools installed outside WP Full Picture. Only controlled tools can be loaded according to visitors\' consents. Use it if you installed any tracking tool with a different plugin. If you are unsure, scan your website with one of online cookie scanners.';
        } else {
            $t_block_14 = esc_html__( 'Check if you need to use the Tracking Tools Manager module', 'full-picture-analytics-cookie-notice' );
            $t_block_15 = esc_html__( 'Tracking Tools Manager let\'s controls tracking tools installed outside WP Full Picture. Only controlled tools can be loaded according to visitors\' consents. Use it if you installed any tracking tool with a different plugin. If you are unsure, scan your website with one of online cookie scanners.', 'full-picture-analytics-cookie-notice' );
        }
        $this->data['blockscr']['pre-setup'][] = [$t_block_14, $t_block_15];
        if ( $is_module_enabled ) {
            $add_extra_info = false;
            if ( !empty( $settings['auto_rules'] ) && is_array( $settings['auto_rules'] ) ) {
                unset($this->data['blockscr']['pre-setup']);
                $auto_rules_str = join( ', ', str_replace( '_', ' ', $settings['auto_rules'] ) );
                if ( $this->format == 'cdb' ) {
                    $t_auto_1 = 'Tracking plugins loaded according to settings in the consent banner:';
                    $t_auto_2 = 'Tracking plugin(s) ' . $auto_rules_str . ' must be loaded according to settings in the consent banner but it is not enabled.';
                    $t_auto_3 = 'Enable consent banner';
                } else {
                    $t_auto_1 = esc_html__( 'Tracking plugins loaded according to settings in the consent banner:', 'full-picture-analytics-cookie-notice' );
                    $t_auto_2 = sprintf( esc_html__( 'Tracking plugin(s) %1$s must be loaded according to settings in the consent banner but it is not enabled.', 'full-picture-analytics-cookie-notice' ), $auto_rules_str );
                    $t_auto_3 = esc_html__( 'Enable consent banner', 'full-picture-analytics-cookie-notice' );
                }
                $this->req_consent_banner = 'yes';
                $add_extra_info = true;
                if ( in_array( 'cook', $this->tools ) ) {
                    if ( $this->format == 'cdb' ) {
                        $t_auto_1 = 'Tracking plugins loaded according to settings in the consent banner:';
                    } else {
                        $t_auto_1 = esc_html__( 'Tracking plugins loaded according to settings in the consent banner:', 'full-picture-analytics-cookie-notice' );
                    }
                    $extra = $this->get_extra_text();
                    $this->data['blockscr']['setup'][] = [$this->consent_status, $t_auto_1 . ' ' . $auto_rules_str, $extra];
                } else {
                    if ( $this->format == 'cdb' ) {
                        $t_auto_2 = 'Tracking plugin(s) ' . $auto_rules_str . ' must be loaded according to settings in the consent banner but it is not enabled.';
                        $t_auto_3 = 'Enable consent banner';
                    } else {
                        $t_auto_2 = sprintf( esc_html__( 'Tracking plugin(s) %1$s must be loaded according to settings in the consent banner but it is not enabled.', 'full-picture-analytics-cookie-notice' ), $auto_rules_str );
                        $t_auto_3 = esc_html__( 'Enable consent banner', 'full-picture-analytics-cookie-notice' );
                    }
                    $this->data['blockscr']['setup'][] = ['alert', $t_auto_2, $t_auto_3];
                }
            }
            if ( !empty( $settings['blocked_scripts'] ) && is_array( $settings['blocked_scripts'] ) ) {
                unset($this->data['blockscr']['pre-setup']);
                $this->req_consent_banner = 'yes';
                $add_extra_info = true;
                foreach ( $settings['blocked_scripts'] as $rules ) {
                    if ( $this->format == 'cdb' ) {
                        $t_block_1 = 'No name provided';
                    } else {
                        $t_block_1 = esc_html__( 'No name provided', 'full-picture-analytics-cookie-notice' );
                    }
                    $title = $rules['id'];
                    if ( !empty( $rules['title'] ) ) {
                        $title = $rules['title'];
                    } else {
                        if ( !empty( $rules['name'] ) ) {
                            $title = $rules['name'];
                        } else {
                            $title = $t_block_1;
                        }
                    }
                    if ( $this->format == 'cdb' ) {
                        $t_block_2 = 'Tracking tool with ' . $rules['block_by'] . '="' . $rules['url_part'] . '" and title/ID "' . $title . '"';
                        $t_block_3 = 'statistics';
                        $t_block_4 = 'marketing';
                        $t_block_5 = 'personalisation';
                    } else {
                        $t_block_2 = sprintf(
                            esc_html__( 'Tracking tool with %1$s="%2$s" and title/ID "%3$s"', 'full-picture-analytics-cookie-notice' ),
                            $rules['block_by'],
                            $rules['url_part'],
                            $title
                        );
                        $t_block_3 = esc_html__( 'statistics', 'full-picture-analytics-cookie-notice' );
                        $t_block_4 = esc_html__( 'marketing', 'full-picture-analytics-cookie-notice' );
                        $t_block_5 = esc_html__( 'personalisation', 'full-picture-analytics-cookie-notice' );
                    }
                    $text = $t_block_2;
                    $extra = false;
                    $req_consents = [];
                    if ( !empty( $rules['stats'] ) ) {
                        $req_consents[] = $t_block_3;
                    }
                    if ( !empty( $rules['market'] ) ) {
                        $req_consents[] = $t_block_4;
                    }
                    if ( !empty( $rules['pers'] ) ) {
                        $req_consents[] = $t_block_5;
                    }
                    $delimiter = ( count( $req_consents ) == 2 ? ' and ' : ', ' );
                    // Don\'t forget to add "force_load" check
                    if ( count( $req_consents ) > 0 ) {
                        $this->req_consent_banner = 'yes';
                        $entry_status = $this->consent_status;
                        if ( $this->format == 'cdb' ) {
                            $t_block_6 = 'is marked as using visitor\'s data for ' . join( $delimiter, $req_consents ) . '.';
                            $t_block_7 = 'The tool is loaded according to incorrectly set up consent banner';
                            $t_block_8 = 'The tool is loaded according to consent banner settings';
                        } else {
                            $t_block_6 = sprintf( esc_html__( 'is marked as using visitor\'s data for %1$s.', 'full-picture-analytics-cookie-notice' ), join( $delimiter, $req_consents ) );
                            $t_block_7 = esc_html__( 'The tool is loaded according to incorrectly set up consent banner', 'full-picture-analytics-cookie-notice' );
                            $t_block_8 = esc_html__( 'The tool is loaded according to consent banner settings', 'full-picture-analytics-cookie-notice' );
                        }
                        $text .= ' ' . $t_block_6;
                        if ( in_array( 'cook', $this->tools ) ) {
                            $extra = $this->get_extra_text();
                            if ( $this->consent_status == 'alert' ) {
                                $text .= ' ' . $t_block_7;
                            } else {
                                $text .= ' ' . $t_block_8;
                            }
                        }
                    } else {
                        if ( $this->format == 'cdb' ) {
                            $t_block_9 = 'is set to load without waiting for consents.';
                            $t_block_10 = 'Make sure this script does not need consents';
                        } else {
                            $t_block_9 = esc_html__( 'is set to load without waiting for consents.', 'full-picture-analytics-cookie-notice' );
                            $t_block_10 = esc_html__( 'Make sure this script does not need consents', 'full-picture-analytics-cookie-notice' );
                        }
                        $entry_status = 'warning';
                        $text .= ' ' . $t_block_9;
                        $extra = $t_block_10;
                    }
                    $this->data['blockscr']['setup'][] = [$entry_status, $text, $extra];
                }
            }
            if ( $this->format == 'cdb' ) {
                $t_block_11 = 'Add information in your privacy policy about additional tracking tools that you use, what data they collect, how is the data used and who is it shared with.';
                $t_block_12 = 'This module does not manage any tracking tools';
                $t_block_13 = 'Are you sure this module needs to stay enabled?';
            } else {
                $t_block_11 = esc_html__( 'Add information in your privacy policy about additional tracking tools that you use, what data they collect, how is the data used and who is it shared with.', 'full-picture-analytics-cookie-notice' );
                $t_block_12 = esc_html__( 'This module does not manage any tracking tools', 'full-picture-analytics-cookie-notice' );
                $t_block_13 = esc_html__( 'Are you sure this module needs to stay enabled?', 'full-picture-analytics-cookie-notice' );
            }
            if ( $add_extra_info ) {
                $this->data['blockscr']['pp comments'][] = $t_block_11;
            } else {
                $this->data['blockscr']['setup'][] = ['ok', $t_block_12, $t_block_13];
            }
        }
    }

    //
    // CONSENT BANNER STATUS
    //
    private function check_cons_banner_module() {
        if ( !in_array( 'cook', $this->tools ) ) {
            return;
        }
        $info = $this->get_module_info( 'cook' );
        $settings = $this->consb_settings;
        $notice_opts = get_option( 'fupi_cookie_notice' );
        $priv_policy_url = get_privacy_policy_url();
        $this->data['cook'] = [
            'module_name' => ( $this->format == 'cdb' ? $info['name'] : $this->modules_names['cook'] ),
            'setup'       => [],
        ];
        if ( $this->format == 'cdb' ) {
            $t_cook_1 = 'Check in what countries opt-in, opt-out and notification banners should be used. <a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/">Read article</a>';
            $t_cook_2 = 'Consent banner uses strict, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Strict mode is intended for websites that use visitor\'s data for marketing purposes and / or collect sensitive information.';
            $t_cook_3 = 'Opt-in mode is used for visitors from:';
            $t_cook_4 = 'Opt-out mode is used for visitors from:';
            $t_cook_5 = 'Visitors from other countries are notified that they are tracked.';
            $t_cook_6 = 'Fallback mode when no location is found: Opt-in mode.';
        } else {
            $t_cook_1 = sprintf( esc_html__( 'Check in what countries opt-in, opt-out and notification banners should be used. %1$sRead article%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/countries-that-require-opt-in-or-opt-out-to-cookies/">', '</a>' );
            $t_cook_2 = esc_html__( 'Consent banner uses strict, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Strict mode is intended for websites that use visitor\'s data for marketing purposes and / or collect sensitive information.', 'full-picture-analytics-cookie-notice' );
            $t_cook_3 = esc_html__( 'Opt-in mode is used for visitors from:', 'full-picture-analytics-cookie-notice' );
            $t_cook_4 = esc_html__( 'Opt-out mode is used for visitors from:', 'full-picture-analytics-cookie-notice' );
            $t_cook_5 = esc_html__( 'Visitors from other countries are notified that they are tracked.', 'full-picture-analytics-cookie-notice' );
            $t_cook_6 = esc_html__( 'Fallback mode when no location is found: Opt-in mode.', 'full-picture-analytics-cookie-notice' );
        }
        $status = 'ok';
        // state levels: ok > warning > alert
        $guide_text = $t_cook_1;
        // texts for the defaults
        $default_geo_texts = [
            ['ok', $t_cook_2],
            ['ok', $t_cook_3 . ' AT, BE, BG, CY, CZ, DE, DK, ES, EE, FI, FR, GB, GR, HR, HU, IE, IS, IT, LI, LT, LU, LV, MT, NG, NL, NO, PL, PT, RO, SK, SI, SE, MX, GP, GF, MQ, YT, RE, MF, IC, AR, BR, TR, SG, ZA, AU, CA, CL, CN, CO, HK, IN, ID, JP, MA, RU, KR, CH, TW, TH.'],
            ['ok', $t_cook_4 . ' US (CA), KZ.'],
            ['ok', $t_cook_5],
            ['ok', $t_cook_6]
        ];
        // default settings
        if ( empty( $settings ) ) {
            if ( $this->format == 'cdb' ) {
                $t_cook_7 = 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).';
                $t_cook_8 = 'Visitors are not asked for new consent when the privacy policy text changes and/or when new tracking modules are enabled.';
                $t_cook_9 = 'Enable it in the consent banner\'s settings';
            } else {
                $t_cook_7 = esc_html__( 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).', 'full-picture-analytics-cookie-notice' );
                $t_cook_8 = esc_html__( 'Visitors are not asked for new consent when the privacy policy text changes and/or when new tracking modules are enabled.', 'full-picture-analytics-cookie-notice' );
                $t_cook_9 = esc_html__( 'Enable it in the consent banner\'s settings', 'full-picture-analytics-cookie-notice' );
            }
            if ( in_array( 'geo', $this->tools ) ) {
                $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );
            } else {
                $this->data['cook']['setup'][] = ['ok', $t_cook_7];
            }
            // No asking for consents again
            if ( $status != 'alert' ) {
                $status = 'alert';
            }
            $this->data['cook']['setup'][] = ['alert', $t_cook_8, $t_cook_9];
            // check user's settings
        } else {
            if ( in_array( 'geo', $this->tools ) ) {
                // check if saved after enabling geo
                $use_default_geo = !isset( $settings['mode'] );
                if ( $use_default_geo ) {
                    $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );
                } else {
                    switch ( $settings['mode'] ) {
                        case 'optin':
                            if ( $this->format == 'cdb' ) {
                                $t_cook_12 = 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).';
                            } else {
                                $t_cook_12 = esc_html__( 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).', 'full-picture-analytics-cookie-notice' );
                            }
                            $this->data['cook']['setup'][] = ['ok', $t_cook_12];
                            break;
                        case 'optout':
                            if ( $this->format == 'cdb' ) {
                                $t_cook_13 = 'Consent banner is set to start tracking visitors from the moment they enter the website but let them decline tracking (Opt-out mode).';
                                $t_cook_14 = 'Change to the opt-in mode or one of automatic modes.';
                            } else {
                                $t_cook_13 = esc_html__( 'Consent banner is set to start tracking visitors from the moment they enter the website but let them decline tracking (Opt-out mode).', 'full-picture-analytics-cookie-notice' );
                                $t_cook_14 = esc_html__( 'Change to the opt-in mode or one of automatic modes.', 'full-picture-analytics-cookie-notice' );
                            }
                            if ( $status != 'alert' ) {
                                $status = 'alert';
                            }
                            $this->data['cook']['setup'][] = ['alert', $t_cook_13, $t_cook_14];
                            break;
                        case 'notify':
                            if ( $this->format == 'cdb' ) {
                                $t_cook_15 = 'Consent banner is set to track all visitors and only notify them that they are tracked. They cannot decline.';
                                $t_cook_16 = 'Change to the opt-in mode or one of automatic modes.';
                            } else {
                                $t_cook_15 = esc_html__( 'Consent banner is set to track all visitors and only notify them that they are tracked. They cannot decline.', 'full-picture-analytics-cookie-notice' );
                                $t_cook_16 = esc_html__( 'Change to the opt-in mode or one of automatic modes.', 'full-picture-analytics-cookie-notice' );
                            }
                            if ( $status != 'alert' ) {
                                $status = 'alert';
                            }
                            $this->data['cook']['setup'][] = ['alert', $t_cook_15, $t_cook_16];
                            break;
                        case 'auto_strict':
                            array_pop( $default_geo_texts );
                            // remove last element of array (text about fallback)
                            $this->data['cook']['setup'] = array_merge( $this->data['cook']['setup'], $default_geo_texts );
                            break;
                        case 'auto_lax':
                            $notif_countries = 'KZ, PH';
                            if ( $this->format == 'cdb' ) {
                                $t_cook_17 = 'Consent banner uses lax, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Lax mode is intended for websites that neither use visitor\'s data for marketing purposes nor collect sensitive information.';
                                $t_cook_18 = 'Opt-in mode is used for visitors from:';
                                $t_cook_19 = 'Opt-out mode is used for visitors from:';
                                $t_cook_20 = 'Visitors from ' . $notif_countries . ' are notified that they are tracked.';
                                $t_cook_21 = 'Visitors from other countries are tracked without notification.';
                                $t_cook_22 = 'Consent banner changes the mode of work depending on visitor\'s location. The list of locations was set manually by the user.';
                            } else {
                                $t_cook_17 = esc_html__( 'Consent banner uses lax, automatic setup mode - it chooses the correct mode of work depending on visitor\'s location. Lax mode is intended for websites that neither use visitor\'s data for marketing purposes nor collect sensitive information.', 'full-picture-analytics-cookie-notice' );
                                $t_cook_18 = esc_html__( 'Opt-in mode is used for visitors from:', 'full-picture-analytics-cookie-notice' );
                                $t_cook_19 = esc_html__( 'Opt-out mode is used for visitors from:', 'full-picture-analytics-cookie-notice' );
                                $t_cook_20 = sprintf( esc_html__( 'Visitors from %1$s are notified that they are tracked.', 'full-picture-analytics-cookie-notice' ), $notif_countries );
                                $t_cook_21 = esc_html__( 'Visitors from other countries are tracked without notification.', 'full-picture-analytics-cookie-notice' );
                            }
                            $this->data['cook']['setup'] = [
                                ['ok', $t_cook_17],
                                ['ok', $t_cook_18 . ' AT, BE, BG, CY, CZ, DE, DK, ES, EE, FI, FR, GB, GR, HR, HU, IE, IS, IT, LI, LT, LU, LV, MT, NG, NL, NO, PL, PT, RO, SK, SI, SE, GP, GF, MQ, YT, RE, MF, IC, TR, ZA, AG, BR, CL, CN, CO, ID, MA, RU, KR, TW, TH, CH.'],
                                ['ok', $t_cook_19 . ' US (CA), JP, CA, IN, MX, SG.'],
                                ['ok', $t_cook_20],
                                ['ok', $t_cook_21]
                            ];
                            break;
                        case 'manual':
                            if ( $this->format == 'cdb' ) {
                                $t_cook_22 = 'Consent banner changes the mode of work depending on visitor\'s location. The list of locations was set manually by the user.';
                                $t_cook_23 = 'Make sure your setup is correct';
                            } else {
                                $t_cook_22 = esc_html__( 'Consent banner changes the mode of work depending on visitor\'s location. The list of locations was set manually by the user.', 'full-picture-analytics-cookie-notice' );
                                $t_cook_23 = esc_html__( 'Make sure your setup is correct', 'full-picture-analytics-cookie-notice' );
                            }
                            if ( $status == 'ok' ) {
                                $status = 'warning';
                            }
                            $this->data['cook']['setup'][] = ['warning', $t_cook_22, $t_cook_23];
                            // Opt-in
                            if ( $settings['optin'] == 'all' ) {
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_24 = 'Opt-in mode is used for visitors from all countries.';
                                } else {
                                    $t_cook_24 = esc_html__( 'Opt-in mode is used for visitors from all countries.', 'full-picture-analytics-cookie-notice' );
                                }
                                $this->data['cook']['tracked_extra_data'][] = [$t_cook_24];
                            }
                            if ( $settings['optin'] == 'none' ) {
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_25 = 'Opt-in mode is not used for visitors from any country.';
                                } else {
                                    $t_cook_25 = esc_html__( 'Opt-in mode is not used for visitors from any country.', 'full-picture-analytics-cookie-notice' );
                                }
                                if ( $status != 'alert' ) {
                                    $status = 'alert';
                                }
                                $this->data['cook']['setup'][] = ['alert', $t_cook_25, $guide_text];
                            }
                            if ( $settings['optin'] == 'specific' ) {
                                if ( isset( $settings['optin_countries'] ) ) {
                                    if ( $this->format == 'cdb' ) {
                                        $t_cook_26 = 'Opt-in mode is used for visitors from:';
                                    } else {
                                        $t_cook_26 = esc_html__( 'Opt-in mode is used for visitors from:', 'full-picture-analytics-cookie-notice' );
                                    }
                                    $this->data['cook']['setup'][] = ['warning', $t_cook_26 . ' ' . $settings['optin_countries'] . '.', $guide_text];
                                } else {
                                    if ( $this->format == 'cdb' ) {
                                        $t_cook_27 = 'Opt-in mode is not used for visitors from any country.';
                                    } else {
                                        $t_cook_27 = esc_html__( 'Opt-in mode is not used for visitors from any country.', 'full-picture-analytics-cookie-notice' );
                                    }
                                    if ( $status != 'alert' ) {
                                        $status = 'alert';
                                    }
                                    $this->data['cook']['setup'][] = ['alert', $t_cook_27, $guide_text];
                                }
                            }
                            // Opt-out
                            if ( $settings['optout'] == 'all' ) {
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_28 = 'Opt-out mode is used for visitors from all countries.';
                                } else {
                                    $t_cook_28 = esc_html__( 'Opt-out mode is used for visitors from all countries.', 'full-picture-analytics-cookie-notice' );
                                }
                                if ( $status != 'alert' ) {
                                    $status = 'alert';
                                }
                                $this->data['cook']['setup'][] = ['alert', $t_cook_28, $guide_text];
                            }
                            if ( $settings['optout'] == 'none' ) {
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_29 = 'Opt-out mode is not used for visitors from any country.';
                                } else {
                                    $t_cook_29 = esc_html__( 'Opt-out mode is not used for visitors from any country.', 'full-picture-analytics-cookie-notice' );
                                }
                                $this->data['cook']['setup'][] = ['ok', $t_cook_29];
                            }
                            if ( $settings['optout'] == 'specific' ) {
                                if ( isset( $settings['optout_countries'] ) ) {
                                    if ( $this->format == 'cdb' ) {
                                        $t_cook_30 = 'Opt-out mode is used for visitors from: ' . $settings['optout_countries'] . '.';
                                    } else {
                                        $t_cook_30 = sprintf( esc_html__( 'Opt-out mode is used for visitors from: %1$s.', 'full-picture-analytics-cookie-notice' ), $settings['optout_countries'] );
                                    }
                                    if ( $status == 'ok' ) {
                                        $status = 'warning';
                                    }
                                    $this->data['cook']['setup'][] = ['warning', $t_cook_30, $guide_text];
                                } else {
                                    if ( $this->format == 'cdb' ) {
                                        $t_cook_30_b = 'Opt-out mode is not used for visitors from any country.';
                                    } else {
                                        $t_cook_30_b = esc_html__( 'Opt-out mode is not used for visitors from any country.', 'full-picture-analytics-cookie-notice' );
                                    }
                                    $this->data['cook']['setup'][] = ['ok', $t_cook_30_b];
                                }
                            }
                            // Notify
                            if ( $settings['inform'] == 'all' ) {
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_31 = 'Visitors from all countries are notified about tracking but can\'t opt-out';
                                    $t_cook_32 = 'Change to opt-in mode or one of the automatic ones';
                                } else {
                                    $t_cook_31 = esc_html__( 'Visitors from all countries are notified about tracking but can\'t opt-out', 'full-picture-analytics-cookie-notice' );
                                    $t_cook_32 = esc_html__( 'Change to opt-in mode or one of the automatic ones', 'full-picture-analytics-cookie-notice' );
                                }
                                if ( $status != 'alert' ) {
                                    $status = 'alert';
                                }
                                $this->data['cook']['setup'][] = ['alert', $t_cook_31, $t_cook_32];
                            }
                            if ( $this->format == 'cdb' ) {
                                $t_cook_33 = 'Visitors from no country are only informed that they are tracked.';
                                // in 2 places
                                $t_cook_34 = 'Visitors from these countries are notified about tracking but can\'t opt-out:';
                            } else {
                                $t_cook_33 = esc_html__( 'Visitors from no country are only informed that they are tracked.', 'full-picture-analytics-cookie-notice' );
                                // in 2 places
                                $t_cook_34 = esc_html__( 'Visitors from these countries are notified about tracking but can\'t opt-out:', 'full-picture-analytics-cookie-notice' );
                            }
                            if ( $settings['inform'] == 'none' ) {
                                $this->data['cook']['tracked_extra_data'][] = [$t_cook_33];
                            } else {
                                if ( $settings['inform'] == 'specific' ) {
                                    if ( isset( $settings['inform_countries'] ) ) {
                                        if ( $status == 'ok' ) {
                                            $status = 'warning';
                                        }
                                        $this->data['cook']['setup'][] = ['warning', $t_cook_34 . ' ' . $settings['inform_countries'] . '.', $guide_text];
                                    } else {
                                        $this->data['cook']['setup'][] = ['ok', $t_cook_33];
                                    }
                                }
                            }
                            // Other
                            if ( $this->format == 'cdb' ) {
                                $t_cook_35 = 'Visitors from other countries are tracked without notification.';
                            } else {
                                $t_cook_35 = esc_html__( 'Visitors from other countries are tracked without notification.', 'full-picture-analytics-cookie-notice' );
                            }
                            $this->data['cook']['setup'][] = ['warning', $t_cook_35, $guide_text];
                            break;
                    }
                    // Geo fallback
                    if ( $settings['mode'] == 'auto_strict' || $settings['mode'] == 'auto_lax' || $settings['mode'] == 'manual' ) {
                        if ( !isset( $settings['enable_scripts_after'] ) ) {
                            $settings['enable_scripts_after'] = 'optin';
                        }
                        switch ( $settings['enable_scripts_after'] ) {
                            case 'optin':
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_36 = 'When visitor location is not found, consent banner will start tracking visitors only after they consent to tracking (Opt-in mode).';
                                } else {
                                    $t_cook_36 = esc_html__( 'When visitor location is not found, consent banner will start tracking visitors only after they consent to tracking (Opt-in mode).', 'full-picture-analytics-cookie-notice' );
                                }
                                $this->data['cook']['setup'][] = ['ok', $t_cook_36];
                                break;
                            case 'optout':
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_37 = 'When visitor location is not found, consent banner will start tracking visitors from the moment they enter the website but will let them decline tracking (Opt-out mode)';
                                    $t_cook_38 = 'Change to opt-in mode.';
                                } else {
                                    $t_cook_37 = esc_html__( 'When visitor location is not found, consent banner will start tracking visitors from the moment they enter the website but will let them decline tracking (Opt-out mode)', 'full-picture-analytics-cookie-notice' );
                                    $t_cook_38 = esc_html__( 'Change to opt-in mode.', 'full-picture-analytics-cookie-notice' );
                                }
                                if ( $status != 'alert' ) {
                                    $status = 'alert';
                                }
                                $this->data['cook']['setup'][] = ['alert', $t_cook_37, $t_cook_38];
                                break;
                            case 'notify':
                                if ( $this->format == 'cdb' ) {
                                    $t_cook_39 = 'When visitor location is not found, visitors will be notified that they are tracked bu they will not be able to decline tracking.';
                                    $t_cook_40 = 'Change to opt-in mode.';
                                } else {
                                    $t_cook_39 = esc_html__( 'When visitor location is not found, visitors will be notified that they are tracked bu they will not be able to decline tracking.', 'full-picture-analytics-cookie-notice' );
                                    $t_cook_40 = esc_html__( 'Change to opt-in mode.', 'full-picture-analytics-cookie-notice' );
                                }
                                if ( $status != 'alert' ) {
                                    $status = 'alert';
                                }
                                $this->data['cook']['setup'][] = ['alert', $t_cook_39, $t_cook_40];
                                break;
                        }
                    }
                }
                // when geo is disabled, the mode is set in the setting "enable_scripts_after"
            } else {
                if ( !isset( $settings['enable_scripts_after'] ) ) {
                    $settings['enable_scripts_after'] = 'optin';
                }
                switch ( $settings['enable_scripts_after'] ) {
                    case 'optin':
                        if ( $this->format == 'cdb' ) {
                            $t_cook_41 = 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).';
                        } else {
                            $t_cook_41 = esc_html__( 'Consent banner is set to start tracking visitors from all countries only after they consent to tracking (Opt-in mode).', 'full-picture-analytics-cookie-notice' );
                        }
                        $this->data['cook']['setup'][] = ['ok', $t_cook_41];
                        break;
                    case 'optout':
                        if ( $this->format == 'cdb' ) {
                            $t_cook_42 = 'Consent banner is set to start tracking visitors from the moment they enter the website but will let them decline tracking (Opt-out mode)';
                            $t_cook_43 = 'Change to the opt-in mode or one of automatic modes.';
                        } else {
                            $t_cook_42 = esc_html__( 'Consent banner is set to start tracking visitors from the moment they enter the website but will let them decline tracking (Opt-out mode)', 'full-picture-analytics-cookie-notice' );
                            $t_cook_43 = esc_html__( 'Change to the opt-in mode or one of automatic modes.', 'full-picture-analytics-cookie-notice' );
                        }
                        if ( $status != 'alert' ) {
                            $status = 'alert';
                        }
                        $this->data['cook']['setup'][] = ['alert', $t_cook_42, $t_cook_43];
                        break;
                    case 'notify':
                        if ( $this->format == 'cdb' ) {
                            $t_cook_44 = 'Visitors are notified that they are tracked but they are not able to decline tracking.';
                            $t_cook_45 = 'Change to the opt-in mode or one of automatic modes.';
                        } else {
                            $t_cook_44 = esc_html__( 'Visitors are notified that they are tracked but they are not able to decline tracking.', 'full-picture-analytics-cookie-notice' );
                            $t_cook_45 = esc_html__( 'Change to the opt-in mode or one of automatic modes.', 'full-picture-analytics-cookie-notice' );
                        }
                        if ( $status != 'alert' ) {
                            $status = 'alert';
                        }
                        $this->data['cook']['setup'][] = ['alert', $t_cook_44, $t_cook_45];
                        break;
                }
            }
            // reset when modules or PP change
            if ( isset( $settings['ask_for_consent_again'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_cook_46 = 'Visitors are asked for consent again, when the privacy policy text changes or when tracking modules are enabled.';
                } else {
                    $t_cook_46 = esc_html__( 'Visitors are asked for consent again, when the privacy policy text changes or when tracking modules are enabled.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data['cook']['setup'][] = ['ok', $t_cook_46];
            } else {
                if ( $this->format == 'cdb' ) {
                    $t_cook_47 = 'Visitors are not asked for new consent when the privacy policy text changes and/or when new tracking modules are enabled.';
                    $t_cook_48 = 'Enable it in the consent banner\'s settings';
                } else {
                    $t_cook_47 = esc_html__( 'Visitors are not asked for new consent when the privacy policy text changes and/or when new tracking modules are enabled.', 'full-picture-analytics-cookie-notice' );
                    $t_cook_48 = esc_html__( 'Enable it in the consent banner\'s settings', 'full-picture-analytics-cookie-notice' );
                }
                if ( $status != 'alert' ) {
                    $status = 'alert';
                }
                $this->data['cook']['setup'][] = ['alert', $t_cook_47, $t_cook_48];
            }
            // URL passthrough (checked later in Google Analytics and Google Ads)
            if ( isset( $settings['url_passthrough'] ) ) {
                $this->url_pass_enabled = true;
            }
        }
        // Privacy policy page
        if ( empty( $priv_policy_url ) ) {
            if ( $this->format == 'cdb' ) {
                $t_cook_49 = 'Privacy policy page is not set or is not published';
                $t_cook_50 = 'Please set it in "Settings > Privacy" page and make sure that it is published.';
            } else {
                $t_cook_49 = esc_html__( 'Privacy policy page is not set or is not published', 'full-picture-analytics-cookie-notice' );
                $t_cook_50 = esc_html__( 'Please set it in "Settings > Privacy" page and make sure that it is published.', 'full-picture-analytics-cookie-notice' );
            }
            if ( $status != 'alert' ) {
                $status = 'alert';
            }
            $this->data['cook']['setup'][] = ['alert', $t_cook_49, $t_cook_50];
        }
        // Are consent banner switches pre-selected?
        $styling_options = get_option( 'fupi_cookie_notice' );
        // if there are some pre-selected optin switches and these switches are also used in the optin mode
        if ( isset( $styling_options['switches_on'] ) && is_array( $styling_options['switches_on'] ) && !empty( $styling_options['optin_switches'] ) ) {
            // and we are not hiding the whole section with settings
            if ( isset( $styling_options['hide'] ) && is_array( $styling_options['hide'] ) && !in_array( 'settings_btn', $styling_options['hide'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_cook_51 = 'When visitors are asked for tracking consent (opt-in), switches for choosing allowed uses of tracked data are pre-selected.';
                    $t_cook_52 = 'Disable pre-selection of switches';
                } else {
                    $t_cook_51 = esc_html__( 'When visitors are asked for tracking consent (opt-in), switches for choosing allowed uses of tracked data are pre-selected.', 'full-picture-analytics-cookie-notice' );
                    $t_cook_52 = esc_html__( 'Disable pre-selection of switches', 'full-picture-analytics-cookie-notice' );
                }
                if ( $status != 'alert' ) {
                    $status = 'alert';
                }
                $this->data['cook']['setup'][] = ['alert', $t_cook_51, $t_cook_52];
            }
        }
        if ( $this->format == 'cdb' ) {
            $t_cook_53 = 'Add to your privacy policy information that WP Full Picture uses the following cookies:';
            $t_cook_54 = 'fp_cookie - a necessary cookie. It stores information on visitor\'s tracking consents, a list of tracking tools that a user agreed to and the date of the last update of the privacy policy page. Does not expire.';
            $t_cook_55 = 'fp_current_session - an optional cookie. It requires consent to tracking statistics. In the free version it does not hold any value and is only used to check if a new session has started. In the Pro version it holds the number and type of pages that a visitor viewed in a session, domain of the traffic source, URL parameters of the first landing page in a session and visitor\'s lead score. Expires when a visitor is inactive for 30 minutes.';
        } else {
            $t_cook_53 = esc_html__( 'Add to your privacy policy information that WP Full Picture uses the following cookies:', 'full-picture-analytics-cookie-notice' );
            $t_cook_54 = esc_html__( 'fp_cookie - a necessary cookie. It stores information on visitor\'s tracking consents, a list of tracking tools that a user agreed to and the date of the last update of the privacy policy page. Does not expire.', 'full-picture-analytics-cookie-notice' );
            $t_cook_55 = esc_html__( 'fp_current_session - an optional cookie. It requires consent to tracking statistics. In the free version it does not hold any value and is only used to check if a new session has started. In the Pro version it holds the number and type of pages that a visitor viewed in a session, domain of the traffic source, URL parameters of the first landing page in a session and visitor\'s lead score. Expires when a visitor is inactive for 30 minutes.', 'full-picture-analytics-cookie-notice' );
        }
        $pp_cookies_info = [$t_cook_53, [$t_cook_54, $t_cook_55]];
        // Saving consents
        // The data is from fupi_cook but shows in a separate section
        $cdb_section_title = ( $this->format == 'cdb' ? 'Saving consents' : esc_html__( 'Saving consents', 'full-picture-analytics-cookie-notice' ) );
        $this->data['cdb'] = [
            'module_name' => $cdb_section_title,
            'setup'       => [],
        ];
        // Default
        if ( $this->format == 'cdb' ) {
            $t_cook_10 = 'Saving proofs of visitor\'s tracking consents is disabled.';
            $t_cook_11 = 'Enable saving proofs of consent in the Consent Banner > Saving Consents. You may need it during audits or investigations by authorities or data protection agencies, if a user complains about being tracked without permission, in legal cases where privacy issues are involved.';
        } else {
            $t_cook_10 = esc_html__( 'Saving proofs of visitor\'s tracking consents is disabled.', 'full-picture-analytics-cookie-notice' );
            $t_cook_11 = esc_html__( 'Enable saving proofs of consent in the Consent Banner > Saving Consents. You may need it during audits or investigations by authorities or data protection agencies, if a user complains about being tracked without permission, in legal cases where privacy issues are involved.', 'full-picture-analytics-cookie-notice' );
        }
        $is_cdb_enabled = false;
        if ( !empty( $settings ) ) {
            if ( isset( $settings['cdb_key'] ) && !empty( $priv_policy_url ) ) {
                if ( $this->format != 'cdb' ) {
                    $t_cook_56 = esc_html__( 'Saving proofs of visitor\'s tracking consents is enabled.', 'full-picture-analytics-cookie-notice' );
                    $is_cdb_enabled = true;
                    $this->data['cdb']['setup'][] = ['ok', $t_cook_56];
                }
            }
            // Back to cookie notice settings
            if ( isset( $settings['cdb_key'] ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_cook_59 = 'cdb_id - a necessary cookie. It is saved after visitors agree or disagree to tracking in the opt-in banner. It stores a random device identifier used to match consents saved in the remote database with the device. Does not expire.';
                } else {
                    $t_cook_59 = esc_html__( 'cdb_id - a necessary cookie. It is saved after visitors agree or decline tracking in the opt-in banner. It stores a random device identifier used to match consents saved in the remote database with the device. Does not expire.', 'full-picture-analytics-cookie-notice' );
                }
                $pp_cookies_info[1][] = $t_cook_59;
            }
        }
        if ( $this->format != 'cdb' ) {
            if ( !$is_cdb_enabled ) {
                $this->data['cdb']['setup'][] = ['alert', $t_cook_10, $t_cook_11];
            }
        }
        $this->data['cook']['pp comments'][] = $pp_cookies_info;
        // Button which toggles consent banner
        $toggle_btn_enabled = !empty( $notice_opts['enable_toggle_btn'] );
        if ( $toggle_btn_enabled ) {
            if ( $this->format == 'cdb' ) {
                $t_cook_62 = 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click an icon in the corner of the screen';
            } else {
                $t_cook_62 = esc_html__( 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click an icon in the corner of the screen', 'full-picture-analytics-cookie-notice' );
            }
            $this->data['cook']['setup'][] = ['ok', $t_cook_62];
        } else {
            $priv_policy_id = get_option( 'wp_page_for_privacy_policy' );
            $priv_policy_post = get_post( $priv_policy_id );
            $toggler_found = false;
            if ( !empty( $priv_policy_post ) ) {
                $priv_policy_content = $priv_policy_post->post_content;
                $priv_policy_content = apply_filters( 'the_content', $priv_policy_content );
                $priv_policy_content = do_shortcode( $priv_policy_content );
                $toggle_selectors = ['fp_show_cookie_notice'];
                if ( !empty( $settings['toggle_selector'] ) && strlen( $settings['toggle_selector'] ) > 3 ) {
                    $toggle_selectors[] = ltrim( esc_attr( $settings['toggle_selector'] ), $settings['toggle_selector'][0] );
                }
                foreach ( $toggle_selectors as $sel ) {
                    if ( str_contains( $priv_policy_content, $sel ) ) {
                        $toggler_found = true;
                    }
                }
                if ( $toggler_found ) {
                    if ( $this->format == 'cdb' ) {
                        $t_cook_63 = 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click a link/button in the privacy policy.';
                    } else {
                        $t_cook_63 = esc_html__( 'Visitors who want to change their tracking preferences can do it in the consent banner which shows after they click a link/button in the privacy policy.', 'full-picture-analytics-cookie-notice' );
                    }
                    $this->data['cook']['setup'][] = ['ok', $t_cook_63];
                } else {
                    $toggle_selectors_str = '.fp_show_cookie_notice';
                    if ( !empty( $settings['toggle_selector'] ) && strlen( $settings['toggle_selector'] ) > 3 ) {
                        $toggle_selectors_str = $toggle_selectors_str . ', ' . esc_attr( $settings['toggle_selector'] );
                    }
                    if ( $this->format == 'cdb' ) {
                        $t_cook_64 = 'Make sure your visitors can open the consent banner popup to change their tracking preferences.';
                        $t_cook_65 = 'Please enable a toggle icon in the theme customizer (Appearance > Customize > Consent Banner) or add a button in your privacy policy with the CSS selector(s):';
                    } else {
                        $t_cook_64 = esc_html__( 'Make sure your visitors can open the consent banner popup to change their tracking preferences.', 'full-picture-analytics-cookie-notice' );
                        $t_cook_65 = esc_html__( 'Please enable a toggle icon in the theme customizer (Appearance > Customize > Consent Banner) or add a button in your privacy policy with the CSS selector(s):', 'full-picture-analytics-cookie-notice' );
                    }
                    $this->data['cook']['setup'][] = ['warning', $t_cook_64, $t_cook_65 . ' ' . $toggle_selectors_str . '.'];
                }
            } else {
                if ( $this->format == 'cdb' ) {
                    $t_cook_66 = 'Privacy policy page is missing or is not marked as such.';
                    $t_cook_67 = 'Create a privacy policy page and mark it as such in in the WordPress admin > Settings menu > Privacy page.';
                } else {
                    $t_cook_66 = esc_html__( 'Privacy policy page is missing or is not marked as such.', 'full-picture-analytics-cookie-notice' );
                    $t_cook_67 = esc_html__( 'Create a privacy policy page and mark it as such in in the WordPress admin > Settings menu > Privacy page.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data['cook']['setup'][] = ['alert', $t_cook_66, $t_cook_67];
            }
        }
        // Position of the consent banner
        $notice_position = ( !empty( $notice_opts['position'] ) ? esc_attr( $notice_opts['position'] ) : 'popup' );
        if ( $notice_position != 'popup' ) {
            if ( $this->format == 'cdb' ) {
                $t_cook_68 = 'The consent banner is not set to display in the center of the screen';
                $t_cook_69 = 'To collect maximum number of consents we recommend that you place your notice in the central position of the screen. This way people will not be able to navigate the site without making a choice, thus giving you more consents. You will also not lose information on the source of traffic, which can be accessed only on the first page the visitor sees.';
            } else {
                $t_cook_68 = esc_html__( 'The consent banner is not set to display in the center of the screen', 'full-picture-analytics-cookie-notice' );
                $t_cook_69 = esc_html__( 'To collect maximum number of consents we recommend that you place your notice in the central position of the screen. This way people will not be able to navigate the site without making a choice, thus giving you more consents. You will also not lose information on the source of traffic, which can be accessed only on the first page the visitor sees.', 'full-picture-analytics-cookie-notice' );
            }
            $this->data['cook']['opt-setup'][] = [$t_cook_68, $t_cook_69];
        }
        // TEXTS & STYLING
        $hidden_elements = ( isset( $notice_opts['hide'] ) && is_array( $notice_opts['hide'] ) ? $notice_opts['hide'] : [] );
        $hidden_descr = [];
        $default_texts = [
            'notif_h'           => '',
            'notif_descr'       => esc_html__( 'We use cookies to provide you with the best browsing experience, personalize content of our site, analyse its traffic and show you relevant ads. See our {{privacy policy}} for more information.', 'full-picture-analytics-cookie-notice' ),
            'agree'             => esc_html__( 'Agree', 'full-picture-analytics-cookie-notice' ),
            'ok'                => esc_html__( 'I understand', 'full-picture-analytics-cookie-notice' ),
            'decline'           => esc_html__( 'Decline', 'full-picture-analytics-cookie-notice' ),
            'cookie_settings'   => esc_html__( 'Settings', 'full-picture-analytics-cookie-notice' ),
            'agree_to_selected' => esc_html__( 'Agree to selected', 'full-picture-analytics-cookie-notice' ),
            'return'            => esc_html__( 'Return', 'full-picture-analytics-cookie-notice' ),
            'necess_h'          => '',
            'necess_descr'      => '',
            'stats_h'           => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
            'stats_descr'       => esc_html__( 'I want to help you make this site better so I will provide you with data about my use of this site.', 'full-picture-analytics-cookie-notice' ),
            'pers_h'            => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
            'pers_descr'        => esc_html__( 'I want to have the best experience on this site so I agree to saving my choices, recommending things I may like and modifying the site to my liking', 'full-picture-analytics-cookie-notice' ),
            'market_h'          => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
            'market_descr'      => esc_html__( 'I want to see ads with your offers, coupons and exclusive deals rather than random ads from other advertisers.', 'full-picture-analytics-cookie-notice' ),
        ];
        $current_texts = [
            'notification_headline'           => ( !empty( $notice_opts['notif_headline_text'] ) ? esc_html( $notice_opts['notif_headline_text'] ) : $default_texts['notif_h'] ),
            'agree_to_all_cookies_button'     => ( !empty( $notice_opts['agree_text'] ) ? esc_html( $notice_opts['agree_text'] ) : $default_texts['agree'] ),
            'i_understand_button'             => ( !empty( $notice_opts['ok_text'] ) ? esc_html( $notice_opts['ok_text'] ) : $default_texts['ok'] ),
            'decline_button'                  => ( !empty( $notice_opts['decline_text'] ) ? esc_html( $notice_opts['decline_text'] ) : $default_texts['decline'] ),
            'cookie_settings_button'          => ( !empty( $notice_opts['cookie_settings_text'] ) ? esc_html( $notice_opts['cookie_settings_text'] ) : $default_texts['cookie_settings'] ),
            'agree_to_selected_button'        => ( !empty( $notice_opts['agree_to_selected_text'] ) ? esc_html( $notice_opts['agree_to_selected_text'] ) : $default_texts['agree_to_selected'] ),
            'return_button'                   => ( !empty( $notice_opts['return_text'] ) ? esc_html( $notice_opts['return_text'] ) : $default_texts['return'] ),
            'necessary_cookies_headline'      => ( !empty( $notice_opts['necess_headline_text'] ) ? esc_html( $notice_opts['necess_headline_text'] ) : '' ),
            'statistics_hookies_headline'     => ( !empty( $notice_opts['stats_headline_text'] ) ? esc_html( $notice_opts['stats_headline_text'] ) : $default_texts['stats_h'] ),
            'peronalisation_cookies_headline' => ( !empty( $notice_opts['pers_headline_text'] ) ? esc_html( $notice_opts['pers_headline_text'] ) : $default_texts['pers_h'] ),
            'marketing_cookies_headline'      => ( !empty( $notice_opts['marketing_headline_text'] ) ? esc_html( $notice_opts['marketing_headline_text'] ) : $default_texts['market_h'] ),
            'notification_main_descr'         => ( !empty( $notice_opts['notif_text'] ) ? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['notif_text'] ) ) : $this->fupi_modify_cons_banner_text( $default_texts['notif_descr'] ) ),
            'necessary_cookies_descr'         => ( !empty( $notice_opts['necess_text'] ) ? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['necess_text'] ) ) : '' ),
            'statistics_cookies_descr'        => ( !empty( $notice_opts['stats_text'] ) ? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['stats_text'] ) ) : $default_texts['stats_descr'] ),
            'personalisation_cookies_descr'   => ( !empty( $notice_opts['pers_text'] ) ? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['pers_text'] ) ) : $default_texts['pers_descr'] ),
            'marketing_cookies_descr'         => ( !empty( $notice_opts['marketing_text'] ) ? $this->fupi_modify_cons_banner_text( esc_html( $notice_opts['marketing_text'] ) ) : $default_texts['market_descr'] ),
        ];
        $this->data['cook']['notice_texts'] = $current_texts;
        if ( count( $hidden_descr ) > 0 ) {
            if ( $this->format == 'cdb' ) {
                $t_cook_70 = 'button opening panel with cookie settings';
                $t_cook_71 = 'section where users can consent to the use of their data for statistics';
                $t_cook_72 = 'section where users can consent to the use of their data for marketing';
                $t_cook_73 = 'section where users can consent to the use of their data for personalisation';
                $t_cook_74 = '"Decline" button';
                $t_cook_75 = 'The consent banner does not display the "Decline" button';
                $t_cook_76 = 'Do not hide the "Decline" button';
                $t_cook_77 = 'Hidden consent baner elements:';
            } else {
                $t_cook_70 = esc_html__( 'button opening panel with cookie settings', 'full-picture-analytics-cookie-notice' );
                $t_cook_71 = esc_html__( 'section where users can consent to the use of their data for statistics', 'full-picture-analytics-cookie-notice' );
                $t_cook_72 = esc_html__( 'section where users can consent to the use of their data for marketing', 'full-picture-analytics-cookie-notice' );
                $t_cook_73 = esc_html__( 'section where users can consent to the use of their data for personalisation', 'full-picture-analytics-cookie-notice' );
                $t_cook_74 = esc_html__( '"Decline" button', 'full-picture-analytics-cookie-notice' );
                $t_cook_75 = esc_html__( 'The consent banner does not display the "Decline" button', 'full-picture-analytics-cookie-notice' );
                $t_cook_76 = esc_html__( 'Do not hide the "Decline" button', 'full-picture-analytics-cookie-notice' );
                $t_cook_77 = esc_html__( 'Hidden consent baner elements:', 'full-picture-analytics-cookie-notice' );
            }
            if ( in_array( 'settings_btn', $hidden_elements ) ) {
                $hidden_descr[] = $t_cook_70;
            }
            if ( in_array( 'stats', $hidden_elements ) ) {
                $hidden_descr[] = $t_cook_71;
            }
            if ( in_array( 'market', $hidden_elements ) ) {
                $hidden_descr[] = $t_cook_72;
            }
            if ( in_array( 'pers', $hidden_elements ) ) {
                $hidden_descr[] = $t_cook_73;
            }
            if ( in_array( 'decline_btn', $hidden_elements ) ) {
                $status = 'alert';
                $hidden_descr[] = $t_cook_74;
                $this->data['cook']['setup'][] = ['alert', $t_cook_75, $t_cook_76];
            }
            $this->data['cook']['hidden_elements'] = $t_cook_77 . ' ' . join( ', ', $hidden_descr ) . '.';
        }
        $this->consent_status = $status;
    }

    //
    // GTM STATUS
    //
    private function get_gtm_status( $info, $settings ) {
        if ( $this->req_consent_banner != 'yes' ) {
            $this->req_consent_banner = 'maybe';
        }
        // PP
        $tracked_priv_info = [];
        if ( $this->format == 'cdb' ) {
            $t_gtm_1 = 'User ID';
            $t_gtm_2 = 'Name and surname of a user or a client';
            $t_gtm_3 = 'User\'s email address and/or an email address of a client (even when not logged in, collected at the time of purchase)';
            $t_gtm_4 = 'User\'s phone number and/or phone number of a client (even when not logged in, collected at the time of purchase)';
            $t_gtm_5 = 'User\'s physical address and/or address of a client (even when not logged in, collected at the time of purchase)';
        } else {
            $t_gtm_1 = esc_html__( 'User ID', 'full-picture-analytics-cookie-notice' );
            $t_gtm_2 = esc_html__( 'Name and surname of a user or a client', 'full-picture-analytics-cookie-notice' );
            $t_gtm_3 = esc_html__( 'User\'s email address and/or an email address of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice' );
            $t_gtm_4 = esc_html__( 'User\'s phone number and/or phone number of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice' );
            $t_gtm_5 = esc_html__( 'User\'s physical address and/or address of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice' );
        }
        if ( isset( $settings['user_id'] ) ) {
            $tracked_priv_info[] = $t_gtm_1;
        }
        if ( isset( $settings['user_realname'] ) ) {
            $tracked_priv_info[] = $t_gtm_2;
        }
        if ( isset( $settings['user_email'] ) ) {
            $tracked_priv_info[] = $t_gtm_3;
        }
        if ( isset( $settings['user_phone'] ) ) {
            $tracked_priv_info[] = $t_gtm_4;
        }
        if ( isset( $settings['user_address'] ) ) {
            $tracked_priv_info[] = $t_gtm_5;
        }
        if ( in_array( 'woo', $this->tools ) ) {
            if ( $this->format == 'cdb' ) {
                $t_gtm_6 = 'Order ID (in WooCommerce)';
            } else {
                $t_gtm_6 = esc_html__( 'Order ID (in WooCommerce)', 'full-picture-analytics-cookie-notice' );
            }
            $tracked_priv_info[] = $t_gtm_6;
        }
        if ( isset( $settings['track_cf'] ) && is_array( $settings['track_cf'] ) ) {
            foreach ( $settings['track_cf'] as $tracked_meta ) {
                if ( substr( $tracked_meta['id'], 0, 5 ) == 'user|' ) {
                    if ( $this->format == 'cdb' ) {
                        $t_gtm_7 = 'User metadata with ID';
                    } else {
                        $t_gtm_7 = esc_html__( 'User metadata with ID', 'full-picture-analytics-cookie-notice' );
                    }
                    $tracked_priv_info[] = $t_gtm_7 . ' ' . substr( $tracked_meta['id'], 5 );
                }
            }
        }
        if ( count( $tracked_priv_info ) > 0 ) {
            foreach ( $tracked_priv_info as $str ) {
                $this->data['gtm']['tracked_extra_data'][] = [$str];
            }
        }
        if ( $this->format == 'cdb' ) {
            $t_gtm_8 = 'Your privacy policy must include information about tracking tools that are loaded with GTM, what data is tracked, what you and these tools use it for and who the providers of these tools share this data with or sell it to.';
        } else {
            $t_gtm_8 = esc_html__( 'The privacy policy must include information about tracking tools that are loaded with GTM, what data is tracked, what you and these tools use it for and who the providers of these tools share this data with or sell it to.', 'full-picture-analytics-cookie-notice' );
        }
        $this->data['gtm']['pp comments'][] = $t_gtm_8;
        // Setup (always second)
        if ( count( $tracked_priv_info ) > 0 ) {
            if ( !in_array( 'cook', $this->tools ) ) {
                if ( $this->format == 'cdb' ) {
                    $t_gtm_10 = 'Tracking tools loaded with GTM need to be used with a consent banner';
                    $t_gtm_11 = 'Enable and set up the consent banner module and trigger GTM tags after visitors consent to tracking.';
                } else {
                    $t_gtm_10 = esc_html__( 'Tracking tools loaded with GTM need to be used with a consent banner', 'full-picture-analytics-cookie-notice' );
                    $t_gtm_11 = esc_html__( 'Enable and set up the consent banner module and trigger GTM tags after visitors consent to tracking.', 'full-picture-analytics-cookie-notice' );
                }
                $this->data['gtm']['setup'][] = ['alert', $t_gtm_10, $t_gtm_11];
            } else {
                $gtm_setup_status = ( $this->consent_status == 'alert' ? 'alert' : 'warning' );
                if ( $this->format == 'cdb' ) {
                    $t_gtm_12 = 'Make sure to trigger GTM tags after visitors consent to tracking.';
                    $t_gtm_13 = 'Tracking tools loaded with GTM need to be loaded after visitors consent to tracking in the consent banner.';
                } else {
                    $t_gtm_12 = esc_html__( 'Make sure to trigger GTM tags after visitors consent to tracking.', 'full-picture-analytics-cookie-notice' );
                    $t_gtm_13 = esc_html__( 'Tracking tools loaded with GTM need to be loaded after visitors consent to tracking in the consent banner.', 'full-picture-analytics-cookie-notice' );
                }
                $extra = $this->get_extra_text() . ' ' . $t_gtm_12;
                $this->data['gtm']['setup'][] = [$gtm_setup_status, $t_gtm_13, $extra];
            }
        } else {
            if ( $this->format == 'cdb' ) {
                $t_gtm_14 = 'Tracking tools loaded with GTM may require the use of a consent banner';
                $t_gtm_15 = 'Make sure that none of the tools you install with GTM track personaly indentifiable information.';
            } else {
                $t_gtm_14 = esc_html__( 'Tracking tools loaded with GTM may require the use of a consent banner', 'full-picture-analytics-cookie-notice' );
                $t_gtm_15 = esc_html__( 'Make sure that none of the tools you install with GTM track personaly indentifiable information.', 'full-picture-analytics-cookie-notice' );
            }
            $this->data['gtm']['setup'][] = ['warning', $t_gtm_14, $t_gtm_15];
        }
    }

}
