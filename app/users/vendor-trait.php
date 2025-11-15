<?php

namespace Voxel\Users;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Vendor_Trait {

	public static function get_by_stripe_vendor_id( $vendor_id ): ?\Voxel\User {
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_account_id' : 'voxel:stripe_account_id';
		$results = get_users( [
			'meta_key' => $meta_key,
			'meta_value' => $vendor_id,
			'number' => 1,
			'fields' => 'ID',
		] );

		return \Voxel\User::get( array_shift( $results ) );
	}

	public function get_stripe_vendor_id() {
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_account_id' : 'voxel:stripe_account_id';
		return get_user_meta( $this->get_id(), $meta_key, true );
	}

	public function get_stripe_vendor() {
		$vendor_id = $this->get_stripe_vendor_id();
		if ( empty( $vendor_id ) ) {
			throw new \Exception( _x( 'Stripe account not set up for this user.', 'orders', 'voxel' ) );
		}

		$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
		return $stripe->accounts->retrieve( $vendor_id );
	}

	public function get_or_create_stripe_vendor() {
		try {
			$account = $this->get_stripe_vendor();
		} catch ( \Exception $e ) {
			$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
			$account = $stripe->accounts->create( [
				'type' => 'express',
				'email' => $this->get_email(),
			] );

			$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_account_id' : 'voxel:stripe_account_id';
			update_user_meta( $this->get_id(), $meta_key, $account->id );
			$this->stripe_vendor_updated( $account );
		}

		return $account;
	}

	public function stripe_vendor_updated( \Voxel\Vendor\Stripe\Account $account ) {
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_account' : 'voxel:stripe_account';
		update_user_meta( $this->get_id(), $meta_key, wp_slash( wp_json_encode( [
			'charges_enabled' => $account->charges_enabled,
			'details_submitted' => $account->details_submitted,
			'payouts_enabled' => $account->payouts_enabled,
		] ) ) );
	}

	public function get_stripe_vendor_details() {
		if ( ! is_null( $this->account_details ) ) {
			return $this->account_details;
		}

		$account_id = $this->get_stripe_vendor_id();
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_account' : 'voxel:stripe_account';
		$details = (array) json_decode( get_user_meta( $this->get_id(), $meta_key, true ), true );

		$this->account_details = (object) [
			'exists' => ! empty( $account_id ),
			'id' => $account_id,
			'charges_enabled' => $details['charges_enabled'] ?? false,
			'details_submitted' => $details['details_submitted'] ?? false,
			'payouts_enabled' => $details['payouts_enabled'] ?? false,
		];

		return $this->account_details;
	}

	public function is_active_vendor(): bool {
		if ( \Voxel\get( 'payments.provider' ) !== 'stripe' ) {
			return false;
		}

		if ( ! \Voxel\get( 'payments.stripe.stripe_connect.enabled' ) ) {
			return false;
		}

		if ( $this->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
			return false;
		}

		$details = $this->get_stripe_vendor_details();

		return $details->exists && $details->charges_enabled;
	}

	public function is_vendor_of( $order_id ): bool {
		$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
		return $order->get_vendor_id() === $this->get_id();
	}

	public function get_vendor_stats() {
		if ( ! is_null( $this->vendor_stats ) ) {
			return $this->vendor_stats;
		}

		$this->vendor_stats = new \Voxel\Product_Types\Vendor_Stats( $this );
		return $this->vendor_stats;
	}

