<?php
/**
 * Metabox area output file
 *
 * @package Newproject.WordPress.plugin
 */

namespace NikolayS93\Payments;

use NikolayS93\WPAdminForm\Form as Form;

$form = new Form(
	[
		[
			'id'    => 'TEST',
			'type'  => 'checkbox',
			'label' => 'Тестовый режим',
		]
	],
	$is_table = true
);
$form->display();

submit_button( 'Сохранить', 'primary right', 'save_changes' );
echo '<div class="clear"></div>';
