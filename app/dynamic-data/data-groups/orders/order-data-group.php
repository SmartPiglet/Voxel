<?php

namespace Voxel\Dynamic_Data\Data_Groups\Orders;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {

	public function get_type(): string {
		return 'order';
	}

	protected static $instances = [];
	public static function get( \Voxel\Product_Types\Orders\Order $order ): self {
		if ( ! array_key_exists( $order->get_id(), static::$instances ) ) {
			static::$instances[ $order->get_id() ] = new static( $order );
		}

		return static::$instances[ $order->get_id() ];
	}

	public $order;
	protected function __construct( \Voxel\Product_Types\Orders\Order $order ) {
		$this->order = $order;
	}

	protected function properties(): array {
		return [
			'id' => Tag::Number('ID')->render( function() {
				return $this->order->get_id();
			} ),
			'created_at' => Tag::Date('Date created')->render( function() {
				$created_at = $this->order->get_created_at();
				return $created_at !== null ? $created_at->format( 'Y-m-d H:i:s' ) : '';
			} ),
			'link' => Tag::URL('Permalink')->render( function() {
				return $this->order->get_link();
			} ),
			'pricing' => Tag::Object('Pricing')->properties( function() {
				return [
					'total' => Tag::String('Total amount')->render( function() {
						$total = $this->order->get_total();
						if ( is_numeric( $total ) ) {
							return \Voxel\currency_format( $total, $this->order->get_currency(), false );
						}
					} ),
					'subtotal' => Tag::String('Subtotal')->render( function() {
						$subtotal = $this->order->get_subtotal();
						if ( is_numeric( $subtotal ) ) {
							return \Voxel\currency_format( $subtotal, $this->order->get_currency(), false );
						}
					} ),
					'tax' => Tag::String('Tax amount')->render( function() {
						$tax = $this->order->get_tax_amount();
						if ( is_numeric( $tax ) ) {
							return \Voxel\currency_format( $tax, $this->order->get_currency(), false );
						}
					} ),
					'discount' => Tag::String('Discount amount')->render( function() {
						$discount = $this->order->get_discount_amount();
						if ( is_numeric( $discount ) ) {
							return \Voxel\currency_format( $discount, $this->order->get_currency(), false );
						}
					} ),
					'shipping' => Tag::String('Shipping amount')->render( function() {
						$shipping = $this->order->get_shipping_amount();
						if ( is_numeric( $shipping ) ) {
							return \Voxel\currency_format( $shipping, $this->order->get_currency(), false );
						}
					} ),
				];
			} ),
			'status' => Tag::Object('Status')->properties( function() {
				return [
					'key' => Tag::String('Key')->render( function() {
						return $this->order->get_status();
					} ),
					'label' => Tag::String('Label')->render( function() {
						return $this->order->get_status_label();
					} ),
				];
			} ),
			'shipping' => Tag::Object('Shipping')->properties( function() {
				return [
					'status' => Tag::Object('Shipping status')->properties( function() {
						return [
							'key' => Tag::String('Key')->render( function() {
								if ( $this->order->should_handle_shipping() ) {
									return $this->order->get_shipping_status();
								}
							} ),
							'label' => Tag::String('Label')->render( function() {
								if ( $this->order->should_handle_shipping() ) {
									return $this->order->get_shipping_status_label();
								}
							} ),
						];
					} ),
					'tracking_link' => Tag::URL('Tracking link')->render( function() {
						if ( $this->order->should_handle_shipping() ) {
							return $this->order->get_details('shipping.tracking_details.link');
						}
					} ),
					'shipping_rate' => Tag::Object('Shipping rate')->properties( function() {
						return [
							'label' => Tag::String('Label')->render( function() {
								if ( $this->order->should_handle_shipping() ) {
									if ( $this->order->get_details('shipping.method') === 'vendor_rates' ) {
										if ( $this->order->has_vendor() ) {
											$shipping_rate = $this->order->get_shipping_rate_for_vendor( $this->order->get_vendor_id() );
										} else {
											$shipping_rate = $this->order->get_shipping_rate_for_platform();
										}
									} else {
										$shipping_rate = $this->order->get_shipping_rate();
									}

									if ( $shipping_rate ) {
										return $shipping_rate->get_label();
									}
								}
							} ),
							'delivery_estimate' => Tag::String('Delivery estimate')->render( function() {
								if ( $this->order->should_handle_shipping() ) {
									if ( $this->order->get_details('shipping.method') === 'vendor_rates' ) {
										if ( $this->order->has_vendor() ) {
											$shipping_rate = $this->order->get_shipping_rate_for_vendor( $this->order->get_vendor_id() );
										} else {
											$shipping_rate = $this->order->get_shipping_rate_for_platform();
										}
									} else {
										$shipping_rate = $this->order->get_shipping_rate();
									}

									if ( $shipping_rate ) {
										return $shipping_rate->get_delivery_estimate_message();
									}
								}
							} ),
						];
					} ),
				];
			} ),
			'customer_notes' => Tag::String('Order notes')->render( function() {
				return $this->order->get_order_notes();
			} ),
		];
	}

	protected function aliases(): array {
		return [
			':id' => 'id',
			':created_at' => 'created_at',
			':link' => 'link',
			':pricing' => 'pricing',
			':status' => 'status',
			':shipping' => 'shipping',
			':customer_notes' => 'customer_notes',
		];
	}

	public static function mock(): self {
		return new static( \Voxel\Product_Types\Orders\Order::mock() );
	}
}
