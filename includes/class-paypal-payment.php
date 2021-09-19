<?php

namespace NikolayS93\ContactFormOrders;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class Paypal_Payment extends Payment_Method_Base implements Payment_Method
{
	public static function getPaymentLabel(): string
	{
		return 'PayPal';
	}

    public function request(Order $order): array
    {
        $request = new OrdersCreateRequest();
        $request->prefer( 'return=representation' );
        $request->body = array(
            'intent'              => 'CAPTURE',
            'purchase_units'      => array(
                array(
                    'reference_id' => $order->id,
                    'amount'       => array(
                        'value'         => $order->get_amount(),
                        'currency_code' => 'RUB',
                    ),
                ),
            ),
            'application_context' => array(
                'cancel_url' => $this->cancel_url($order),
                'return_url' => $this->return_url($order),
            ),
        );

        // Call API with your client and get a response for your call
        $response =  static::client()->execute( $request );

        return [
            'code' => $response->result->id,
            'link' => static::getApproveLink($response),
        ];
    }

    public function validateConfirm(array $requestBody): array
    {
        return [
            'payment_code' => $requestBody['resource']['id'],
            'confirm' => 'CHECKOUT.ORDER.APPROVED' === $requestBody['event_type'],
        ];
    }

    private static function getApproveLink($response): string
    {
        foreach ( $response->result->links as $link ) {
            if ( 'approve' === $link->rel ) {
                return $link->href;
            }
        }

        return '';
    }

    private static function client() {
        return new PayPalHttpClient( self::environment() );
    }

    private static function environment() {
        $Option = Option::get_instance();

        if ( $Option->get( 'TEST' ) ) {
            return new SandboxEnvironment(
                $Option->get( 'PAYPAL_CLIENT_ID_TEST' ),
                $Option->get( 'PAYPAL_CLIENT_SECRET_TEST' )
            );
        }

        return new ProductionEnvironment(
            $Option->get( 'PAYPAL_CLIENT_ID' ),
            $Option->get( 'PAYPAL_CLIENT_SECRET' )
        );
    }
}
