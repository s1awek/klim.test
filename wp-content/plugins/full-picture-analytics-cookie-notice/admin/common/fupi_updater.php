<?php

class  Fupi_Updater {

    /*
    
    REMEMBER

    To enable a module on the tools list, always set the value to boolean "true", not a digit "1" - it will not work.

    $this->o['fupi_tools']['proofrec'] = true;
    
    */

    private $prev_version = false;
    private $regenerate_cdb = false;
    private $o = []; // holds all the options
    private $opts_to_remove = [];
    
    public function run( $restore_backup = false, $backup_data = [] ){

        if ( $restore_backup && count( $backup_data ) > 0 ) {
            $this->get_options_from_backup( $backup_data );
            $this->set_fupi_versions(); // only if backup is older than 9.0
        }

        $this->set_default_options();
        $this->check_fupi_versions();
        
        $options_changed = false;
        
        // check if the saved version number is older than the current one
        if ( version_compare( $this->prev_version, FUPI_VERSION ) == -1 ) {

            $options_changed = true;
    
            $this->update_to_8_5();
            $this->update_to_8_5_1();
            $this->update_to_9_0();
            $this->update_to_9_2();
        }
        
        if ( $restore_backup || $options_changed ) {

            add_filter( 'fupi_updating_many_options', '__return_true' ); // this is used to stop "sanitize" functions from other modules from generating files, sending data to CDB or clearing cache

            if ( $restore_backup ) {
                $returned_status =  $this->restore_options_from_backup();
            } else {
                $this->update_wp_options();
            }

            add_filter( 'fupi_updating_many_options', '__return_false' );

            $this->regenerate_files(); // after every update
            $this->send_plugin_settings_to_cdb();
            $this->clear_cache(); // after every update

            if ( $restore_backup ) return $returned_status;
        }
    }

    private function set_default_options(){
        $tools_o = get_option( 'fupi_tools' );
        if ( empty( $tools_o ) ) add_option( 'fupi_tools', [] );
    }

    // SAFELY MANIPULATE OPTIONS

    private function get_fupi_options( $opt_names_arr ){
        foreach ( $opt_names_arr as $opt_name ) {

            if ( ! is_string( $opt_name ) ) continue;

            $opt_name = sanitize_key( $opt_name );
            
            if ( ! isset( $this->o[$opt_name] ) ) {
                $this->o[$opt_name] = get_option( $opt_name );
                if ( $this->o[$opt_name] === false ) $this->o[$opt_name] = [];
            }
        }
    }

    private function get_options_from_backup( $backup_data ){
        foreach ( $backup_data as $key => $value ) {
            
            if ( $value == 'no_value' ) {
                $this->opts_to_remove[] = $key; // we use it later, to remove the option from the DB
                $this->o[$key] = []; // we save empty arr to prevent errors while processing options
            } else {
                $this->o[$key] = $value;
            }
        }
    }
    
    private function add_fupi_option( $opt_name, $opt_value ){
        $this->o[$opt_name] = $opt_value;
    }

    private function update_wp_options(){
        foreach ( $this->o as $opt_name => $opt_val ) {
            update_option( $opt_name, $opt_val );
        }
    }

    // GET PREVIOUS VERSION

    private function set_fupi_versions(){

        // set version number for backups that do not contain it

        // versions before 8.3.2 do not have fupi_version option at all
        if ( ! isset( $this->o['fupi_versions'] ) ) {
            $this->o['fupi_versions'] = [
                time(),
                '8.3.2'
            ];
        } else {

            // v 9.0.0 had a bug, which set the fupi_versions to false
            if ( $this->o['fupi_versions'] === false ) {
                $this->o['fupi_versions'] = [
                time(),
                '9.0.0'
            ];
            }
        }
    }

