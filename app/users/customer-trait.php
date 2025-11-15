<?php

namespace Voxel\Users;

use Voxel\Vendor\Paddle\SDK\Resources\Customers\Operations\CreateCustomer as PaddleCreateCustomer;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Customer_Trait {

	public static function get_by_customer_id( $customer_id ) {
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_customer_id' : 'voxel:stripe_customer_id';
		$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

		$results = get_users( [
			'meta_key' => $meta_key,
			'meta_value' => $customer_id,
			'number' => 1,
			'fields' => 'ID',
		] );

		return \Voxel\User::get( array_shift( $results ) );
	}

	public function get_stripe_customer_id() {
		$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_customer_id' : 'voxel:stripe_customer_id';
		$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

		return get_user_meta( $this->get_id(), $meta_key, true );
	}

	public function get_stripe_customer() {
		$customer_id = $this->get_stripe_customer_id();
		if ( empty( $customer_id ) ) {
			throw new \Exception( _x( 'Stripe customer account not set up for this user.', 'orders', 'voxel' ) );
		}

		$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
		return $stripe->customers->retrieve( $customer_id );
	}

	public function get_or_create_stripe_customer() {
		try {
			$customer = $this->get_stripe_customer();
		} catch ( \Exception $e ) {
			$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
			$customer = $stripe->customers->create( [
				'email' => $this->get_email(),
				'name' => $this->get_display_name(),
			] );

			$meta_key = \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode() ? 'voxel:test_stripe_customer_id' : 'voxel:stripe_customer_id';
			$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

			update_user_meta( $this->get_id(), $meta_key, $customer->id );
		}

		return $customer;
	}

	public function is_customer_of( $order_id ): bool {
		$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
		return $order->get_customer_id() === $this->get_id();
	}

	protected $customer_cart;
	public function get_cart() {
		if ( $this->customer_cart === null ) {
			$this->customer_cart = new \Voxel\Product_Types\Cart\Customer_Cart( $this );
			$this->customer_cart->sync();
		}

		return $this->customer_cart;
	}

	protected $_has_bought_product_cache = [];
	public function has_bought_product( int $product_id ): bool {
		if ( isset( $this->_has_bought_product_cache[ $product_id ] ) ) {
			return $this->_has_bought_product_cache[ $product_id ];
		}

		global $wpdb;

		$sql = $wpdb->prepare( <<<SQL
			SELECT orders.id AS order_id FROM {$wpdb->prefix}vx_orders AS orders
			LEFT JOIN {$wpdb->prefix}vx_order_items AS order_items ON (
				orders.id = order_items.order_id
			)
			WHERE orders.customer_id = %d
				AND orders.status IN ('completed','sub_active')
				AND order_items.post_id = %d
			LIMIT 1
		SQL, $this->get_id(), $product_id );

		$result = $wpdb->get_var( $sql );

		$this->_has_bought_product_cache[ $product_id ] = is_numeric( $result );
		return is_numeric( $result );
	}

	public function has_bought_product_from_vendor( int $vendor_id ): bool {
		global $wpdb;

		$sql = $wpdb->prepare( <<<SQL
			SELECT orders.id AS order_id FROM {$wpdb->prefix}vx_orders AS orders
			WHERE orders.customer_id = %d
				AND orders.status IN ('completed','sub_active')
				AND orders.vendor_id = %d
			LIMIT 1
		SQL, $this->get_id(), $vendor_id );

		$result = $wpdb->get_var( $sql );

		return is_numeric( $result );
	}

	public function has_bought_product_from_platform(): bool {
		global $wpdb;

		$sql = $wpdb->prepare( <<<SQL
			SELECT orders.id AS order_id FROM {$wpdb->prefix}vx_orders AS orders
			WHERE orders.customer_id = %d
				AND orders.status IN ('completed','sub_active')
				AND orders.vendor_id IS NULL
			LIMIT 1
		SQL, $this->get_id() );

		$result = $wpdb->get_var( $sql );

		return is_numeric( $result );
	}

	/* Paddle */
	public static function get_by_paddle_customer_id( $customer_id ) {
		$meta_key = \Voxel\Modules\Paddle_Payments\Paddle_Client::is_test_mode() ? 'voxel:test_paddle_customer_id' : 'voxel:paddle_customer_id';
		$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

		$results = get_users( [
			'meta_key' => $meta_key,
			'meta_value' => $customer_id,
			'number' => 1,
			'fields' => 'ID',
		] );

		return \Voxel\User::get( array_shift( $results ) );
	}

	public function get_paddle_customer_id() {
		$meta_key = \Voxel\Modules\Paddle_Payments\Paddle_Client::is_test_mode() ? 'voxel:test_paddle_customer_id' : 'voxel:paddle_customer_id';
		$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

		return get_user_meta( $this->get_id(), $meta_key, true );
	}

	public function get_paddle_customer() {
		$customer_id = $this->get_paddle_customer_id();
		if ( empty( $customer_id ) ) {
			throw new \Exception( _x( 'Paddle customer account not set up for this user.', 'orders', 'voxel' ) );
		}

		$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
		return $paddle->customers->get( $customer_id );
	}

	public function get_or_create_paddle_customer() {
		try {
			$customer = $this->get_paddle_customer();
		} catch ( \Exception $e ) {
			$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
			$customer = $paddle->customers->create( new PaddleCreateCustomer(
				email: $this->get_email(),
				name: $this->get_display_name(),
			) );

			$meta_key = \Voxel\Modules\Paddle_Payments\Paddle_Client::is_test_mode() ? 'voxel:test_paddle_customer_id' : 'voxel:paddle_customer_id';
			$meta_key = \Voxel\get_site_specific_user_meta_key( $meta_key );

			update_user_meta( $this->get_id(), $meta_key, $customer->id );
		}

		return $customer;
	}
}
