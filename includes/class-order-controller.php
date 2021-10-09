<?php

namespace NikolayS93\ContactFormOrders;

class Order_Controller
{
    /**
     * POST by contact form 7
     */
	public static function payment_request( $contact_form, $result )
	{
		// class exists
		$cases = (array) apply_filters( 'wpcf0_submit_if', array( 'mail_sent' ) );

		if ( empty( $result['status'] ) || ! in_array( $result['status'], $cases ) ) {
			return;
		}

		$submission = \WPCF7_Submission::get_instance();

		if ( ! $submission || ! $posted_data = $submission->get_posted_data() ) {
			return;
		}

		if ( $submission->get_meta( 'do_not_store' ) ) {
			return;
		}

		$posted_data = $submission->get_posted_data();
		$response_order = [];

		if (isset($posted_data['order_amount'])) {
			$order = new Order(array_merge($posted_data, ['amount' => $posted_data['order_amount']]));

			try {
				$payment = Payment_Factory::getPaymentMethod($order);
				$requestResult = $payment->request($order);

				if (empty($requestResult['link'])) {
					throw new \Exception('Empty response url');
				}

				$order->payment_type = $payment::TYPE;
				$order->payment_code = $requestResult['code'];
				$order->save();

				$response_order['returnLink'] = $payment->return_url($order);
				$response_order['approveLink'] = $requestResult['link'];
				$response_order['message'] = 'Мы получили ваше сообщение. Сейчас вы будете перенаправленны на страницу оплаты.';
			} catch (\Exception $e) {
				$response_order['message'] = 'Мы получили ваше сообщение. Но сервис платежей сейчас не доступен. Попробуйте выбрать другой способ оплаты или свяжитесь с администратором.';
				$response_order['status'] = 'mail_sent_but_payment_request_fail';

				if (defined('WP_DEBUG_DISPLAY') && true === WP_DEBUG_DISPLAY) {
					$response_order['message'] = $e->getMessage();
				}
			}
		}

		add_filter('wpcf7_feedback_response', static function( $response ) use ( $response_order ) {
			return array_merge( $response, $response_order );
		});

		add_filter( 'flamingo_add_inbound', static function ( $args ) use ( $order ) {
			$args[ 'fields' ][ 'payment_code' ] = $order->payment_code;
			return $args;
		} );

		add_action('wpcf7_after_flamingo', static function ( $result ) use ( $order ) {
			update_post_meta( $result['flamingo_inbound_id'], 'payment_code', $order->payment_code );
		});
	}

    /**
     * ANY /payment/{payment_method}/confirm/
     */
    public static function payment_confirm(): void
    {
	    if ( ! $payment_method = get_query_var( 'payment_method' ) ) {
	    	return;
	    }

	    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

	    $allowed_payment_methods = Payment_Factory::getPaymentMethods();
	    if ( ! in_array($payment_method, array_keys($allowed_payment_methods)) ) {
		    header($protocol . ' 405 Method Not Allowed', true, 405);
		    error_log( 'not allowed payment method ' . $payment_method );
		    wp_die( 'На сайте произошла непредвиденная ошибка, повторите позже, или обратитесь к администратору' );
	    }

        $requestBody = json_decode( file_get_contents( 'php://input' ), true );
        $payment = Payment_Factory::getPaymentMethodByType($payment_method);

        try {
        	if (!is_array($requestBody)) {
        		throw new \Exception('Invalid request body');
        	}

            $result = $payment->validateConfirm($requestBody);

            if (true === $result['confirm']) {
                $order = Order::get($result['payment_code']);
                $order->complete();
            }
        } catch (\Exception $e) {
	        error_log( $e->getMessage() );
	        header( $protocol . ' 500 Internal Server Error', true, 500 );
	        wp_die( 'На сайте произошла непредвиденная ошибка, повторите позже, или обратитесь к администратору' );
        }

	    header( $protocol . ' 200 OK', true, 200 );
    }

    /**
     * GET /order/{order_id}/return/ or /order/{order_id}/cancel/
     */
    public static function paymentResultPage()
    {
	    global $wp_query;

	    if ( $order_id = get_query_var( 'order_id' ) ) {
	    	try {
			    $order = Order::get( $order_id );
		    } catch (\Exception $e) {
			    $wp_query->set_404();
			    status_header( 404 );
			    nocache_headers();
			    return;
		    }

		    if ($order->status === Order::STATUS_DONE) {
			    wp_redirect( Option::get_instance()->get( 'PAGE_SUCCESS' ) ?: '/success/' );
			    exit;
		    }

		    add_filter(
			    'template_include',
			    function ( $template ) {
				    return locate_template('page-payment-confirm.php') ?:
					    PLUGIN_DIR . 'page-payment-confirm.php';
			    },
			    99
		    );
	    }
    }
}
