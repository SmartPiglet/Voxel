<?php

namespace Voxel\Events\Promotions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Promotion_Canceled_Event extends Promotion_Activated_Event {

	public function get_key(): string {
		return 'promotions/promotion:canceled';
	}

	public function get_label(): string {
		return 'Promotion canceled';
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
					'subject' => 'Your promotion has been canceled',
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
					'subject' => 'Your promotion has been canceled',
					'message' => <<<HTML
					Your promotion for post <strong>@post(:title)</strong> has been canceled.<br>
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
					'subject' => 'Promotion canceled for post @post(:title)',
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
					'subject' => 'Promotion canceled for post @post(:title)',
					'message' => <<<HTML
					Promotion for post <strong>@post(:title)</strong> has been canceled.<br>
					<a href="@order(:link)">Order details</a>
					<a href="@post(:url)">View post</a>
					HTML,
				],
			],
		];
	}
}
