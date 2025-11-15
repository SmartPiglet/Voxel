<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Time_Field extends Base_Post_Field {

	protected $props = [
		'type' => 'time',
		'label' => 'Time',
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
				'placeholder' => 'Enter a time string e.g. 12:30 or 9pm',
			] ),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		$timestamp = strtotime( (string) $value );
		if ( ! $timestamp ) {
			return null;
		}

		return date( 'H:i', $timestamp );
	}

	public function update( $value ): void {
		if ( empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), $value );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_value = $this->render_default_value( $this->get_prop('default') );
			$timestamp = strtotime( $default_value ?? '' );

			return $timestamp ? date( 'H:i', $timestamp ) : null;
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
			return $this->get_value();
		} );
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}
}
