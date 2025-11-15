<?php

namespace Voxel\Events\Bookings;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Booking_Canceled_By_Vendor_Event extends Booking_Confirmed_Event {

	public function get_key(): string {
		return sprintf( 'product-types/%s/bookings/booking:canceled_by_vendor', $this->product_type->get_key() );
	}

	public function get_label(): string {
		return 'Booking canceled by vendor';
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
					'subject' => 'Your booking has been canceled',
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
					'subject' => 'Your booking has been canceled',
					'message' => <<<HTML
					Your booking request on <strong>@post(:title)</strong> has been canceled.<br>
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
					'subject' => 'Booking request canceled',
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
					'subject' => 'Booking request canceled',
					'message' => <<<HTML
					The booking request by <strong>@customer(:display_name)</strong>
					on <strong>@post(:title)</strong> has been canceled.<br>
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
					'subject' => 'Booking request canceled',
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
					'subject' => 'Booking request canceled',
					'message' => <<<HTML
					The booking request by <strong>@customer(:display_name)</strong>
					on <strong>@post(:title)</strong> has been canceled.<br>
					<a href="@order(:link)">Order details</a>
					HTML,
				],
			],
		];
	}
}
