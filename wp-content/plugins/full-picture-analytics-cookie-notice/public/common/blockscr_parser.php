<?php

// Combine manual and automatic rules to make an array of all the tools that are managed

$blockscr_rules = array();

if ( ! empty( $this->cook['scrblk_auto_rules'] ) ) {
    foreach ( $this->cook['scrblk_auto_rules'] as $source ) {
        switch ( $source ) {
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
        }
    }
}

if ( ! empty ( $this->woo['block_sbjs'] ) ) {
    $blockscr_rules[] = array(
        'id'            => 'auto_woo_sbjs',
        'block_by'      => 'src',
        'url_part'      => 'sourcebuster',
        'stats'         => true,
        'market'        => false,
        'pers'          => false,
    );
}

if ( ! empty( $this->cook['control_other_tools'] ) && ! empty( $this->cook['scrblk_manual_rules'] ) ) {
    $blockscr_rules = array_merge( $blockscr_rules, $this->cook['scrblk_manual_rules'] );
}

// Go over all the rules and block what needs to be blocked

foreach ( $blockscr_rules as $scr_data ) {
    
    $html_has_changed = false;
    $modif_html = $html;

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

    // if we want to block a script with specific content
    if ( $scr_data['block_by'] == 'content' ){

        // Find all script tags using a simple pattern
        preg_match_all('/<script[^>]*>.*?<\/script>/si', $html, $script_matches, PREG_OFFSET_CAPTURE);
        
        $offset_adjustment = 0;
        
        // For each script tag...
        foreach ($script_matches[0] as $match) {
            
            $full_script_tag = $match[0];
            $position = $match[1] + $offset_adjustment;
            
            // ... check if it contains our search phrase
            if ( strpos( $full_script_tag, $search_phrase ) !== false) {
                
                // Extract the content between script tags
                if ( preg_match('/<script[^>]*>(.*?)<\/script>/si', $full_script_tag, $content_match) ) {
                    
                    $html_has_changed = true;
                    $script_content = $content_match[1];
                    
                    // Create the replacement
                    $replacement = '<!--noptimize-->
                <script id="'.$scr_id .'_temp" type="text/plain" data-no-optimize="1" nowprocket>' . $script_content . '</script>
                <script data-no-optimize="1" nowprocket>fp.blocked_scripts.push( [ false, "empty","' . $scr_id . '", ' . $force_load . ', ' . $permissions . ', ' . $geo . ', \'script\' ] )</script>
                <!--/noptimize-->';
                    
                    // Replace in the HTML
                    $modif_html = substr_replace($modif_html, $replacement, $position, strlen($full_script_tag));
                    
                    // Adjust offset for next replacements
                    $offset_adjustment += strlen($replacement) - strlen($full_script_tag);
                }
            }
        }

        if ( $html_has_changed ) {
            $html = $modif_html;
        }
        
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

        if ( ! empty ( $new_html ) ) {
            $html_has_changed = true;
            $html = $new_html;
        }

        // $html_msgs[] = [$pattern];
    };	
    
    if ( ! $html_has_changed ) {
        // Add a JS notice to HTML about the error
        $html = str_replace( '</body>', '<script class="fupi_ttr_error_msg">if ( fp.main.debug ) console.error("[FP] Tracking Tools Manager - could not match custom rule with ID ' . $scr_data['id'] . '");</script></body>', $html );
    };
}