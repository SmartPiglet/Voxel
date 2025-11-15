<?php

namespace Voxel\Events\Bookings;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Canceled_By_Customer_Event extends Booking_Confirmed_Event {

	public function get_key(): string {
		return sprintf( 'product-types/%s/bookings/booking:canceled_by_customer', $this->product_type->get_key() );
	}

	public function get_label(): string {
		return 'Booking canceled by customer';
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
					'subject' => 'You have canceled your booking',
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
					'subject' => 'You have canceled your booking',
					'message' => <<<HTML
					You have canceled your booking request on <strong>@post(:title)</strong><br>
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
					'subject' => '@customer(:display_name) canceled their booking',
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
					'subject' => '@customer(:display_name) canceled their booking',
					'message' => <<<HTML
					<strong>@customer(:display_name)</strong> canceled their booking request on <strong>@post(:title)</strong>.<br>
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
					'subject' => '@customer(:display_name) canceled their booking',
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
					'subject' => '@customer(:display_name) canceled their booking',
					'message' => <<<HTML
					<strong>@customer(:display_name)</strong> canceled their booking request on <strong>@post(:title)</strong>.<br>
					<a href="@order(:link)">Order details</a>
					HTML,
				],
			],
		];
	}
}
