<?php

namespace Voxel\Events\Bookings;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Placed_Event extends \Voxel\Events\Base_Event {

	public $product_type;

	public $order, $order_item, $post, $customer, $vendor;

	public function __construct( \Voxel\Product_Type $product_type ) {
		$this->product_type = $product_type;
	}

	public function prepare( $order_item_id ) {
		$order_item = \Voxel\Product_Types\Order_Items\Order_Item::get( $order_item_id );
		if ( ! ( $order_item && $order_item->get_type() === 'booking' ) ) {
			throw new \Exception( 'Missing information.', 101 );
		}

		$order = $order_item->get_order();
		$post = $order_item->get_post();
		if ( ! ( $order && $post ) ) {
			throw new \Exception( 'Missing information.', 102 );
		}

		$vendor = $order_item->get_vendor() ?? $order->get_vendor();
		$customer = $order->get_customer();
		if ( ! ( $customer && $vendor ) ) {
			throw new \Exception( 'Missing information.', 103 );
		}

		$this->order_item = $order_item;
		$this->order = $order;
		$this->post = $post;
		$this->customer = $customer;
		$this->vendor = $vendor;
	}

	public function get_key(): string {
		return sprintf( 'product-types/%s/bookings/booking:placed', $this->product_type->get_key() );
	}

	public function get_label(): string {
		return 'Booking placed';
	}

	public function get_category() {
		return sprintf( 'product-type:%s', $this->product_type->get_key() );
	}

	public static function notifications(): array {
		return [
			'customer' => [
				'label' => 'Notify customer',
				'recipient' => function( $event ) {
					return $event->customer;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'Your booking request has been submitted',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your booking request has been submitted',
					'message' => <<<HTML
					Your booking request on <strong>@post(:title)</strong> has been submitted.<br>
					Booking details: @booking(order_summary)<br>
					<a href="@order(:link)">Order details</a>
					HTML,
				],
			],
			'vendor' => [
				'label' => 'Notify vendor',
				'recipient' => function( $event ) {
					return $event->vendor;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'New booking request by @customer(:display_name)',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->customer->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'New booking request by @customer(:display_name)',
					'message' => <<<HTML
					<strong>@customer(:display_name)</strong> placed a booking request on <strong>@post(:title)</strong><br>
					Booking details: @booking(order_summary)<br>
					<a href="@order(:link)">Order details</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'New booking request by @customer(:display_name)',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->order->get_link(); },
					'image_id' => function( $event ) { return $event->customer->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'New booking request by @customer(:display_name)',
					'message' => <<<HTML
					<strong>@customer(:display_name)</strong> placed a booking request on <strong>@post(:title)</strong><br>
					Booking details: @booking(order_summary)<br>
					<a href="@order(:link)">Order details</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->order_item = \Voxel\Product_Types\Order_Items\Order_Item_Booking::mock();
		$this->customer = \Voxel\User::mock();
		$this->vendor = \Voxel\User::mock();
		$this->post = \Voxel\Post::mock();
		$this->order = \Voxel\Product_Types\Orders\Order::mock();
	}

	public function dynamic_tags(): array {
		return [
			'booking' => \Voxel\Dynamic_Data\Group::Order_Item_Booking( $this->order_item ),
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->customer ),
			'vendor' => \Voxel\Dynamic_Data\Group::User( $this->vendor ),
			'post' => \Voxel\Dynamic_Data\Group::Simple_Post( $this->post ),
			'order' => \Voxel\Dynamic_Data\Group::Order( $this->order ),
		];
	}
}
