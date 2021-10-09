<?php

namespace NikolayS93\ContactFormOrders;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\NotificationEventType;

class YooKassa_Payment extends Payment_Method_Base implements Payment_Method
{
	const TYPE = 'yookassa';

	public static function getPaymentLabel(): string
	{
		return 'YooKassa';
	}

    public function request(Order $order): array
    {
	    /**
	     * @url https://github.com/yoomoney/yookassa-sdk-php/blob/master/docs/examples/02-payments.md#запрос-на-создание-платежа
	     */
	    $client = static::environment();
	    /** @var \YooKassa\Request\Payments\CreatePaymentResponse $payment */
	    $payment = $client->createPayment(
		    array(
			    'amount'       => array(
				    'value'    => $order->get_amount(),
				    'currency' => 'RUB',
			    ),
			    'confirmation' => array(
				    'type'       => 'redirect',
				    'return_url' => $this->return_url($order),
			    ),
			    'capture'      => true,
			    'description'  => 'Благотвортиельный платеж',
		    ),
		    uniqid( '', true )
	    );

	    return [
		    'code' => $payment->getId(),
		    'link' => $payment->getConfirmation()->getConfirmationUrl(),
	    ];
    }

    public function validateConfirm(array $requestBody): array
    {
	    if ( empty( $requestBody['event'] ) ) {
		    throw new \Exception( 'Corrupted request body. Event is empty.' );
	    }

	    return [
		    'payment_code' => (new NotificationSucceeded( $requestBody ))->getObject()->getId(),
		    'confirm' => NotificationEventType::PAYMENT_SUCCEEDED === $requestBody['event'],
	    ];
    }

    private static function environment() {
	    $Option = Option::get_instance();

	    $client = new Client();
	    $client->setAuth(
		    $Option->get( 'on' === $Option->get( 'TEST' ) ? 'SHOP_ID_TEST' : 'SHOP_ID' ),
		    $Option->get( 'on' === $Option->get( 'TEST' ) ? 'SECRET_KEY_TEST' : 'SECRET_KEY' )
	    );

	    return $client;
    }
}
