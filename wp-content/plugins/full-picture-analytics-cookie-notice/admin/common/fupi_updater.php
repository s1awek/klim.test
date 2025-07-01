<?php

// GET PREVIOUS VERSION
$prev_version = '1.0.0';
// default
$is_fresh_install = false;
$regenerate_cdb = false;
$updated = false;
// probably a new install or updated from version before v4
if ( empty( $this->versions ) ) {
    // maybe a new install or updated from 1 (it had no "tools")
    if ( empty( $this->tools ) ) {
        // is a new install
        $is_fresh_install = true;
        $prev_version = $this->version;
        // is updated from v2
    } else {
        $prev_version = '2.0.0';
    }
    // updated from after v4 or newer
} else {
    $prev_version = $this->versions[1];
}
// GET THE TIME OF THE FIRST INSTALL
if ( $is_fresh_install || version_compare( $prev_version, '4.2.0' ) == -1 ) {
    //-1 if prev is lower, 0 if equal
    $date = new DateTime();
    $install_date = $date->getTimestamp();
} else {
    $install_date = $this->versions[0];
}
// if is fresh install
if ( $is_fresh_install ) {
    $this->versions = array($install_date, $this->version);
    update_option( 'fupi_versions', array($install_date, $this->version) );
}
// DO UPDATES STEP BY STEP
// UPDATE TO V2
if ( version_compare( $prev_version, '2.0.0' ) == -1 ) {
    $updated = true;
    // we check if Consent Banner is enabled
    if ( isset( $this->main['cookie_notice'] ) ) {
        $this->main['cook'] = '1';
        // cookies are enabled
        add_option( 'fupi_cook', $this->main );
        // cookies get separate settings page
    }
    add_option( 'fupi_tools', $this->main );
    // tools get separate settings page
}
// UPDATE TO V4
if ( version_compare( $prev_version, '4.0.0' ) == -1 ) {
    $updated = true;
    $this->tools = get_option( 'fupi_tools' );
    // we need to take it again since it could have been updated by previous update
    // track 404
    if ( isset( $this->main['track_404'] ) ) {
        $this->tools['track404'] = 1;
        if ( isset( $this->main['redirect_404'] ) ) {
            add_option( 'fupi_track404', [
                'redirect_404' => $this->main['redirect_404'],
            ] );
        }
    }
    // label pages
    if ( isset( $this->main['label_pages'] ) ) {
        $this->tools['labelpages'] = 1;
    }
    // label pages
    $this->tools['privex'] = 1;
    // track custom meta
    if ( isset( $this->main['custom_data_ids'] ) ) {
        $this->tools['trackmeta'] = 1;
        add_option( 'fupi_trackmeta', [
            'custom_data_ids' => $this->main['custom_data_ids'],
        ] );
    }
    // geolocation & iframes blocking
    if ( !empty( $this->tools['cook'] ) ) {
        $this->tools['geo'] = 1;
        $this->tools['iframeblock'] = 1;
        $cook = get_option( 'fupi_cook' );
        if ( !empty( $cook ) ) {
            // geo options
            $geo_opts = [];
            if ( !empty( $cook['geo'] ) ) {
                $geo_opts['geo'] = $cook['geo'];
            }
            if ( !empty( $cook['cf_worker_url'] ) ) {
                $geo_opts['cf_worker_url'] = $cook['cf_worker_url'];
            }
            if ( !empty( $cook['ipdata_api_key'] ) ) {
                $geo_opts['ipdata_api_key'] = $cook['ipdata_api_key'];
            }
            if ( !empty( $cook['remember_geo'] ) ) {
                $geo_opts['remember_geo'] = $cook['remember_geo'];
            }
            add_option( 'fupi_geo', $geo_opts );
            // iframe blocking options
            $iframe_opts = [];
            if ( !empty( $cook['iframe_img'] ) ) {
                $iframe_opts['iframe_img'] = $cook['iframe_img'];
            }
            if ( !empty( $cook['iframe_lazy'] ) ) {
                $iframe_opts['iframe_lazy'] = $cook['iframe_lazy'];
            }
            add_option( 'fupi_iframeblock', $iframe_opts );
        }
    }
    // update in db
    update_option( 'fupi_tools', $this->tools );
}
// UPDATE TO V4.1
if ( version_compare( $prev_version, '4.1.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower than
    $updated = true;
    // Consent Banner UPDATES
    $cook_opts = get_option( 'fupi_cook' );
    // we need to take it again since it could have been updated by the previous update
    if ( !empty( $cook_opts ) ) {
        // here we add "optin" and "optout" options based on the options in the DB
        // we do not change "enable_scripts_after" option
        // it doesn't matter whether the geo is enabled or not - admin might have enabled geo without changing & saving Consent Banner options
        // GET CURRENT OPTIONS
        // get where to show the notice
        $show_notice_in = 'all countries';
        if ( isset( $cook_opts['show_notice'] ) && $cook_opts['show_notice'] == 'in_countries' && !empty( $cook_opts['show_to_countries'] ) ) {
            $show_notice_in = 'some countries';
            $show_countries_str = $cook_opts['show_to_countries'];
            $show_countries_str_trimmed = str_replace( ' ', '', $show_countries_str );
            $show_countries_arr = explode( ',', $show_countries_str_trimmed );
        }
        // get where to require consent
        $require_consent_in = 'all countries';
        if ( $cook_opts['enable_scripts_after'] == 'pageload' ) {
            $require_consent_in = 'nowhere';
        } else {
            if ( $cook_opts['enable_scripts_after'] == 'auto' ) {
                $require_consent_in = 'some countries';
                $req_consent_str = 'AT, BE, BG, CY, CZ, DE, DK, ES, EE, FI, FR, GB, GR, HR, HU, IE, IT, LT, LU, LV, MT, NL, PL, PT, RO, SK, SI, SE, MX, NG';
                // this string is used below
                $req_consent_str_trimmed = str_replace( ' ', '', $req_consent_str );
                $req_countries_arr = explode( ',', $req_consent_str_trimmed );
            } else {
                if ( $cook_opts['enable_scripts_after'] == 'choice' && !empty( $cook_opts['req_consent_in_countries'] ) ) {
                    $require_consent_in = 'some countries';
                    $req_consent_str = $cook_opts['req_consent_in_countries'];
                    $req_consent_str_trimmed = str_replace( ' ', '', $req_consent_str );
                    $req_countries_arr = explode( ',', $req_consent_str_trimmed );
                }
            }
        }
        // "TRANSLATE" CURRENT OPTIONS INTO "OPTIN" AND "OPTOUT"
        $optin = 'all';
        $optin_countries = '';
        $optout = 'none';
        $optout_countries = '';
        if ( $show_notice_in == 'all countries' && $require_consent_in == 'nowhere' ) {
            $optin = 'none';
            $optout = 'rest';
        } else {
            if ( $show_notice_in == 'all countries' && $require_consent_in == 'some countries' ) {
                $optin = 'specific';
                $optin_countries = $req_consent_str;
                $optout = 'rest';
            } else {
                if ( $show_notice_in == 'some countries' && $require_consent_in == 'all countries' ) {
                    // this situation is only when the user makes an error when setting things up
                    $optin = 'specific';
                    $optin_countries = $show_countries_str;
                } else {
                    if ( $show_notice_in == 'some countries' && $require_consent_in == 'nowhere' ) {
                        // we load tools on pageload in some countries and don't show the notice in the rest
                        $optin = 'none';
                        $optout = 'specific';
                        $optout_countries = $show_countries_str;
                    } else {
                        if ( $show_notice_in == 'some countries' && $require_consent_in == 'some countries' ) {
                            // it is possible that the user made an error while specyfying countries
                            $optin = 'specific';
                            $optin_countries_arr = array_intersect( $show_countries_arr, $req_countries_arr );
                            $optin_countries = implode( ', ', $optin_countries_arr );
                            $optout = 'specific';
                            $optout_countries_arr = array_diff( $show_countries_arr, $optin_countries_arr );
                            $optout_countries = implode( ', ', $optout_countries_arr );
                        }
                    }
                }
            }
        }
        $cook_opts['optin'] = $optin;
        // all, none or specific
        if ( !empty( $optin_countries ) ) {
            $cook_opts['optin_countries'] = $optin_countries;
        }
        $cook_opts['optout'] = $optout;
        // rest, none or specific
        if ( !empty( $optout_countries ) ) {
            $cook_opts['optout_countries'] = $optout_countries;
        }
        update_option( 'fupi_cook', $cook_opts );
    }
}
// UPDATE TO V4.3
if ( version_compare( $prev_version, '4.3.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    $this->string_settings_to_array( 'fupi_ga1', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_ga2', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_cegg', [
        'tag_affiliate',
        'tag_elems',
        'tag_forms',
        'tag_views'
    ] );
    $this->string_settings_to_array( 'fupi_clar', [
        'tag_affiliate',
        'tag_elems',
        'tag_forms',
        'tag_views'
    ] );
    $this->string_settings_to_array( 'fupi_fbp1', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_fbp2', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_ga41', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_ga42', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_gads', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_gtm', [
        'track_views',
        'track_affiliate',
        'track_elems',
        'track_forms'
    ] );
    $this->string_settings_to_array( 'fupi_hotj', [
        'tag_affiliate',
        'tag_elems',
        'tag_forms',
        'tag_views'
    ] );
    $this->string_settings_to_array( 'fupi_insp', [
        'tag_affiliate',
        'tag_elems',
        'tag_forms',
        'tag_views'
    ] );
    $this->string_settings_to_array( 'fupi_linkd', ['track_affiliate', 'track_elems', 'track_forms'] );
    $this->string_settings_to_array( 'fupi_mads', ['track_affiliate', 'track_elems', 'track_forms'], ['track_file_downl'] );
    $this->string_settings_to_array( 'fupi_pla', ['track_affiliate_2', 'track_elems_2', 'track_forms_2'] );
    $this->string_settings_to_array( 'fupi_sbee', ['track_affiliate', 'track_elems', 'track_forms'] );
    $this->string_settings_to_array( 'fupi_tik', ['track_elems', 'track_forms'] );
    $this->string_settings_to_array( 'fupi_twit', ['track_affiliate', 'track_elems', 'track_forms'] );
}
// UPDATE TO V5.0
// Copy FBP2 pixel id to the FBP1 settings
if ( version_compare( $prev_version, '5.0.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    if ( isset( $this->tools['fbp2'] ) ) {
        $fbp2_data = get_option( 'fupi_fbp2' );
        if ( isset( $fbp2_data['pixel_id'] ) ) {
            if ( isset( $this->tools['fbp1'] ) ) {
                $fbp1_data = get_option( 'fupi_fbp1' );
                $fbp1_data['pixel_id_2'] = $fbp2_data['pixel_id'];
                $fbp1_data['extra_install'] = 1;
                update_option( 'fupi_fbp1', $fbp1_data );
            }
        }
    }
}
// UPDATE VERSION DATA
// if ( $updated ) {
//     $versions = get_option('fupi_versions');
//     if ( is_array( $versions ) && isset( $versions[1] ) ) {
//         $versions[1] = $this->version;
//         update_option( 'fupi_versions' , $versions );
//     }
// }
// UPDATE TO V5.1
// CHANGE THE KEYS AND VALUES OF SOME CONSENT BANNER SETTINGS
if ( version_compare( $prev_version, '5.1.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    $cook = get_option( 'fupi_cook' );
    if ( !empty( $cook ) ) {
        if ( isset( $cook['enable_scripts_after'] ) ) {
            $val = $cook['enable_scripts_after'];
            $cook['enable_scripts_after'] = ( (( $val == 'pageload' ? 'optout' : $val == 'consent' )) ? 'optin' : 'notify' );
        }
        if ( isset( $cook['default_behavior'] ) ) {
            $val = $cook['default_behavior'];
            $cook['enable_scripts_after'] = ( (( $val == 'pageload' ? 'optout' : $val == 'consent' )) ? 'optin' : 'notify' );
            unset($cook['default_behavior']);
        }
        if ( isset( $cook['auto_mode'] ) ) {
            $val = $cook['auto_mode'];
            $cook['mode'] = ( (( $val == 'strict' ? 'auto_strict' : $val == 'lax' )) ? 'auto_lax' : 'manual' );
            unset($cook['auto_mode']);
        }
        update_option( 'fupi_cook', $cook );
    }
}
// UPDATE TO V5.2
// if ( version_compare( $prev_version, '5.2.0' ) == -1 ) { // 0 if equal, -1 if prev is lower
//     $updated = true;
//     $new_data = [];
//     // change format of data with checklist tasks statuses
//     if ( ! empty ( $this->versions ) ) {
//         foreach( $this->versions as $page_id => $task_data ) {
//             if ( is_array( $task_data ) ) {
//                 foreach( $task_data as $task ){
//                     $new_data[$page_id][$task[0]] = $task[1];
//                 };
//             } else {
//                 $new_data[$page_id] = $task_data;
//             }
//         };
//     };
//     $new_data[0] = $install_date;
//     $new_data[1] = $this->version;
//     update_option( 'fupi_versions' , $new_data );
// }
// UPDATE TO V6.1.0
if ( version_compare( $prev_version, '6.1.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    // we need to take the fresh data every time
    $tools_opts = get_option( 'fupi_tools' );
    $reports_opts = get_option( 'fupi_reports' );
    $pla_opts = get_option( 'fupi_pla' );
    if ( !empty( $reports_opts ) || !empty( $pla_opts ) && isset( $pla_opts['shared_link_url'] ) ) {
        $tools_opts['reports'] = 1;
        update_option( 'fupi_tools', $tools_opts );
    }
}
if ( version_compare( $prev_version, '7.0.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    $tools_opts = get_option( 'fupi_tools' );
    if ( !empty( $tools_opts['gtm'] ) ) {
        $gtm_options = get_option( 'fupi_gtm' );
        if ( !empty( $gtm_options ) && !empty( $gtm_options['format'] ) && $gtm_options['format'] !== 'v2' ) {
            // prepare and send email to admin
            $recipient = get_bloginfo( 'admin_email' );
            $email_headers = 'Content-Type: text/html; charset=UTF-8';
            $email_title = 'Important changes to the GTM dataLayer for ' . get_bloginfo( 'url' );
            $email_body = 'This is an automatic message sent by the plugin from your website.<br><br>
        
            Dear admin of ' . get_bloginfo( 'url' ) . ' and a user of WP Full Picture plugin.<br><br>
            The new and greatly improved formatting of the GTM dataLayer introduced in WP Full Picture 4.8.2 in June 2023 has now become a standard. The old formatting is no longer available.<br><br>
            You are getting this email, because we have noticed that you have not moved to the new version yet. Please check your GTM setup, tags, variables and triggers. They may have been affected by the change and no longer work.<br><br>
            Krzysztof Planeta<br>
            The developer of WP Full Picture';
            // send the mail
            mail(
                $recipient,
                $email_title,
                $email_body,
                $email_headers
            );
            // ! do not use wp_mail - it won't work because it doesn't include the wp-load.php
        }
    }
    if ( !empty( $tools_opts['sbee'] ) ) {
        // prepare and send email to admin
        $recipient = get_bloginfo( 'admin_email' );
        $email_headers = 'Content-Type: text/html; charset=UTF-8';
        $email_title = 'Planned removal of to the Splitbee integration in WP Full Picture plugin installed in ' . get_bloginfo( 'url' );
        $email_body = 'This is an automatic message sent by the plugin from your website.<br><br>
    
        Dear admin of ' . get_bloginfo( 'url' ) . ' and a user of WP Full Picture plugin.<br><br>
        Since Splitbee tool is no longer available for new users, we have decided to remove the integration with Splitbee from WP Full Picture at the begining of March this year.<br><br>
        If you wish to continue using Splitbee after this time, please install its tracking script using the "Custom Scripts" module or do not update WP Full Picture (not recommended).<br><br>
        Krzysztof Planeta<br>
        The developer of WP Full Picture';
        // send the mail
        mail(
            $recipient,
            $email_title,
            $email_body,
            $email_headers
        );
        // ! do not use wp_mail - it won't work because it doesn't include the wp-load.php
    }
}
if ( version_compare( $prev_version, '7.3.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    $tools_opts = get_option( 'fupi_tools' );
    if ( !empty( $tools_opts['sbee'] ) ) {
        // prepare and send email to admin
        $recipient = get_bloginfo( 'admin_email' );
        $email_headers = 'Content-Type: text/html; charset=UTF-8';
        $email_title = 'Splitbee integration has been removed from ' . get_bloginfo( 'url' );
        $email_body = 'This is an automatic message sent by the WP Full Picture plugin from your website.<br><br>
    
        Dear administrator of ' . get_bloginfo( 'url' ) . ' and a user of WP Full Picture plugin.<br><br>
        As previously announced, integration with Splitbee tool has been removed from WP Full Picture.<br><br>
        If you wish to continue using Splitbee, please install its tracking script using the "Custom Scripts" module.<br><br>
        Krzysztof Planeta<br>
        The author of WP Full Picture';
        // send the mail
        mail(
            $recipient,
            $email_title,
            $email_body,
            $email_headers
        );
        // ! do not use wp_mail - it won't work because it doesn't include the wp-load.php
    }
}
// Introduced for 7.4.0 (ver num below is lower in order not to mess up settings of beta users)
if ( version_compare( $prev_version, '7.3.5' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    $ga41_42 = ['fupi_ga41', 'fupi_ga42'];
    foreach ( $ga41_42 as $ga_id ) {
        $ga_opts = get_option( $ga_id );
        if ( empty( $ga_opts ) ) {
            continue;
        }
        // add values to new or changed fields
        $fields = [
            [
                'page_type',
                // if this field has any value
                'page_type',
                // then set the value of this field
                'page_type',
            ],
            ['page_id', 'page_id', 'page_id'],
            ['page_number', 'page_number', 'page_number'],
            ['post_date', 'post_date', 'post_date'],
            ['user_role', 'user_role', 'user_role'],
            ['page_lang', 'page_lang', 'page_lang'],
            ['tax_terms', 'tax_terms', 'taxonomy_terms'],
            ['seo_title', 'seo_title', 'seo_title'],
            ['post_author', 'post_author', 'post_author'],
            ['search_results_nr', 'search_results_nr', 'search_results_nr'],
            ['author_id', 'author_id', 'author_id'],
            ['track_scroll', 'track_scroll_method', 'params'],
            ['track_views', 'track_views_method', 'params'],
            ['track_forms', 'track_forms_method', 'params'],
            ['track_elems', 'track_elems_method', 'params'],
            ['track_affiliate', 'track_affil_method', 'params'],
            ['track_email_tel', 'track_email_tel', 'params']
        ];
        foreach ( $fields as $field_a ) {
            if ( !empty( $ga_opts[$field_a[0]] ) ) {
                $ga_opts[$field_a[1]] = $field_a[2];
            }
        }
        update_option( $ga_id, $ga_opts );
    }
    $tools_opts = get_option( 'fupi_tools' );
    if ( !empty( $tools_opts['ga41'] ) ) {
        // prepare and send email to admin
        $recipient = get_bloginfo( 'admin_email' );
        $email_headers = 'Content-Type: text/html; charset=UTF-8';
        $email_title = 'Changes to Google Analytics event\'s names in ' . get_bloginfo( 'url' );
        $email_body = 'This is an automatic message sent by the WP Full Picture plugin from your website.<br><br>
    
        Dear administrator of ' . get_bloginfo( 'url' ) . ' and a user of WP Full Picture plugin.<br><br>
        In the latest update of WP Full picture, we corrected the names of some Google Analytics events:<br><br>
        - "js error" event is now "js_error"<br>
        - "email_link click" is now "email_link_click"<br>
        - "tel_link click" is now "tel_link_click"<br><br>
        If you used them in GA\'s custom reports, you may want to update them to reflect the latest changes.<br><br>
        Krzysztof Planeta<br>
        The author of WP Full Picture';
        // send the mail
        mail(
            $recipient,
            $email_title,
            $email_body,
            $email_headers
        );
        // ! do not use wp_mail - it won't work because it doesn't include the wp-load.php
    }
}
// Introduced for 8.0 (ver num below is lower in order not to mess up settings for beta users)
if ( version_compare( $prev_version, '7.6.56' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $updated = true;
    // Updates user selector in Reports options
    $reports_opts = get_option( 'fupi_reports' );
    if ( !empty( $reports_opts ) && !empty( $reports_opts['allowed_users'] ) ) {
        $result = [];
        foreach ( $reports_opts['allowed_users'] as $user_val ) {
            if ( is_array( $user_val ) && isset( $user_val['user'] ) ) {
                if ( is_numeric( $user_val['user'] ) ) {
                    if ( is_user_member_of_blog( (int) $user_val['user'] ) ) {
                        $result[] = $user_val['user'];
                    }
                } else {
                    $user = get_user_by( 'email', $user_val['user'] );
                    if ( !empty( $user ) && is_user_member_of_blog( $user->ID ) ) {
                        $result[] = $user->ID;
                    }
                }
            }
        }
        $reports_opts['selected_users'] = $result;
        update_option( 'fupi_reports', $reports_opts );
    }
    // Updates user selector in Main options
    $main_opts = get_option( 'fupi_main' );
    if ( !empty( $main_opts ) && !empty( $main_opts['extra_users'] ) ) {
        $result = [];
        foreach ( $main_opts['extra_users'] as $user_val ) {
            if ( is_array( $user_val ) && isset( $user_val['user'] ) ) {
                if ( is_numeric( $user_val['user'] ) ) {
                    if ( is_user_member_of_blog( (int) $user_val['user'] ) ) {
                        $result[] = $user_val['user'];
                    }
                } else {
                    $user = get_user_by( 'email', $user_val['user'] );
                    if ( !empty( $user ) && is_user_member_of_blog( $user->ID ) ) {
                        $result[] = $user->ID;
                    }
                }
            }
        }
        $main_opts['extra_users_2'] = $result;
        update_option( 'fupi_main', $main_opts );
    }
    // Update advanced triggers - change "instant" to "dom_loaded"
    $atrig_opts = get_option( 'fupi_atrig' );
    if ( !empty( $atrig_opts ) ) {
        foreach ( $atrig_opts['triggers'] as &$trigger ) {
            // "&" is used to make a reference to the array - it will not create a new array, but will change the original one
            if ( !empty( $trigger['events'] ) && in_array( 'instant', $trigger['events'] ) ) {
                $trigger['events'] = array_diff( $trigger['events'], array('instant') );
                $trigger['events'][] = 'dom_loaded';
            }
        }
        update_option( 'fupi_atrig', $atrig_opts );
    }
    // Creates script titles for "cscr" and "blockscr"
    $tools = ['cscr', 'blockscr'];
    foreach ( $tools as $tool_slug ) {
        $opts = get_option( 'fupi_' . $tool_slug );
        $changed = false;
        $settings_ids = ['fupi_head_scripts', 'fupi_footer_scripts', 'blocked_scripts'];
        foreach ( $settings_ids as $settings_id ) {
            if ( !empty( $opts[$settings_id] ) ) {
                foreach ( $opts[$settings_id] as $setting_key => $setting_vals ) {
                    // if ( empty ( $setting_vals['title'] ) ) {
                    //     $changed = true;
                    //     if ( ! empty( $setting_vals['name'] ) ) {
                    //         $opts[$settings_id][$setting_key]['title'] = $setting_vals['name'];
                    //     } else {
                    //         $opts[$settings_id][$setting_key]['title'] = 'Script ' . $setting_vals['id'];
                    //     }
                    // }
                    if ( !empty( $setting_vals['name'] ) ) {
                        $changed = true;
                        $opts[$settings_id][$setting_key]['title'] = $setting_vals['name'];
                    } else {
                        if ( empty( $setting_vals['title'] ) ) {
                            $changed = true;
                            $opts[$settings_id][$setting_key]['title'] = 'Script ' . $setting_vals['id'];
                        }
                    }
                }
            }
        }
        if ( $changed ) {
            update_option( 'fupi_' . $tool_slug, $opts );
        }
    }
}
if ( version_compare( $prev_version, '8.5.0' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $woo_data = get_option( 'fupi_woo' );
    if ( !empty( $woo_data ) && !empty( $woo_data['variable_as_simple'] ) ) {
        $updated = true;
        $woo_data['variable_tracking_method'] = 'track_parents';
        update_option( 'fupi_woo', $woo_data );
    }
}
if ( version_compare( $prev_version, '8.5.0.4' ) == -1 ) {
    // 0 if equal, -1 if prev is lower
    $regenerate_cdb = true;
}
// Update version number to match the current version
$versions = get_option( 'fupi_versions' );
$versions[1] = FUPI_VERSION;
update_option( 'fupi_versions', $versions );
// Regenerate head file
if ( !empty( $this->main['save_settings_file'] ) ) {
    include_once FUPI_PATH . '/admin/common/generate-files.php';
    $generator = new Fupi_Generate_Files();
    $generator->make_head_js_file( 'updater', false );
}
if ( $regenerate_cdb ) {
    if ( !empty( $this->tools['cook'] ) && !empty( $this->cook['cdb_key'] ) && !empty( get_privacy_policy_url() ) ) {
        trigger_error( 'Regenerate GDPR info for the ConsentsDB' );
        include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
        new Fupi_compliance_status_checker('cdb', $this->cook, false);
    }
}
// Clear cache if any updates were made
if ( $updated ) {
    include 'fupi-clear-cache.php';
}