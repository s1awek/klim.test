<?php

$option_arr_id = 'fupi_proofrec';
$storage_location_options = array(
    'cdb' => esc_html__( 'In the cloud (with initial free storage for 1000 proofs)', 'full-picture-analytics-cookie-notice' ),
);
$under_field_storage_location = '<label><input type="radio" disabled>' . esc_html__( 'On my email account (Pro only)', 'full-picture-analytics-cookie-notice' ) . '</label>';
$under_field_storage_location .= '<p style="margin-top: 10px;">' . sprintf( esc_html__( '%1$sWhat are the differences?%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/records-of-consents-in-inbox-vs-the-cloud/">', '</a>' ) . '</p>';
$sections = array(array(
    'section_id'    => 'fupi_proofrec_cdb',
    'section_title' => esc_html__( 'Records of consents', 'full-picture-analytics-cookie-notice' ),
    'fields'        => array(
        array(
            'type'           => 'radio',
            'label'          => esc_html__( 'Where to store proofs of consent', 'full-picture-analytics-cookie-notice' ),
            'field_id'       => 'storage_location',
            'option_arr_id'  => $option_arr_id,
            'must_have'      => 'privacy_policy',
            'el_class'       => 'fupi_condition',
            'el_data_target' => 'fupi_cdb_cond',
            'options'        => $storage_location_options,
            'under field'    => $under_field_storage_location,
            'default'        => 'cdb',
        ),
        // FOR THE CDB OPTION
        array(
            'type'          => 'text',
            'label'         => esc_html__( 'ConsentsDB secret key', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'cdb_key',
            'must_have'     => 'privacy_policy',
            'class'         => 'fupi_simple_r3 fupi_sub fupi_cdb_cond fupi_cond_val_cdb fupi_disabled',
            'option_arr_id' => $option_arr_id,
            'after field'   => '
                        <p><strong>' . esc_html__( 'To start saving consents:', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
                        <ol>
                            <li>' . sprintf( esc_html__( 'Make sure your site is set up correctly in the %1$sGDPR setup info%2$s page.', 'full-picture-analytics-cookie-notice' ), '<a href="' . get_admin_url() . 'admin.php?page=full_picture_tools&tab=gdpr_setup_helper">', '</a>' ) . '</li>
                            <li><a href="https://consentsdb.com/">' . esc_html__( 'Create an account at ConsentsDB.com.', 'full-picture-analytics-cookie-notice' ) . '</a></li>
                            <li>' . esc_html__( 'Add this website to your account.', 'full-picture-analytics-cookie-notice' ) . '</li>
                            <li>' . esc_html__( 'When you get a secret key, paste it in the field above and follow the rest of instructions in the ConsentsDB.', 'full-picture-analytics-cookie-notice' ) . '</li>
                        </ol>
                        <p><strong>' . esc_html__( 'Learn more:', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
                        <ol>
                            <li><a href="https://wpfullpicture.com/pricing#hook_cdb_plans">' . esc_html__( 'Pricing', 'full-picture-analytics-cookie-notice' ) . '</a></li>
                            <li><a href="https://wpfullpicture.com/support/documentation/introduction-to-consentsdb/">' . esc_html__( 'About ConsentsDB', 'full-picture-analytics-cookie-notice' ) . '</a></li>
                            <li><a href="https://wpfullpicture.com/support/documentation/how-to-start-collecting-consents-in-the-consentsdb/" target="_blank">' . esc_html__( 'Detailed setup guide', 'full-picture-analytics-cookie-notice' ) . '</a></li>
                        </ol>',
        ),
        array(
            'type'          => 'toggle',
            'label'         => esc_html__( 'Allow site visitors to view consent data', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'consent_access',
            'option_arr_id' => $option_arr_id,
            'must_have'     => 'privacy_policy',
            'class'         => 'fupi_simple_r3 fupi_sub fupi_cdb_cond fupi_cond_val_cdb fupi_disabled',
            'popup2'        => '<p>' . esc_html__( 'This will display a link at the bottom of the consent banner. When clicked, it will open a new browser tab with the visitor\'s consent details as registered by the ConsentsDB.', 'full-picture-analytics-cookie-notice' ) . '</p>
                    <h3>' . esc_html__( 'How to enable it', 'full-picture-analytics-cookie-notice' ) . '</h3>
                    <p class="fupi_warning_text">' . esc_html__( 'To use this feature, you need to:', 'full-picture-analytics-cookie-notice' ) . '</p>
                    <ol class="fupi_warning_text">
                        <li>' . esc_html__( 'enable this option', 'full-picture-analytics-cookie-notice' ) . '</li>
                        <li>' . esc_html__( 'go to your ConsentsDB panel > Website settings > enable the option "Allow site visitors to view consent data"', 'full-picture-analytics-cookie-notice' ) . '</li>
                    </ol>',
        ),
        // FOR THE EMAIL OPTION
        array(
            'type'          => 'email',
            'label'         => esc_html__( 'Send data to this email address', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'local_backup_email',
            'must_have'     => 'privacy_policy pro',
            'class'         => 'fupi_simple_r3 fupi_sub fupi_cdb_cond fupi_cond_val_email fupi_disabled',
            'option_arr_id' => $option_arr_id,
            'popup'         => '<p>' . esc_html__( 'We will send proofs of consents, copy of a privacy policy (after every update to its text) and information about the WP FP configuration (every time there is a change to a privacy-related option).', 'full-picture-analytics-cookie-notice' ) . '</p>',
            'under field'   => '<p>' . esc_html__( 'Leave empty, to send to the administration email address', 'full-picture-analytics-cookie-notice' ) . '</p>',
        ),
        array(
            'type'          => 'select',
            'label'         => esc_html__( 'Sending frequency', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'email_frequency',
            'option_arr_id' => $option_arr_id,
            'must_have'     => 'privacy_policy pro',
            'class'         => 'fupi_simple_r3 fupi_sub fupi_cdb_cond fupi_cond_val_email fupi_disabled',
            'options'       => array(
                'daily'      => esc_html__( 'Every day', 'full-picture-analytics-cookie-notice' ),
                'twicedaily' => esc_html__( 'Twice a day', 'full-picture-analytics-cookie-notice' ),
                'hourly'     => esc_html__( 'Every hour (for large traffic sites)', 'full-picture-analytics-cookie-notice' ),
            ),
            'default'       => 'daily',
        ),
        // COMMON SETTINGS FOR BOTH
        array(
            'type'          => 'toggle',
            'label'         => esc_html__( 'Do not filter bot traffic', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'save_all_consents',
            'option_arr_id' => $option_arr_id,
            'must_have'     => 'privacy_policy',
            'popup'         => '<p>' . esc_html__( 'By default, WP Full Picture does not save consents of visitors recognized as bots and those who consented within 1 second from the moment the page has loaded.', 'full-picture-analytics-cookie-notice' ) . '</p>
                <p>' . esc_html__( 'If this filters too much traffic change the "Bot detection list" in the Shared tracking settings page or enable this option.', 'full-picture-analytics-cookie-notice' ) . '</p>',
        ),
    ),
));