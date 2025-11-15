<?php

namespace Voxel\Product_Types\Data_Inputs;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Base_Data_Type, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Data_Input extends Base_Data_Input {

	public function get_type(): string {
		return 'number';
	}

	public function get_schema(): Data_Object {
		return Schema::Object( [
			'key' => Schema::String()->default( $this->get_type() ),
			'label' => Schema::String()->default( 'Number' ),
			'placeholder' => Schema::String(),
			'min' => Schema::Int()->default(0),
			'max' => Schema::Int()->default(100),
			'display_mode' => Schema::Enum( [ 'input', 'stepper' ] )->default('input'),
			'required' => Schema::Bool()->default( false ),
		] );
	}

	public function get_controls(): array {
		return [
			Form_Models::Text( [
				'v-model' => 'dataInput.label',
				'label' => 'Label',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Key( [
				'v-model' => 'dataInput.key',
				'label' => 'Key',
				'ref' => 'keyInput',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Select( [
				'v-model' => 'dataInput.display_mode',
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'stepper' => 'Stepper',
					'input' => 'Input',
				],
			] ),
			Form_Models::Text( [
				'v-model' => 'dataInput.placeholder',
				'label' => 'Placeholder',
				'classes' => 'x-col-12',
			] ),
			Form_Models::Number( [
				'v-model' => 'dataInput.min',
				'label' => 'Min',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Number( [
				'v-model' => 'dataInput.max',
				'label' => 'Max',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Switcher( [
				'v-model' => 'dataInput.required',
				'label' => 'Is required?',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_product_form_schema(): Base_Data_Type {
		return Schema::Float();
	}

	public function get_product_form_frontend_props(): array {
		return [
			'placeholder' => $this->get_prop( 'placeholder' ),
			'min' => $this->get_prop( 'min' ),
			'max' => $this->get_prop( 'max' ),
			'display_mode' => $this->get_prop( 'display_mode' ),
		];
	}

	public function validate_in_cart_item( $value ): void {
		if ( $this->is_required() && $value === null ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@input_name is required', 'data input', 'voxel' ), [
				'@input_name' => $this->get_label(),
			] ) );
		}

		if ( $value !== null ) {
			$min = $this->get_prop('min');
			$max = $this->get_prop('max');

			if ( is_numeric( $min ) && $value < $min ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@input_name can\'t be less than @value', 'data input', 'voxel' ), [
					'@input_name' => $this->get_label(),
					'@value' => $min,
				] ) );
			}

			if ( is_numeric( $max ) && $value > $max ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@input_name can\'t be more than @value', 'data input', 'voxel' ), [
					'@input_name' => $this->get_label(),
					'@value' => $max,
				] ) );
			}
		}
	}
}
