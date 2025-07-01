<?php
/**
 * @package BaseLinker
 * @version 1.0.12
 */
/*
Plugin Name: BaseLinker
Plugin URI: http://connectors.baselinker.com/extensions/woocommerce/
Description: Rozszerzenie funkcjonalności API WooCommerce na potrzeby integracji z BaseLinkerem.
Author: BaseLinker
Version: 1.0.12
Author URI: http://baselinker.com/
*/

function baselinker_version($data)
{
	return '1.0.12';
}

function baselinker_prepare_shop_order($response, $post, $request)
{
	if (empty($response->data))
	{
		return $response;
	}
	     
	$q = array('post_parent' => $post->get_id(), 'post_type' => 'shipment');
	$shipments = get_children($q);

	if (count($shipments))
	{
		$shipment = array_shift($shipments);
		$response->data['shipment_meta'] = serialize(get_post_meta($shipment->ID));
	}

	return $response;
}

function baselinker_insert_shop_order($object, $request, $create)
{
	if (!$create)
	{
		return;
	}

	$locker_id = false;

	if (!empty($request['meta_data']))
	{
		foreach ($request['meta_data'] as $meta)
		{
			if ($meta['key'] == '_paczkomat_id' and !empty($meta['value']))
			{
				$locker_id = $meta['value'];
				break;
			}
		}
	}

	if (!$locker_id)
	{
		return;
	}

	wp_insert_post(array(
		'post_type' => 'shipment',
		'post_status' => 'fs-new',
		'post_parent' => $object->get_id(),
		'meta_input' => array(
			'_paczkomat_id' => $locker_id,
			'_integration' => 'paczkomaty',
		),
	));
}

function baselinker_query_by_order_number($args, $request)
{
	if (isset($request['order_number']) and (intval($request['order_number']) or $request['order_number'] == '%'))
	{
		$args['meta_key'] = isset($request['order_number_meta']) ? $request['order_number_meta'] : '_order_number';

		if ($request['order_number'] == '%')
		{
			$args['meta_value'] = '';
			$args['compare'] = 'LIKE';
		}
		else
		{
			$args['meta_value'] = intval($request['order_number']);
		}
	}

	return $args;
}

function baselinker_shipping_methods($data)
{
	$result = array();
	$api_methods = WC()->shipping->get_shipping_methods();
	$ext_methods = array();

	foreach ($api_methods as $id => $m)
	{
		if ($m->enabled == 'yes')
		{
			if ($id == 'flexible_shipping')
			{
				if ($rates = get_option('flexible_shipping_rates'))
				{
					foreach ($rates as $rid => $rate)
					{
						$ext_methods["$id:$rid"] = $m->method_title . ' - ' . $rate['title'];
					}
				}

			}

			$ext_methods[$id] = $m->method_title;
		}
	}


	return $ext_methods;
}

function baselinker_additional_order_statuses($data)
{
	if ($statuses = get_option('wcj_orders_custom_statuses_array'))
	{
		return $statuses;
	}

	return array();
}

