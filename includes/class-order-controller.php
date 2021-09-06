<?php

namespace NikolayS93\PluginName;

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

    /**
     * POST by contact form 7
     */
    public static function payment_request($response)
    {
        if ('mail_sent' === $response['status']) {
            $posted_data = \WPCF7_Submission::get_instance()->get_posted_data();
            $order = new Order($posted_data);
            $payment = Payment_Factory::getPaymentMethod($order);

            $requestResult = $payment->request($order);

            $order->payment_code = $requestResult['code'];
            $order->save();

            $response['redirect'] = $requestResult['url'];
        }

        return $response;
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
