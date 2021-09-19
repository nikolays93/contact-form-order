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
		'id'    => 'QIWI_TOKEN',
		'type'  => 'text',
		'label' => 'Token',
		'desc'  => 'Секретный ключ',
	),
);

$form = new Form( $data, $is_table = true );
$form->display();

?>
<i>Платежная система Qiwi не поддерживает запрос в тестовом режиме.</i>