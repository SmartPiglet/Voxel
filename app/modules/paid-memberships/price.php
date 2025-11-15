<?php

namespace Voxel\Modules\Paid_Memberships;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Price {

	public $plan, $price;

	protected $plan_key, $price_key;

	public function __construct( string $plan_key, string $price_key ) {
		$this->plan_key = $plan_key;
		$this->price_key = $price_key;

		if ( ! is_string( $this->price_key ) ) {
			throw new \Exception( _x( 'Could not find plan.', 'pricing plans', 'voxel' ) );
		}

		$plan = Module\Plan::get( $this->plan_key );
		if ( ! $plan ) {
			throw new \Exception( _x( 'Plan does not exist.', 'pricing plans', 'voxel' ) );
		}

		$prices = $plan->config('prices');
		$price = null;
		foreach ( (array) $prices as $p ) {
			if ( $p['key'] === $this->price_key ) {
				$price = $p;
				break;
			}
		}

		if ( $price === null ) {
			throw new \Exception( _x( 'Price does not exist.', 'pricing plans', 'voxel' ) );
		}

		$this->plan = $plan;
		$this->price = $price;
	}

	public function get_key() {
		return $this->price['key'];
	}

	public function get_label() {
		return $this->price['label'];
	}

	public function get_amount() {
		return $this->price['amount'] ?? null;
	}

	public function get_discount_amount() {
		return $this->price['discount_amount'] ?? null;
	}

	public function get_currency() {
		return $this->price['currency'] ?? null;
	}

	public function get_billing_interval() {
		return $this->price['interval'] ?? null;
	}

	public function get_billing_frequency() {
		return $this->price['frequency'] ?? null;
	}

	public function get_trial_days(): ?int {
		$has_trial = !! ( $this->price['trial']['enabled'] ?? false );
		if ( ! $has_trial ) {
			return null;
		}

		$trial_days = $this->price['trial']['days'] ?? null;
		if ( ! is_numeric( $trial_days ) ) {
			return null;
		}

		return absint( $trial_days );
	}

	public function to_checkout_key() {
		return sprintf( '%s@%s', $this->plan->get_key(), $this->key );
	}

	public function get_product(): \Voxel\Post {
		$product = \Voxel\Post::find( [
			'post_type' => '_vx_catalog',
			'post_status' => 'publish',
			'meta_query' => [
				[
					'key' => '_vx_catalog_category',
					'value' => 'paid_memberships_price',
				],
				[
					'key' => '_vx_plan_key',
					'value' => $this->plan_key,
				],
				[
					'key' => '_vx_price_key',
					'value' => $this->price_key,
				],
			],
		] );

		if ( ! $product ) {
			$product_id = wp_insert_post( [
				'post_type' => '_vx_catalog',
				'post_status' => 'publish',
				'post_title' => sprintf( '%s: %s', $this->plan->get_label(), $this->price['label'] ),
				'meta_input' => [
					'_vx_catalog_category' => 'paid_memberships_price',
					'_vx_plan_key' => $this->plan_key,
					'_vx_price_key' => $this->price_key,
				],
			] );

			$product = \Voxel\Post::get( $product_id );
		}

		if ( ! $product->get_author() ) {
			wp_update_post( [
				'ID' => $product->get_id(),
				'post_author' => \Voxel\get_main_admin()?->get_id(),
			] );

			$product = \Voxel\Post::force_get( $product->get_id() );
		}

		return $product;
	}

	public static function from_checkout_key( string $checkout_key ) {
		$price_key = substr( strrchr( $checkout_key, '@' ), 1 );
		$plan_key = str_replace( '@'.$price_key, '', $checkout_key );

		return new static( $plan_key, $price_key );
	}

	public static function get( string $plan_key, string $price_key ): static {
		return new static( $plan_key, $price_key );
	}

}
