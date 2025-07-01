<?php

$ret_text = '';

switch( $section_id ){

    case 'fupi_cook_main':
        $ret_text = '<p>' . sprintf( esc_html__( 'This consent banner controls the loading of all tracking tools managed by WP Full Picture (installed with separate modules, managed with the Tracking Tools Manager module and Iframes Manager module). It does not need cookie scans.', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>';
    break;

    case 'fupi_cook_cdb':

        $ret_text = '<p>' . sprintf( esc_html__( 'Keeping records of consents is %1$srequired by GDPR%2$s. Most WordPress plugins save them in the site\'s database, but this makes them easy to modify, and they may not be considered as valid proofs. That is why we created a cloud database, where you can keep your consents safely and with all necessary data. You can save 1000 proofs for free and purchase one of %3$svery affordable plans%4$s when you need more.', 'full-picture-analytics-cookie-notice' ), '<button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_gdpr_info_popup">', '</button>', '<a href="https://wpfullpicture.com/pricing#hook_cdb_plans">', '</a>')  . '</p>';

        if ( fupi_fs()->can_use_premium_code() ) {
            $ret_text .= '<p style="border: 2px solid #a7d4e2; border-radius: 10px; padding: 20px 15px;">' . sprintf( esc_html__( 'As a Premium user, you can save additional %1$s500 records of consent/decline a day%2$s, for free, until the end of August 2025.', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>';
        }

        $ret_text .= '
        <div style="display: flex; flex-wrap: wrap; gap: 30px;">
            <div style="flex: 0 0 calc(66% - 15px); min-width: 300px">
                <p><strong>'. esc_html__( 'To start saving consents:', 'full-picture-analytics-cookie-notice') .'</strong></p>
                <ol>
                    <li>'. esc_html__( 'Go to the "GDPR setup helper" (see menu on the left) and make sure your site is set up correctly.', 'full-picture-analytics-cookie-notice') .'</li>
                    <li><a href="https://consentsdb.com/">'. esc_html__( 'Create an account at ConsentsDB.com.', 'full-picture-analytics-cookie-notice') .'</a></li>
                    <li>'. esc_html__( 'Add this website to your account. You will get your secret key (to paste in the field below) and a free package of 1000 records.', 'full-picture-analytics-cookie-notice') .'</li>
                </ol>
            </div>
            <div style="flex: 0 0 calc(33% - 15px); min-width: 250px">
                <p><strong>'. esc_html__( 'Learn more:', 'full-picture-analytics-cookie-notice') .'</strong></p>
                <ol>
                    <li><a href="https://wpfullpicture.com/pricing#hook_cdb_plans">'. esc_html__( 'Pricing', 'full-picture-analytics-cookie-notice') .'</a></li>
                    <li><a href="https://wpfullpicture.com/support/documentation/introduction-to-consentsdb/">'. esc_html__( 'About ConsentsDB', 'full-picture-analytics-cookie-notice') .'</a></li>
                    <li><a href="https://wpfullpicture.com/support/documentation/how-to-start-collecting-consents-in-the-consentsdb/" target="_blank">' . esc_html__( 'Setup guide', 'full-picture-analytics-cookie-notice') . '</a></li>
                </ol>
            </div>
        </div>';

    break;
};

?>
