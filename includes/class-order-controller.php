<?php

class Order_Controller
{
    /**
     * @param  array  $vars Previous vars
     * @return array
     */
    public static function vars(array $vars) {
        $vars[] = 'order';
        $vars[] = 'payment';

        return $vars;
    }

    private static function get_order() {
        return new Order([
            'payment_type' => sanitize_text_field( $_REQUEST['payment_type'] ?? '' ),
            'name'    => sanitize_text_field( $_REQUEST['name'] ?? '' ),
            'email'   => sanitize_text_field( $_REQUEST['email'] ?? '' ),
            'phone'   => sanitize_text_field( $_REQUEST['phone'] ?? '' ),
            'amount'  => intval( $_REQUEST['amount'] ?? 0 ),
            'comment' => sanitize_textarea_field( $_REQUEST['comment'] ?? '' ),
        ]);
    }

    /**
     * POST by contact form 7
     */
    public static function payment_request()
    {
        $order = self::get_order();
        $payment = Payment_Factory::getPaymentMethod($order);
        $response = $payment->request();

        $order->payment_code = $response['code'];
        $order->save();

        wp_redirect($response['url']);
        exit;
    }

    /**
     * ANY /order/{$payment_type}/confirm/
     */
    public function payment_confirm(): void
    {
        $requestBody = json_decode( file_get_contents( 'php://input' ), true );
        $payment = Payment_Factory::getPaymentMethodByType($payment_type);

        try {
            $result = $payment->validateReturn($requestBody);

            if (true === $result['confirm']) {
                $order = Order::get($result['payment_code']);
                $order->complete();
                $order->save();
            }

        } catch (\Exception $e) {
        }
    }

    /**
     * GET /order/{$order->id}/
     */
    public function paymentResultPage()
    {
        global $wp, $wp_query;

        $current_url = home_url( $wp->request );

        preg_match( '#/order/([0-9]+)/#', $current_url, $matches );

        $order_id = intval($matches[1]);

        if ($order_id > 0) {
            echo 'All about ' . $order_id;
            return;
        }

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}