    private function check_fupi_versions(){
        
        $this->get_fupi_options( [ 'fupi_versions', 'fupi_tools' ] );

        // Recognize ver 9.0
        // it had a bug which saved fupi_versions to false
        if ( isset( $this->o['fupi_versions'] ) && $this->o['fupi_versions'] === false ){
            $this->o['fupi_versions'][0] = time();
            $this->o['fupi_versions'][1] = '9.0.0';
        }

        // for all other versions

        // is fresh install if the fupi_versions option is an empty array
        if ( count ( $this->o['fupi_versions'] ) === 0 ) {

            // set current time and ver
            $this->o['fupi_versions'][0] = time();
            $this->o['fupi_versions'][1] = FUPI_VERSION;

        // is old install if array is not empty
        } else {

            $this->prev_version = $this->o['fupi_versions'][1];
            $this->o['fupi_versions'][1] = FUPI_VERSION;
        }

        // Save changes - must do it here, not later
        update_option( 'fupi_versions', $this->o['fupi_versions'] );
    }

    // SEND PLUGIN SETTINGS FOR THE CONSENTSDB

    private function send_plugin_settings_to_cdb(){

        if ( ! $this->regenerate_cdb ) return;

        $this->get_fupi_options( ['fupi_tools'] );
        
        if ( isset ( $this->o['fupi_tools']['cook'] ) && isset ( $this->o['fupi_tools']['proofrec'] ) && ! empty ( get_privacy_policy_url() ) ) {
            include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
            $gdpr_checker = new Fupi_compliance_status_checker();
            $gdpr_checker->send_and_return_status();
        }
    }

    // REGENERATE FILES

    private function regenerate_files(){

        $this->get_fupi_options( ['fupi_main', 'fupi_tools', 'fupi_cscr'] );

        $gen_head_js = ! empty( $this->o['fupi_main']['save_settings_file' ] );
        $gen_cscr_file = ! empty( $this->o['fupi_tools']['cscr'] ) && ! empty ( $this->o['fupi_main']['save_cscr_file'] );

        if ( $gen_head_js || $gen_cscr_file ) {

            include_once FUPI_PATH . '/admin/common/generate-files.php';
            $generator = new Fupi_Generate_Files();

            if ( $gen_head_js ) $generator->make_head_js_file( 'updater', false );
            if ( $gen_cscr_file ) $generator->make_cscr_js_files( false );
        }
    }

    // CLEAR CACHE
    private function clear_cache(){
        include_once 'fupi-clear-cache.php';
    }

    // DO UPDATES STEP BY STEP

    private function update_to_8_5(){
        if ( version_compare( $this->prev_version, '8.5.0' ) != -1 ) return;

        $this->get_fupi_options( ['fupi_woo'] );

        if ( ! empty( $this->o['fupi_woo']['variable_as_simple'] ) ) {
            $this->o['fupi_woo']['variable_tracking_method'] = 'track_parents';
        }
    }

    private function update_to_8_5_1(){
        if ( version_compare( $this->prev_version, '8.5.1' ) != -1 ) return;
        $this->regenerate_cdb = true;
    }

