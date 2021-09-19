<?php

namespace NikolayS93\ContactFormOrders;

abstract class Payment_Method_Base {
	public function return_url($order) {
		return home_url() . "/order/{$order->id}/return/";
	}

	public function cancel_url($order) {
		return home_url() . "/order/{$order->id}/cancel/";
	}
}