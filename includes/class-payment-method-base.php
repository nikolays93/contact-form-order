<?php

namespace NikolayS93\PluginName;

abstract class Payment_Method_Base {
	public function success_url($order) {
		return home_url() . "/order/{$order->id}/";
	}

	public function restore_url() {
		return home_url();
	}
}