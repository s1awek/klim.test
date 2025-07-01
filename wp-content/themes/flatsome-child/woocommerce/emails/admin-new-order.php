<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author WooThemes
 * @package WooCommerce/Templates/Emails/HTML
 * @version 2.5.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }

 /**
  * @hooked WC_Emails::email_header() Output the email header
  */
 do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

 <p><?php printf( __( 'You have received an order from %s. The order is as follows:', 'woocommerce' ), $order->get_formatted_billing_full_name() ); ?></p>

 <?php

 /**
  * @hooked WC_Emails::order_details() Shows the order details table.
  * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
  * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
  * @since 2.5.0
  */
 do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

 /**
  * @hooked WC_Emails::order_meta() Shows order meta data.
  */
 do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

 /**
  * @hooked WC_Emails::customer_details() Shows customer details
  * @hooked WC_Emails::email_address() Shows email address
  */
 do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if($order->shipping_address_1): ?>
<h2 style='color: #96588a; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;'>Adres wysyłkowy</h2>

			<address class="address" style="padding: 12px 12px 0; color: #636363; border: 1px solid #e5e5e5;">
				<?php echo $order->shipping_first_name; ?>
				<br>
				<?php echo $order->shipping_last_name; ?>
				<br>
				<?php echo $order->shipping_address_1; ?>
				<br>
				<?php echo $order->shipping_address_2; ?>
				<br>
				<?php echo $order->shipping_city; ?>
				<br>
				<?php echo $order->shipping_postcode; ?>
				</address>
<?php
endif;
 /**
  * @hooked WC_Emails::email_footer() Output the email footer
  */
 do_action( 'woocommerce_email_footer', $email );
