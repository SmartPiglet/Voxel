<?php

namespace Voxel\Product_Types\Cart;

use \Voxel\Utils\Config_Schema\Schema;
use Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Customer_Cart extends Base_Cart {

	public $customer;

	public function __construct( \Voxel\User $customer ) {
		$this->customer = $customer;
	}

	public function get_type(): string {
		return 'customer_cart';
	}

	public function update(): void {
		if ( empty( $this->items ) ) {
			delete_user_meta( $this->customer->get_id(), 'voxel:cart' );
		} else {
			$value = [
				'items' => [],
			];

			foreach ( $this->items as $item ) {
				$value['items'][ $item->get_key() ] = $item->get_value_for_storage();
			}

			update_user_meta( $this->customer->get_id(), 'voxel:cart', wp_slash( wp_json_encode( $value ) ) );
		}
	}

	public function sync() {
		$this->items = [];

		$meta_value = (array) json_decode( get_user_meta( $this->customer->get_id(), 'voxel:cart', true ), true );
		foreach ( (array) ( $meta_value['items'] ?? [] ) as $key => $item ) {
			try {
				$item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $item, $key );
				$this->add_item( $item );
			} catch ( \Exception $e ) {}
		}
	}

	public function get_customer_id(): ?int {
		return $this->customer->get_id();
	}

	public function get_payment_method(): ?string {
		$payment_service = Payment_Service::get_active();
		return $payment_service ? $payment_service->get_payment_handler() : null;
	}
}
