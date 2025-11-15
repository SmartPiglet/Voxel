<?php

namespace Voxel\Product_Types\Data_Inputs;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Base_Data_Type, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Email_Data_Input extends Base_Data_Input {

	public function get_type(): string {
		return 'email';
	}

	public function get_schema(): Data_Object {
		return Schema::Object( [
			'key' => Schema::String()->default( $this->get_type() ),
			'label' => Schema::String()->default( 'Email' ),
			'placeholder' => Schema::String(),
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
		];
	}

	public function sanitize_in_cart_item( $value ) {
		if ( $value === null ) {
			return null;
		}

		$value = sanitize_email( $value );
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
			$maxlength = 150;
			if ( mb_strlen( $value ) > $maxlength ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@input_name can\'t be longer than @length characters', 'data input', 'voxel' ), [
					'@input_name' => $this->get_label(),
					'@length' => $maxlength,
				] ) );
			}

			if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@input_name must be a valid email address', 'data input', 'voxel' ), [
						'@input_name' => $this->get_label(),
					]
				) );
			}
		}
	}
}
