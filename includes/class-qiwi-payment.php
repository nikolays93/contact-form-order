<?php

namespace NikolayS93\ContactFormOrders;

class Qiwi_Payment extends Payment_Method_Base implements Payment_Method
{
	const TYPE = 'qiwi';

	public static function getPaymentLabel(): string
	{
		return 'Qiwi';
	}

    public function request(Order $order): array
    {
        $billPayments = static::environment();
        $billId = $billPayments->generateId();

        $fields = [
            'amount' => $order->get_amount(),
            'currency' => 'RUB',
            'comment' => '',
            'expirationDateTime' => $billPayments->getLifetimeByDay(1),
        ];

        $response = $billPayments->createBill($billId, $fields);

        return [
            'code' => $response['billId'],
            'link' => $response['payUrl']
        ];
    }

    public function validateConfirm(array $requestBody): array
    {
        $payment = $requestBody['payment'];

    	if (empty($requestBody['payment']['billId']) || empty($requestBody['payment']['status']['value'])) {
		    throw new \Exception( 'Corrupted request body.' );
	    }

        return [
            'payment_code' => $requestBody['payment']['billId'],
            'confirm' => 'SUCCESS' === $requestBody['payment']['status']['value'],
        ];
    }

    static function get_token() {
        if (!$token = Option::get_instance()->get( 'QIWI_TOKEN' )) {
            throw new \Exception("QIWI_TOKEN is empty");
        }

        return $token;
    }

    static function environment() {
        /** @var \Qiwi\Api\BillPayments $billPayments */
        return new \Qiwi\Api\BillPayments( static::get_token() );
    }
}
