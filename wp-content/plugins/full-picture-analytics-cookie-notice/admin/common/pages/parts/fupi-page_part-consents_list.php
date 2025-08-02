<?php

//
// Lists all stored consents, paginated.
//

class Fupi_PROOFREC_Consents_Display {

    public function render_consents_list() {
        
        global $wpdb;

        $table  = $wpdb->prefix . 'fupi_consents';
        $page   = isset($_GET['cpage']) ? abs((int)$_GET['cpage']) : 1;
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $total  = $wpdb->get_var("SELECT COUNT(id) FROM $table");

        $consents = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table ORDER BY consent_date DESC LIMIT %d OFFSET %d", $limit, $offset)
        );

        ?>
        <div id="fupi_settings_form">
            <h2><?php esc_html_e('Latest consents', 'full-picture-analytics-cookie-notice' ); ?></h2>
            <div class="fupi_section_descr fupi_el">
                <p><?php esc_html_e('These are records of consent collected since the last email backup.', 'full-picture-analytics-cookie-notice' ); ?></p>
            </div>
        </div>

        <div class="fupi_tablenav top">
            <div class="fupi_tablenav-pages">
                <?php 
                echo paginate_links(array(
                    'base'    => add_query_arg('cpage', '%#%'),
                    'format'  => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total'   => ceil($total / $limit),
                    'current' => $page
                ));
                ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th><?php esc_html_e('Date', 'full-picture-analytics-cookie-notice' ); ?></th>
                <th><?php esc_html_e('Consent ID', 'full-picture-analytics-cookie-notice' ); ?></th>
                <th><?php esc_html_e('Consents', 'full-picture-analytics-cookie-notice' ); ?></th>
                <th><?php esc_html_e('Details', 'full-picture-analytics-cookie-notice' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($consents)): ?>
                <tr>
                    <td colspan="4"><?php esc_html_e('No consents found.', 'full-picture-analytics-cookie-notice' ); ?></td>
                </tr>
            <?php else: foreach ($consents as $consent): ?>
                <tr>
                    <td><?php echo date('d M Y H:i:s', round( $consent->consent_date / 1000 ) ); ?></td>
                    <td><?php echo esc_html($consent->consent_id); ?></td>
                    <td><?php echo esc_html($consent->provided_consents); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=full_picture_proofrec&tab=consents_list&fupi_cons_id=' . $consent->id); ?>" class="button-secondary"><?php esc_html_e('Consent details', 'full-picture-analytics-cookie-notice' ); ?></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <div class="fupi_tablenav bottom">
            <div class="fupi_tablenav-pages">
                <?php 
                echo paginate_links(array(
                    'base' => add_query_arg('cpage', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil($total / $limit),
                    'current' => $page
                ));
                ?>
            </div>
        </div>
        <?php
    }

    public function render_consent_details( $db_id ) {
        
        global $wpdb;
        $table   = $wpdb->prefix . 'fupi_consents';
        $consent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %s", $db_id));

        ?>

        <div id="fupi_settings_form">
            <h2><?php esc_html_e('Consents details', 'full-picture-analytics-cookie-notice' ); ?></h2>
            <div class="fupi_section_descr fupi_el">
                <p><?php esc_html_e('This consent will be sent to your email account according to the schedule you set. Afterwards, it will be deleted from the database.', 'full-picture-analytics-cookie-notice' ); ?></p>
            </div>
        </div>

        <div class="wrap">
            <h3><?php esc_html_e('Consent Details', 'full-picture-analytics-cookie-notice' ); ?></h3>
            <?php if (!$consent): ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e('There is no consent with the provided ID.', 'full-picture-analytics-cookie-notice' ); ?></p>
                </div>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=fupi-consents'); ?>" class="button button-secondary"><?php esc_html_e('Back to the list', 'full-picture-analytics-cookie-notice' ); ?></a>
                </p>
            <?php else:
                $extra_data = json_decode($consent->extra_data, true); ?>
                <table class="wp-list-table widefat fixed">
                    <tr>
                        <th><?php esc_html_e('Consent ID', 'full-picture-analytics-cookie-notice' ); ?></th>
                        <td><?php echo esc_html($consent->consent_id); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Consent Date', 'full-picture-analytics-cookie-notice' ); ?></th>
                        <td><?php echo date('d M Y H:i:s', round( $consent->consent_date / 1000 ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Provided Consents', 'full-picture-analytics-cookie-notice' ); ?></th>
                        <td><?php echo esc_html($consent->provided_consents); ?></td>
                    </tr>
                </table>
                <h3><?php esc_html_e('Extra Data', 'full-picture-analytics-cookie-notice' ); ?></h3>
                <table class="wp-list-table widefat fixed">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Key', 'full-picture-analytics-cookie-notice' ); ?></th>
                        <th><?php esc_html_e('Value', 'full-picture-analytics-cookie-notice' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($extra_data as $key => $value): ?>
                        <tr>
                            <td><?php echo esc_html($key); ?></td>
                            <td><?php echo is_array($value) || is_object($value)
                                    ? esc_html(json_encode($value))
                                    : esc_html($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p>
                <a href="<?php echo admin_url('admin.php?page=full_picture_proofrec&tab=consents_list'); ?>" class="button-secondary"><?php esc_html_e('Back to Consents', 'full-picture-analytics-cookie-notice' ); ?></a>
            </p>
        </div>
        <?php
    }
}

// PREVIOUS CONSENTS
if ( ! empty( $this->tools['proofrec'] ) && ! empty ( $this->proofrec['storage_location'] ) && $this->proofrec['storage_location'] == 'email' ) {

    $records_display_class = new Fupi_PROOFREC_Consents_Display();

    if ( ! empty ( $consent_id ) ) {
        $records_display_class->render_consent_details( $consent_id );
    } else {
        $records_display_class->render_consents_list();
    }

} else {

    echo '<div id="fupi_settings_form">
        <h2>' . esc_html__('Latest consents', 'full-picture-analytics-cookie-notice' ) . '</h2>
        <div class="fupi_section_descr fupi_el">
            <p>' . esc_html__('Please, enable the opion to store consents in the email account.', 'full-picture-analytics-cookie-notice' ) . '</p>
        </div>
    </div>';
}


