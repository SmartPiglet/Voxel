<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Subscription_Interval_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'subscription-interval',
		'label' => 'Subscription interval',
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
			'settings.payments.mode' => 'subscription',
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'subscription', Schema::Object( [
			'frequency' => Schema::Int()->min(1)->default(1),
			'unit' => Schema::Enum( [ 'day', 'week', 'month', 'year' ] )->default( 'month' ),
		] ) );
	}

	public function validate( $value ): void {
		if ( $value['subscription']['frequency'] === null || $value['subscription']['unit'] === null ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Subscription interval is required', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}

		$frequency = $value['subscription']['frequency'];
		$unit = $value['subscription']['unit'];

		if (
			( $unit === 'year' && $frequency > 3 )
			|| ( $unit === 'month' && $frequency > 36 )
			|| ( $unit === 'week' && $frequency > 156 )
			|| ( $unit === 'day' && $frequency > 365 )
		) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Subscription interval cannot be longer than three years', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}
	}
}