    private function update_to_9_0(){

        if ( version_compare( $this->prev_version, '8.5.3.13' ) != -1 ) return;

        $this->regenerate_cdb = true;
        
        $this->get_fupi_options( ['fupi_tools', 'fupi_main', 'fupi_cook', 'fupi_trackmeta', 'fupi_track404', 'fupi_privex', 'fupi_iframeblock', 'fupi_blockscr', 'fupi_woo', 'fupi_hotj'] );

        // MAKE FUPI_TRACK OPTS
        // fupi_main + fupi_trackmeta + fupi_track404 + labelpages (setting of fupi_tools)

        // JOIN options (this must go first)

        $track_opts = array_merge( $this->o['fupi_main'], $this->o['fupi_trackmeta'], $this->o['fupi_track404'] );

        // ADD options

        // enable 404 tracking
        if ( isset ( $this->o['fupi_tools']['track404'] ) ) $track_opts['track_404'] = true;

        // enable page labels
        if ( isset ( $this->o['fupi_tools']['labelpages'] ) ) $track_opts['page_labels'] = true;

        // Remember option data
        $this->add_fupi_option( 'fupi_track', $track_opts );

        // ADD PRIVEX (fp_info shortcode) TO FUPI_MAIN
        
        if ( ! empty( $this->o['fupi_tools']['privex'] ) && count( $this->o['fupi_privex'] ) > 0 ) {
            $this->o['fupi_main'] = array_merge( $this->o['fupi_main'], $this->o['fupi_privex'] );
        }

        // (everything below requires fupi_cook)

        // COPY FUPI_IFRAMEBLOCK AND FUPI_BLOCKSCR TO FUPI_COOK

        // iframeblock to fupi_cook
        if ( isset( $this->o['fupi_tools']['iframeblock'] ) ) {

            if ( ! empty( $this->o['fupi_iframeblock']['auto_rules'] ) ) $this->o['fupi_cook']['iframe_auto_rules'] = $this->o['fupi_iframeblock']['auto_rules'];

            if ( ! empty( $this->o['fupi_iframeblock']['manual_rules'] ) ) {
                $this->o['fupi_cook']['iframe_manual_rules'] = $this->o['fupi_iframeblock']['manual_rules'];
                $this->o['fupi_cook']['control_other_iframes'] = true;
            }

            if ( ! empty( $this->o['fupi_iframeblock']['btn_text'] ) ) $this->o['fupi_cook']['iframe_btn_text'] = $this->o['fupi_iframeblock']['btn_text'];
            
            if ( ! empty( $this->o['fupi_iframeblock']['caption_txt'] ) ) $this->o['fupi_cook']['iframe_caption_txt'] = $this->o['fupi_iframeblock']['caption_txt'];

            if ( ! empty( $this->o['fupi_iframeblock']['iframe_img'] ) ) $this->o['fupi_cook']['iframe_img'] = $this->o['fupi_iframeblock']['iframe_img'];

            if ( ! empty( $this->o['fupi_iframeblock']['iframe_lazy'] ) ) $this->o['fupi_cook']['iframe_lazy'] = $this->o['fupi_iframeblock']['iframe_lazy'];
        }

        // blockscr to fupi_cook
        if ( isset( $this->o['fupi_tools']['blockscr'] ) ) {

            if ( ! empty( $this->o['fupi_blockscr']['auto_rules'] ) ) $this->o['fupi_cook']['scrblk_auto_rules'] = $this->o['fupi_blockscr']['auto_rules'];

            if ( ! empty( $this->o['fupi_blockscr']['blocked_scripts'] ) ) {
                $this->o['fupi_cook']['scrblk_manual_rules'] = $this->o['fupi_blockscr']['blocked_scripts'];
                $this->o['fupi_cook']['control_other_tools'] = true;
            }
        }

        // Make a new Proofrec option

        if ( isset( $this->o['fupi_cook']['cdb_key'] ) ) {

            // Enable proofrec module
            $this->o['fupi_tools']['proofrec'] = true;
        
            $fupi_proofrec_opts = [
                'cdb_key' => $this->o['fupi_cook']['cdb_key'],
            ];
            
            if ( ! empty( $this->o['fupi_cook']['save_all_consents'] ) ) $fupi_proofrec_opts['save_all_consents'] = $this->o['fupi_cook']['save_all_consents'];

            if ( ! empty( $this->o['fupi_cook']['consent_access'] ) ) $fupi_proofrec_opts['consent_access'] = $this->o['fupi_cook']['consent_access'];
            
            $this->add_fupi_option( 'fupi_proofrec', $fupi_proofrec_opts );
        }
        
        // Make a new Google Tag option

        $gtag_opts = [];

        if ( ! empty( $this->o['fupi_cook']['url_passthrough'] ) ) {
            $gtag_opts['url_passthrough'] = true;
            $this->add_fupi_option( 'fupi_gtag', $gtag_opts );
        }

        // Add blocking Sourcebuster.js to fupi_woo

        if ( ! empty( $this->o['fupi_cook']['scrblk_auto_rules'] ) && in_array( 'woo_sbjs', $this->o['fupi_cook']['scrblk_auto_rules'] ) ) {
            $this->o['fupi_woo']['block_sbjs'] = true;
        }

        // Send email to users of Hotjar who use a consent banner and Privacy mode in Hotjar

        if ( ! empty( $this->o['fupi_tools']['cook'] ) && ! empty( $this->o['fupi_tools']['hotj'] ) ) {

            if ( ! empty( $this->o['fupi_hotj']['no_pii'] ) ) {

                $email_to = get_option( 'admin_email' );
                $sitename = get_bloginfo('name');

                $subject = sprintf( esc_attr__( 'Important changes to privacy settings of Hotjar integration on %1$s', 'full-picture-analytics-cookie-notice' ), $sitename );

				$content = sprintf( esc_attr__( "This email was sent automatically from %1\$s by WP Full Picture plugin.\n\nWe would like to inform you that WP Full Picture plugin has been updated to version 9.0 and comes with an important, privacy-related change to Hotjar integration.\n\nDue to privacy laws, we decided to disable privacy mode in Hotjar. As a result, Hotjar will no longer load without asking for consent. Instead, we have introduced \"Data suppression\" option, which you can now find in the settings of the Hotjar module. It will allow you to use Hotjar without asking for consent in some regions.\n\nKind regards\nWP Full Picture's team", 'full-picture-analytics-cookie-notice' ), get_bloginfo('url') );

				wp_mail( $email_to, $subject, $content );
            }
        }
    }

