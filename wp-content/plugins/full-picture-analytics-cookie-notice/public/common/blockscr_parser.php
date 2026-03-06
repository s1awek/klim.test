<?php

// Combine manual and automatic rules to make an array of all the tools that are managed

$tools_to_block = array();

if ( ! empty( $this->cook['scrblk_auto_rules'] ) ) {
    foreach ( $this->cook['scrblk_auto_rules'] as $source ) {
        switch ( $source ) {

            // JETPACK STATS

            case 'jetpack':
                $tools_to_block[] = array(
                    'id'            => 'auto_jetpack_stats',
                    'rules'         => array(
                        array( 
                            'block_by' => 'src',
                            'unique' => 'stats.wp.com' 
                        ),
                    ),
                    'stats'         => true,
                    'market'        => false,
                    'pers'          => false,
                );
            break;

            // PIXELYOURSITE

            case 'pys':
                $tools_to_block[] = array(
                    'id'            => 'auto_pys',
                    'rules'         => array(
                        array( 
                            'block_by' => 'src',
                            'unique' => 'pixelyoursite' 
                        ),
                        array( 
                            'block_by' => 'content',
                            'unique' => 'pysOptions' 
                        ),
                    ),
                    'stats'         => true,
                    'market'        => true,
                    'pers'          => true,
                );
            break;
        }
    }
}

if ( ! empty ( $this->woo['block_sbjs'] ) ) {
    $tools_to_block[] = array(
        'id'            => 'auto_woo_sbjs',
        'rules'         => array(
            array( 
                'block_by' => 'src', 
                'unique' => 'sourcebuster' 
            ),
        ),
        'url_part'      => '',
        'stats'         => true,
        'market'        => false,
        'pers'          => false,
    );
}

if ( ! empty( $this->cook['control_other_tools'] ) && ! empty( $this->cook['scrblk_manual_rules'] ) ) {
    $tools_to_block = array_merge( $tools_to_block, $this->cook['scrblk_manual_rules'] );
}

// Go over all the rules and block what needs to be blocked

