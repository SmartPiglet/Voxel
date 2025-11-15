<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ui_Step_Field extends Base_Post_Field {
	use Traits\Ui_Field;

	protected $props = [
		'type' => 'ui-step',
		'label' => 'UI Step',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
		];
	}

	public static function is_repeatable(): bool {
		return false;
	}
}
