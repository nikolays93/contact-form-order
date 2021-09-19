<?php

use NikolayS93\ContactFormOrders\Order;
use NikolayS93\ContactFormOrders\Payment_Factory;

add_action( 'wpcf7_init', 'wpcf0_add_form_tag_payment_type', 10, 0 );

function wpcf0_add_form_tag_payment_type() {
	wpcf7_add_form_tag(
		array( 'payment_type' ),
		'wpcf0_payment_type_form_tag_handler',
		[]
	);
}

function wpcf0_payment_type_form_tag_handler( $tag ) {
	$tag->name = 'payment_type';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-select' );

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

	$value = (string) reset( $tag->values );
	$value = wpcf7_get_hangover( $tag->name, $tag->get_default_option( $value ) );

	$atts['name'] = $tag->name;

	$payment_methods = Payment_Factory::getPaymentMethods();
	$options = array_combine(array_keys($payment_methods), array_map(function($payment_method) {
		return $payment_method::getPaymentLabel();
	}, $payment_methods));

	array_walk($options, function(&$option, $key) use ($value) {
		$option = sprintf('<option value="%s"%s>%s</option>',
			esc_attr($key),
			selected($key, $value),
			esc_html($option)
		);
	});

	ob_start();
	?>
	<script>
	document.addEventListener( 'wpcf7mailsent', function( event ) {
	    const response = event.detail.apiResponse;

        if ('mail_sent' === response.status && response.approveLink) {
            const redirectWindow = window.open(response.approveLink, '_blank');
            // Try this instead focus to avoid blocking
            redirectWindow.location;

            let timer = setInterval(function() {
                if (redirectWindow.closed) {
                    clearInterval(timer);
                    window.location.href = response.returnLink;
                }
            }, 1000);
        }
	}, false );
	</script>
	<?php

	return sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>' . ob_get_clean(),
		sanitize_html_class( $tag->name ),
		wpcf7_format_atts( $atts ),
		join('', $options),
		$validation_error
	);
}

add_filter( 'wpcf7_validate_payment_type', 'wpcf0_payment_type_validation_filter', 10, 2 );

function wpcf0_payment_type_validation_filter( $result, $tag ) {
	$tag->name = 'payment_type';

	$value = isset( $_POST[$tag->name] )
		? trim( wp_unslash( strtr( (string) $_POST[$tag->name], "\n", " " ) ) )
		: '';

	if ( $tag->is_required() and '' === $value ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}

	return $result;
}