<?php

namespace NikolayS93\ContactFormOrders;

interface Payment_Method
{
	public static function getPaymentLabel(): string;

    public function request(Order $order): array;

    public function validateConfirm(array $requestBody): array;

	public function return_url( Order $order );
}
