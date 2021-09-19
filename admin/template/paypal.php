<?php
/**
 * Meta box area output file
 *
 * @package Newproject.WordPress.plugin
 */

namespace NikolayS93\Payments;

use NikolayS93\WPAdminForm\Form as Form;

$data = array(
	array(
		'id'    => 'PAYPAL_CLIENT_ID',
		'type'  => 'text',
		'label' => 'Client ID',
		'desc'  => 'Идентификатор магазина',
	),
	array(
		'id'    => 'PAYPAL_CLIENT_SECRET',
		'type'  => 'text',
		'label' => 'Secret Key',
		'desc'  => 'Секретный ключ аутентификации магазина',
	),
	array(
		'id'    => 'PAYPAL_CLIENT_ID_TEST',
		'type'  => 'text',
		'label' => 'Client ID (test)',
		'desc'  => 'Тестовый идентификатор магазина',
	),
	array(
		'id'    => 'PAYPAL_CLIENT_SECRET_TEST',
		'type'  => 'text',
		'label' => 'Secret Key (test)',
		'desc'  => 'Тестовый секретный ключ аутентификации магазина',
	),
);

$form = new Form( $data, $is_table = true );
$form->display();
