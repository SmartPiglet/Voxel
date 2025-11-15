<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Phone_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'phone',
		'label' => 'Phone',
		'placeholder' => '',
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_model( 'key', [ 'width' => '1/1' ]),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

	public function update( $value ): void {
		if ( $this->is_empty( $value ) ) {
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
			return $this->render_default_value( $this->get_prop('default') );
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
