<?php

// Combine manual and automatic rules to make an array of all the tools that are managed

$blockscr_rules = array();

/*
    'exact_metrics'			=> 'ExactMetrics',
    'ga_jeff_star'			=> 'GA Google Analytics (by Jeff Starr)',
    'monster_insights'		=> 'MonsterInsights',
    'pixel_caffeine' 		=> 'Pixel Caffeine',
    'rank_math'				=> 'Rank Math',
    'site_kit'				=> 'Site Kit by Google'
*/

$gtag_file_1 = false;
$gtag_file_2 = false;
$fbp_file_1 = false;

if ( ! empty( $this->blockscr_settings['auto_rules'] ) ) {

    foreach ( $this->blockscr_settings['auto_rules'] as $source ) {
        switch ( $source ) {

            case 'exact_metrics':
            case 'monster_insights':
                
                if ( ! $gtag_file_1 ) {
                    
                    $gtag_file_1 = true;
                    
                    $blockscr_rules[] = array(
                        'id'            => 'auto_gtag_file_1',
                        'block_by'      => 'src',
                        'url_part'      => 'googletagmanager.com/gtag/js',
                        'stats'         => true,
                        'market'        => false,
                        'pers'          => false,
                    );
                }
                
                if ( ! $gtag_file_2 ) {
                    
                    $gtag_file_2 = true;
                    
                    $blockscr_rules[] = array(
                        'id'            => 'auto_gtag_file_2',
                        'block_by'      => 'src',
                        'url_part'      => 'frontend-gtag.js',
                        'stats'         => true,
                        'market'        => false,
                        'pers'          => false,
                    );
                }

            break;

            case 'woo_sbjs':

                $blockscr_rules[] = array(
                    'id'            => 'auto_woo_sbjs',
                    'block_by'      => 'src',
                    'url_part'      => 'sourcebuster',
                    'stats'         => true,
                    'market'        => false,
                    'pers'          => false,
                );
            break;

            case 'ga_jeff_star':
            case 'rank_math':
            case 'site_kit':

                if ( ! $gtag_file_1 ) {

                    $gtag_file_1 = true;

                    $blockscr_rules[] = array(
                        'id'            => 'auto_gtag_file_1',
                        'block_by'      => 'src',
                        'url_part'      => 'googletagmanager.com/gtag/js',
                        'stats'         => true,
                        'market'        => false,
                        'pers'          => false,
                    );
                }

            break;

            case 'jetpack':                

                $blockscr_rules[] = array(
                    'id'            => 'auto_jetpack_stats',
                    'block_by'      => 'src',
                    'url_part'      => 'stats.wp.com',
                    'stats'         => true,
                    'market'        => false,
                    'pers'          => false,
                );

            break;

            case 'pixel_caffeine':

                if ( ! $fbp_file_1 ) {

                    $fbp_file_1 = true;

                    $blockscr_rules[] = array(
                        'id'            => 'auto_fbp_file_1',
                        'block_by'      => 'content',
                        'url_part'      => 'connect.facebook.net',
                        'stats'         => true,
                        'market'        => true,
                        'pers'          => false,
                    );
                }
                
                $blockscr_rules[] = array(
                    'id'            => 'auto_fbpcoff_file_1',
                    'block_by'      => 'src',
                    'url_part'      => 'pixel-caffeine/build/frontend.js',
                    'stats'         => true,
                    'market'        => true,
                    'pers'          => false,
                );

            break;
        }
    }
}

if ( ! empty( $this->blockscr_settings['blocked_scripts'] ) ) {
    $blockscr_rules = array_merge( $blockscr_rules, $this->blockscr_settings['blocked_scripts'] );
}

// Go over all the rules and block what needs to be blocked

