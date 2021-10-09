<?php

namespace NikolayS93\ContactFormOrders;

class Payment_Factory
{
	public static function getPaymentMethods(): array
	{
		// @todo check payment methods instance.
		return apply_filters( 'wpcf0_payment_methods', [] );
	}

    public static function getPaymentMethodByType(string $type): Payment_Method
    {
    	$payment_methods = Payment_Factory::getPaymentMethods();

    	if (empty($payment_methods[$type]) ) {
		    throw new \Exception("Unknown Payment Method");
	    }

    	return new $payment_methods[$type];
    }

    /**
     * @param  array  $args [description]
     * @return [type]       [description]
     */
    public static function getPaymentMethod(Order $order): Payment_Method
    {
        return self::getPaymentMethodByType($order->payment_type);
    }
}
