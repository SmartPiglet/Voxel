<?php

namespace Voxel\Product_Types\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Offline_Payment extends Base_Payment_Method {

	public function get_type(): string {
		return 'offline_payment';
	}

	public function get_label(): string {
		return _x( 'Offline payment', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		if ( $this->get_order_approval() === 'manual' ) {
			$this->order->set_status( \Voxel\ORDER_PENDING_APPROVAL );
		} else {
			$this->order->set_status( \Voxel\ORDER_COMPLETED );
		}

		$this->order->set_details( 'pricing.total', $this->order->get_details( 'pricing.subtotal' ) );
		$this->order->set_transaction_id( sprintf( 'offline_%d', $this->order->get_id() ) );

		$this->order->save();

		return [
			'success' => true,
			'redirect_url' => $this->order->get_success_redirect(),
		];
	}

	public function get_order_approval() {
		if ( count( $this->order->get_items() ) === 1 ) {
			foreach ( $this->order->get_items() as $item ) {
				if ( $product_type = $item->get_product_type() ) {
					$approval = $product_type->config('settings.payments.mode_offline.order_approval');
					if ( in_array( $approval, [ 'manual', 'automatic' ], true ) ) {
						return $approval;
					}
				}

				if ( $item->get_product_field_key() === 'voxel:promotion' ) {
					$approval = \Voxel\get( 'paid_listings.settings.promotions.order_approval', 'automatic' );
					return $approval === 'manual' ? 'manual' : 'automatic';
				} elseif ( $item->get_product_field_key() === 'voxel:listing_plan' ) {
					return 'automatic';
				}
			}
		}

		return \Voxel\get( 'product_settings.offline_payments.order_approval', 'automatic' ) === 'manual' ? 'manual' : 'automatic';
	}

	public function get_vendor_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'vendor.approve',
				'label' => _x( 'Approve', 'order actions', 'voxel' ),
				'type' => 'primary',
				'handler' => function() {
					$this->order->set_status( \Voxel\ORDER_COMPLETED );
					$this->order->save();

					( new \Voxel\Events\Products\Orders\Vendor_Approved_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'vendor.decline',
				'label' => _x( 'Decline', 'order actions', 'voxel' ),
				'handler' => function() {
					$this->order->set_status( \Voxel\ORDER_CANCELED );
					$this->order->save();

					( new \Voxel\Events\Products\Orders\Vendor_Declined_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		return $actions;
	}

	public function get_customer_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'customer.cancel',
				'label' => _x( 'Cancel order', 'order customer actions', 'voxel' ),
				'handler' => function() {
					$this->order->set_status( \Voxel\ORDER_CANCELED );
					$this->order->save();

					( new \Voxel\Events\Products\Orders\Customer_Canceled_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		return $actions;
	}

	public function get_notes_to_customer(): ?string {
		if ( ! \Voxel\get( 'product_settings.offline_payments.notes_to_customer.enabled' ) ) {
			return null;
		}

		$content = \Voxel\get( 'product_settings.offline_payments.notes_to_customer.content' );
		if ( ! is_string( $content ) || empty( $content ) ) {
			return null;
		}

		$content = \Voxel\render( $content, [
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->order->get_customer() ),
			'vendor' => \Voxel\Dynamic_Data\Group::User( $this->order->get_vendor() ),
			'site' => \Voxel\Dynamic_Data\Group::Site(),
		] );

		$content = esc_html( $content );
		$content = links_add_target( make_clickable( $content ) );

		return $content;
	}
}
