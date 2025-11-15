<?php

namespace Voxel\Product_Types\Payment_Services;

use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Payment_Service {

	abstract public function get_key(): string;

	abstract public function get_label(): string;

	abstract public function is_test_mode(): bool;

	public function get_description(): ?string {
		return null;
	}

	public function get_settings_schema(): Data_Object {
		return Schema::Object( [
			//
		] );
	}

	public function get_settings(): array {
		$schema = $this->get_settings_schema();
		$value = \Voxel\get( sprintf( 'payments.%s', $this->get_key() ) );

		$schema->set_value( $value );

		return $schema->export();
	}

	public function render_settings() {
		//
	}

	public function get_settings_component(): ?array {
		return null;
	}

	public function get_payment_handler(): ?string {
		return null;
	}

	public function get_subscription_handler(): ?string {
		return null;
	}

	public function get_primary_currency(): ?string {
		return null;
	}

	public function get_supported_currencies(): array {
		return array_keys( \Voxel\Utils\Currency_List::all() );
	}

	public static function get_all(): array {
		return apply_filters( 'voxel/product-types/payment-services', [
			//
		] );
	}

	public static function get( string $key ): ?Base_Payment_Service {
		return static::get_all()[ $key ] ?? null;
	}

	public static function get_active(): ?Base_Payment_Service {
		return static::get( (string) \Voxel\get( 'payments.provider' ) );
	}

}
