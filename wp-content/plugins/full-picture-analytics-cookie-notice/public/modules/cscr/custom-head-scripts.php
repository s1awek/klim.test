<?php

$scripts_a = get_option( 'fupi_cscr' );
if ( empty( $scripts_a ) ) {
    return;
}
if ( is_array( $scripts_a ) && isset( $scripts_a['fupi_head_scripts'] ) && is_array( $scripts_a['fupi_head_scripts'] ) ) {
    foreach ( $scripts_a['fupi_head_scripts'] as $script_data ) {
        if ( !empty( $script_data['disable'] ) || empty( $script_data['id'] ) || empty( $script_data['scr'] ) ) {
            continue;
        }
        // prepare head script id
        $scr_id = 'fp_' . $script_data['id'];
        // get required cookie permissions
        $permissions = [];
        if ( !empty( $script_data['stats'] ) && $script_data['stats'] == '1' ) {
            array_push( $permissions, '\'stats\'' );
        }
        if ( !empty( $script_data['pers'] ) && $script_data['pers'] == '1' ) {
            array_push( $permissions, '\'personalisation\'' );
        }
        if ( !empty( $script_data['market'] ) && $script_data['market'] == '1' ) {
            array_push( $permissions, '\'marketing\'' );
        }
        $permissions = '[' . implode( ',', $permissions ) . ']';
        // get force load
        $force_load = ( empty( $script_data['force_load'] ) ? 'false' : 'true' );
        // get geo requirements
        $geo = 0;
        // OUTPUT
        $output = "if ( FP.isAllowedToLoad_basic( '{$scr_id}', {$force_load}, {$permissions}, {$geo} ) ) {\r\n\t\t\tFP.loadScript('{$scr_id}');\r\n\t\t} else {\r\n\t\t\tfp.blocked_scripts.push( [ false, 'empty', '{$scr_id}', {$force_load}, {$permissions}, {$geo} ] );\r\n\t\t}";
        echo "<!--noptimize--><script id='{$scr_id}_temp' type='text/plain' data-no-optimize=\"1\" nowprocket>" . html_entity_decode( $script_data['scr'], ENT_QUOTES ) . '</script><script class="fupi_cscr_head" data-no-optimize="1" nowprocket>' . $output . '</script><!--/noptimize-->';
    }
}