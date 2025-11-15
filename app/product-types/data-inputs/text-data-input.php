<?php

namespace Voxel\Product_Types\Data_Inputs;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Base_Data_Type, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Data_Input extends Base_Data_Input {

	public function get_type(): string {
		return 'text';
	}

	public function get_schema(): Data_Object {
		return Schema::Object( [
			'key' => Schema::String()->default( $this->get_type() ),
			'label' => Schema::String()->default( 'Text' ),
			'placeholder' => Schema::String(),
			'minlength' => Schema::Int()->min(0)->default(0),
			'maxlength' => Schema::Int()->min(0)->default(100),
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
			Form_Models::Text( [
				'v-model' => 'dataInput.placeholder',
				'label' => 'Placeholder',
				'classes' => 'x-col-12',
			] ),
			Form_Models::Number( [
				'v-model' => 'dataInput.minlength',
				'label' => 'Min. length',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Number( [
				'v-model' => 'dataInput.maxlength',
				'label' => 'Max. length',
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
		return Schema::String();
	}

	public function get_product_form_frontend_props(): array {
		return [
			'placeholder' => $this->get_prop( 'placeholder' ),
			'minlength' => $this->get_prop( 'minlength' ),
			'maxlength' => $this->get_prop( 'maxlength' ),
		];
	}

	public function sanitize_in_cart_item( $value ) {
		if ( $value === null ) {
			return null;
		}

		$value = sanitize_text_field( $value );
		if ( empty( $value ) ) {
			return null;
		}

		return $value;
	}

	public function validate_in_cart_item( $value ): void {
		if ( $this->is_required() && $value === null ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@input_name is required', 'data input', 'voxel' ), [
				'@input_name' => $this->get_label(),
			] ) );
		}

		if ( $value !== null ) {
			$length = mb_strlen( $value );
			$minlength = $this->get_prop('minlength');
			$maxlength = $this->get_prop('maxlength');

			if ( $length < $minlength ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@input_name can\'t be shorter than @length characters', 'data input', 'voxel' ), [
					'@input_name' => $this->get_label(),
					'@length' => $minlength,
				] ) );
			}

			if ( $length > $maxlength ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@input_name can\'t be longer than @length characters', 'data input', 'voxel' ), [
					'@input_name' => $this->get_label(),
					'@length' => $maxlength,
				] ) );
			}
		}
	}
}