	public function get_vendor_fees(): array {
		$schema = Schema::Object_List( [
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
		] )->default([]);

		$schema->set_value( \Voxel\get('payments.stripe.stripe_connect.vendor_fees') );
		$items = $schema->export();

		$fees = [];

		$membership = $this->get_membership();
		$plan_key = $membership->is_active() ? $membership->get_active_plan()->get_key() : 'default';

		foreach ( $items as $item ) {
			if ( $item['apply_to'] === 'custom' ) {
				$passes_conditions = false;
				foreach ( $item['conditions'] as $condition ) {
					if ( $condition['source'] === 'vendor_plan' ) {
						if ( $condition['comparison'] === 'equals' && $condition['value'] === $plan_key ) {
							$passes_conditions = true;
							break;
						} elseif ( $condition['comparison'] === 'not_equals' && $condition['value'] !== $plan_key ) {
							$passes_conditions = true;
							break;
						}
					} elseif ( $condition['source'] === 'vendor_role' ) {
						if ( $condition['comparison'] === 'equals' && $this->has_role( $condition['value'] ) ) {
							$passes_conditions = true;
							break;
						} elseif ( $condition['comparison'] === 'not_equals' && ! $this->has_role( $condition['value'] ) ) {
							$passes_conditions = true;
							break;
						}
					} elseif ( $condition['source'] === 'vendor_id' ) {
						if ( $condition['comparison'] === 'equals' && absint( $condition['value'] ) === absint( $this->get_id() ) ) {
							$passes_conditions = true;
							break;
						} elseif ( $condition['comparison'] === 'not_equals' && absint( $condition['value'] ) !== absint( $this->get_id() ) ) {
							$passes_conditions = true;
							break;
						}
					}
				}

				if ( ! $passes_conditions ) {
					continue;
				}
			}

			if ( $item['type'] === 'fixed' ) {
				if ( ! ( is_numeric( $item['fixed_amount'] ) && $item['fixed_amount'] > 0 ) ) {
					continue;
				}

				$fees[] = [
					'key' => $item['key'],
					'label' => $item['label'],
					'type' => $item['type'],
					'fixed_amount' => $item['fixed_amount'],
				];
			} elseif ( $item['type'] === 'percentage' ) {
				if ( ! ( is_numeric( $item['percentage_amount'] ) && $item['percentage_amount'] > 0 && $item['percentage_amount'] <= 100 ) ) {
					continue;
				}

				$fees[] = [
					'key' => $item['key'],
					'label' => $item['label'],
					'type' => $item['type'],
					'percentage_amount' => $item['percentage_amount'],
				];
			}
		}

		return $fees;
	}

	public function get_vendor_shipping_zones_schema(): \Voxel\Utils\Config_Schema\Data_Object_List {
		$shipping_countries = \Voxel\Modules\Stripe_Payments\Country_Codes::shipping_supported();
		return Schema::Object_List( [
			'key' => Schema::String(),
			'label' => Schema::String(),
			'countries' => Schema::List()->unique()->validator( function( $country_code ) use ( $shipping_countries ) {
				return isset( $shipping_countries[ $country_code ] );
			} )->default([]),
			'rates' => Schema::Object_List( [
				'key' => Schema::String(),
				'label' => Schema::String(),
				'type' => Schema::Enum( [ 'free_shipping', 'fixed_rate' ] )->default('free_shipping'),
				'free_shipping' => Schema::Object( [
					'requirements' => Schema::Enum( [ 'none', 'minimum_order_amount' ] )->default('none'),
					'minimum_order_amount' => Schema::Float()->min(0)->default(0),
					'delivery_estimate' => Schema::Object( [
						'enabled' => Schema::Bool()->default(false),
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
					'delivery_estimate' => Schema::Object( [
						'enabled' => Schema::Bool()->default(false),
						'minimum' => Schema::Object( [
							'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
							'value' => Schema::Int()->min(1)->default(1),
						] ),
						'maximum' => Schema::Object( [
							'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
							'value' => Schema::Int()->min(1)->default(1),
						] ),
					] ),
					'amount_per_unit' => Schema::Float()->min(0)->default(0),
					'shipping_classes' => Schema::Object_List( [
						'shipping_class' => Schema::String(),
						'amount_per_unit' => Schema::Float()->min(0)->default(0),
					] )->validator( function( $item ) {
						return !! \Voxel\Product_Types\Shipping\Shipping_Class::get( $item['shipping_class'] ?? null );
					} )->default([]),
				] ),
			] )->default([]),
		] )->default([]);
	}

	public function get_vendor_shipping_zones_config(): array {
		$schema = $this->get_vendor_shipping_zones_schema();
		$shipping_zones = (array) json_decode( get_user_meta( $this->get_id(), 'voxel:vendor_shipping_zones', true ), true );
		$schema->set_value( $shipping_zones );

		return $schema->export();
	}

	protected $_get_vendor_shipping_zones;
	public function get_vendor_shipping_zones(): array {
		if ( $this->_get_vendor_shipping_zones === null ) {
			$this->_get_vendor_shipping_zones = [];

			foreach ( $this->get_vendor_shipping_zones_config() as $data ) {
				$shipping_zone = new \Voxel\Product_Types\Shipping\Vendor_Shipping_Zone( $data );
				$this->_get_vendor_shipping_zones[ $shipping_zone->get_key() ] = $shipping_zone;
			}
		}

		return $this->_get_vendor_shipping_zones;
	}

	public function get_vendor_shipping_zone( ?string $shipping_zone_key ) {
		$shipping_zones = $this->get_vendor_shipping_zones();
		return $shipping_zones[ $shipping_zone_key ] ?? null;
	}

}
