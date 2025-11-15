<?php

namespace Voxel\Product_Types\Order_Items;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Order_Item {
	use Traits\Query_Trait;

	protected
		$id,
		$order_id,
		$post_id,
		$product_type,
		$field_key,
		$details;

	protected function __construct( array $data ) {
		$this->id = absint( $data['id'] );
		$this->order_id = absint( $data['order_id'] );
		$this->post_id = absint( $data['post_id'] );
		$this->product_type = $data['product_type'];
		$this->field_key = $data['field_key'];
		$this->details = is_array( $data['details'] ) ? $data['details'] : (array) json_decode( (string) $data['details'], true );
	}

	abstract public function get_type(): string;

	public function get_id(): int {
		return $this->id;
	}

	public function get_order_id(): int {
		return $this->order_id;
	}

	public function set_order_id( int $order_id ): void {
		$this->order_id = $order_id;
	}

	public function get_order(): \Voxel\Product_Types\Orders\Order {
		return \Voxel\Product_Types\Orders\Order::get( $this->get_order_id() );
	}

	public function get_post_id(): int {
		return $this->post_id;
	}

	public function get_post(): ?\Voxel\Post {
		return \Voxel\Post::get( $this->post_id );
	}

	public function get_product_type_key(): string {
		return $this->product_type;
	}

	public function get_product_type(): ?\Voxel\Product_Type {
		return \Voxel\Product_Type::get( $this->product_type );
	}

	public function get_product_field_key(): string {
		return $this->field_key;
	}

	public function get_product_field(): ?\Voxel\Post_Types\Fields\Product_Field {
		$post = $this->get_post();
		if ( ! $post ) {
			return null;
		}

		$field = $post->get_field( $this->field_key );
		if ( ! ( $field && $field->get_type() === 'product' ) ) {
			return null;
		}

		return $field;
	}

	public function get_currency() {
		return $this->get_details( 'currency' );
	}

	public function get_quantity(): ?int {
		$quantity = $this->get_details( 'summary.quantity' );
		return is_numeric( $quantity ) ? absint( $quantity ) : null;
	}

	public function get_subtotal_per_unit() {
		$amount = $this->get_details( 'summary.amount_per_unit' );
		return is_numeric( $amount ) ? abs( $amount ) : null;
	}

	public function get_subtotal() {
		$amount = $this->get_details( 'summary.total_amount' );
		return is_numeric( $amount ) ? abs( $amount ) : null;
	}

	public function get_vendor_id(): ?int {
		$vendor_id = $this->get_details( 'vendor.id' );
		return is_numeric( $vendor_id ) ? absint( $vendor_id ) : null;
	}

	public function get_vendor(): ?\Voxel\User {
		return \Voxel\User::get( $this->get_vendor_id() );
	}

	protected $_summary_items_cache = null;
	public function get_summary_items(): array {
		if ( $this->_summary_items_cache === null ) {
			$this->_summary_items_cache = [];

			$summary = (array) $this->get_details( 'summary.summary', [] );
			foreach ( $summary as $item ) {
				if ( ! isset( $item['key'] ) ) {
					continue;
				}

				$this->_summary_items_cache[ $item['key'] ] = $item;
			}

		}

		return $this->_summary_items_cache;
	}

	public function has_summary_item( $item_key ): bool {
		return isset( $this->get_summary_items()[ $item_key ] );
	}

	public function get_summary_item( $item_key ) {
		return $this->get_summary_items()[ $item_key ] ?? null;
	}

	public function get_product_label() {
		return $this->get_details( 'product.label' );
	}

	public function get_product_description() {
		return '';
	}

	public function get_product_thumbnail_url() {
		$image_id = $this->get_details( 'product.image_id' );
		return wp_get_attachment_image_url( $image_id ) ?: null;
	}

	public function get_product_link() {
		$product = $this->get_post();
		$link = $product ? $product->get_link() : null;

		return apply_filters( 'voxel/order_item/product_link', $link, $this );
	}

	public function is_shippable(): bool {
		return !! $this->get_details( 'shipping.enabled' );
	}

	public function get_shipping_class(): ?\Voxel\Product_Types\Shipping\Shipping_Class {
		if ( ! $this->is_shippable() ) {
			return null;
		}

		return \Voxel\Product_Types\Shipping\Shipping_Class::get( $this->get_details( 'shipping.shipping_class.key' ) );
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

		$wpdb->update( $wpdb->prefix.'vx_order_items', [
			'order_id' => $this->order_id,
			'post_id' => $this->post_id,
			'product_type' => $this->product_type,
			'field_key' => $this->field_key,
			'details' => wp_json_encode( Schema::optimize_for_storage( $this->details ) ),
		], $where = [
			'id' => $this->id,
		] );
	}

	public function get_order_page_details(): array {
		return [
			//
		];
	}

	public function get_data_inputs_for_display(): array {
		$data_inputs = $this->get_details('data_inputs');
		if ( ! is_array( $data_inputs ) ) {
			return [];
		}

		$formatted = [];
		foreach ( $data_inputs as $data_input_key => $data_input ) {
			$type = $data_input['type'] ?? null;
			$label = $data_input['label'] ?? null;
			if ( empty( $type ) || empty( $label ) ) {
				continue;
			}

			if ( in_array( $type, [ 'text', 'textarea', 'url', 'email', 'phone' ], true ) ) {
				if ( ! is_string( $data_input['value'] ?? null ) ) {
					continue;
				}

				$content = esc_html( $data_input['value'] );
				if ( mb_strlen( $content ) > 0 ) {
					$formatted[] = [
						'type' => $type,
						'label' => $label,
						'content' => $content,
					];
				}
			} elseif ( $type === 'select' ) {
				if ( ! is_string( $data_input['value']['label'] ?? null ) ) {
					continue;
				}

				$content = esc_html( $data_input['value']['label'] );
				if ( mb_strlen( $content ) > 0 ) {
					$formatted[] = [
						'type' => $type,
						'label' => $label,
						'content' => $content,
					];
				}
			} elseif ( $type === 'multiselect' ) {
				if ( ! is_array( $data_input['value'] ?? null ) ) {
					continue;
				}

				$choices = [];
				foreach ( $data_input['value'] as $choice ) {
					if ( is_string( $choice['label'] ?? null ) && mb_strlen( $choice['label'] ) > 0 ) {
						$choices[] = $choice['label'];
					}
				}

				$content = esc_html( join( ', ', $choices ) );
				if ( mb_strlen( $content ) > 0 ) {
					$formatted[] = [
						'type' => $type,
						'label' => $label,
						'content' => $content,
					];
				}
			} elseif ( $type === 'number' ) {
				if ( ! is_numeric( $data_input['value'] ?? null ) ) {
					continue;
				}

				$formatted[] = [
					'type' => $type,
					'label' => $label,
					'content' => number_format_i18n( $data_input['value'] ),
				];
			} elseif ( $type === 'switcher' ) {
				if ( ! is_bool( $data_input['value'] ?? null ) ) {
					continue;
				}

				$formatted[] = [
					'type' => $type,
					'label' => $label,
					'content' => $data_input['value'] ? _x( 'Yes', 'switcher data input', 'voxel' ) : _x( 'No', 'switcher data input', 'voxel' ),
				];
			}
		}

		return $formatted;
	}

	public function delete() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}vx_order_items WHERE id = %d",
			$this->get_id()
		) );
	}

	public static function mock(): self {
		return new static( [
			'id' => null,
			'order_id' => null,
			'post_id' => null,
			'product_type' => null,
			'field_key' => null,
			'details' => null,
		] );
	}
}
