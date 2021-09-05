<?php

abstract class Payment_Method_Base {
	public function success_url($order) {
		return home_url() . "/order/{$order->id}/payment/{$order->payment_type}/";
	}

	public function restore_url() {
		return home_url();
	}
}