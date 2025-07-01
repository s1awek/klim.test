<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_export_main">
    
    <div id="product-type-notice" style="display:block;">
        <?php
            // Define unsupported types to check
            $unsupported_types = array(
                'variable'     => 'Variable',
                'subscription' => 'Subscription',
                'bundle'       => 'Bundle',
                'composite'    => 'Composite',
            );

            $detected_types = array();

            foreach ($unsupported_types as $type => $label) {
                $args = array(
                    'type'   => $type,
                    'limit'  => 1,
                    'return' => 'ids',
                );
                $products = wc_get_products($args);
                if (!empty($products)) {
                    $detected_types[] = $label;
                }
            }

            if (!empty($detected_types)) {
                $last = array_pop($detected_types);
                if (empty($detected_types)) {
                    $types_string = $last;
                } else {
                    $types_string = implode(', ', $detected_types) . ' and ' . $last;
                }
                
                ?>
                <div class="notice notice-warning" style="width: 100%; max-width: 810px; margin-left: 0px; display: inline-flex; padding: 16px 18px 16px 26px; justify-content: flex-end; align-items: center; border-radius: 8px; border: 1px solid var(--Warning-W300, #EACB78); background: var(--Warning-W50, #FFFDF5); box-sizing: border-box;">
                    <div style="flex: 1 1 0; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 7px; display: inline-flex; width: 100%;">  
                        <div style="align-self: stretch; color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 600; line-height: 16px; word-wrap: break-word">
                            Uh oh! Unsupported Product Types Detected
                        </div>
                        <div style="align-self: stretch; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 4px; display: flex; width: 100%;">
                            <div style="width: 100%; max-width: 679px">
                                <span style="color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                Your site has <?php echo esc_html($types_string); ?> products that the free version does not support.
                                </span>
                                <a href="https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_file_upload&utm_medium=basic_revamp&utm_campaign=Product_Import_Export2.5.3" style="color: #0576FE; font-size: 14px; font-family: Inter; font-weight: 400; text-decoration: underline; word-wrap: break-word" target="_blank" rel="noopener noreferrer">
                                    Upgrade to Pro
                                </a>
                                <span style="color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 400; word-wrap: break-word">
                                    to include them in your export/import.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
    </div>
    
	<p><?php echo $step_info['description']; ?></p>
	
    <div class="wt_iew_warn wt_iew_method_export_wrn" style="display:none;">
		<?php _e('Please select an export method');?>
	</div>

    <div class="wt_iew_warn wt_iew_export_template_wrn" style="display:none;">
        <?php _e('Please select an export template.');?>
    </div>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php _e('Select an export method');?></label></th>
			<td colspan="2" style="width:75%;">
                <div class="wt_iew_radio_block">
                    <?php
					if(empty($this->mapping_templates)){
						unset($this->export_obj->export_methods['template']);
					}					
                    foreach($this->export_obj->export_methods as $key => $value) 
                    {
                        ?>
                        <p>
                            <input type="radio" value="<?php echo $key;?>" id="wt_iew_export_<?php echo $key;?>_export" name="wt_iew_export_method_export" <?php echo ($this->export_method==$key ? 'checked="checked"' : '');?>><b><label for="wt_iew_export_<?php echo $key;?>_export"><?php echo $value['title']; ?></label></b> <br />
                            <span><label for="wt_iew_export_<?php echo $key;?>_export"><?php echo $value['description']; ?></label></span>
                        </p>
                        <?php
                    }
                    ?>
                </div>

			</td>
		</tr>
		<?php if(!empty($this->mapping_enabled_fields)):?>
        <tr class="wt-iew-export-method-options wt-iew-export-method-options-quick">
            <th style="width:150px; text-align:left; vertical-align:top;"><label><?php _e('Include fields from the respective groups');?></label></th>
            <td colspan="2" style="width:75%;">
                <?php
                foreach($this->mapping_enabled_fields as $mapping_enabled_field_key=>$mapping_enabled_field)
                {
                    $mapping_enabled_field=(!is_array($mapping_enabled_field) ? array($mapping_enabled_field, 0) : $mapping_enabled_field);
                    
                    if($this->rerun_id>0) /* check this is a rerun request */
                    {
                        if(in_array($mapping_enabled_field_key, $form_data_mapping_enabled))
                        {
                            $mapping_enabled_field[1]=1; //mark it as checked
                        }else
                        {
                            $mapping_enabled_field[1]=0; //mark it as unchecked
                        }
                    }
                    ?>
                    <div class="wt_iew_checkbox" style="padding-left:0px;">
                        <input type="checkbox" id="wt_iew_<?php echo $mapping_enabled_field_key;?>" name="wt_iew_include_these_fields[]" value="<?php echo $mapping_enabled_field_key;?>" <?php echo ($mapping_enabled_field[1]==1 ? 'checked="checked"' : '');?> /> 
                        <label for="wt_iew_<?php echo $mapping_enabled_field_key;?>"><?php echo $mapping_enabled_field[0];?></label>
                    </div>  
                    <?php
                }
                ?>
                <span class="wt-iew_form_help"><?php _e('Enabling any of these ensures that all the fields from the respective groups are included in your export.');?></span>
            </td>
        </tr>
		<?php endif; ?>

		<tr class="wt-iew-export-method-options wt-iew-export-method-options-template" style="display:none;">
    		<th><label><?php _e('Export template');?></label></th>
    		<td>
    			<select class="wt-iew-export-template-sele">
    				<option value="0">-- <?php _e('Select a template'); ?> --</option>
    				<?php
    				foreach($this->mapping_templates as $mapping_template)
    				{
    				?>
    					<option value="<?php echo $mapping_template['id'];?>" <?php echo ($form_data_export_template==$mapping_template['id'] ? ' selected="selected"' : ''); ?>>
    						<?php echo $mapping_template['name'];?>
    					</option>
    				<?php
    				}
    				?>
    			</select>
    		</td>
    		<td>
    		</td>
    	</tr>
	</table>
</div>