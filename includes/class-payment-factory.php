<?php

class Payment_Factory
{
    public static function getPaymentMethodByType(string $type): PaymentMethod
    {
        switch ($type) {
            case "cc":
                return new CreditCardPayment();
            case "paypal":
                return new PayPalPayment();
            default:
                throw new \Exception("Unknown Payment Method");
        }
    }

    /**
     * @param  array  $args [description]
     * @return [type]       [description]
     */
    public static function getPaymentMethod(Order $order): PaymentMethod
    {
        return self::getPaymentMethodByType($order->type);
    }
}