    private function update_to_9_2(){

        if ( version_compare( $this->prev_version, '9.1.10' ) != -1 ) return;
        
        $this->get_fupi_options( ['fupi_versions'] );
        $this->o['fupi_versions']['use_adv_mode'] = true; // this will make sure that users who updated from earlier versions will see advanced settings and not be surprised with the basic ones
    }

    private function restore_options_from_backup(){

		if ( empty( $this->o ) ) return false;

        // trigger_error('Restoring options from backup: ' . json_encode( $this->o ));

		// Update the settings

		foreach ( $this->o as $option_id => $option_value ) {

			switch ( $option_id ) {
				
				case 'theme_mods':
					foreach ( $option_value as $mod_id => $value ) {
						if ( $value == "no_value" ) {
							remove_theme_mod($mod_id);
						} else {
							set_theme_mod( $mod_id, $value );
						}
					}
				break;
				
				case 'fupi_reports':

					if ( in_array('fupi_reports', $this->opts_to_remove ) ) {
						delete_option( 'fupi_reports' );
					} else {
						
						// scripts are encoded during sanitisation. We must decode them before they are sanitized with html_entity_decode( $saved_value, ENT_QUOTES ) 
						if ( ! empty( $option_value['dashboards'] ) ) {
							foreach ( $option_value['dashboards'] as $i => $dash ) {
								$option_value['dashboards'][$i]['iframe'] = html_entity_decode( $option_value['dashboards'][$i]['iframe'], ENT_QUOTES );
							}
						}
						
						update_option( 'fupi_reports', $option_value );
					}

				break;

				case 'fupi_cscr':
					if ( in_array( 'fupi_cscr', $this->opts_to_remove ) ) {
						delete_option( 'fupi_cscr' );
					} else {
						// scripts are encoded during sanitisation. We must encode them before they are sanitized with html_entity_decode( $saved_value, ENT_QUOTES )
						$placements = array('fupi_head_scripts', 'fupi_footer_scripts');

						foreach ( $placements as $placement_name ) { // gets string 'fupi_head_scripts'
							if ( ! empty ( $option_value[$placement_name] ) ) {
								$placement_scripts = $option_value[$placement_name];
								$i = 0;
								foreach ( $placement_scripts as $single_script_data ) {
									$decoded_val = html_entity_decode( $single_script_data['scr'], ENT_QUOTES );
									$option_value[$placement_name][$i]['scr'] = $decoded_val;
									if ( ! empty ( $option_value[$placement_name][$i]['html'] ) ) {
										$decoded_html = html_entity_decode( $single_script_data['html'], ENT_QUOTES );
										$option_value[$placement_name][$i]['html'] = $decoded_html;
									}
									$i++;
								}
								
							}
						}

						update_option( 'fupi_cscr', $option_value );
					}
				break;

				default:
					if ( in_array( $option_id, $this->opts_to_remove ) ) {
						delete_option( $option_id );
					} else {
						update_option( $option_id, $option_value );
					};
                break;
			}
		}
	
		return true;
	}
}

?>