foreach ( $blockscr_rules as $scr_data ) {

    // do not make any changes if the script is in "force load" mode
    if ( ! empty( $scr_data['force_load'] ) ) continue;

    // prepare a search phrase to use in REGEXs
    $search_phrase = str_replace( '/', '\/', esc_attr($scr_data['url_part'] ) );
    $search_phrase = str_replace( '.', '\.', $search_phrase );
    $search_phrase = str_replace( '-', '\-', $search_phrase );

    // prepare script id
    $scr_id = 'fp_' . esc_attr( $scr_data['id'] );

    // prepare cookie permissions
    $permissions = [];
    if ( ! empty( $scr_data['stats'] ) ) array_push($permissions, '\'stats\'');
    if ( ! empty( $scr_data['pers'] ) ) array_push($permissions, '\'personalisation\'');
    if ( ! empty( $scr_data['market'] ) ) array_push($permissions, '\'marketing\'');
    $permissions = '[' . implode(',', $permissions) . ']';

    // prepare force load
    $force_load = ! empty( $scr_data['force_load'] ) ? 'true' : 'false';

    // prepare geo
    $geo = '';
    if ( ! empty( $scr_data['method'] ) && ! empty( $scr_data['countries'] ) ) {
        $geo = '["' . esc_attr($scr_data['method']) . '","' . esc_attr($scr_data['countries']) . '"]';
    }

    // if we want to block a script with a specific content
    if ( $scr_data['block_by'] == 'content' ){

        $pattern = "/<script[^>]*>((?:(?!<\/script>)[\s\S])*" . $search_phrase . "[\s\S]*?)<\/script>/si";

        $new_html = preg_replace_callback(
            $pattern, 
            function( $matches ) use ( $scr_id, $force_load, $permissions, $geo ) {
                return '<!--noptimize-->
                <script id="'.$scr_id .'_temp" type="text/plain" data-no-optimize="1" nowprocket>' . $matches[1] . '</script>
                <script data-no-optimize="1" nowprocket>fp.blocked_scripts.push( [ false, "empty","' . $scr_id . '", ' . $force_load . ', ' . $permissions . ', ' . $geo . ', \'script\' ] )</script>
                <!--/noptimize-->';
            }, 
            $html
        );
        
    // if we want to block a script, image or link with a specific URL
    } else {

        // default - script
        $pattern = "/<script([\w\s\d=\-\._:'\"\/]*src=['\"]?[\w:\/\.\-\_]+" . $search_phrase . "[\?\w\d\s=\-_'\"\.\/]*)><\/script>/";
        $type = 'script';

        // alt - link or image
        if ( $scr_data['block_by'] == 'link_href' ) {
            $pattern = "/<link([\w\s\d=\-\._:'\"\/]*href=['\"]?[\w:\/\.\-\_]+" . $search_phrase . "[\?\w\d\s=\-_'\"\.\/]*)>/";
            $type = 'link';
        } else if ( $scr_data['block_by'] == 'img_src' ) {
            $pattern = "/<img([\w\s\d=\-\._:'\"\/]*src=['\"]?[\w:\/\.\-\_]+" . $search_phrase . "[\?\w\d\s=\-_'\"\.\/]*)>/";
            $type = 'img';
        }

        $new_html = preg_replace_callback(
            $pattern, 
            function( $matches ) use ( $scr_id, $force_load, $permissions, $geo, $scr_data, $type ) {
                
                // get only the URL
                $url = preg_replace('/.*src=[\'"]{1}?(.*?)[\'"]{1}.*/', '\1', $matches[1] );
                
                if ( $scr_data['block_by'] == 'link_href' ) {
                    $url = preg_replace('/.*href=[\'"]{1}?(.*?)[\'"]{1}.*/', '\1', $matches[1] );
                };
                
                $url = '"' . trim($url) . '"';
                
                // get only the attributes
                
                $atts = preg_replace('/src=[\'"]{1}?.*?[\'"]{1}/', '', $matches[1] );
                
                if ( $scr_data['block_by'] == 'link_href' ) {
                    $atts = preg_replace('/href=[\'"]{1}?.*?[\'"]{1}/', '', $matches[1] );
                };
                
                $atts = str_replace('"', '\"', $atts ); // escape apostrophes
                $atts = '"' . trim($atts) . '"';
                if ( empty( $atts ) ) $atts = 'empty';
                
                return '<!--noptimize--><script data-no-optimize="1" nowprocket>fp.blocked_scripts.push( [ ' . $url . ', ' . $atts . ', "' . $scr_id . '", ' . $force_load . ', ' . $permissions . ', ' . $geo . ', "' . $type . '" ] )</script><!--/noptimize-->';
            },
            $html
        );

        $html_msgs[] = [$pattern];
    };	
    
    if ( ! empty( $new_html ) ) {
        $html = $new_html;
    } else {
        // Add a JS notice to HTML about the error
        $html = str_replace( '</body>', '<script class="fupi_ttr_error_msg">if ( fp.vars.debug ) console.error("[FP] Tracking Tools Manager - could not match custom rule with ID ' . $scr_data['id'] . ' using regex ' . $pattern . '");</script></body>', $html );
    };
}