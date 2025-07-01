<?php

/**
 * Class WC_Gateway_Przelewy24
 */
class WC_Gateway_Przelewy24 extends WC_Payment_Gateway
{
    /**
     * WC_Gateway_Przelewy24 constructor.
     */
    public function __construct()
    {

        $this->id = 'przelewy24';
        $this->icon = PRZELEWY24_URI . 'logo.png';
        $this->method_title = 'Przelewy24';
        $this->method_description = __( 'Moduł Przelewy24.pl w tej chwili posiada podstawową funkcjonalność, która sukcesywnie będzie rozszerzana.', 'przelewy24' );
        $this->has_fields = false;

        $this->init_settings();

        $this->title = (isset($this->settings['title']) ? $this->settings['title'] : '');
        $this->description = (isset($this->settings['description'])) ? $this->settings['description'] : '';
        $this->instructions = $this->get_option('instructions', $this->description);
        $this->merchant_id = (isset($this->settings['merchant_id'])) ? $this->settings['merchant_id'] : 0;
        $this->shop_id = (isset($this->settings['shop_id'])) ? $this->settings['shop_id'] : 0;
        $this->salt = (isset($this->settings['CRC_key'])) ? $this->settings['CRC_key'] : '';
        $this->p24_testmod = (isset($this->settings['p24_testmod']) ? $this->settings['p24_testmod'] : 0);

        $this->init_form_fields();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));

        add_action('woocommerce_receipt_przelewy24', array(&$this, 'receipt_page'));
        add_action('woocommerce_thankyou_przelewy24', array($this, 'thankyou_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);

        //Listener API
        add_action('woocommerce_api_wc_gateway_przelewy24', array($this, 'przelewy24_response'));
    }

    /**
     *
     */
    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Włącz/Wyłącz', 'przelewy24'),
                'type' => 'checkbox',
                'label' => __('Aktywuj moduł płatności Przelewy24.', 'przelewy24'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Tytuł:', 'przelewy24'),
                'type' => 'text',
                'description' => __('Tekst który zobaczą klienci podczas dokonywania zakupu', 'przelewy24'),
                'default' => __('Przelewy24', 'przelewy24')),
            'merchant_id' => array(
                'title' => __('ID Sprzedawcy', 'przelewy24'),
                'type' => 'text',
                'description' => __('Identyfikator sprzedawcy nadany w systemie Przelewy24.', 'przelewy24'),
                'default' => 0,
                'required' => true),
            'shop_id' => array(
                'title' => __('ID Sklepu', 'przelewy24'),
                'type' => 'text',
                'description' => __('Identyfikator sklepu nadany w systemie Przelewy24.', 'przelewy24'),
                'default' => 0,
                'required' => true),
            'CRC_key' => array(
                'title' => __('Klucz CRC', 'przelewy24'),
                'type' => 'text',
                'description' => __('Klucz do CRC nadany w systemie Przelewy24.', 'przelewy24'),
                'placeholder' => __('(16 znaków)', 'przelewy24'),
                'required' => true),
            'p24_testmod' => array(
                'title' => __('Tryb modułu', 'przelewy24'),
                'type' => 'select',
                'options' => $this->get_options(),
                'description' => __('Tryb przeprowadzania transakcji', 'przelewy24')),
            'description' => array(
                'title' => __('Opis', 'przelewy24'),
                'type' => 'textarea',
                'description' => __('Tekst który zobaczą klienci przy wyborze metody płatności', 'przelewy24'),
                'default' => __('Płać z Przelewy24', 'przelewy24')),
        );
    }

    /**
     * @param $key
     * @param $error
     * @return string
     */
    public function validate_id($key, $error)
    {

        $ret = $this->get_option($key);
        $valid = false;
        if (isset($_POST[$this->plugin_id . $this->id . '_' . $key])) {
            $ret = $_POST[$this->plugin_id . $this->id . '_' . $key];
            if (is_numeric($ret) && $ret >= 1000) $valid = true;
        }
        if (!$valid) $this->errors[$key] = $error;
        return $ret;
    }

    /**
     * @param $key
     * @return string
     */
    public function validate_crc($key)
    {
        $ret = $this->get_option($key);
        $valid = false;
        if (isset($_POST[$this->plugin_id . $this->id . '_' . $key])) {
            $ret = $_POST[$this->plugin_id . $this->id . '_' . $key];
            if (strlen($ret) == 16 && ctype_xdigit($ret)) $valid = true;
        }
        if (!$valid) $this->errors[$key] = __('Klucz do CRC powinien mieć 16 znaków.', 'przelewy24');
        return $ret;
    }

    /**
     * @param string $key
     * @param null $empty_value
     * @return string
     */
    public function get_option($key, $empty_value = null)
    {
        if (isset($this->sanitized_fields[$key])) {
            return $this->sanitized_fields[$key];
        }
        return parent::get_option($key, $empty_value);
    }

    /**
     *
     */
    public function display_errors()
    {
        foreach ($this->errors as $v) {
            echo '<div class="error">' . __('Błąd', 'przelewy24') . ': ' . filter_var($v, FILTER_SANITIZE_STRING) . '</div>';
        }
        echo '<script type="text/javascript">jQuery(document).ready(function () {jQuery(".updated").remove();});</script>';
    }

    /**
     * @param string $error
     */
    public function add_error($error)
    {
        if (!in_array($error, $this->errors)) {
            parent::add_error($error);
        }
    }

    /**
     * @return bool|void
     */
    public function process_admin_options()
    {
        parent::process_admin_options();
        $this->validate_fields();
        if (!empty($this->errors)) {
            $this->display_errors();
        }
    }

    /**
     * @param bool $form_fields
     * @return bool|void
     */
    public function validate_fields($form_fields = false)
    {
        $this->sanitized_fields['p24_testmod'] = $_POST[$this->plugin_id . $this->id . '_p24_testmod'] == 'secure' ? 'secure' : 'sandbox';
        $this->sanitized_fields['merchant_id'] = $this->validate_id('merchant_id', __('Błędny ID Sprzedawcy.', 'przelewy24'));
        $this->sanitized_fields['shop_id'] = $this->validate_id('shop_id', __('Błędny ID Sklepu.', 'przelewy24'));
        $this->sanitized_fields['CRC_key'] = $this->validate_crc('CRC_key');
        $this->sanitized_fields['p24_api'] = $_POST[$this->plugin_id . $this->id . '_p24_api'];

        $P24 = new Przelewy24Class($this->sanitized_fields['merchant_id'], $this->sanitized_fields['shop_id'], $this->sanitized_fields['CRC_key'], ($this->sanitized_fields['p24_testmod'] == 'sandbox'));
        $ret = $P24->testConnection();
        if ($ret['error'] != 0)
            $this->errors['p24_testmod'] = __('Błędny ID Sklepu, Sprzedawcy lub Klucz do CRC dla tego trybu pracy wtyczki.', 'przelewy24');

        $_SESSION['P24'] = $this->sanitized_fields;
    }

    /**
     * Receipt Page
     **/
    function receipt_page($order)
    {
        echo $this->generate_przelewy24_form($order, true);
    }

    /**
     * Generate przelewy24 button link
     **/
    private function generate_fields_array($order_id, $transaction_id = null)
    {
        global $locale;

        $localization = !empty($locale) ? explode("_", $locale) : 'pl';
        $order = new WC_Order($order_id);
        if (!$order) return false;

        if (is_null($transaction_id)) {
            $transaction_id = $order_id . "_" . uniqid(md5($order_id . '_' . date("ymds")), true);
        }

        // modifies order number if Sequential Order Numbers Pro plugin is installed
        if (class_exists('WC_Seq_Order_Number_Pro')) {
            $seq = new WC_Seq_Order_Number_Pro();
            $description_order_id = $seq->get_order_number($order_id, $order);
        } else if (class_exists('WC_Seq_Order_Number')) {
            $seq = new WC_Seq_Order_Number();
            $description_order_id = $seq->get_order_number($order_id, $order);
        } else {
            $description_order_id = $order_id;
        }

        //p24_opis depend of test mode
        $desc = ($this->p24_testmod == "sandbox" ? __("Transakcja testowa", 'przelewy24') . ", " : '') .
            __('Zamówienie nr', 'przelewy24') . ': ' . $description_order_id . ', ' . $order->billing_first_name . ' ' . $order->billing_last_name . ', ' . date('Ymdhi');

        //return address URL
        $payment_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24', 'order_id' => $order_id), home_url('/'));
        $status_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24'), home_url('/'));

        $accept = (int)$this->get_custom_data('user', get_current_user_id(), 'accept');

        if ($accept) {
            $regulationAccept = $accept;
        } else {
            $regulationAccept = (int)get_post_meta($order->get_order_number(), 'p24_regulation_accept') ? 1 : '';
        }
        /*Form send to przelewy24*/

        $amount = $order->order_total * 100;
        $amount = number_format($amount, 0, "", "");

        $p24_settings = get_option('woocommerce_przelewy24_settings');

        $currency = strtoupper($order->get_currency());
        $przelewy24_arg = array(
            'p24_session_id' => addslashes($transaction_id),
            'p24_merchant_id' => (int)$this->merchant_id,
            'p24_pos_id' => (int)$this->shop_id,
            'p24_email' => filter_var($order->billing_email, FILTER_SANITIZE_EMAIL),
            'p24_amount' => (int)$amount,
            'p24_currency' => filter_var($currency, FILTER_SANITIZE_STRING),
            'p24_description' => addslashes($desc),
            'p24_language' => filter_var($localization[0], FILTER_SANITIZE_STRING),
            'p24_client' => $order->billing_first_name . ' ' . $order->billing_last_name,
            'p24_address' => $order->billing_address_1,
            'p24_city' => $order->billing_city,
            'p24_zip' => $order->billing_postcode,
            'p24_country' => $order->billing_country,
            'p24_encoding' => 'UTF-8',
            'p24_url_status' => filter_var($status_page, FILTER_SANITIZE_URL),
            'p24_url_return' => filter_var($payment_page, FILTER_SANITIZE_URL),
            'p24_url_cancel' => filter_var($payment_page, FILTER_SANITIZE_URL),
            'p24_api_version' => P24_VERSION,
            'p24_ecommerce' => 'woocommerce_' . WOOCOMMERCE_VERSION,
            'p24_ecommerce2' => '1.0.0',
            'p24_method' => (int)get_post_meta($order->get_order_number(), 'p24_method', true),
            'p24_regulation_accept' => $regulationAccept,
            'p24_time_limit' => !empty($this->p24_timelimit) ? $this->p24_timelimit : 15,
            'p24_channel' => $this->p24_payslow == 'yes' ? '16' : '',
            'p24_wait_for_result' => ($p24_settings['p24_wait_for_result'] == 'yes') ? 1 : 0,
            'p24_shipping' => number_format($order->get_shipping_total() * 100, 0, '', ''),
        );

        $productsInfo = array();
        foreach ($order->get_items() as $product) {
            $productsInfo[] = array(
                'name' => filter_var($product['name'], FILTER_SANITIZE_STRING),
                'description' => strip_tags(get_post($product['product_id'])->post_content),
                'quantity' => (int)$product['qty'],
                'price' => ($product['line_total'] / $product['qty']) * 100,
                'number' => (int)$product['product_id'],
            );
        }

        $shipping = number_format($order->get_shipping_total() * 100, 0, '', '');
        $translations = array(
            'virtual_product_name' => __('Dodatkowe kwoty [VAT, rabaty]', 'przelewy24'),
            'cart_as_product' => __('Twoje zamówienie', 'przelewy24'),
        );
        $p24Product = new Przelewy24Product($translations);
        $p24ProductItems = $p24Product->prepareCartItems($amount, $productsInfo, $shipping);
        $przelewy24_arg = array_merge($przelewy24_arg, $p24ProductItems);

        $P24 = new Przelewy24Class($this->merchant_id, $this->shop_id, $this->salt, ($this->p24_testmod == 'sandbox'));
        $przelewy24_arg['p24_sign'] = $P24->trnDirectSign($przelewy24_arg);
        $P24->checkMandatoryFieldsForAction($przelewy24_arg, 'trnDirect');


        return $przelewy24_arg;
    }

    /**
     * @param $order_id
     * @param bool $autoSubmit
     * @return string
     */
    public function generate_przelewy24_form($order_id, $autoSubmit = true)
    {
        $order = new WC_Order((int)$order_id);
        $przelewy24_arg = $this->generate_fields_array((int)$order_id);
        $przelewy_form = '';
        foreach ($przelewy24_arg as $key => $value)
            $przelewy_form .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';

        $P24 = new Przelewy24Class($this->merchant_id, $this->shop_id, $this->salt, ($this->p24_testmod == 'sandbox'));
        $return = '<div id="payment" style="background: none"> ' .
            '<form action="' . $P24->trnDirectUrl() . '" method="post" id="przelewy_payment_form"' .
            ($autoSubmit ? '' : ' onSubmit="return p24_processPayment()" ') .
            '>' .
            $przelewy_form .
            '<input type="submit" class="button alt" id="place_order" value="' . __('Potwierdzam zamówienie', 'przelewy24') . '" /> ' .
            '<p style="text-align:right; float:right; width:100%; font-size:12px;">' . __('Złożenie zamówienia wiąże się z obowiązkiem zapłaty', 'przelewy24') . '</p>' .
            '<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Anuluj zamówienie', 'przelewy24') . '</a>' .
            ($autoSubmit ?
                '<script type="text/javascript">jQuery(function(){jQuery("body").block({message: "' .
                __('Dziękujemy za złożenie zamówienia. Za chwilę nastąpi przekierowanie na stronę przelewy24.pl', 'przelewy24') .
                '",overlayCSS: {background: "#fff",opacity: 0.6},css: {padding:20,textAlign:"center",color:"#555",border:"2px solid #AF2325",backgroundColor:"#fff",cursor:"wait",lineHeight:"32px"}});' .
                'jQuery("#przelewy_payment_form input[type=submit]").click();});' .
                '</script>' : '') .
            '</form>' .
            '</div>' .
            '';
        return $return;
    }

    /**
     * Process the payment and return the result
     **/
    function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url($order));
    }

    /**
     * /*Check przelewy24 response
     **/
    function przelewy24_response()
    {
        global $woocommerce;
        $P24 = new Przelewy24Class($this->merchant_id, $this->shop_id, $this->salt, ($this->p24_testmod == 'sandbox'));

        if (isset($_POST['p24_session_id']) && isset($_POST['action']) && $_POST['action'] == 'trnRegister' && isset($_POST['order_id'])) {
            $P24C = new Przelewy24Class($this->merchant_id, $this->shop_id, $this->salt, ($this->p24_testmod == 'sandbox'));
            $post_data = $this->generate_fields_array($_POST['order_id'], $_POST['p24_session_id']);
            foreach ($post_data as $k => $v) {
                $P24C->addValue($k, $v);
            }
            $token = $P24C->trnRegister();
            if (is_array($token)) {
                $token = $token['token'];
                exit(json_encode(array(
                    'p24jsURL' => $P24C->getHost() . 'inchtml/ajaxPayment/ajax.js?token=' . $token,
                    'p24cssURL' => $P24C->getHost() . 'inchtml/ajaxPayment/ajax.css',
                )));
            }

            exit();
        }

        if (isset($_POST['p24_session_id'])) {
            $p24_session_id = $_POST['p24_session_id'];
            $reg_session = "/^[0-9a-zA-Z_\.]+$/D";
            if (!preg_match($reg_session, $p24_session_id)) exit;
            $session_id = explode('_', $p24_session_id);
            $order_id = $session_id[0];
            $order = new WC_Order($order_id);
            $validation = array('p24_amount' => number_format($order->order_total * 100, 0, "", ""));
            $WYNIK = $P24->trnVerifyEx($validation);

            if ($WYNIK === null) {
                exit("\n" . 'MALFORMED POST');
            } elseif ($WYNIK === true) {
                $order->add_order_note(__('IPN payment completed', 'woocommerce'));
                $order->payment_complete();

            }
            if (!isset($_GET['order_id'])) exit;
        }

        if (isset($_GET['order_id'])) {
            $order = new WC_Order($_GET['order_id']);

            if ($order->get_status == 'failed') {
                $this->addNotice(
                // Sorry your transaction did not go through successfully, please try again.
                    $woocommerce,
                    __('Błąd płatności: ', 'przelewy24') . __('Przepraszamy, ale twoja transakcja nie została przeprowadzona pomyślnie, prosimy spróbować ponownie.', 'przelewy24'),
                    'error'
                );

                wp_redirect($order->get_cancel_order_url_raw());
            } else if ($order->get_status == 'completed' || $order->get_status == 'processing') {
                $woocommerce->cart->empty_cart();
                wp_redirect($this->get_return_url($order));

            } else {
                // We did not received information about payment. If you are sure you completed your payment please contact our customer service
                $this->addNotice(
                    $woocommerce,
                    __('Płatność realizowana przez Przelewy24 nie została jeszcze potwierdzona. Jeśli potwierdzenie nadejdzie w czasie późniejszym, płatność zostanie automatycznie przekazana do sklepu', 'przelewy24'),
                    'notice'
                );

                wp_redirect($this->get_return_url($order));
            }
        }
    }

    /**
     * @param $woocommerce
     * @param $message
     * @param $type
     */
    function addNotice($woocommerce, $message, $type)
    {
        if ($type == 'error' && method_exists($woocommerce, 'add_error')) {
            $woocommerce->add_error($message);
        } else if (in_array($type, array('success', 'notice')) && method_exists($woocommerce, 'add_message')) {
            $woocommerce->add_message($message);
        } else {
            wc_add_notice($message, $type);
        }
    }

    /**
     * @return array
     */
    function get_options()
    {
        $option_list = array();
        $option_list['secure'] = __('normalny', 'przelewy24');
        $option_list['sandbox'] = __('testowy', 'przelewy24');

        return $option_list;
    }


    /**
     * Output for the order received page.
     */

    function thankyou_page()
    {
        if ($this->instructions) {
            echo wpautop(wptexturize($this->instructions));
        }
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */

    function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && 'przelewy24' === $order->payment_method) {
            echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
        }
    }

    /**
     * @param $data_type
     * @param $data_id
     * @param $key
     * @return array|mixed|null|object
     */
    private static function get_custom_data($data_type, $data_id, $key)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_p24_custom_data';

        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE data_type = %s AND data_id = %d AND custom_key = %s",
            [
                $data_type,
                $data_id,
                $key
            ]
        );

        $fields = $wpdb->get_results(
            $query,
            OBJECT
        );

        foreach ($fields as $field) {
            $value = json_decode($field->custom_value, true);
            if ($value != null) return $value;
            else return $field->custom_value;
        }
        return null;
    }

}
