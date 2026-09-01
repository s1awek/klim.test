<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Compiles a structured filter rule list into the same XPath predicate and
 * `filters_output` markup that wp-all-import-pro's native Step-2 filter
 * builder produces client-side (static/js/admin.js: xpath_builder() and the
 * #pmxi_add_rule handler). Kept here so both MCP abilities and the LLM
 * autoconfigure flow share one implementation.
 *
 * Rule  = [ 'element' => string, 'operator' => string, 'value' => string|null, 'condition' => 'and'|'or'|null ]
 * Group = [ 'group' => Rule[]|Group[], 'condition' => 'and'|'or'|null ]
 */
class WPAI_Bridge_Filter_Compiler {

	private static $comparison_operators = array(
		'equals'    => '=',
		'not_equals' => '!=',
		'greater'   => '>',
		'equals_or_greater' => '>=',
		'less'      => '<',
		'equals_or_less' => '<=',
	);

	private static $rule_labels = array(
		'equals'            => 'equals',
		'not_equals'        => 'not equals',
		'greater'           => 'greater than',
		'equals_or_greater' => 'equals or greater than',
		'less'              => 'less than',
		'equals_or_less'    => 'equals or less than',
		'contains'          => 'contains',
		'not_contains'      => 'not contains',
		'is_empty'          => 'is empty',
		'is_not_empty'      => 'is not empty',
	);

	/**
	 * @param array $rules
	 * @return array { predicate: string, filters_output: string } | { error: string, invalid_operators: string[] }
	 */
	public static function compile( array $rules ) {
		if ( empty( $rules ) ) {
			return array(
				'predicate'      => '',
				'filters_output' => '',
			);
		}

		// An unknown operator must never compile to an empty predicate -- that would
		// silently drop the filter and import everything (fail-open). Reject it up front.
		$invalid = self::collect_invalid_operators( $rules );
		if ( ! empty( $invalid ) ) {
			$invalid = array_values( array_unique( $invalid ) );
			return array(
				'error'             => sprintf(
					/* translators: 1: invalid operator(s), 2: supported operators */
					__( 'Unknown filter operator(s): %1$s. Supported operators are: %2$s.', 'wpai-ai-bridge-plugin' ),
					implode( ', ', $invalid ),
					implode( ', ', array_keys( self::$rule_labels ) )
				),
				'invalid_operators' => $invalid,
			);
		}

		return array(
			'predicate'      => self::compile_predicate( $rules, false ),
			'filters_output' => self::compile_filters_output( $rules ),
		);
	}

	/**
	 * Recursively gather any operator on a leaf rule that the compiler does not
	 * recognize. Group items (they carry a nested `group`) have no operator.
	 */
	private static function collect_invalid_operators( array $items ) {
		$invalid = array();

		foreach ( $items as $item ) {
			if ( isset( $item['group'] ) && is_array( $item['group'] ) ) {
				$invalid = array_merge( $invalid, self::collect_invalid_operators( $item['group'] ) );
				continue;
			}

			// A leaf rule with no element is a no-op fragment (dropped intentionally),
			// so it needs no operator; only validate rules that actually target an element.
			$element = isset( $item['element'] ) ? (string) $item['element'] : '';
			if ( '' === $element ) {
				continue;
			}

			$operator = isset( $item['operator'] ) ? (string) $item['operator'] : '';
			if ( ! isset( self::$rule_labels[ $operator ] ) ) {
				$invalid[] = '' === $operator ? '(empty)' : $operator;
			}
		}

		return $invalid;
	}

	/**
	 * XPath 1.0 has no in-literal escape character, so a value containing
	 * both quote characters must be split into a concat() of pieces that
	 * each only ever use the delimiter they don't contain.
	 */
	public static function xpath_literal( $value ) {
		$value = (string) $value;

		$has_double = false !== strpos( $value, '"' );
		$has_single = false !== strpos( $value, "'" );

		if ( ! $has_double ) {
			return '"' . $value . '"';
		}

		if ( ! $has_single ) {
			return "'" . $value . "'";
		}

		// Both quote characters present: concat('a', '"', 'b', ...) using
		// single-quoted pieces for text and double-quoted pieces for the
		// literal single quotes they hold.
		$pieces = array();
		$parts  = explode( "'", $value );

		foreach ( $parts as $i => $part ) {
			if ( '' !== $part ) {
				$pieces[] = "'" . $part . "'";
			}
			if ( $i < count( $parts ) - 1 ) {
				$pieces[] = '"\'"';
			}
		}

		if ( empty( $pieces ) ) {
			$pieces[] = "''";
		}

		return 'concat(' . implode( ', ', $pieces ) . ')';
	}

	private static function compile_predicate( array $items, $wrap_in_parens ) {
		$fragments = array();

		foreach ( $items as $item ) {
			$fragment = isset( $item['group'] ) && is_array( $item['group'] )
				? self::compile_predicate( $item['group'], count( $item['group'] ) > 1 )
				: self::compile_rule_fragment( $item );

			if ( '' === $fragment ) {
				continue;
			}

			$condition = isset( $item['condition'] ) ? $item['condition'] : null;

			if ( ! empty( $fragments ) && ( 'and' === $condition || 'or' === $condition ) ) {
				$fragments[] = ' ' . $condition . ' ' . $fragment;
			} else {
				$fragments[] = $fragment;
			}
		}

		$predicate = implode( '', $fragments );

		if ( $wrap_in_parens && '' !== $predicate ) {
			$predicate = '(' . $predicate . ')';
		}

		return $predicate;
	}

	private static function compile_rule_fragment( array $rule ) {
		$element  = isset( $rule['element'] ) ? $rule['element'] : '';
		$operator = isset( $rule['operator'] ) ? $rule['operator'] : '';
		$value    = isset( $rule['value'] ) ? $rule['value'] : null;

		if ( '' === $element ) {
			return '';
		}

		switch ( $operator ) {
			case 'equals':
				return $element . ' = ' . self::xpath_literal( $value );
			case 'not_equals':
				return $element . ' != ' . self::xpath_literal( $value );
			case 'greater':
				return $element . ' > ' . $value;
			case 'equals_or_greater':
				return $element . ' >= ' . $value;
			case 'less':
				return $element . ' < ' . $value;
			case 'equals_or_less':
				return $element . ' <= ' . $value;
			case 'contains':
				return 'contains(' . $element . ',' . self::xpath_literal( $value ) . ')';
			case 'not_contains':
				return 'not(contains(' . $element . ',' . self::xpath_literal( $value ) . '))';
			case 'is_empty':
				return 'not(string(' . $element . '))';
			case 'is_not_empty':
				return 'string(' . $element . ')';
			default:
				return '';
		}
	}

	/**
	 * Mirrors #pmxi_add_rule's generated <li> markup for each rule, and
	 * nests groups the way the nestedSortable / xpath_builder pairing
	 * expects: an outer <li> whose <div class="drag-element"> carries the
	 * (empty) rule inputs, followed by a sibling <ol class="filtering_rules">
	 * holding the group's own <li> items. (This nested-<ol> shape is inferred
	 * from xpath_builder()'s `$(this).children('ol')` lookup; admin.js has no
	 * "add group" button of its own to mirror directly — groups are built by
	 * dragging rules into each other.)
	 */
	private static function compile_filters_output( array $items ) {
		$html = '';

		foreach ( $items as $item ) {
			$html .= self::render_item( $item );
		}

		return $html;
	}

	private static function render_item( array $item ) {
		$condition = isset( $item['condition'] ) ? $item['condition'] : 'and';
		if ( 'and' !== $condition && 'or' !== $condition ) {
			$condition = 'and';
		}

		if ( isset( $item['group'] ) && is_array( $item['group'] ) ) {
			$inner = self::compile_filters_output( $item['group'] );

			$html = '<li><div class="drag-element">';
			$html .= '<input type="hidden" value="" class="pmxi_xml_element"/>';
			$html .= '<input type="hidden" value="" class="pmxi_rule"/>';
			$html .= '<input type="hidden" value="" class="pmxi_value"/>';
			$html .= self::render_condition_span( $condition );
			$html .= '</div>';
			$html .= '<ol class="filtering_rules">' . $inner . '</ol>';
			$html .= '</li>';

			return $html;
		}

		$element = isset( $item['element'] ) ? (string) $item['element'] : '';
		$operator = isset( $item['operator'] ) ? (string) $item['operator'] : '';
		$value    = isset( $item['value'] ) ? (string) $item['value'] : '';
		$label    = isset( self::$rule_labels[ $operator ] ) ? self::$rule_labels[ $operator ] : $operator;

		$html  = '<li><div class="drag-element">';
		$html .= '<input type="hidden" value="' . esc_attr( $element ) . '" class="pmxi_xml_element"/>';
		$html .= '<input type="hidden" value="' . esc_attr( $operator ) . '" class="pmxi_rule"/>';
		$html .= '<input type="hidden" value="' . esc_attr( $value ) . '" class="pmxi_value"/>';
		$html .= '<span class="rule_element">' . esc_html( $element ) . '</span>';
		$html .= '<span class="rule_as_is">' . esc_html( $label ) . '</span>';
		$html .= '<span class="rule_condition_value">"' . esc_html( $value ) . '"</span>';
		$html .= self::render_condition_span( $condition );
		$html .= '</div></li>';

		return $html;
	}

	private static function render_condition_span( $checked_condition ) {
		$rel = wp_generate_uuid4();

		$and_checked = 'and' === $checked_condition ? ' checked="checked"' : '';
		$or_checked  = 'or' === $checked_condition ? ' checked="checked"' : '';

		$html  = '<span class="condition"> ';
		$html .= '<label for="rule_and_' . $rel . '">AND</label>';
		$html .= '<input id="rule_and_' . $rel . '" type="radio" value="and" name="rule_' . $rel . '"' . $and_checked . ' class="rule_condition"/>';
		$html .= '<label for="rule_or_' . $rel . '">OR</label>';
		$html .= '<input id="rule_or_' . $rel . '" type="radio" value="or" name="rule_' . $rel . '"' . $or_checked . ' class="rule_condition"/>';
		$html .= ' </span>';

		return $html;
	}
}
