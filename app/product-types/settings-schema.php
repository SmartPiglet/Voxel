<?php

namespace Voxel\Product_Types;

use Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Settings_Schema {

	public static function get(): Data_Object {
		return Schema::Object( [
			'stripe_payments' => Schema::Object( [
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

			'stripe_subscriptions' => Schema::Object( [
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

			'offline_payments' => Schema::Object( [
				'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
				'notes_to_customer' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'content' => Schema::String(),
				] ),
			] ),

			'multivendor' => Schema::Object( [
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
			] ),

			'promotions' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'packages' => Schema::Object_List( [
					'key' => Schema::String(),
					'post_types' => Schema::List()->default([]),
					'duration' => Schema::Object( [
						'type' => Schema::Enum( ['days'] ),
						'amount' => Schema::Int()->min(1)->default(7),
					] ),
					'priority' => Schema::Int()->min(1)->default(2),
					'price' => Schema::Object( [
						'amount' => Schema::Float()->min(0),
					] ),
					'ui' => Schema::Object( [
						'label' => Schema::String(),
						'description' => Schema::String(),
						'icon' => Schema::String(),
						'color' => Schema::String(),
					] ),
				] )->default( [] ),
				'payments' => Schema::Object( [
					'mode' => Schema::Enum( [ 'payment', 'offline' ] )->default('payment'),
				] ),
				'order_approval' => Schema::enum( [ 'automatic', 'manual' ] )->default('automatic'),
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

			'cart_summary' => Schema::Object( [
				'guest_customers' => Schema::Object( [
					'behavior' => Schema::Enum( [ 'require_account', 'proceed_with_email' ] )->default( 'proceed_with_email' ),
					'proceed_with_email' => Schema::Object( [
						'require_verification' => Schema::Bool()->default(true),
						'require_tos' => Schema::Bool()->default(false),
						'email_account_details' => Schema::Bool()->default(true),
					] ),
				] ),
			] ),

			'shipping' => Schema::Object( [
				'shipping_classes' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'description' => Schema::String(),
				] )->default([]),
				'shipping_zones' => Schema::Object_List( [
					'key' => Schema::String(),
					'label' => Schema::String(),
					'regions' => Schema::Object_List( [
						'type' => Schema::Enum( [ 'country' ] )->default('country'),
						'country' => Schema::String(),
					] )->default([]),
					'rates' => Schema::Object_List( [
						'key' => Schema::String(),
						'label' => Schema::String(),
						'type' => Schema::Enum( [ 'free_shipping', 'fixed_rate' ] )->default('free_shipping'),
						'free_shipping' => Schema::Object( [
							'requirements' => Schema::Enum( [ 'none', 'minimum_order_amount' ] )->default('none'),
							'minimum_order_amount' => Schema::Float()->min(0),
							'delivery_estimate' => Schema::Object( [
								'minimum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
								'maximum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
							] ),
						] ),
						'fixed_rate' => Schema::Object( [
							'tax_behavior' => Schema::Enum( [ 'default', 'inclusive', 'exclusive' ] )->default('default'),
							'tax_code' => Schema::Enum( [ 'shipping', 'nontaxable' ] )->default('shipping'),
							'delivery_estimate' => Schema::Object( [
								'minimum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
								'maximum' => Schema::Object( [
									'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
									'value' => Schema::Int()->min(1),
								] ),
							] ),
							'amount_per_unit' => Schema::Float()->min(0)->default(0),
							'shipping_classes' => Schema::Object_List( [
								'shipping_class' => Schema::String(),
								'amount_per_unit' => Schema::Float()->min(0)->default(0),
							] )->default([]),
						] ),
					] )->default([]),
				] )->default([]),
			] ),

			'orders' => Schema::Object( [
				'managed_by' => Schema::Enum( [ 'platform', 'product_author' ] )->default('product_author'),
				'direct_messages' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),
			] ),
		] );
	}

}
