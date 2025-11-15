<?php

namespace Voxel\Events\Promotions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Promotion_Activated_Event extends \Voxel\Events\Base_Event {

	public $order, $order_item, $post, $customer;

	public function prepare( $order_item_id ) {
		$order_item = \Voxel\Product_Types\Order_Items\Order_Item::get( $order_item_id );
		if ( ! $order_item ) {
			throw new \Exception( 'Missing information.' );
		}

		$order = $order_item->get_order();
		$post = $order_item->get_post();
		if ( ! ( $order && $post ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$customer = $order->get_customer();
		if ( ! $customer ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->order_item = $order_item;
		$this->order = $order;
		$this->post = $post;
		$this->customer = $customer;
	}

	public function get_key(): string {
		return 'promotions/promotion:activated';
	}

	public function get_label(): string {
		return 'Promotion activated';
	}

	public function get_category() {
		return 'promotions';
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
					'subject' => 'Your post has been promoted',
					'details' => function( $event ) {
						return [
							'order_item_id' => $event->order_item->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['order_item_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->post->get_link(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your post has been promoted',
					'message' => <<<HTML
					Your post <strong>@post(:title)</strong> has been promoted.<br>
					<a href="@order(:link)">Order details</a>
					<a href="@post(:url)">View post</a>
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
					'subject' => '@customer(:display_name) promoted their post',
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
					'subject' => '@customer(:display_name) promoted their post',
					'message' => <<<HTML
					<strong>@customer(:display_name)</strong> promoted their post <strong>@post(:title)</strong>.<br>
					<a href="@order(:link)">Order details</a>
					<a href="@post(:url)">View post</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->order_item = \Voxel\Product_Types\Order_Items\Order_Item_Regular::mock();
		$this->customer = \Voxel\User::mock();
		$this->post = \Voxel\Post::mock();
		$this->order = \Voxel\Product_Types\Orders\Order::mock();
	}

	public function dynamic_tags(): array {
		return [
			'promotion' => \Voxel\Dynamic_Data\Group::Order_Item_Promotion( $this->order_item ),
			'customer' => \Voxel\Dynamic_Data\Group::User( $this->customer ),
			'post' => \Voxel\Dynamic_Data\Group::Simple_Post( $this->post ),
			'order' => \Voxel\Dynamic_Data\Group::Order( $this->order ),
		];
	}
}
