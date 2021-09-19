<?php

// `payment_id` varchar(100) NULL UNIQUE,
// `payment_type` tinyint unsigned NOT NULL,

use NikolayS93\ContactFormOrders\Order;

add_action( 'wpcf7_init', 'wpcf0_add_form_tag_order_amount', 10, 0 );

function wpcf0_add_form_tag_order_amount(): void {
	wpcf7_add_form_tag(
		array( 'order_amount' ),
		'wpcf0_order_amount_form_tag_handler',
		[]
	);
}

function wpcf0_order_amount_form_tag_handler( WPCF7_FormTag $tag ): string {
	$tag->name = 'order_amount';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['autocomplete'] = $tag->get_option( 'autocomplete', '[-0-9a-zA-Z]+', true );

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid'] = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	} else {
		$atts['aria-invalid'] = 'false';
	}

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = wpcf7_get_hangover( $tag->name, $tag->get_default_option( $value ) );

	$atts['value'] = $value;
	$atts['type'] = 'text';
	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	return sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error
	);
}

add_filter( 'wpcf7_validate_order_amount', 'wpcf0_order_amount_validation_filter', 10, 2 );

function wpcf0_order_amount_validation_filter( WPCF7_Validation $result, WPCF7_FormTag $tag ) {
	$tag->name = 'order_amount';

	$value = isset( $_POST[$tag->name] )
		? trim( wp_unslash( strtr( (string) $_POST[$tag->name], "\n", " " ) ) )
		: '';

	if ( Order::sanitize_amount($value) <= 0 ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}

	return $result;
}