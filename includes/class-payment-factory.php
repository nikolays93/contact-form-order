<?php

namespace NikolayS93\PluginName;

class Payment_Factory
{
    public static function getPaymentMethodByType(string $type): Payment_Method
    {
        switch ($type) {
            case "cc":
                return new CreditCardPayment();
            case "paypal":
                return new Paypal_Payment();
            default:
                throw new \Exception("Unknown Payment Method");
        }
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
