<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ui_Image_Field extends Base_Post_Field {
	use Traits\Ui_Field;

	protected $props = [
		'type' => 'ui-image',
		'label' => 'UI Image',
		'image' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-6' ]),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'image' => [
				'type' => \Voxel\Utils\Form_Models\Media_Model::class,
				'label' => '',
				'classes' => 'x-col-12',
				'multiple' => false,
			],
			'css_class' => $this->get_css_class_model(),
		];
	}

	protected function frontend_props() {
		return [
			'url' => wp_get_attachment_image_url( $this->props['image'], 'medium_large' ),
			'alt' => get_post_meta( $this->props['image'], '_wp_attachment_image_alt', true ),
		];
	}
}
