<?php

include FUPI_PATH . '/includes/fupi_modules_data.php';
include FUPI_PATH . '/includes/fupi_modules_names.php';
// fupi_field_html($recipe, $values, $arr_id)
// -	$recipe 		array 		no default			recipe for setting up a single field
// -	$field_id 		string  	default: false 		field's name and ID (optional - passed by r3)
// -	$saved_value 	array 		default: false 		saved value of this field (optional - passed by r3)
//
// ! important - remember that this code generates a SINGLE field (or a repeater / multi field) and not ALL fields
// GET SAVED VALUES
// if we don't have any - values are passed by r3 repeater
if ( empty( $field_id ) ) {
    // if id of options array is given (for serialized data)
    if ( isset( $recipe['option_arr_id'] ) ) {
        // get the options array (returns false if not exists)
        $saved_data_arr = get_option( $recipe['option_arr_id'] );
        if ( !empty( $saved_data_arr ) && isset( $saved_data_arr[$recipe['field_id']] ) ) {
            // if options array and field value exist
            $saved_value = $saved_data_arr[$recipe['field_id']];
            // get the value of the field we are about to generate
        } else {
            // if array doesn't exist
            $saved_value = ( isset( $recipe['default'] ) ? $recipe['default'] : false );
            // set default or nothing
        }
        $field_id = $recipe['option_arr_id'] . '[' . $recipe['field_id'] . ']';
        // get correct field id
        // for unserialized data (not used anywhere in the plugin)
    } else {
        $saved_value = get_option( $recipe['field_id'] );
        // get the current field value (returns false if not exists)
        if ( !$saved_value ) {
            $value = ( isset( $recipe['default'] ) ? $recipe['default'] : false );
        }
        // set default or nothing
        $field_id = $recipe['field_id'];
    }
} else {
    $is_r3 = true;
}
// CHECK IF THE FIELD IS AVAILABLE
$must_have_parts = [];
$must_have_html = '';
$el_class = '';
if ( !empty( $recipe['must_have'] ) ) {
    $must_have_val = esc_attr( $recipe['must_have'] );
    // break must have into array
    $must_have_arr = explode( ' ', $must_have_val );
    foreach ( $must_have_arr as $must_have ) {
        // check for missing licence
        if ( $must_have == 'pro' || $must_have == 'pro_round' ) {
            $class_suffix = ( $must_have == 'pro_round' ? '_round' : '' );
            $must_have_html = '<div class="fupi_must_have_pro_ico' . $class_suffix . ' fupi_tooltip"><span class="dashicons dashicons-lock"></span><span class="fupi_tooltiptext">' . esc_html__( 'Requires Pro licence', 'full-picture-analytics-cookie-notice' ) . '</span></div>';
            $el_class = 'fupi_disable_fields ';
            $must_have_parts = [];
            break;
            // check for missing privacy policy
        } else {
            if ( $must_have == 'privacy_policy' ) {
                $priv_policy_url = get_privacy_policy_url();
                if ( empty( $priv_policy_url ) ) {
                    $must_have_parts[] = esc_html__( 'Website Privacy Policy', 'full-picture-analytics-cookie-notice' );
                }
                // check for missing admin capabilities
            } else {
                if ( $must_have == 'admin' ) {
                    $is_admin = current_user_can( 'manage_options' );
                    if ( !$is_admin ) {
                        $must_have_parts[] = esc_html__( 'Administrator role', 'full-picture-analytics-cookie-notice' );
                    }
                    // check for disabled woo module or woo plugin
                } else {
                    if ( $must_have == 'woo' ) {
                        if ( !$this->is_woo_enabled ) {
                            $must_have_parts[] = esc_html__( 'WooCommerce Tracking module', 'full-picture-analytics-cookie-notice' );
                        }
                    } else {
                        if ( str_starts_with( $must_have, 'field' ) ) {
                            $field_params_a = explode( '|', $must_have );
                            if ( count( $field_params_a ) == 5 ) {
                                $other_opt_name = $field_params_a[1];
                                $other_field_id = $field_params_a[2];
                                $expected_field_val = $field_params_a[3];
                                $must_have_text = str_replace( '"', '', $field_params_a[4] );
                                $must_have_text = str_replace( '_', ' ', $must_have_text );
                                $other_option_data = get_option( $other_opt_name );
                                if ( empty( $other_option_data ) || empty( $other_option_data[$other_field_id] ) ) {
                                    $must_have_parts[] = $must_have_text;
                                } else {
                                    $field_value = $other_option_data[$other_field_id];
                                    $value_matches = $expected_field_val == 'exists' || $expected_field_val == $field_value;
                                    if ( !$value_matches ) {
                                        $must_have_parts[] = $must_have_text;
                                    }
                                }
                            }
                            // check for missing modules
                        } else {
                            if ( !isset( $this->tools[$must_have] ) ) {
                                foreach ( $fupi_modules as $module ) {
                                    if ( $module['id'] == $must_have ) {
                                        $must_have_parts[] = esc_html__( 'Module', 'full-picture-analytics-cookie-notice' ) . ' "' . $fupi_modules_names[$module['id']] . '"';
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
if ( count( $must_have_parts ) > 0 ) {
    $must_have_html = '<div class="fupi_must_have_info">' . esc_html__( 'Requires', 'full-picture-analytics-cookie-notice' ) . ': <span class="fupi_req">' . join( '</span> <span class="fupi_req">', $must_have_parts ) . '</span></div>';
    $el_class = 'fupi_disable_fields ';
}
// GET DATA COMMON TO MOST FIELDS
$placeholder = ( isset( $recipe['placeholder'] ) ? $recipe['placeholder'] : '' );
$el_class .= ( !empty( $recipe['el_class'] ) ? $recipe['el_class'] : '' );
if ( isset( $recipe['required'] ) ) {
    $el_class .= ' fupi_req';
}
$el_data_target = ( !empty( $recipe['el_data_target'] ) ? $recipe['el_data_target'] : '' );
$data_format = ( !empty( $recipe['format'] ) ? 'data-dataformat="' . $recipe['format'] . '"' : '' );
// if ( !empty ($data_format) ) trigger_error('data format ' . $data_format);
// BUILD HTML
// BEFORE FIELD
if ( isset( $recipe['before field'] ) ) {
    printf( '<span class="fupi_before_field">%s</span>', $recipe['before field'] );
    // Show it
}
// Check which type of field we want
switch ( $recipe['type'] ) {
    // =======
    case 'r3':
        $fields = ( isset( $recipe['fields'] ) && is_array( $recipe['fields'] ) ? $recipe['fields'] : false );
        if ( !$fields ) {
            return;
        }
        $repeater = !empty( $recipe['is_repeater'] );
        $el_class .= ( $repeater ? ' fupi_r3_repeater' : '' );
        $btns_class = ( !empty( $recipe['btns_class'] ) ? $recipe['btns_class'] : '' );
        $sections_nr = ( $repeater && is_array( $saved_value ) ? count( $saved_value ) : 0 );
        $i = 0;
        for ($i = 0; $i <= $sections_nr; $i++) {
            // here we want to show empty section if there is no data yet or we want to remove an empty section if it doesn't have any values
            if ( empty( $saved_value[$i] ) && $sections_nr > 0 ) {
                continue;
            }
            echo '<div class="fupi_r3_section ' . $el_class . '">';
            foreach ( $fields as $field_recipe ) {
                $f_id = ( !empty( $field_recipe['field_id'] ) ? $field_recipe['field_id'] : '' );
                $f_default = ( !empty( $field_recipe['default'] ) ? $field_recipe['default'] : '' );
                $f_label = ( !empty( $field_recipe['label'] ) ? $field_recipe['label'] : false );
                $f_type = ( !empty( $field_recipe['type'] ) ? $field_recipe['type'] : '' );
                $class = ( !empty( $field_recipe['class'] ) ? $field_recipe['class'] : '' );
                if ( $repeater ) {
                    $f_name = $field_id . '[' . $i . '][' . $f_id . ']';
                    $f_val = ( isset( $saved_value[$i][$f_id] ) ? $saved_value[$i][$f_id] : $f_default );
                } else {
                    $f_name = $field_id . '[' . $f_id . ']';
                    $f_val = ( isset( $saved_value[$f_id] ) ? $saved_value[$f_id] : $f_default );
                }
                // (optional) start a wrapper around a group of fields
                if ( isset( $field_recipe['start_sub_section'] ) ) {
                    echo '<div class="fupi_r3_fields_group">';
                }
                echo '<div class="fupi_r3_field fupi_field_type_' . $f_type . ' fupi_field_' . $f_id . '_wrap ' . $class . '">';
                if ( !empty( $f_label ) && $f_type != 'checkbox' ) {
                    if ( $f_type == 'r3' ) {
                        echo '<p class="fupi_r3_section_label">' . esc_attr( $f_label ) . '</p>';
                    } else {
                        echo '<label class="fupi_r3_field_label">' . esc_attr( $f_label ) . '</label>';
                    }
                }
                if ( $f_type != 'label' ) {
                    $this->fupi_field_html( $field_recipe, $f_name, $f_val );
                }
                echo '</div>';
                // (optional) start a new sub section wrapper
                if ( isset( $field_recipe['end_sub_section'] ) ) {
                    echo '</div>';
                }
            }
            if ( $repeater ) {
                echo '<div class="fupi_r3_btns ' . $btns_class . '">
						<button type="button" class="fupi_r3_btn fupi_btn_remove dashicons dashicons-minus"></button>
						<button type="button" class="fupi_r3_btn fupi_btn_add dashicons dashicons-plus-alt2"></button>
					</div>';
            }
            echo '</div>';
            // r3 section end
        }
        if ( !empty( $output ) ) {
            echo $output;
        }
        break;
    // =======
    case 'text':
    case 'number':
    case 'url':
    case 'email':
    case 'password':
        // If it is a text field
        $disabled = ( !empty( $recipe['disabled'] ) ? ' disabled="' . esc_attr( $recipe['disabled'] ) . '"' : '' );
        $multiple_attr = ( $recipe['type'] == 'email' ? 'multiple' : '' );
        if ( $recipe['type'] == 'number' ) {
            printf( '<div class="fupi_number_field_wrap">
			<button type="button" class="fupi_number_decrease"><span class="dashicons dashicons-minus"></span></button>' );
        }
        printf(
            '<input type="' . $recipe['type'] . '" name="%1$s" id="%1$s" placeholder="%2$s" value="%3$s" class="%4$s" data-target="%5$s" %6$s %7$s %8$s/>',
            $field_id,
            $placeholder,
            esc_attr( $saved_value ),
            $el_class,
            $el_data_target,
            $disabled,
            $data_format,
            $multiple_attr
        );
        if ( $recipe['type'] == 'number' ) {
            printf( '<button type="button" class="fupi_number_increase"><span class="dashicons dashicons-plus-alt2"></span></button></div>' );
        }
        break;
    // =======
    case 'toggle':
        $el_data_name = ( !empty( $recipe['el_data_name'] ) ? $recipe['el_data_name'] : '' );
        $tags = ( !empty( $recipe['tags'] ) ? $recipe['tags'] : '' );
        printf(
            '<label class="fupi_switch"><input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s class="%3$s" data-tags="%6$s" data-target="%4$s" data-name="%5$s"/><span class="fupi_switch_slider"></span></label>',
            $field_id,
            checked( 1, $saved_value, false ),
            $el_class,
            $el_data_target,
            $el_data_name,
            $tags
        );
        break;
    // =======
    case 'checkbox':
        if ( !empty( $is_r3 ) ) {
            echo '<label>';
        }
        printf(
            '<input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s class="%3$s" data-target="%4$s"/>',
            $field_id,
            checked( 1, $saved_value, false ),
            $el_class,
            $el_data_target
        );
        if ( !empty( $is_r3 ) ) {
            echo $recipe['label'] . '</label>';
        }
        break;
    // =======
    case 'select2':
        if ( !empty( $recipe['options'] ) && is_array( $recipe['options'] ) ) {
            $multiple = ( empty( $recipe['multiple'] ) ? '' : 'multiple="multiple"' );
            $save_as_multiple = ( empty( $recipe['multiple'] ) ? '' : '[]' );
            wp_enqueue_style( 'fupi-select2-css' );
            wp_enqueue_script( 'fupi-select2-js' );
            $output = '<select name="' . $field_id . $save_as_multiple . '" class="fupi_select2 ' . $el_class . '" ' . $multiple . '>';
            foreach ( $recipe['options'] as $key => $label ) {
                // Get html that marks option as selected
                $selected_html = '';
                if ( empty( $recipe['multiple'] ) ) {
                    $selected_html = selected( $saved_value, $key, false );
                    // multi-select keeps values in an array
                } else {
                    if ( !empty( $saved_value ) && is_array( $saved_value ) ) {
                        foreach ( $saved_value as $val ) {
                            if ( $val == $key ) {
                                $selected_html = 'selected="selected"';
                                break;
                            }
                        }
                    }
                }
                $output .= '<option value="' . $key . '" ' . $selected_html . '>' . $label . '</option>';
            }
            $output .= '</select>';
            echo $output;
        }
        break;
    // =======
    case 'user_search':
        wp_enqueue_style( 'fupi-select2-css' );
        wp_enqueue_script( 'fupi-select2-js' );
        $selected_users = ( current_user_can( 'manage_options' ) && is_array( $saved_value ) ? $saved_value : array() );
        echo '<select name="' . $field_id . '[]" id="' . $field_id . '" class="fupi_select2 fupi_user_search ' . $el_class . '" multiple="multiple" data-placeholder_text="' . esc_html__( 'Search users...', 'full-picture-analytics-cookie-notice' ) . '">';
        foreach ( $selected_users as $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user ) {
                echo '<option value="' . $user_id . '" selected>' . esc_html( $user->user_login ) . ' (' . esc_html( $user->user_email ) . ')</option>';
            }
        }
        echo '</select>';
        break;
    // =======
    case 'page_search':
        wp_enqueue_style( 'fupi-select2-css' );
        wp_enqueue_script( 'fupi-select2-js' );
        $selected_pages = ( current_user_can( 'manage_options' ) && is_array( $saved_value ) ? $saved_value : array() );
        echo '<select name="' . $field_id . '[]" id="' . $field_id . '" class="fupi_select2 fupi_page_search ' . $el_class . '" multiple="multiple" data-placeholder_text="' . esc_html__( 'Search pages...', 'full-picture-analytics-cookie-notice' ) . '">';
        foreach ( $selected_pages as $page_id ) {
            $page = get_post( $page_id );
            if ( !empty( $page ) ) {
                echo '<option value="' . $page_id . '" selected>' . esc_html( $page->post_title ) . ' (' . esc_html( $page->post_status ) . ')</option>';
            }
        }
        echo '</select>';
        break;
    // =======
    case 'multi checkbox':
        if ( !empty( $recipe['options'] ) && is_array( $recipe['options'] ) ) {
            foreach ( $recipe['options'] as $key => $label ) {
                $checked = false;
                if ( is_array( $saved_value ) ) {
                    if ( in_array( $key, $saved_value ) ) {
                        $checked = true;
                    }
                } else {
                    if ( $saved_value == $key ) {
                        $checked = true;
                    }
                }
                $option_data_target = '';
                if ( !empty( $recipe['options_data_targets'] ) && !empty( $recipe['options_data_targets'][$key] ) ) {
                    $option_data_target = $recipe['options_data_targets'][$key];
                }
                printf(
                    '<label><input type="checkbox" name="%1$s[]" value="%2$s" id="%1$s_%2$s" %4$s class="%5$s" data-target="%6$s">%3$s</label><div class="fupi_spacer"></div>',
                    $field_id,
                    $key,
                    $label,
                    checked( $checked, true, false ),
                    $el_class,
                    $option_data_target
                );
            }
            break;
        } else {
            break;
        }
        break;
    // =======
    case 'roles multi checkbox':
        global $wp_roles;
        $roles = $wp_roles->roles;
        foreach ( $roles as $key => $data_arr ) {
            if ( $key == 'administrator' ) {
                continue;
            }
            $checked = false;
            if ( is_array( $saved_value ) ) {
                if ( in_array( $key, $saved_value ) ) {
                    $checked = true;
                }
            } else {
                if ( $saved_value == $key ) {
                    $checked = true;
                }
            }
            printf(
                '<label><input type="checkbox" name="%1$s[]" value="%2$s" id="%1$s_%2$s" class="%5$s" %4$s>%3$s</label><div class="fupi_spacer"></div>',
                $field_id,
                $key,
                $data_arr['name'],
                checked( $checked, true, false ),
                $el_class
            );
        }
        break;
    // =======
    case 'taxonomies multi checkbox':
        $taxonomies = get_taxonomies();
        // $fupi_main = get_option('fupi_main');
        foreach ( $taxonomies as $tax_slug ) {
            if ( $tax_slug == 'fupi_page_labels' || $tax_slug == 'category' || $tax_slug == 'post_tag' || $tax_slug == 'post_format' ) {
                continue;
            }
            $checked = false;
            if ( is_array( $saved_value ) ) {
                if ( in_array( $tax_slug, $saved_value ) ) {
                    $checked = true;
                }
            } else {
                if ( $saved_value == $tax_slug ) {
                    $checked = true;
                }
            }
            printf(
                '<label><input type="checkbox" name="%1$s[]" value="%2$s" id="%1$s_%2$s" %3$s class="%4$s">%2$s</label><div class="fupi_spacer"></div>',
                $field_id,
                $tax_slug,
                checked( $checked, true, false ),
                $el_class
            );
        }
        break;
    // =======
    case 'woo_order_statuses':
        $statuses = wc_get_order_statuses();
        wp_enqueue_style( 'fupi-select2-css' );
        wp_enqueue_script( 'fupi-select2-js' );
        $output = '<select name="' . $field_id . '[]" class="fupi_select2 fupi_select2_keep_always_enabled ' . $el_class . '" multiple="multiple">';
        foreach ( $statuses as $status_slug => $status_name ) {
            if ( $status_slug == 'wc-pending' ) {
                continue;
            }
            // Get html that marks option as selected
            $selected_html = ( in_array( $status_slug, $saved_value ) ? 'selected="selected"' : '' );
            $output .= '<option value="' . $status_slug . '" ' . $selected_html . '>' . $status_name . '</option>';
        }
        $output .= '</select>';
        echo $output;
        break;
    // =======
    case 'textarea':
        if ( !empty( $recipe['format'] ) ) {
            if ( $recipe['format'] == 'htmlentities' ) {
                $val = esc_textarea( html_entity_decode( $saved_value, ENT_QUOTES ) );
            } else {
                $val = esc_textarea( $saved_value );
            }
        } else {
            $val = esc_textarea( $saved_value );
        }
        printf(
            '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50" class="%4$s" data-target="%5$s" %6$s>%3$s</textarea>',
            $field_id,
            $placeholder,
            $val,
            $el_class,
            $el_data_target,
            $data_format
        );
        break;
    // =======
    case 'button':
        $button_text = ( !empty( $recipe['button_text'] ) ? esc_attr( $recipe['button_text'] ) : '' );
        $icon = ( !empty( $recipe['icon'] ) ? '<span class="' . esc_attr( $recipe['icon'] ) . '"></span> ' : '' );
        $href = ( !empty( $recipe['href'] ) ? esc_attr( $recipe['href'] ) : '' );
        $target = ( !empty( $recipe['target'] ) ? 'target="' . esc_attr( $recipe['target'] ) . '"' : '' );
        $popup_target = ( !empty( $recipe['popup_target'] ) ? 'data-popup="' . esc_attr( $recipe['popup_target'] ) . '"' : '' );
        if ( !empty( $href ) ) {
            printf(
                '<a href="%1$s" %2$s class="%3$s">%4$s%5$s</a>',
                $href,
                $target,
                $el_class,
                $icon,
                $button_text
            );
        } else {
            printf(
                '<button type="button" class="%1$s" %4$s>%2$s%3$s</button>',
                $el_class,
                $icon,
                $button_text,
                $popup_target
            );
        }
        break;
    case 'upload_button':
        $button_text = ( !empty( $recipe['button_text'] ) ? esc_attr( $recipe['button_text'] ) : '' );
        $icon = ( !empty( $recipe['icon'] ) ? '<span class="' . esc_attr( $recipe['icon'] ) . '"></span> ' : '' );
        $accept_type = ( !empty( $recipe['accept_type'] ) ? esc_attr( $recipe['accept_type'] ) : '' );
        // allowed file formats. Examples: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        printf(
            '<button type="button" class="%1$s">%2$s%3$s</button>
		<input type="file" id="%4$s" accept="%5$s" style="display: none;">',
            $el_class,
            $icon,
            $button_text,
            $field_id,
            $accept_type
        );
        break;
    // =======
    case 'select':
        if ( !empty( $recipe['options'] ) && is_array( $recipe['options'] ) ) {
            $options_markup = '';
            foreach ( $recipe['options'] as $key => $label ) {
                $options_markup .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    $key,
                    selected( $saved_value, $key, false ),
                    $label
                );
            }
            printf(
                '<select name="%1$s" id="%1$s" class="%2$s" data-target="%3$s">%4$s</select>',
                $field_id,
                $el_class,
                $el_data_target,
                $options_markup
            );
        }
        break;
    // =======
    case 'page_select':
        $pages = get_pages();
        $options_markup = '<option value="" false>' . esc_attr__( 'Choose page', 'full-picture-analytics-cookie-notice' ) . '</option>';
        foreach ( $pages as $page ) {
            $options_markup .= sprintf(
                '<option value="%1$s" %2$s>%3$s</option>',
                $page->ID,
                selected( $saved_value, $page->ID, false ),
                $page->post_title
            );
        }
        printf(
            '<select name="%1$s" id="%1$s" class="%2$s" data-target="%3$s">%4$s</select>',
            $field_id,
            $el_class,
            $el_data_target,
            $options_markup
        );
        break;
    // =======
    case 'taxonomies select':
        $taxonomies = get_taxonomies();
        $options_markup = '<option value=\'\'>' . esc_html__( 'None', 'full-picture-analytics-cookie-notice' ) . '</option>';
        foreach ( $taxonomies as $tax_slug ) {
            if ( $tax_slug !== 'fupi_woo_brand' ) {
                $options_markup .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    $tax_slug,
                    selected( $saved_value, $tax_slug, false ),
                    $tax_slug
                );
            }
        }
        printf(
            '<select name="%1$s" id="%1$s" class="%2$s" data-target="%3$s">%4$s</select>',
            $field_id,
            $el_class,
            $el_data_target,
            $options_markup
        );
        break;
    // =======
    case 'atrig_select':
        $atrig_opts = get_option( 'fupi_atrig' );
        $default_option_text = ( !empty( $recipe['default_option_text'] ) ? esc_attr( $recipe['default_option_text'] ) : esc_attr__( 'Please select', 'full-picture-analytics-cookie-notice' ) );
        $options_markup = '<option value=\'\'>' . $default_option_text . '</option>';
        $selected_trigger_active = false;
        if ( !empty( $atrig_opts ) ) {
            // add advanced triggers options
            if ( !empty( $atrig_opts['triggers'] ) && count( $atrig_opts['triggers'] ) > 0 ) {
                foreach ( $atrig_opts['triggers'] as $trigger ) {
                    $selected = selected( $saved_value, $trigger['id'], false );
                    if ( !empty( $selected ) ) {
                        $selected_trigger_active = true;
                    }
                    $options_markup .= sprintf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        $trigger['id'],
                        $selected,
                        $trigger['name']
                    );
                }
            }
            // Add "Reached Lead Score X" options
            if ( !empty( $atrig_opts['lead_scoring_levels'] ) ) {
                $trimmed_string = trim( $atrig_opts['lead_scoring_levels'] );
                $split_array = explode( ',', $trimmed_string );
                $score_levels = array_map( 'trim', $split_array );
                if ( count( $score_levels ) > 0 ) {
                    foreach ( $score_levels as $level ) {
                        $selected = selected( $saved_value, $trigger['id'], false );
                        if ( !empty( $selected ) ) {
                            $selected_trigger_active = true;
                        }
                        $level_txt = esc_attr__( 'Reached lead score ', 'full-picture-analytics-cookie-notice' ) . $level;
                        $options_markup .= sprintf(
                            '<option value="fp_leadscore_%1$s" %2$s>%3$s</option>',
                            $level,
                            selected( $saved_value, 'fp_leadscore_' . $level, false ),
                            $level_txt
                        );
                    }
                }
            }
        }
        $trigger_status = false;
        if ( empty( $saved_value ) ) {
            $trigger_status = 'not chosen';
        } else {
            if ( $selected_trigger_active ) {
                $trigger_status = 'set';
            } else {
                $trigger_status = 'removed';
            }
        }
        printf(
            '<select name="%1$s" id="%1$s" class="%2$s" data-target="%3$s" data-trigger="%5$s">%4$s</select>',
            $field_id,
            $el_class,
            $el_data_target,
            $options_markup,
            $trigger_status
        );
        break;
    // =======
    case 'custom_meta_select':
        $trackmeta_opts = get_option( 'fupi_trackmeta' );
        $options_markup = '<option value=\'\'>' . esc_html__( 'Please select', 'full-picture-analytics-cookie-notice' ) . '</option>';
        if ( !empty( $trackmeta_opts ) && !empty( $trackmeta_opts['custom_data_ids'] ) && count( $trackmeta_opts['custom_data_ids'] ) > 0 ) {
            foreach ( $trackmeta_opts['custom_data_ids'] as $trackmeta ) {
                // Name
                $name_type = '(Post meta)';
                if ( !empty( $trackmeta['meta'] ) ) {
                    if ( $trackmeta['meta'] == 'term' ) {
                        $name_type = '(Term meta)';
                    } else {
                        if ( $trackmeta['meta'] == 'user' ) {
                            $name_type = '(User meta)';
                        }
                    }
                }
                $name = ( empty( $trackmeta['name'] ) ? esc_attr( $trackmeta['id'] ) : esc_attr( $trackmeta['name'] ) );
                $name = $name_type . ' ' . $name;
                // Value
                $field_value = esc_attr( $trackmeta['id'] );
                if ( !empty( $trackmeta['meta'] ) ) {
                    if ( $trackmeta['meta'] == 'term' ) {
                        $field_value = 'term|' . $field_value;
                    } else {
                        if ( $trackmeta['meta'] == 'user' ) {
                            $field_value = 'user|' . $field_value;
                        }
                    }
                }
                // Option HTML
                $options_markup .= sprintf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    $field_value,
                    selected( $saved_value, $field_value, false ),
                    $name
                );
            }
        }
        printf(
            '<select name="%1$s" id="%1$s" class="%2$s" data-target="%3$s">%4$s</select>',
            $field_id,
            $el_class,
            $el_data_target,
            $options_markup
        );
        break;
    // =======
    case 'radio':
        if ( !empty( $recipe['options'] ) && is_array( $recipe['options'] ) ) {
            $options_markup = '';
            foreach ( $recipe['options'] as $key => $label ) {
                $checked = ( $saved_value == $key ? true : false );
                printf(
                    '<label><input type="radio" name="%1$s" value="%2$s" id="%1$s_%2$s" %4$s class="%5$s" data-target="%6$s">%3$s</label><div class="fupi_spacer"></div>',
                    $field_id,
                    $key,
                    $label,
                    checked( $checked, true, false ),
                    $el_class,
                    $el_data_target
                );
            }
            break;
        } else {
            break;
        }
        break;
    // =======
    case 'hidden':
        printf( '<input type="hidden" name="%1$s" id="%1$s" value="%2$s"/>', $field_id, $saved_value );
        break;
    // =======
    case 'empty':
        break;
}
// REQUIRED TEXT
if ( isset( $recipe['required'] ) ) {
    $req_txt = esc_html__( 'Required', 'full-picture-analytics-cookie-notice' );
    echo '<span class="fupi_req_txt">' . $req_txt . '</span>';
}
// TOOLTIP
if ( isset( $recipe['tooltip'] ) ) {
    printf( '<div class="fupi_tooltip"><span class="dashicons dashicons-editor-help"></span><span class="fupi_tooltiptext">%s</span></div>', $recipe['tooltip'] );
}
// TEXT AFTER FIELD
if ( isset( $recipe['after field'] ) ) {
    printf( '<span class="fupi_after_field"> %s</span>', $recipe['after field'] );
}
// POPUP
if ( isset( $recipe['popup'] ) ) {
    $clean_id = str_replace( array('[', ']'), '_', $field_id );
    printf( '<div id="' . $clean_id . '_popup" class="fupi_popup_content">%s</div>', $recipe['popup'] );
}
// POPUP 2 - with a warning
if ( isset( $recipe['popup2'] ) ) {
    $clean_id = str_replace( array('[', ']'), '_', $field_id );
    printf( '<div id="' . $clean_id . '_popup"  class="fupi_popup_content fupi_popup2">%s</div>', $recipe['popup2'] );
}
// POPUP 2 - with alert
if ( isset( $recipe['popup3'] ) ) {
    $clean_id = str_replace( array('[', ']'), '_', $field_id );
    printf( '<div id="' . $clean_id . '_popup"  class="fupi_popup_content fupi_popup3">%s</div>', $recipe['popup3'] );
}
// POPUP ID
if ( isset( $recipe['popup_id'] ) ) {
    printf( '<span class="fupi_create_popup_link" data-popup_id="%1$s" aria-hidden="true"></span>', $recipe['popup_id'] );
}
// POPUP2 ID
if ( isset( $recipe['popup2_id'] ) ) {
    printf( '<span class="fupi_create_popup_link fupi_popup2" data-popup_id="%1$s" aria-hidden="true"></span>', $recipe['popup2_id'] );
}
// POPUP3 ID
if ( isset( $recipe['popup3_id'] ) ) {
    printf( '<span class="fupi_create_popup_link fupi_popup3" data-popup_id="%1$s" aria-hidden="true"></span>', $recipe['popup3_id'] );
}
// TEXT BELOW FIELD
if ( isset( $recipe['under field'] ) ) {
    printf( '<div class="fupi_under_field">%s</div>', $recipe['under field'] );
}
// "MUST HAVE" INFO
echo $must_have_html;