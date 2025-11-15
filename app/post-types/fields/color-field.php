<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Color_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'color',
		'label' => 'Color',
		'placeholder' => '',
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model( [
				'placeholder' => 'Enter a hex color code e.g. #4129d9',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		$value = sanitize_hex_color( strtolower( $value ) );
		if ( empty( $value ) ) {
			return null;
		}

		return $value;
	}

	public function validate( $value ): void {
		//
	}

	public function update( $value ): void {
		if ( $this->is_empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( $value ) );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_value = $this->render_default_value( $this->get_prop('default') );
			if ( ! is_string( $default_value ) ) {
				return null;
			}

			return sanitize_hex_color( strtolower( $default_value ) );
		} else {
			return $this->get_value();
		}
	}

	protected function frontend_props() {
		return [
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
		];
	}

	public function dynamic_data() {
		return Tag::String( $this->get_label() )->render( function() {
			return sanitize_hex_color( $this->get_value() );
		} );
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}
}
