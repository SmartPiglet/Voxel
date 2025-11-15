<?php

namespace Voxel\Post_Types\Fields\Singular;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Title_Field extends \Voxel\Post_Types\Fields\Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'label' => 'Title',
		'type' => 'title',
		'key' => 'title',
		'placeholder' => '',
		'minlength' => null,
		'maxlength' => null,
		'required' => true,
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_key_model(),
			'minlength' => $this->get_model( 'minlength', [ 'classes' => 'x-col-6' ] ),
			'maxlength' => $this->get_model( 'maxlength', [ 'classes' => 'x-col-6' ] ),
			'description' => $this->get_description_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

	public function validate( $value ): void {
		$this->validate_minlength( $value );
		$this->validate_maxlength( $value );
	}

	public function update( $value ): void {
		// update_post_meta( $this->post->get_id(), $this->get_key(), $value );
	}

	public function get_value() {
		return $this->post->get_title();
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			return $this->render_default_value( $this->get_prop('default') );
		} else {
			return $this->get_value();
		}
	}

	protected function frontend_props() {
		return [
			'placeholder' => $this->get_model_value('placeholder'),
			'minlength' => is_numeric( $this->get_model_value('minlength') ) ? absint( $this->get_model_value('minlength') ) : null,
			'maxlength' => is_numeric( $this->get_model_value('maxlength') ) ? absint( $this->get_model_value('maxlength') ) : null,
		];
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}

	public static function is_singular(): bool {
		return true;
	}

	protected function overridable_models(): array {
		return [
			'minlength' => [
				'type' => 'number',
				'label' => 'Minlength',
			],
			'maxlength' => [
				'type' => 'number',
				'label' => 'Maxlength',
			],
		];
	}
}
