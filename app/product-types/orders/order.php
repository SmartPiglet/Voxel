<?php

namespace Voxel\Product_Types\Orders;

use \Voxel\Product_Types\Payment_Methods\Base_Payment_Method;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order {
	use Traits\Query_Trait;

	protected
		$id,
		$customer_id,
		$vendor_id,
		$status,
		$shipping_status,
		$payment_method,
		$transaction_id,
		$details,
		$parent_id,
		$testmode,
		$created_at,
		$items,
		$child_orders;

	protected function __construct( array $data ) {
		$this->id = absint( $data['id'] );
		$this->customer_id = absint( $data['customer_id'] );
		$this->vendor_id = is_numeric( $data['vendor_id'] ) ? absint( $data['vendor_id'] ) : null;
		$this->status = (string) $data['status'];
		$this->shipping_status = is_string( $data['shipping_status'] ) ? $data['shipping_status'] : null;
		$this->payment_method = (string) $data['payment_method'];
		$this->details = (array) json_decode( $data['details'], true );
		$this->parent_id = is_numeric( $data['parent_id'] ) ? absint( $data['parent_id'] ) : null;
		$this->testmode = !! ( $data['testmode'] ?? false );
		$this->created_at = \DateTime::createFromFormat( 'Y-m-d H:i:s', $data['created_at'] ) ?: new \DateTime( 'now', new \DateTimeZone('UTC') );
		$this->items = ! empty( $data['items'] ) ? array_map( 'absint', explode( ',', (string) $data['items'] ) ) : [];
		$this->child_orders = ! empty( $data['child_orders'] ) ? array_map( 'absint', explode( ',', (string) $data['child_orders'] ) ) : [];
		$this->transaction_id = (string) $data['transaction_id'];
		if ( empty( $this->transaction_id ) ) {
			$this->transaction_id = null;
		}
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_customer_id(): int {
		return $this->customer_id;
	}

	public function set_customer_id( int $customer_id ): void {
		$this->customer_id = $customer_id;
	}

	public function get_customer(): ?\Voxel\User {
		return \Voxel\User::get( $this->get_customer_id() );
	}

	public function get_vendor_id(): ?int {
		if ( $this->vendor_id === null ) {
			return \Voxel\get( 'settings.notifications.admin_user' );
		}

		return $this->vendor_id;
	}

	public function get_vendor(): ?\Voxel\User {
		return \Voxel\User::get( $this->get_vendor_id() );
	}

	public function has_vendor(): bool {
		return $this->vendor_id !== null;
	}

	public function get_status(): string {
		return $this->status;
	}

	protected $previous_status = null;
	public function set_status( string $status ): void {
		if ( $this->status !== $status ) {
			$this->previous_status = $this->status;
			$this->status = $status;
			$this->set_details( 'status.last_updated', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
		}
	}

	public function get_previous_status(): ?string {
		return $this->previous_status;
	}

	public function get_status_label(): string {
		$config = static::get_status_config();
		return $config[ $this->status ]['label'] ?? $this->status;
	}

	public function get_status_last_updated() {
		$last_updated = $this->get_details( 'status.last_updated' );
		if ( $last_updated === null ) {
			return $this->created_at;
		}

		return \DateTime::createFromFormat( 'Y-m-d H:i:s', $last_updated ) ?: $this->created_at;
	}

	public function get_status_last_updated_for_display() {
		$date = $this->get_status_last_updated();

		$from = $date->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );

		return sprintf( _x( '%s ago', 'order created at', 'voxel' ), human_time_diff( $from, $to ) );
	}

	public function get_status_long_label(): ?string {
		if ( $this->status === 'canceled' && $this->payment_method === 'stripe_payment' && $this->get_details( 'payment_intent.cancellation_reason' ) === 'requested_by_customer' ) {
			return _x( 'Order canceled by customer', 'orders', 'voxel' );
		}

		return null;
	}

	public function get_shipping_status(): ?string {
		if ( ! $this->should_handle_shipping() ) {
			return null;
		}

		if ( $this->shipping_status === null || ! isset( static::get_shipping_status_config()[ $this->shipping_status ] ) ) {
			return 'processing';
		}

		return $this->shipping_status;
	}

	protected $previous_shipping_status = null;
	public function set_shipping_status( ?string $shipping_status ): void {
		if ( $this->shipping_status !== $shipping_status ) {
			$this->previous_shipping_status = $this->shipping_status;
			$this->shipping_status = $shipping_status;
			$this->set_details( 'shipping.status_last_updated', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
		}
	}

	public function get_previous_shipping_status(): ?string {
		return $this->previous_shipping_status;
	}

	public function get_shipping_status_label(): ?string {
		$config = static::get_shipping_status_config();
		return $config[ $this->get_shipping_status() ]['label'] ?? null;
	}

	public function get_shipping_status_long_label(): ?string {
		$config = static::get_shipping_status_config();
		return $config[ $this->get_shipping_status() ]['long_label'] ?? null;
	}

	public function get_shipping_status_class(): ?string {
		$config = static::get_shipping_status_config();
		return $config[ $this->get_shipping_status() ]['class'] ?? null;
	}

	public function get_shipping_status_last_updated() {
		$last_updated = $this->get_details( 'shipping.status_last_updated' );
		if ( $last_updated === null ) {
			return null;
		}

		return \DateTime::createFromFormat( 'Y-m-d H:i:s', $last_updated ) ?: null;
	}

	public function get_shipping_status_last_updated_for_display() {
		if ( ! ( $date = $this->get_shipping_status_last_updated() ) ) {
			return null;
		}

		$from = $date->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );

		return sprintf( _x( '%s ago', 'order created at', 'voxel' ), human_time_diff( $from, $to ) );
	}

	public function is_test_mode(): bool {
		return $this->testmode;
	}

	public function get_created_at(): ?\DateTime {
		return $this->created_at;
	}

	public function get_created_at_for_display() {
		$from = $this->created_at->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );

		return sprintf( _x( '%s ago', 'order created at', 'voxel' ), human_time_diff( $from, $to ) );
	}

	public function get_currency() {
		return $this->get_details( 'pricing.currency' );
	}

	public function get_subtotal() {
		return $this->get_details( 'pricing.subtotal' );
	}

	public function get_total() {
		return $this->get_details( 'pricing.total' );
	}

	public function get_billing_interval(): ?array {
		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			return $payment_method->get_billing_interval();
		}

		return null;
	}

	public function get_tax_amount() {
		$tax_amount = $this->get_details( 'pricing.tax' );
		if ( ! is_numeric( $tax_amount ) || $tax_amount <= 0 ) {
			return null;
		}

		return $tax_amount;
	}

	public function get_discount_amount() {
		$discount_amount = $this->get_details( 'pricing.discount' );
		if ( ! is_numeric( $discount_amount ) || $discount_amount <= 0 ) {
			return null;
		}

		return $discount_amount;
	}

	public function get_shipping_amount() {
		if ( $parent_order = $this->get_parent_order() ) {
			$vendor_key = $this->has_vendor() ? sprintf( 'vendor_%d', $this->get_vendor_id() ) : 'platform';
			$vendor_shipping_amount = $parent_order->get_details( sprintf( 'shipping.amounts_by_vendor.%s.amount_in_cents', $vendor_key ) );
			if ( is_numeric( $vendor_shipping_amount ) ) {
				if ( $vendor_shipping_amount > 0 && ! \Voxel\Utils\Currency_List::is_zero_decimal( $this->get_currency() ) ) {
					$vendor_shipping_amount /= 100;
				}

				return $vendor_shipping_amount;
			}
		}

		if ( $this->get_details('shipping.method') === 'vendor_rates' ) {
			$vendor_amounts = (array) $this->get_details( 'shipping.amounts_by_vendor', [] );
			$vendor_amount_sum = 0;
			foreach ( $vendor_amounts as $vendor_data ) {
				if ( is_numeric( $vendor_data['amount_in_cents'] ?? null ) ) {
					$vendor_amount_sum += $vendor_data['amount_in_cents'];
				}
			}

			if ( $vendor_amount_sum > 0 && ! \Voxel\Utils\Currency_List::is_zero_decimal( $this->get_currency() ) ) {
				$vendor_amount_sum /= 100;
			}

			return $vendor_amount_sum;
		}

		$shipping_amount = $this->get_details( 'pricing.shipping' );
		if ( ! is_numeric( $shipping_amount ) || $shipping_amount <= 0 ) {
			return null;
		}

		return $shipping_amount;
	}

	public function get_transaction_id(): ?string {
		return $this->transaction_id;
	}

	public function set_transaction_id( string $transaction_id ): void {
		$this->transaction_id = $transaction_id;
		if ( empty( $this->transaction_id ) ) {
			$this->transaction_id = null;
		}
	}

	public function get_payment_method_key(): string {
		return $this->payment_method;
	}

	public function get_payment_method(): ?Base_Payment_Method {
		$methods = Base_Payment_Method::get_all();
		if ( ! isset( $methods[ $this->payment_method ] ) ) {
			return null;
		}

		$method_class = $methods[ $this->payment_method ];
		return new $method_class( $this );
	}

	public function set_payment_method( string $payment_method ): void {
		$this->payment_method = $payment_method;
		if ( empty( $this->payment_method ) ) {
			$this->payment_method = null;
		}
	}

	public function get_parent_order_id(): ?int {
		return $this->parent_id;
	}

	public function get_parent_order(): ?\Voxel\Product_Types\Orders\Order {
		return static::get( $this->get_parent_order_id() );
	}

	public function get_customer_details(): array {
		$details = [];

		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			$details = $payment_method->get_customer_details();
		}

		$details = apply_filters( 'voxel/order/customer_details', $details, $this );

		return $details;
	}

	public function get_shipping_details(): array {
		$details = [];

		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			if ( $payment_method->get_type() === 'stripe_transfer' ) {
				return $payment_method->get_shipping_details();
			} elseif ( $payment_method->get_type() === 'stripe_transfer_platform' ) {
				return $payment_method->get_shipping_details();
			}
		}

		$details = apply_filters( 'voxel/order/shipping_details', $details, $this );

		return $details;
	}

	public function get_vendor_fees_summary(): array {
		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			if ( $payment_method->get_type() === 'stripe_payment' ) {
				return $payment_method->get_vendor_fees_summary();
			} elseif ( $payment_method->get_type() === 'stripe_subscription' ) {
				return $payment_method->get_vendor_fees_summary();
			} elseif ( $payment_method->get_type() === 'stripe_transfer' ) {
				return $payment_method->get_vendor_fees_summary();
			}
		}

		return [];
	}

	public function get_link() {
		return add_query_arg( 'order_id', $this->id, get_permalink( \Voxel\get( 'templates.orders' ) ) );
	}

	public function get_success_redirect() {
		$redirect_to = $this->get_link();

		$custom_redirect = $this->get_meta('redirect_to');
		if ( ! empty( $custom_redirect ) && wp_validate_redirect( $custom_redirect ) ) {
			$redirect_to = wp_validate_redirect( $custom_redirect );
		}

		return apply_filters( 'voxel/order/success_redirect', $redirect_to, $this );
	}

	public function get_backend_link() {
		return add_query_arg( 'order_id', $this->id, admin_url( 'admin.php?page=voxel-orders' ) );
	}

	public function get_actions( \Voxel\User $user ): array {
		$actions = [];
		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			$vendor = $this->get_vendor();
			if ( $vendor && $vendor->get_id() === $user->get_id() ) {
				foreach ( $payment_method->get_vendor_actions() as $action ) {
					$action['action'] = sprintf( 'payments/%s/vendors/%s', $payment_method->get_type(), $action['action'] );
					$actions[] = $action;
				}
			}

			$customer = $this->get_customer();
			if ( $customer && $customer->get_id() === $user->get_id() ) {
				foreach ( $payment_method->get_customer_actions() as $action ) {
					$action['action'] = sprintf( 'payments/%s/customers/%s', $payment_method->get_type(), $action['action'] );
					$actions[] = $action;
				}
			}

			if ( current_user_can( 'administrator' ) ) {
				foreach ( $payment_method->get_admin_actions() as $action ) {
					$action['action'] = sprintf( 'payments/%s/admin/%s', $payment_method->get_type(), $action['action'] );
					$actions[] = $action;
				}
			}
		}

		return $actions;
	}

	public function has_shippable_products(): bool {
		foreach ( $this->get_items() as $item ) {
			if ( $item->is_shippable() ) {
				return true;
			}
		}

		return false;
	}

	public function has_shippable_products_from_vendor( ?int $vendor_id ): bool {
		foreach ( $this->get_items() as $item ) {
			if ( $item->is_shippable() && $item->get_vendor_id() === $vendor_id ) {
				return true;
			}
		}

		return false;
	}

	public function should_handle_shipping(): bool {
		$parent_order = $this->get_parent_order();

		if ( $parent_order = $this->get_parent_order() ) {
			if ( $this->has_shippable_products() && $parent_order->get_details('shipping.method') === 'vendor_rates' ) {
				return true;
			} else {
				return false;
			}
		} elseif ( $this->has_shippable_products() ) {
			return true;
		} elseif ( $this->get_details('shipping.method') === 'platform_rates' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get shipping zone for platform handled shipping, applied to all
	 * shippable order items.
	 *
	 * @since 1.4.1
	 */
	public function get_shipping_zone(): ?\Voxel\Product_Types\Shipping\Shipping_Zone {
		return \Voxel\Product_Types\Shipping\Shipping_Zone::get( $this->get_details( 'shipping.shipping_zone.key' ) );
	}

	/**
	 * Get shipping rate for platform handled shipping, applied to all
	 * shippable order items.
	 *
	 * @since 1.4.1
	 */
	public function get_shipping_rate(): ?\Voxel\Product_Types\Shipping\Rates\Base_Shipping_Rate {
		if ( ! ( $shipping_zone = $this->get_shipping_zone() ) ) {
			return null;
		}

		return $shipping_zone->get_rate( $this->get_details( 'shipping.shipping_rate.key' ) );
	}

	/**
	 * Get shipping rate for vendor handled shipping, applied to shippable
	 * order items belonging to the specified vendor.
	 *
	 * @since 1.4.1
	 */
	public function get_shipping_rate_for_vendor( int $vendor_id ): ?\Voxel\Product_Types\Shipping\Vendor_Rates\Vendor_Base_Shipping_Rate {
		if ( ! ( $vendor = \Voxel\User::get( $vendor_id ) ) ) {
			return null;
		}

		$zone_key = $this->get_details( sprintf( 'shipping.rates_by_vendor.vendor_%d.shipping_zone', $vendor->get_id() ) );
		if ( ! ( $shipping_zone = $vendor->get_vendor_shipping_zone( $zone_key ) ) ) {
			return null;
		}

		$rate_key = $this->get_details( sprintf( 'shipping.rates_by_vendor.vendor_%d.shipping_rate', $vendor->get_id() ) );
		return $shipping_zone->get_rate( $rate_key );
	}

	/**
	 * Get shipping rate for vendor handled shipping, applied to shippable
	 * order items belonging to the platform.
	 *
	 * @since 1.4.1
	 */
	public function get_shipping_rate_for_platform(): ?\Voxel\Product_Types\Shipping\Rates\Base_Shipping_Rate {
		$zone_key = $this->get_details( 'shipping.rates_by_vendor.platform.shipping_zone' );
		if ( ! ( $shipping_zone = \Voxel\Product_Types\Shipping\Shipping_Zone::get( $zone_key ) ) ) {
			return null;
		}

		$rate_key = $this->get_details( 'shipping.rates_by_vendor.platform.shipping_rate' );
		return $shipping_zone->get_rate( $rate_key );
	}

	public function get_shipping_country(): string {
		return $this->get_details( 'shipping.country' );
	}

	public function delete() {
		do_action( 'voxel/product-types/orders/order:before_delete', $this );

		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}vx_orders WHERE id = %d",
			$this->get_id()
		) );
	}

	public function get_item_ids(): array {
		return $this->items;
	}

	public function get_item_count(): int {
		return count( $this->items );
	}

	protected $_get_items_cache;
	public function get_items(): array {
		if ( $this->_get_items_cache === null ) {
			$this->_get_items_cache = [];
			foreach ( $this->items as $item_id ) {
				$item = \Voxel\Product_Types\Order_Items\Order_Item::get( $item_id );
				if ( $item !== null ) {
					$this->_get_items_cache[ $item->get_id() ] = $item;
				}
			}
		}

		return $this->_get_items_cache;
	}

	public function get_item( int $order_item_id ): ?\Voxel\Product_Types\Order_Items\Order_Item {
		return $this->get_items()[ $order_item_id ] ?? null;
	}

	protected $_get_child_orders_cache;
	public function get_child_orders(): array {
		if ( $this->_get_child_orders_cache === null ) {
			$this->_get_child_orders_cache = [];
			foreach ( $this->child_orders as $child_order_id ) {
				$child_order = static::get( $child_order_id );
				if ( $child_order !== null ) {
					$this->_get_child_orders_cache[ $child_order->get_id() ] = $child_order;
				}
			}
		}

		return $this->_get_child_orders_cache;
	}

	public function get_details( $setting_key = null, $default = null ) {
		$details = $this->details;

		if ( $setting_key !== null ) {
			$keys = explode( '.', $setting_key );
			foreach ( $keys as $key ) {
				if ( ! isset( $details[ $key ] ) ) {
					return $default;
				}

				$details = $details[ $key ];
			}
		}

		return $details;
	}

	public function set_details( string $setting_key, $value ) {
		$keys = explode( '.', $setting_key );
		$details = $this->details;
		$original_details = &$details;

		$last_index = count( $keys ) - 1;
		foreach ( $keys as $index => $key ) {
			if ( $index === $last_index ) {
				if ( $value === null ) {
					unset( $details[ $key ] );
				} else {
					$details[ $key ] = $value;
				}

				break;
			}

			if ( ! isset( $details[ $key ] ) ) {
				$details[ $key ] = [];
			}

			$details = &$details[ $key ];
		}

		$this->details = $original_details;
	}

	public function get_meta( $setting_key = null, $default = null ) {
		return $this->get_details( sprintf( 'meta.%s', $setting_key ), $default );
	}

	public function set_meta( string $setting_key, $value ) {
		$this->set_details( sprintf( 'meta.%s', $setting_key ), $value );
	}

	public function update_meta( string $setting_key, $value ) {
		$this->set_meta( $setting_key, $value );
	}

	public function delete_meta( string $setting_key ) {
		$this->set_meta( $setting_key, null );
	}

	public function save(): void {
		global $wpdb;

		$wpdb->update( $wpdb->prefix.'vx_orders', [
			'customer_id' => $this->customer_id,
			'vendor_id' => $this->vendor_id,
			'status' => $this->status,
			'shipping_status' => $this->shipping_status,
			'payment_method' => $this->payment_method,
			'transaction_id' => $this->transaction_id,
			'details' => wp_json_encode( Schema::optimize_for_storage( $this->details ) ),
			'parent_id' => $this->parent_id,
			'testmode' => $this->testmode ? 1 : 0,
			'created_at' => $this->created_at->format('Y-m-d H:i:s'),
		], $where = [
			'id' => $this->id,
		] );

		do_action( 'voxel/product-types/orders/order:updated', $this );
	}

	public static function create_from_cart( \Voxel\Product_Types\Cart\Base_Cart $cart, array $args = [] ): self {
		$items = $cart->get_items();
		if ( empty( $items ) ) {
			throw new \Exception( _x( 'No cart items added.', 'cart', 'voxel' ) );
		}

		if ( ! $cart->get_payment_method() ) {
			throw new \Exception( _x( 'No payment method available.', 'cart', 'voxel' ) );
		}

		global $wpdb;

		$details = [
			'cart' => [
				'type' => $cart->get_type(),
				'items' => array_map( function( $item ) {
					return $item->get_value_for_storage();
				}, $cart->get_items() ),
			],
			'pricing' => [
				'currency' => $cart->get_currency(),
				'subtotal' => $cart->get_subtotal(),
			],
			'order_notes' => $cart->get_order_notes(),
			'meta' => [],
		];

		if ( isset( $args['meta'] ) && is_array( $args['meta'] ) ) {
			foreach ( $args['meta'] as $order_meta_key => $order_meta_value ) {
				$details['meta'][ $order_meta_key ] = $order_meta_value;
			}
		}

		if ( $cart->has_shippable_products() ) {
			if ( $cart->get_shipping_method() === 'vendor_rates' ) {
				$details['shipping'] = [
					'method' => 'vendor_rates',
					'country' => $cart->get_shipping_country(),
					'rates_by_vendor' => array_map( function( $data ) {
						return [
							'shipping_zone' => $data['zone']->get_key(),
							'shipping_rate' => $data['rate']->get_key(),
						];
					}, $cart->get_shipping_rates_by_vendor() ),
				];
			} else {
				$shipping_zone = $cart->get_shipping_zone();
				$shipping_rate = $cart->get_shipping_rate();
				$details['shipping'] = [
					'method' => 'platform_rates',
					'country' => $cart->get_shipping_country(),
					'shipping_zone' => [
						'key' => $shipping_zone->get_key(),
					],
					'shipping_rate' => [
						'type' => $shipping_rate->get_type(),
						'key' => $shipping_rate->get_key(),
					],
				];
			}
		}

		$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
			'customer_id' => $cart->get_customer_id(),
			'vendor_id' => $cart->get_vendor_id(),
			'status' => 'pending_payment',
			'shipping_status' => null,
			'payment_method' => $cart->get_payment_method(),
			'transaction_id' => null,
			'details' => wp_json_encode( Schema::optimize_for_storage( $details ) ),
			'parent_id' => null,
			'testmode' => \Voxel\is_test_mode() ? 1 : 0,
			'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		] );

		if ( $result === false ) {
			throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ) );
		}

		$order_id = $wpdb->insert_id;

		foreach ( $items as $item ) {
			$result = $wpdb->insert( $wpdb->prefix.'vx_order_items', [
				'order_id' => $order_id,
				'post_id' => $item->get_post()->get_id(),
				'product_type' => $item->get_product_type()->get_key(),
				'field_key' => $item->get_product_field()->get_key(),
				'details' => wp_json_encode( Schema::optimize_for_storage( $item->get_order_item_config() ) ),
			] );
		}

		return static::get( $order_id );
	}

	public function get_cart(): \Voxel\Product_Types\Cart\Base_Cart {
		$cart_type = $this->get_details( 'cart.type' );

		if ( $cart_type === 'direct_cart' ) {
			$cart = new \Voxel\Product_Types\Cart\Direct_Cart();
		} else {
			$cart = new \Voxel\Product_Types\Cart\Customer_Cart( $this->get_customer() );
		}

		foreach ( (array) ( $this->get_details('cart.items') ?? [] ) as $key => $item ) {
			$item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $item, $key );
			$cart->add_item( $item );
		}

		return $cart;
	}

	public function get_order_notes(): ?string {
		$notes = $this->get_details( 'order_notes' );
		if ( ! is_string( $notes ) || empty( $notes ) ) {
			return null;
		}

		$notes = esc_html( $notes );
		$notes = links_add_target( make_clickable( $notes ) );

		return $notes;
	}

	public function get_notes_to_customer(): ?string {
		$payment_method = $this->get_payment_method();
		if ( $payment_method !== null ) {
			if ( $payment_method->get_type() === 'offline_payment' ) {
				return $payment_method->get_notes_to_customer();
			}
		}

		return null;
	}

	public static function get_status_config(): array {
		return [
			'completed' => [
				'label' => _x( 'Approved', 'order status', 'voxel' ),
				'long_label' => _x( 'Order is approved', 'order long status', 'voxel' ),
				'class' => 'vx-green',
			],
			'pending_payment' => [
				'label' => _x( 'Pending payment', 'order status', 'voxel' ),
				'long_label' => _x( 'Order is pending payment', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'pending_approval' => [
				'label' => _x( 'Pending approval', 'order status', 'voxel' ),
				'long_label' => _x( 'Order is pending approval', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'canceled' => [
				'label' => _x( 'Canceled', 'order status', 'voxel' ),
				'long_label' => _x( 'Order declined', 'order long status', 'voxel' ),
				'class' => 'vx-red',
			],
			'refunded' => [
				'label' => _x( 'Refunded', 'order status', 'voxel' ),
				'long_label' => _x( 'Order has been refunded', 'order long status', 'voxel' ),
				'class' => 'vx-red',
			],
			'sub_active' => [
				'label' => _x( 'Active', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is active', 'order long status', 'voxel' ),
				'class' => 'vx-green',
			],
			'sub_trialing' => [
				'label' => _x( 'Trialing', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is active', 'order long status', 'voxel' ),
				'class' => 'vx-neutral',
			],
			'sub_incomplete' => [
				'label' => _x( 'Incomplete', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription incomplete', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'sub_incomplete_expired' => [
				'label' => _x( 'Expired', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription expired', 'order long status', 'voxel' ),
				'class' => 'vx-red',
			],
			'sub_past_due' => [
				'label' => _x( 'Past due', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is past due', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'sub_canceled' => [
				'label' => _x( 'Canceled', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is canceled', 'order long status', 'voxel' ),
				'class' => 'vx-red',
			],
			'sub_unpaid' => [
				'label' => _x( 'Unpaid', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is unpaid', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'sub_paused' => [
				'label' => _x( 'Paused', 'order status', 'voxel' ),
				'long_label' => _x( 'Subscription is paused', 'order long status', 'voxel' ),
				'class' => 'vx-orange',
			],
		];
	}

	public static function get_shipping_status_config(): array {
		return [
			'processing' => [
				'label' => _x( 'Processing', 'order shipping status', 'voxel' ),
				'long_label' => _x( 'Order is being processed', 'order shipping status', 'voxel' ),
				'class' => 'vx-orange',
			],
			'shipped' => [
				'label' => _x( 'Shipped', 'order shipping status', 'voxel' ),
				'long_label' => _x( 'Order has been shipped', 'order shipping status', 'voxel' ),
				'class' => 'vx-green',
			],
			'delivered' => [
				'label' => _x( 'Delivered', 'order shipping status', 'voxel' ),
				'long_label' => _x( 'Order has been delivered', 'order shipping status', 'voxel' ),
				'class' => 'vx-green',
			],
		];
	}

	public function sync( bool $blocking = true ): void {
		if ( $payment_method = $this->get_payment_method() ) {
			if ( $blocking ) {
				$payment_method->sync();
			} else {
				\Voxel\Utils\Async_Requests\Sync_Order::instance()->data( [
					'order_id' => $this->get_id(),
				] )->dispatch();
			}
		}
	}

	public static function mock(): self {
		return new static( [
			'id' => null,
			'customer_id' => null,
			'vendor_id' => null,
			'status' => null,
			'shipping_status' => null,
			'payment_method' => null,
			'details' => '',
			'created_at' => \Voxel\now()->format('Y-m-d H:i:s'),
			'transaction_id' => null,
			'parent_id' => null,
		] );
	}
}
