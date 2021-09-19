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
		'id'    => 'SHOP_ID',
		'type'  => 'text',
		'label' => 'Shop ID',
		'desc'  => 'Идентификатор магазина',
	),
	array(
		'id'    => 'SECRET_KEY',
		'type'  => 'text',
		'label' => 'Secret Key',
		'desc'  => 'Секретный ключ аутентификации магазина',
	),
	array(
		'id'    => 'SHOP_ID_TEST',
		'type'  => 'text',
		'label' => 'Shop ID (test)',
		'desc'  => 'Тестовый идентификатор магазина',
	),
	array(
		'id'    => 'SECRET_KEY_TEST',
		'type'  => 'text',
		'label' => 'Secret Key (test)',
		'desc'  => 'Тестовый секретный ключ аутентификации магазина',
	),
);

$form = new Form( $data, $is_table = true );
$form->display();
