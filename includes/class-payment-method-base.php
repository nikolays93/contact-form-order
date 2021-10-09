<?php

namespace NikolayS93\ContactFormOrders;

abstract class Payment_Method_Base {
	const TYPE = '';

	public function return_url(Order $order) {
		return home_url() . "/order/{$order->id}/return/";
	}

	public function cancel_url(Order $order) {
		return home_url() . "/order/{$order->id}/cancel/";
	}
}