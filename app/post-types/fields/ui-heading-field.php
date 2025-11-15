<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ui_Heading_Field extends Base_Post_Field {
	use Traits\Ui_Field;

	protected $props = [
		'type' => 'ui-heading',
		'label' => 'UI Heading',
		'description' => '',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'description' => $this->get_description_model(),
			'css_class' => $this->get_css_class_model(),
		];
	}
}
