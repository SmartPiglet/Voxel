<?php

namespace Voxel\Events\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Order_Event extends \Voxel\Events\Base_Event {

	static $default_enabled = [
		'customer' => false,
		'vendor' => false,
		'admin' => false,
	];

	public $order, $customer, $vendor;

	public function prepare( $order_id ) {
		$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
		if ( ! ( $order && $order->get_customer() && $order->get_vendor() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->order = $order;
		$this->customer = $order->get_customer();
		$this->vendor = $order->get_vendor();
	}

	public function get_category() {
		return 'orders';
	}

	abstract public static function get_customer_subject();
	abstract public static function get_customer_message();

	abstract public static function get_vendor_subject();
	abstract public static function get_vendor_message();

	abstract public static function get_admin_subject();
	abstract public static function get_admin_message();

	public static function notifications(): array {
		return [
			'customer' => [
				'label' => 'Notify customer',
				'recipient' => function( $event ) {
					return $event->customer;
				},
				'inapp' => [
					'enabled' => static::$default_enabled['customer'],
					'subject' => static::get_customer_subject(),
					'details' => function( $event ) {
						return [
							'order_id' => $event->order->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => null,
				],
				'email' => [
					'enabled' => false,
					'subject' => static::get_customer_subject(),
					'message' => static::get_customer_message(),
				],
			],
			'vendor' => [
				'label' => 'Notify vendor',
				'recipient' => function( $event ) {
					return $event->vendor;
				},
				'inapp' => [
					'enabled' => static::$default_enabled['vendor'],
					'subject' => static::get_vendor_subject(),
					'details' => function( $event ) {
						return [
							'order_id' => $event->order->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->customer->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => static::get_vendor_subject(),
					'message' => static::get_vendor_message(),
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => static::$default_enabled['admin'],
					'subject' => static::get_admin_subject(),
					'details' => function( $event ) {
						return [
							'order_id' => $event->order->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->customer->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => static::get_admin_subject(),
					'message' => static::get_admin_message(),
				],
			],
		];
	}

	public function set_mock_props() {
		$this->customer = \Voxel\User::mock();
		$this->vendor = \Voxel\User::mock();
		$this->order = \Voxel\Product_Types\Orders\Order::mock();
	}

	public function dynamic_tags(): array {
		return [
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->customer ),
			'vendor' => \Voxel\Dynamic_Data\Group::User( $this->vendor ),
			'order' => \Voxel\Dynamic_Data\Group::Order( $this->order ),
		];
	}
}
