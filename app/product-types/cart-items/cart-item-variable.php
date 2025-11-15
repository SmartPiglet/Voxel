<?php

namespace Voxel\Product_Types\Cart_Items;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Item_Variable extends Cart_Item {

	public function get_type(): string {
		return 'variable';
	}

	public function get_selected_variation_config() {
		$variations = $this->product_field->get_value()['variations']['variations'];
		return $variations[ $this->value['variations']['variation_id'] ];
	}

	public function get_subtitle(): string {
		$variations = $this->get_form_field('form-variations');
		$variation = $this->get_selected_variation_config();

		return join( ', ', array_filter( array_map( function( $attribute ) use ( $variation ) {
			$choices = $attribute->get_choices();
			if ( ! isset( $choices[ $variation['attributes'][ $attribute->get_key() ] ] ) ) {
				return null;
			}

			return sprintf( '%s: %s', $attribute->get_label(), $choices[ $variation['attributes'][ $attribute->get_key() ] ]['label'] );
		}, $variations->get_active_attributes() ) ) );
	}

	public function get_image_id(): ?int {
		$variations = $this->get_form_field('form-variations');
		$variation = $this->get_selected_variation_config();

		if ( $variation['image'] !== null ) {
			$image = wp_get_attachment_image( $variation['image'] );

			if ( ! empty( $image ) ) {
				return $variation['image'];
			}
		}

		return parent::get_image_id();
	}

	public function get_stock_quantity() {
		return $this->value['variations']['quantity'] ?? 0;
	}

	public function set_stock_quantity( int $quantity ) {
		$this->value['variations']['quantity'] = $quantity;
	}

	public function get_max_stock_quantity() {
		$variation = $this->get_selected_variation_config();
		return $variation['config']['stock']['quantity'] ?? 0;
	}

	public function get_frontend_config(): array {
		$config = parent::get_frontend_config();

		if ( $stock = $this->product_field->get_product_field('variations')->get_variation_field('stock') ) {
			$variation = $this->get_selected_variation_config();

			if ( $variation['config']['stock']['enabled'] && ! $variation['config']['stock']['sold_individually'] ) {
				$config['quantity'] = [
					'enabled' => true,
					'max' => $variation['config']['stock']['quantity'],
				];
			}
		}

		return $config;
	}

	public function get_pricing_summary(): array {
		$value = $this->get_value();

		$summary = [];

		$variations = $this->product_field->get_product_field('variations');
		$variation = $this->get_selected_variation_config();

		$amount = $variation['config']['base_price']['discount_amount'] ?? $variation['config']['base_price']['amount'];
		$summary[] = [
			'key' => 'base_price',
			'amount' => $amount,
		];

		$total_amount = 0;
		foreach ( $summary as $summary_item ) {
			$total_amount += $summary_item['amount'];
		}

		$amount_per_unit = $total_amount;
		$quantity = null;
		if ( $stock = $variations->get_variation_field('stock') ) {
			$quantity = $value['variations']['quantity'];
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

		$selected_variation = $this->get_selected_variation_config();
		$variation_data = [
			'attributes' => [],
			'variation_id' => $this->value['variations']['variation_id'],
			'sku' => $selected_variation['config']['stock']['sku'] ?? null,
		];

		foreach ( $this->get_form_field('form-variations')->get_active_attributes() as $attribute ) {
			$choices = $attribute->get_choices();
			if ( ! isset( $choices[ $selected_variation['attributes'][ $attribute->get_key() ] ] ) ) {
				continue;
			}

			$variation_data['attributes'][ $attribute->get_key() ] = [
				'attribute' => [
					'key' => $attribute->get_key(),
					'label' => $attribute->get_label(),
				],
				'value' => [
					'key' => $selected_variation['attributes'][ $attribute->get_key() ],
					'label' => $choices[ $selected_variation['attributes'][ $attribute->get_key() ] ]['label'],
				],
			];
		}

		$config['variation'] = $variation_data;

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
