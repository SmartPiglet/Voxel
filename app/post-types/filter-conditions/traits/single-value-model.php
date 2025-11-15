<?php

namespace Voxel\Post_Types\Filter_Conditions\Traits;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Single_Value_Model {

	protected function props(): array {
		return [
			'value' => '',
		];
	}

	public function get_models(): array {
		return [
			'value' => [
				'type' => \Voxel\Utils\Form_Models\Text_Model::class,
				'label' => 'Value',
				'classes' => 'x-col-4',
			],
		];
	}
}