function baselinker_product_list($data)
{
	$products = array();
	$page = 1;
	$cut_off_limit = 9999999;

	if (isset($data['limit']) and (int)$data['limit'] > 0)
	{
		$cut_off_limit = (int)$data['limit'];
	}

	if (isset($data['offset']))
	{
		$page = ceil(($data['offset']+1)/100);
		unset($data['offset']);
	}

	do {
		$args = array('status' => 'publish', 'limit' => 100, 'paginate' => true, 'page' => $page, 'orderby' => 'name', 'order' => 'ASC');

		if (isset($data['category_id']) and (int)$data['category_id'])
		{
			foreach (baselinker_category_list($data) as $cat)
			{
				if ($cat->term_id == $data['category_id'])
				{
					$args['category'] = $cat->slug;
					break;
				}
			}
		}

		if (isset($data['lang']))
		{
			$args['lang'] = $data['lang'];
		}

		if (isset($data['exclude']))
		{
			$args['exclude'] = $data['exclude'];
		}

		$res = wc_get_products($args);

		if (!is_object($res) and !isset($res->products))
		{
			break;
		}

		foreach ($res->products as $prod)
		{
			if (!$prod->get_parent_id())
			{
				$attributes = array();

				foreach ($prod->get_attributes() as $attr)
				{
					if ($attr->is_taxonomy())
					{
						$tobj = $attr->get_taxonomy_object();
						$attributes[] = array('name' => (is_object($tobj) and !empty($tobj->attribute_label)) ? $tobj->attribute_label : $attr->get_taxonomy(), 'options' => wc_get_product_terms($prod->get_id(), $attr->get_name(), array('fields' => 'names'))
);
					}
					else
					{
						$attributes[] = array('name' => $attr->get_name(), 'options' => $attr->get_options());
					}
				}

				
				$products[$prod->get_id()] = array(
					'name' => $prod->get_title(),
					'sku' => $prod->get_sku(),
					'price' => $prod->get_price(),
					'regular_price' => $prod->get_regular_price(),
					'quantity' => ($prod->get_stock_status() == 'instock') ? ($prod->get_manage_stock() ? (int)$prod->get_stock_quantity() : 1) : 0,
					'tax_class' => $prod->get_tax_class(),
					'meta_data' => $prod->get_meta_data(),
					'attributes' => $attributes,
				);
			}

			if (count($products) >= $cut_off_limit)
			{
				return $products;
			}
		}
	} while ($page++ < $res->max_num_pages);

	return $products;
}

function baselinker_prepare_product($response, $object, $request)
{
	$variations = array();
	static $atts;

	// tłumaczenie atrybtów (WPML nie robi tego automatycznie!)
	if (isset($response->data['lang']) and is_array($response->data['attributes']))
	{
		foreach ($response->data['attributes'] as $i => $a)
		{
			$response->data['attributes'][$i]['name'] = str_replace('taxonomy singular name: ', '', apply_filters('wpml_translate_single_string', 'taxonomy singular name: '.$a['name'], 'WordPress', 'taxonomy singular name: '.$a['name'], $response->data['lang']));
		}
	}

	if (isset($response->data['variations']) and !empty($response->data['variations']))
	{
		// tablica mapująca nazwę atrybutu do ID
		if (!isset($atts))
		{
			$atts = array();

			foreach (wc_get_attribute_taxonomies() as $att)
			{
				$atts['pa_' . $att->attribute_name] = $att->attribute_id;
			}
		}

		foreach ($response->data['variations'] as $variation_id)
		{
			if ($variation = new WC_Product_Variation($variation_id))
			{
				$vimage = wp_get_attachment_image_src(get_post_thumbnail_id($variation_id), 'shop_catalog');
				$vimage = isset($vimage[0]) ? $vimage[0] : '';
				$attributes = $variation->get_attributes();

				foreach ($attributes as $name => $val)
				{
					$name_orig = $name;

					if ($term = get_term_by('slug', $val, $name))
					{
						$val = $term->name;

						$s = get_taxonomies(array('name' => $term->taxonomy), 'objects');

						if (isset($s[$name]))
						{
							$name = $s[$name]->label;
						}
					}

					$attributes[] = array('id' => isset($atts[$name_orig]) ? $atts[$name_orig] : '-1', 'name' => $name, 'option' => $val);
					unset($attributes[$name_orig]);
				}

				// pełne meta dane, zawierające np EAN
				$vmeta = get_post_meta($variation_id);

				foreach ($vmeta as $meta_key => $meta_value)
				{
					$vmeta[] = array('key' => $meta_key, 'value' => implode('|', $meta_value));
					unset($vmeta[$meta_key]);
				}

				$variations[] = array(
					'id' => $variation_id,
					'sku' => $variation->get_sku(),
					'in_stock' => $variation->is_in_stock(),
					'stock_quantity' => (string)$variation->get_stock_quantity(),
					'price'  => (float)$variation->get_price(),
					'regular_price' => (float)$variation->get_regular_price(),
					'sale_price'  => (float)$variation->get_sale_price(),
					'description' => $variation->get_description(),
					'visible'  => (bool)$variation->is_visible(),
					'manage_stock'  => (bool)$variation->get_manage_stock(),
					'purchasable'  => (bool)$variation->is_purchasable(),
					'on_sale'  => (bool)$variation->is_on_sale(),
					'image' => array('id' => $vimage ? -1 : 0, 'src' => $vimage),
					'attributes' => $attributes,
					'weight' => (string)$variation->get_weight(),
					'meta_data' => $vmeta,
				);
			}
		}
	}
	
	$response->data['baselinker_variations'] = $variations;
	$response->data['baselinker_prod_version'] = '1.0.12';

	return $response;
}

