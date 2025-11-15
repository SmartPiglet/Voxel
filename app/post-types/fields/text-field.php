<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'text',
		'label' => 'Text',
		'placeholder' => '',
		'suffix' => '',
		'minlength' => null,
		'maxlength' => null,
		'pattern' => null,
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'minlength' => $this->get_model( 'minlength', [ 'classes' => 'x-col-6' ]),
			'maxlength' => $this->get_model( 'maxlength', [ 'classes' => 'x-col-6' ]),
			'suffix' => [
				'type' => Form_Models\Text_Model::class,
				'label' => 'Suffix',
				'classes' => 'x-col-6',
			],
			'pattern' => [
				'type' => Form_Models\Text_Model::class,
				'label' => 'Pattern',
				'classes' => 'x-col-6',
				'infobox' => <<<HTML
				Enter a regular expression that all submitted values must match. For example:<br><br>
				&bullet; Only letters: <code>[A-Za-z]+</code><br>
				&bullet; 5-digit ZIP code: <code>\d{5}</code><br>
				&bullet; Phone number (e.g., 123-456-7890): <code>\d{3}-\d{3}-\d{4}</code><br><br>
				Any input that doesn’t fit the pattern will trigger a format error.
				HTML,
			],
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

	public function validate( $value ): void {
		$this->validate_minlength( $value );
		$this->validate_maxlength( $value );
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
			'minlength' => is_numeric( $this->get_model_value('minlength') ) ? absint( $this->get_model_value('minlength') ) : null,
			'maxlength' => is_numeric( $this->get_model_value('maxlength') ) ? absint( $this->get_model_value('maxlength') ) : null,
			'suffix' => $this->props['suffix'],
			'pattern' => is_string( $this->props['pattern'] ) && ! empty( $this->props['pattern'] ) ? $this->props['pattern'] : null,
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
