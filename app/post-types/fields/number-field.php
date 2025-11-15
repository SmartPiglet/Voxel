<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Field extends Base_Post_Field {

	protected $supported_conditions = ['number'];

	protected $props = [
		'type' => 'number',
		'label' => 'Number',
		'placeholder' => '',
		'suffix' => '',
		'min' => 0,
		'max' => 1000,
		'step' => 1,
		'display' => 'input',
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'description' => $this->get_description_model(),
			'min' => [
				'type' => Form_Models\Number_Model::class,
				'label' => 'Minimum value',
				'classes' => 'x-col-4',
				'placeholder' => 0,
			],
			'max' => [
				'type' => Form_Models\Number_Model::class,
				'label' => 'Maximum value',
				'classes' => 'x-col-4',
				'placeholder' => 1000,
			],
			'step' => [
				'type' => Form_Models\Number_Model::class,
				'label' => 'Step size',
				'classes' => 'x-col-4',
			],
			'display' => [
				'type' => Form_Models\Select_Model::class,
				'label' => 'Display as',
				'classes' => 'x-col-4',
				'choices' => [
					'input' => 'Input',
					'stepper' => 'Stepper',
				],
			],
			'suffix' => [
				'v-if' => 'field.display === "input"',
				'type' => Form_Models\Text_Model::class,
				'label' => 'Suffix',
				'classes' => 'x-col-4',
			],
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		if ( ! is_numeric( $value ) ) {
			return null;
		}

		return $value;
	}

	public function validate( $value ): void {
		if ( (float) $value < $this->_get_min_value() ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( '@field_name cannot be less than @length', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@length' => $this->_get_min_value(),
				] )
			);
		}

		if ( (float) $value > $this->_get_max_value() ) {
			throw new \Exception(
				\Voxel\replace_vars( _x( '@field_name cannot be more than @length', 'field validation', 'voxel' ), [
					'@field_name' => $this->get_label(),
					'@length' => $this->_get_max_value(),
				] )
			);
		}
	}

	protected function _get_min_value() {
		if ( ! is_numeric( $this->get_model_value('min') ) ) {
			return 0;
		}

		return (float) $this->get_model_value('min');
	}

	protected function _get_max_value() {
		if ( ! is_numeric( $this->get_model_value('max') ) ) {
			return 1000;
		}

		return (float) $this->get_model_value('max');
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

	// db helpers
	public function _get_max_int_size() {
		$max = max(
			absint( $this->_get_min_value() ),
			absint( $this->_get_max_value() )
		);

		return ceil( $max * $this->_get_value_multiplier() );
	}

	public function _get_value_multiplier() {
		if ( ! is_numeric( $this->props['step'] ) ) {
			return 1;
		}

		$step = abs( (float) $this->props['step'] );
		$precision = strlen( substr( strrchr( $step, '.' ), 1 ) );

		return pow( 10, $precision );
	}

	public function _get_column_type() {
		$max = $this->_get_max_int_size();

		if ( $max < ((2**7) - 1) ) {
			return 'TINYINT';
		} elseif ( $max < ((2**15) - 1) ) {
			return 'SMALLINT';
		} elseif ( $max < ((2**23) - 1) ) {
			return 'MEDIUMINT';
		} elseif ( $max < ((2**31) - 1) ) {
			return 'INT';
		} else {
			return 'BIGINT';
		}
	}

	public function _prepare_value( $value ) {
		return intval( round( $value * $this->_get_value_multiplier(), 0 ) );
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			$default_value = $this->render_default_value( $this->get_prop('default') );
			return is_numeric( $default_value ) ? $default_value : null;
		} else {
			return $this->get_value();
		}
	}

	protected function frontend_props() {
		$step = abs( (float) $this->props['step'] );
		$precision = absint( strlen( substr( strrchr( $step, '.' ), 1 ) ) );

		return [
			'placeholder' => $this->get_model_value('placeholder') ?: $this->props['label'],
			'min' => $this->_get_min_value(),
			'max' => $this->_get_max_value(),
			'step' => $this->props['step'],
			'precision' => $precision,
			'display' => $this->props['display'],
			'suffix' => $this->props['suffix'],
		];
	}

	public function dynamic_data() {
		return Tag::Number( $this->get_label() )->render( function() {
			return $this->get_value();
		} );
	}

	public function export_to_personal_data() {
		return $this->get_value();
	}

	protected function overridable_models(): array {
		return [
			'min' => [
				'type' => 'number',
				'label' => 'Minimum value',
			],
			'max' => [
				'type' => 'number',
				'label' => 'Maximum value',
			],
		];
	}
}
