<section class="cwginstock-subscribe-form <?php esc_html_e($variation_class); ?>">
	<div class="panel panel-primary cwginstock-panel-primary">
		<div class="panel-heading cwginstock-panel-heading">
			<h4>
				<?php
				$form_title = esc_html__('Email when stock available', 'back-in-stock-notifier-for-woocommerce');
				esc_html_e(isset($get_option['form_title']) && '' != $get_option['form_title'] ? $instock_api->sanitize_text_field($get_option['form_title']) : $form_title);
				?>
			</h4>
		</div>
		<div class="panel-body cwginstock-panel-body">
			<?php
			if (!isset($get_option['enable_troubleshoot']) || '1' != $get_option['enable_troubleshoot']) {
			?>
				<div class="r">
					<div class="c">

					<?php } ?>
					<div class="form-group center-block">
						<?php if ($name_field_visibility) { ?>
							<input type="text" class="cwgstock_name" name="cwgstock_name" placeholder="<?php esc_html_e($instock_api->sanitize_text_field($name_placeholder)); ?>" value="<?php esc_html_e($subscriber_name); ?>" />
						<?php } ?>
						<div class="email-input-wrap">
							<input type="email" class="cwgstock_email" name="cwgstock_email" placeholder="<?php esc_html_e($instock_api->sanitize_text_field($placeholder)); ?>" value="<?php esc_html_e($email); ?>" />
						</div>

						<?php if ($phone_field_visibility) { ?>
							<input type="tel" class="cwgstock_phone" name="cwgstock_phone" />
						<?php } ?>
					</div>
					<input type="hidden" class="cwg-phone-number" name="cwg-phone-number" value="" />
					<input type="hidden" class="cwg-phone-number-meta" name="cwg-phone-number-meta" value="" />
					<input type="hidden" class="cwg-product-id" name="cwg-product-id" value="<?php echo intval($product_id); ?>" />
					<input type="hidden" class="cwg-variation-id" name="cwg-variation-id" value="<?php echo intval($variation_id); ?>" />
					<input type="hidden" class="cwg-security" name="cwg-security" value="<?php esc_html_e($security); ?>" />
					<div class="form-group center-block">
						<?php $additional_class_name = isset($get_option['btn_class']) && '' != $get_option['btn_class'] ? str_replace(',', ' ', $get_option['btn_class']) : ''; ?>
						<button type="submit" name="cwgstock_submit" class="cwgstock_button <?php esc_html_e($additional_class_name); ?>" <?php echo do_shortcode(apply_filters('cwgstock_submit_attr', '', $product_id, $variation_id)); ?>><?php esc_html_e($instock_api->sanitize_text_field($button_label)); ?></button>
					</div>
					<?php do_action('cwg_instock_after_email_field', $product_id, $variation_id);
					?>
					<div class="cwgstock_output"></div>
					<?php
					if (!isset($get_option['enable_troubleshoot']) || '1' != $get_option['enable_troubleshoot']) {
					?>

					</div>
				</div>
			<?php
					}

					do_action('cwginstock_after_submit_button', $product_id, $variation_id);
			?>

			<!-- End ROW -->

		</div>
	</div>
</section>