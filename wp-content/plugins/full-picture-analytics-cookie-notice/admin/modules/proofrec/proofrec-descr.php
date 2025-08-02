<?php

$ret_text = '';
$proofrec_opt = get_option( 'fupi_proofrec' );

switch( $section_id ){

    case 'fupi_proofrec_cdb':

        // div centers the content
        $ret_text = '<div>
            <p>' . esc_html__( 'Keeping records of consents is required by GDPR, protects against potential fines, enables audit trails, and helps demonstrate responsible data handling practices.', 'full-picture-analytics-cookie-notice' ) . '</p>';

            if ( ! empty ( $proofrec_opt['storage_location'] ) && $proofrec_opt['storage_location'] == 'email') {
                $ret_text .= '<p><strong>View latest records of consent:</strong> <a href="' . admin_url( 'admin.php?page=full_picture_proofrec&tab=consents_list' ) . '" class="button button-primary" style="padding: 4px 18px;">View records</a></p>';
            }

        $ret_text .= '</div>';
    break;
};

?>