function baselinker_category_list($data)
{
	$categories = get_terms( 'product_cat', array('hide_empty' => false));

	return $categories;
}

function baselinker_name_search($search, $wp_query)
{
	global $wpdb;

	if (!empty($wp_query->query_vars['search_terms']))
	{
		$qv = $wp_query->query_vars;
		$new_search = array();
		
		foreach ($qv['search_terms'] as $term)
		{
			$new_search[] = $wpdb->prepare("$wpdb->posts.post_title LIKE %s", '%' . $wpdb->esc_like($term) . '%');
		}

		$search = (empty($search) ? '' : "$search AND ") . implode(' AND ', $new_search);
	}

	return $search;
}

function baselinker_product_object_query($args, $request)
{
	// szukanie po nazwie
	$find = $request->get_param('search');

	if (isset($find) and !empty($find))
	{
		$args['s'] = esc_attr($find);
	}

	// szukanie po ean
	$find_ean = $request->get_param('search_ean');
	$find_ean_meta = $request->get_param('search_ean_meta');

	if (isset($find_ean) and isset($find_ean_meta) and !empty($find_ean_meta))
	{
		$args['meta_key'] = $find_ean_meta;
		$args['meta_value'] = $find_ean;
	}

	// ograniczenie wg stanu magazynowego
	$min_stock = $request->get_param('min_stock');
	$max_stock = $request->get_param('max_stock');

	if (isset($min_stock) or isset($max_stock))
	{
		$args['meta_query'][] = array(
			'key' => '_stock',
			'value' => array(isset($min_stock) ? (int)$min_stock : 0, isset($max_stock) ? (int)$max_stock : 99999999),
			'compare' => 'BETWEEN',
			'type' => 'numeric',
		);
		
	}

	// utwórz filtr
	add_filter('posts_search', 'baselinker_name_search', 100, 2);
	return $args;
}

add_action('rest_api_init', function() {
	register_rest_route('bl/v2', '/shipping_methods/', array('methods' => 'GET', 'callback' => 'baselinker_shipping_methods', 'permission_callback' => '__return_true'));
	register_rest_route('bl/v2', '/product_list/', array('methods' => 'GET', 'callback' => 'baselinker_product_list', 'permission_callback' => '__return_true'));
	register_rest_route('bl/v2', '/category_list/', array('methods' => 'GET', 'callback' => 'baselinker_category_list', 'permission_callback' => '__return_true'));
	register_rest_route('bl/v2', '/additional_order_statuses/', array('methods' => 'GET', 'callback' => 'baselinker_additional_order_statuses', 'permission_callback' => '__return_true'));
	register_rest_route('bl/v2', '/version/', array('methods' => 'GET', 'callback' => 'baselinker_version', 'permission_callback' => '__return_true'));
});

add_filter('woocommerce_rest_prepare_shop_order_object', 'baselinker_prepare_shop_order', 10, 3);
add_filter('woocommerce_rest_insert_shop_order_object', 'baselinker_insert_shop_order', 10, 3);
add_filter('woocommerce_rest_shop_order_object_query', 'baselinker_query_by_order_number', 10, 2);
add_filter('woocommerce_rest_prepare_product_object', 'baselinker_prepare_product', 20, 3);
add_filter('woocommerce_rest_product_object_query', 'baselinker_product_object_query', 10, 2);
?>
