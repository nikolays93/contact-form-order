<?php

use NikolayS93\ContactFormOrders\Order;

add_action( 'wpcf7_init', 'wpcf0_add_form_tag_order_status', 10, 0 );

function wpcf0_add_form_tag_order_status(): void {
	wpcf7_add_form_tag(
		array( 'order_status' ),
		'wpcf0_order_form_tag_handler',
		[]
	);
}

function wpcf0_order_form_tag_handler( WPCF7_FormTag $tag ): string {
	$value = '';

	if ('order_status' === $tag->type) {
		$tag->name = 'order_status';
		$value = Order::STATUS_INIT;
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = '-1';

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid'] = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference($tag->name);
	} else {
		$atts['aria-invalid'] = 'false';
	}

	$atts['value'] = $value;
	$atts['type'] = 'hidden';
	$atts['name'] = $tag->name;

	return sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), wpcf7_format_atts( $atts ), $validation_error
	);
}

add_filter( 'wpcf7_validate_order_status', 'wpcf0_order_status_validation_filter', 10, 2 );

function wpcf0_sanitize_request_value($name) {
	return isset( $_POST[$name] )
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';
}

function wpcf0_order_status_validation_filter( $result, WPCF7_FormTag $tag ) {
	$tag->name = $tag->basetype;
	$value = wpcf0_sanitize_request_value($tag->name);

	if ('order_status' === $tag->type && Order::STATUS_INIT !== $value ) {
		$result->invalidate( $tag, 'Order status error' );
	}

	return $result;
}