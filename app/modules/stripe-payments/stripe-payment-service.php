<?php

namespace Voxel\Modules\Stripe_Payments;

use \Voxel\Modules\Stripe_Payments as Module;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Payment_Service extends \Voxel\Product_Types\Payment_Services\Base_Payment_Service {

	public function get_key(): string {
		return 'stripe';
	}

	public function get_label(): string {
		return 'Stripe';
	}

	public function get_description(): ?string {
		return 'Sell digital, physical and subscription products with Stripe';
	}

	public function is_test_mode(): bool {
		return Module\Stripe_Client::is_test_mode();
	}

	public function get_settings_schema(): Data_Object {
		return Schema::Object( [
			'mode' => Schema::Enum( [ 'live', 'sandbox' ] )->default('sandbox'),
			'currency' => Schema::String()->default('USD'),
			'__migrated' => Schema::Bool()->default(false),

			'live' => Schema::Object( [
				'api_key' => Schema::String(),
				'webhook' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'customer_portal' => Schema::Object( [
					'id' => Schema::String(),
					'invoice_history' => Schema::Bool()->default(true),
					'customer_update' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
						'allowed_updates' => Schema::List()
							->allowed_values( [ 'email', 'address', 'phone', 'shipping', 'tax_id', 'name' ] )
							->default( [ 'name', 'email', 'address', 'phone' ] ),
					] ),
				] ),
			] ),

			'sandbox' => Schema::Object( [
				'api_key' => Schema::String(),
				'webhook' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
				'customer_portal' => Schema::Object( [
					'id' => Schema::String(),
					'invoice_history' => Schema::Bool()->default(true),
					'customer_update' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
						'allowed_updates' => Schema::List()
							->allowed_values( [ 'email', 'address', 'phone', 'shipping', 'tax_id', 'name' ] )
							->default( [ 'name', 'email', 'address', 'phone' ] ),
					] ),
				] ),
			] ),

			'payments' => Schema::Object( [
				'order_approval' => Schema::enum( [ 'automatic', 'deferred', 'manual' ] )->default('automatic'),
				'billing_address_collection' => Schema::enum( [ 'auto', 'required' ] )->default('auto'),
				'tax_id_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
				'phone_number_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'promotion_codes' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),

			'subscriptions' => Schema::Object( [
				'billing_address_collection' => Schema::enum( [ 'auto', 'required' ] )->default('auto'),
				'tax_id_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
				'phone_number_collection' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
				'promotion_codes' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),
			] ),

			'stripe_connect' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'charge_type' => Schema::Enum( [ 'destination_charges', 'separate_charges_and_transfers' ] )->default('destination_charges'),
				'settlement_merchant' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				'subscriptions' => Schema::Object( [
					'charge_type' => Schema::Enum( [ 'destination_charges' ] )->default('destination_charges'),
					'settlement_merchant' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				] ),
				'vendor_fees' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'type' => Schema::Enum( [ 'fixed', 'percentage' ] )->default('fixed'),
					'fixed_amount' => Schema::Float()->min(0),
					'percentage_amount' => Schema::Float()->min(0)->max(100),
					'apply_to' => Schema::Enum( [ 'all', 'custom' ] )->default('all'),
					'conditions' => Schema::Object_List( [
						'source' => Schema::Enum( [ 'vendor_plan', 'vendor_role', 'vendor_id' ] ),
						'comparison' => Schema::Enum( [ 'equals', 'not_equals' ] ),
						'value' => Schema::String(),
					] )->default([]),
				] )->default([]),
				'shipping' => Schema::Object( [
					'responsibility' => Schema::Enum( [ 'platform', 'vendor' ] )->default('platform'),
				] ),
				'webhook' => Schema::Object( [
					'live' => Schema::Object( [
						'id' => Schema::String(),
						'secret' => Schema::String(),
					] ),
					'sandbox' => Schema::Object( [
						'id' => Schema::String(),
						'secret' => Schema::String(),
					] ),
				] ),
			] ),

			'tax_collection' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'collection_method' => Schema::enum( [ 'stripe_tax', 'tax_rates' ] )->default('stripe_tax'),
				'stripe_tax' => Schema::Object( [
					'product_types' => Schema::Keyed_Object_List( [
						'tax_behavior' => Schema::Enum( [ 'default', 'inclusive', 'exclusive' ] )->default('default'),
						'tax_code' => Schema::String()->default(''),
					] )->validator( function( $item, $key ) {
						return \Voxel\Product_Type::get( $key ) !== null;
					} ),
				] ),
				'tax_rates' => Schema::Object( [
					'product_types' => Schema::Keyed_Object_List( [
						'fixed_rates' => Schema::Object( [
							'live_mode' => Schema::List()->validator('is_string')->default([]),
							'test_mode' => Schema::List()->validator('is_string')->default([]),
						] ),
						'dynamic_rates' => Schema::Object( [
							'live_mode' => Schema::List()->validator('is_string')->default([]),
							'test_mode' => Schema::List()->validator('is_string')->default([]),
						] ),
						'calculation_method' => Schema::enum( [ 'fixed', 'dynamic' ] )->default('fixed'),
					] )->validator( function( $item, $key ) {
						return \Voxel\Product_Type::get( $key ) !== null;
					} ),
				] ),
			] ),
		] );
	}

	public function get_settings_component(): ?array {
		ob_start();
		require locate_template( 'app/modules/stripe-payments/templates/backend/stripe-settings.php' );
		$template = ob_get_clean();

		$src = trailingslashit( get_template_directory_uri() ).'app/modules/stripe-payments/assets/scripts/stripe-settings.esm.js';
		return [
			'src' => add_query_arg( 'v', \Voxel\get_assets_version(), $src ),
			'template' => $template,
			'data' => [],
		];
	}

	public function get_payment_handler(): ?string {
		return 'stripe_payment';
	}

	public function get_subscription_handler(): ?string {
		return 'stripe_subscription';
	}

	public function get_primary_currency(): ?string {
		$currency = \Voxel\get('payments.stripe.currency', 'USD');
		if ( ! is_string( $currency ) || empty( $currency ) ) {
			return 'USD';
		}

		return $currency;
	}

}
