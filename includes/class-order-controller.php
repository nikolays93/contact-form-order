<?php

namespace NikolayS93\ContactFormOrders;

class Order_Controller
{
    /**
     * @param  array  $vars Previous vars
     * @return array
     */
    public static function register_vars(array $vars) {
        $vars[] = 'order_id';
        $vars[] = 'payment_method';

        return $vars;
    }

    /**
     * POST by contact form 7
     */
    public static function payment_request($response)
    {
        if ('mail_sent' === $response['status']) {
        	$submission = \WPCF7_Submission::get_instance();
	        $posted_data = $submission->get_posted_data();

        	if (isset($posted_data['order_amount'])) {
		        $order = new Order($posted_data);
		        $order->set_amount($posted_data['order_amount']);

		        try {
			        $payment = Payment_Factory::getPaymentMethod($order);
			        $requestResult = $payment->request($order);

			        if (empty($requestResult['link'])) {
			        	throw new \Exception('Empty response url');
			        }

			        $order->payment_code = $requestResult['code'];
			        $order->save();

			        $response['returnLink'] = $payment->return_url($order);
			        $response['approveLink'] = $requestResult['link'];
			        $response['message'] = 'Мы получили ваше сообщение. Сейчас вы будете перенаправленны на страницу оплаты.';
		        } catch (\Exception $e) {
			        $response['message'] = 'Мы получили ваше сообщение. Но сервис платежей сейчас не доступен. Попробуйте еще раз или свяжитесь с администратором.';
			        $response['status'] = 'mail_sent_but_payment_request_fail';

			        if (defined('WP_DEBUG_DISPLAY') && true === WP_DEBUG_DISPLAY) {
				        $response['message'] = $e->getMessage();
			        }
		        }
	        }
        }

        return $response;
    }

    /**
     * ANY /payment/{payment_method}/confirm/
     */
    public static function payment_confirm(): void
    {
	    if ( ! $payment_method = get_query_var( 'payment_method' ) ) {
	    	return;
	    }

	    $allowed_payment_methods = apply_filters('wpcf0_payment_methods', []);
	    if ( ! in_array($payment_method, array_keys($allowed_payment_methods)) ) {
		    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		    header($protocol . ' 405 Method Not Allowed');
		    die('You are not allowed this payment method.');
	    }

        $requestBody = json_decode( file_get_contents( 'php://input' ), true );
        $payment = Payment_Factory::getPaymentMethodByType($payment_method);

        try {
            $result = $payment->validateConfirm($requestBody);

            if (true === $result['confirm']) {
                $order = Order::get($result['payment_code']);
                $order->complete();
                $order->save();
            }

        } catch (\Exception $e) {
        }
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
				    return locate_template( array( 'page-payment.php' ) ) ?: $template;
			    },
			    99
		    );
	    }
    }
}