foreach ( $tools_to_block as $tool_data ) {
    
    // do not make any changes if the script is in "force load" mode
    if ( ! empty( $tool_data['force_load'] ) ) continue;

    // prepare base script id
    $base_scr_id = 'fp_' . esc_attr( $tool_data['id'] );

    // check if always block scr
    $always_block = ! empty( $tool_data['always'] );

    // prepare cookie permissions
    $permissions = [];
    if ( ! empty( $tool_data['stats'] ) ) array_push($permissions, 'stats');
    if ( ! empty( $tool_data['pers'] ) ) array_push($permissions, 'personalisation');
    if ( ! empty( $tool_data['market'] ) ) array_push($permissions, 'marketing');
    $permissions_s = implode(' ', $permissions);

    // prepare force load
    $force_load = empty( $tool_data['force_load'] ) ? '0' : '1';

    // prepare geo
    $geo = '';
    if ( ! empty( $tool_data['method'] ) && ! empty( $tool_data['countries'] ) ) {
        $geo = esc_attr($tool_data['method']) . ',' . esc_attr( str_replace(',', ' ', $script_data['countries']) );;
    }

    // Track match count for script ID suffix
    $match_count = 0;

    // Loop through each rule in the rules array
    foreach ( $tool_data['rules'] as $rule ) {
        
        $block_by = $rule['block_by'];

        if ( empty ( $rule['unique'] ) ) continue;
        
        $search_phrase_original = esc_attr($rule['unique']);  // For strpos()
        $search_phrase = preg_quote( $search_phrase_original, '/' ); // For regex

        // if we want to block a script with specific content
        if ( $block_by == 'content' ){

            $modif_html = $html;

            // Find all script tags using a simple pattern
            preg_match_all('/<script[^>]*>.*?<\/script>/si', $html, $script_matches, PREG_OFFSET_CAPTURE);
            
            $offset_adjustment = 0;
            
            // For each script tag...
            foreach ($script_matches[0] as $match) {
                
                $full_script_tag = $match[0];
                $position = $match[1] + $offset_adjustment;
                
                // Extract the content between script tags FIRST
                if ( preg_match('/<script[^>]*>(.*?)<\/script>/si', $full_script_tag, $content_match) ) {
                    
                    $script_content = $content_match[1];
                    
                    // ... check if the CONTENT contains our search phrase (not the whole tag with attributes)
                    if ( strpos( $script_content, $search_phrase_original ) !== false) {

                        $orig_str_len = strlen($full_script_tag);

                        // Generate script ID with suffix for 2nd+ matches
                        $match_count++;
                        $scr_id = ($match_count === 1) ? $base_scr_id : $base_scr_id . '_' . $match_count;

                        if ( $always_block ) {
                            
                            $replacement = '';
                            
                            // Replace in the HTML
                            $modif_html = substr_replace($modif_html, $replacement, $position, $orig_str_len);

                            // Adjust offset for next replacements
                            $offset_adjustment -= $orig_str_len;

                        } else {
                            
                            // Create the replacement
                            $replacement = '<!--noptimize-->
                        <script type="text/plain" data-fupi_type="inline" data-fupi_id="' . $scr_id . '" data-fupi_class="fupi_blocked_script" data-fupi_force="' . $force_load . '" data-fupi_permiss="' . $permissions_s . '" data-fupi_geo="' . $geo . '"  data-noptimize data-no-optimize="1" nowprocket>' . $script_content . '</script>
                        <!--/noptimize-->';
                            
                            // Replace in the HTML
                            $modif_html = substr_replace($modif_html, $replacement, $position, $orig_str_len);
                            
                            // Adjust offset for next replacements
                            $offset_adjustment += strlen($replacement) - $orig_str_len;
                        }
                    }
                }
            }

            $html = $modif_html;
            
        // if we want to block a script, image or link with a specific URL
        } else {

            // Default: <script>
            $pattern = "/<script([\w\s\d=\-\._:'\"\/]*src=['\"]?[\w:\/\.\-\_\?&=]*" . $search_phrase . "[\?\w\d\s=\-_&'\"\.\/]*)><\/script>/";
            $type = 'script';

            // Alt: <link> or <img>
            if ( $block_by == 'link_href' ) {
                $pattern = "/<link([\w\s\d=\-\._:'\"\/]*href=['\"]?[\w:\/\.\-\_\?&=]*" . $search_phrase . "[\?\w\d\s=\-_&'\"\.\/]*)>/";
                $type = 'link';
            } else if ( $block_by == 'img_src' ) {
                $pattern = "/<img([\w\s\d=\-\._:'\"\/]*src=['\"]?[\w:\/\.\-\_\?&=]*" . $search_phrase . "[\?\w\d\s=\-_&'\"\.\/]*)>/";
                $type = 'img';
            }

            $new_html = preg_replace_callback(
                $pattern,
                function( $matches ) use ( $base_scr_id, $force_load, $permissions_s, $geo, $block_by, $type, $always_block, &$match_count ) {
                    
                    if ( $always_block ) {
                         return '';
                    } else {

                        // Generate script ID with suffix for 2nd+ matches
                        $match_count++;
                        $scr_id = ($match_count === 1) ? $base_scr_id : $base_scr_id . '_' . $match_count;

                        // get the URL from src / href and later remove them from attributes
                        $url = preg_replace('/.*src=[\'"]{1}?(.*?)[\'"]{1}.*/', '\1', $matches[1] );
                        
                        if ( $block_by == 'link_href' ) {
                            $url = preg_replace('/.*href=[\'"]{1}?(.*?)[\'"]{1}.*/', '\1', $matches[1] );
                        };
                        
                        $url = trim($url);
                        
                        $atts = preg_replace('/src=[\'"]{1}?.*?[\'"]{1}/', '', $matches[1] );
                        
                        if ( $block_by == 'link_href' ) {
                            $atts = preg_replace('/href=[\'"]{1}?.*?[\'"]{1}/', '', $matches[1] );
                        };
                        
                        return '<!--noptimize--><script type="text/plain" data-fupi_type="' . $type . '" data-fupi_id="' . $scr_id . '" data-fupi_class="fupi_blocked_script fupi_no_defer" data-fupi_force="' . $force_load . '" data-fupi_permiss="' . $permissions_s . '" data-fupi_geo="' . $geo . '" data-fupi_url="' . $url . '" ' . $atts . ' data-no-optimize="1" data-noptimize nowprocket></script><!--/noptimize-->';
                    }
                },
                $html
            );

            if ( ! empty ( $new_html ) ) {
                $html = $new_html;
            }
        }
    }
}