<?php

namespace Voxel\Product_Types\Cart_Items;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Item_Regular extends Cart_Item {

	public function get_type(): string {
		return 'regular';
	}

	public function get_subtitle(): string {
		$subtitle = [];
		if ( $subscription_interval = $this->product_field->get_product_field('subscription-interval') ) {
			$interval = $this->product_field->get_value()['subscription'];
			if ( $formatted_interval = \Voxel\interval_format( $interval['unit'], $interval['frequency'] ) ) {
				$subtitle[] = \Voxel\replace_vars( _x( 'Renews @interval', 'subscription interval', 'voxel' ), [
					'@interval' => $formatted_interval,
				] );
			}
		}

		if ( $addons = $this->get_form_field('form-addons') ) {
			$subtitle[] = $addons->get_selection_summary( $this->value );
		}

		return join( ', ', $subtitle );
	}

	public function get_stock_quantity() {
		return $this->value['stock']['quantity'] ?? 0;
	}

	public function get_max_stock_quantity() {
		return $this->product_field->get_value()['stock']['quantity'] ?? 0;
	}

	public function set_stock_quantity( int $quantity ) {
		$this->value['stock']['quantity'] = $quantity;
	}

	public function get_frontend_config(): array {
		$config = parent::get_frontend_config();

		if ( $stock = $this->product_field->get_product_field('stock') ) {
			$stock_config = $this->product_field->get_value()['stock'];
			if ( $stock_config['enabled'] && ! $stock_config['sold_individually'] ) {
				$config['quantity'] = [
					'enabled' => true,
					'max' => $stock_config['quantity'],
				];
			}
		}

		return $config;
	}

	public function get_pricing_summary(): array {
		$config = $this->product_field->get_value();
		$value = $this->get_value();

		$summary = [];

		if ( $this->product_field->get_key() === 'voxel:promotion' ) {
			$promotion_package = $this->post->promotions->get_available_packages()[ $value['promotion_package'] ];
			$summary[] = [
				'key' => 'promotion_package',
				'amount' => $promotion_package->get_price_amount(),
			];
		}

		if ( $base_price = $this->product_field->get_product_field('base-price') ) {
			$date = ( new \DateTime( 'now', $this->product_field->get_post()->get_timezone() ) );
			$custom_price = $this->product_field->get_custom_price_for_date( $date );
			if ( $custom_price !== null ) {
				$amount = $custom_price['prices']['base_price']['discount_amount'] ?? $custom_price['prices']['base_price']['amount'];
			} else {
				$amount = $config['base_price']['discount_amount'] ?? $config['base_price']['amount'];
			}

			$summary[] = [
				'key' => 'base_price',
				'amount' => $amount,
			];
		}

		if ( $addons = $this->get_form_field('form-addons') ) {
			$summary[] = $addons->get_pricing_summary( $this->value );
		}

		$total_amount = 0;
		foreach ( $summary as $summary_item ) {
			$total_amount += $summary_item['amount'];
		}

		$amount_per_unit = $total_amount;
		$quantity = null;
		if ( $stock = $this->product_field->get_product_field('stock') ) {
			$quantity = $value['stock']['quantity'];
			$total_amount *= $quantity;
		}

		return [
			'quantity' => $quantity,
			'amount_per_unit' => $quantity !== null ? $amount_per_unit : null,
			'summary' => $summary,
			'total_amount' => $total_amount,
		];
	}

	public function get_order_item_config() {
		$config = parent::get_order_item_config();

		if ( $this->product_field->get_key() === 'voxel:promotion' ) {
			$promotion_package = $this->post->promotions->get_available_packages()[ $this->value['promotion_package'] ];
			$config['promotion_package'] = [
				'key' => $promotion_package->get_key(),
				'duration' => [
					'type' => $promotion_package->get_duration_type(),
					'amount' => $promotion_package->get_duration_amount(),
				],
				'priority' => $promotion_package->get_priority(),
			];
		}

		if ( $stock = $this->product_field->get_product_field('stock') ) {
			$config['sku'] = $this->product_field->get_value()['stock']['sku'] ?? null;
		}

		return $config;
	}

	public function is_shippable(): bool {
		$shipping = $this->product_field->get_product_field( 'shipping' );
		if ( ! $shipping ) {
			return false;
		}

		$config = $this->product_field->get_value();
		if ( ! ( $this->product_type->config('modules.shipping.required') || $config['shipping']['enabled'] ) ) {
			return false;
		}

		return true;
	}

	public function get_shipping_class_key(): ?string {
		if ( ! $this->is_shippable() ) {
			return null;
		}

		$config = $this->product_field->get_value();
		if ( $shipping_class = \Voxel\Product_Types\Shipping\Shipping_Class::get( $config['shipping']['shipping_class'] ) ) {
			return $shipping_class->get_key();
		}

		if ( $shipping_class = \Voxel\Product_Types\Shipping\Shipping_Class::get( $this->product_type->config('modules.shipping.default_shipping_class') ) ) {
			return $shipping_class->get_key();
		}

		return null;
	}
}
