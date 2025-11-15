<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Deliverables_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'deliverables',
		'label' => 'Deliverables',
		'description' => '',
	];

	public function get_models(): array {
		return [
			'label' => Form_Models::Text( [
				'label' => 'Label',
				'classes' => 'x-col-12',
			] ),
			'description' => Form_Models::Textarea( [
				'label' => 'Description',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_conditions(): array {
		return [
			'settings.product_mode' => 'regular',
			'modules.deliverables.enabled' => true,
			'modules.deliverables.delivery_methods.automatic' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'deliverables', Schema::Object( [
			'files' => Schema::String(),
		] ) );
	}

	public function sanitize( $value, $raw_value ) {
		$file_field = $this->get_file_field();
		$value['deliverables']['files'] = $file_field->sanitize( (array) ( $raw_value['deliverables']['files'] ?? [] ) );

		return $value;
	}

	public function validate( $value ): void {
		$file_field = $this->get_file_field();
		$file_field->validate( $value['deliverables']['files'] );
	}

	public function update( $value ) {
		$file_field = $this->get_file_field();
		$value['deliverables']['files'] = $file_field->prepare_for_storage( $value['deliverables']['files'] );

		return $value;
	}

	private function get_file_field() {
		return new \Voxel\Utils\Object_Fields\File_Field( [
			'label' => $this->props['label'],
			'key' => sprintf( '%s.deliverables', $this->product_field->get_key() ),
			'allowed-types' => $this->product_type->config( 'modules.deliverables.uploads.allowed_file_types' ),
			'max-size' => $this->product_type->config( 'modules.deliverables.uploads.max_size' ),
			'max-count' => $this->product_type->config( 'modules.deliverables.uploads.max_count' ),
			'private_upload' => true,
		] );
	}

	public function editing_value( $value ) {
		$files = [];
		$file_ids = explode( ',', (string) ( $value['deliverables']['files'] ?? '' ) );
		foreach ( $file_ids as $file_id ) {
			if ( is_numeric( $file_id ) && ( $attachment = get_post( $file_id ) ) ) {
				$display_filename = get_post_meta( $attachment->ID, '_display_filename', true );
				$files[] = [
					'source' => 'existing',
					'id' => $attachment->ID,
					'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $attachment->ID ) ),
					'type' => $attachment->post_mime_type,
					'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
				];
			}
		}

		$value['deliverables']['files'] = $files;

		return $value;
	}

	public function frontend_props(): array {
		return [
			'allowed_file_types' => $this->product_type->config( 'modules.deliverables.uploads.allowed_file_types' ),
			'max_count' => $this->product_type->config( 'modules.deliverables.uploads.max_count' ),
		];
	}
}
