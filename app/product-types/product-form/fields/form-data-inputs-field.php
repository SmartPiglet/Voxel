<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Data_Inputs_Field extends Base_Field {

	protected $props = [
		'key' => 'form-data-inputs',
		'label' => 'Data inputs',
	];

	public function get_conditions(): array {
		return [
			'modules.data_inputs.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$data_inputs_schema = Schema::Object( [] )->default( [] );
		foreach ( $this->product_type->repository->get_data_inputs() as $data_input ) {
			$data_inputs_schema->set_prop( $data_input->get_key(), $data_input->get_product_form_schema() );
		}

		$schema->set_prop( 'data_inputs', $data_inputs_schema );
	}

	public function validate( $value ) {
		foreach ( $this->product_type->repository->get_data_inputs() as $data_input ) {
			$data_input_value = $value['data_inputs'][ $data_input->get_key() ] ?? null;
			$data_input_value = $data_input->sanitize_in_cart_item( $data_input_value );

			$data_input->validate_in_cart_item( $data_input_value );
		}
	}

	public function frontend_props(): array {
		return [
			'data_inputs' => array_map( function( $data_input ) {
				return $data_input->get_product_form_frontend_config();
			}, $this->product_type->repository->get_data_inputs() ),
		];
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/product-form/form-data-inputs.php' );

		foreach ( $this->product_type->repository->get_data_inputs() as $data_input ) {
			$templates[] = locate_template( sprintf( 'templates/widgets/product-form/form-data-inputs/%s-data-input.php', $data_input->get_type() ) );
		}

		return $templates;
	}

	public function prepare_data_inputs_for_storage( $value ): ?array {
		$prepared = [];
		foreach ( $this->product_type->repository->get_data_inputs() as $data_input ) {
			$data_input_value = $value['data_inputs'][ $data_input->get_key() ] ?? null;
			$data_input_value = $data_input->sanitize_in_cart_item( $data_input_value );
			if ( $data_input_value === null ) {
				continue;
			}

			$order_item_value = $data_input->store_in_order_item( $data_input_value );
			if ( $order_item_value === null ) {
				continue;
			}

			$prepared[ $data_input->get_key() ] = $order_item_value;
		}

		return ! empty( $prepared ) ? $prepared : null;
	}
}
