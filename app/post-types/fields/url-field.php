<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Url_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'url',
		'label' => 'URL',
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
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		if ( ! ( str_starts_with( $value, 'http://' ) || str_starts_with( $value, 'https://' ) ) ) {
			$value = 'https://' . $value;
		}

		return sanitize_url( $value );
	}

	public function validate( $value ): void {
		if ( preg_match( '@^(https?|ftp)://[^\s/$.?#].[^\s]*$@iS', $value ) !== 1 ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name must be a valid url address.', 'url field', 'voxel' ), [
					'@field_name' => $this->get_label(),
				]
			) );
		}
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
		return Tag::URL( $this->get_label() )->render( function() {
			return $this->get_value();
		} );
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}
}
