<?php

namespace Voxel\Modules\Claim_Listings\App_Events;

use \Voxel\Modules\Claim_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Approved_Event extends \Voxel\Events\Base_Event {
	use Claim_Event_Commons;

	public function get_key(): string {
		return 'claim_listings/claim:approved';
	}

	public function get_label(): string {
		return 'Claim request approved';
	}

	public static function notifications(): array {
		return [
			'customer' => [
				'label' => 'Notify customer',
				'recipient' => fn($event) => $event->customer,
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your claim request has been approved',
					'details' => fn($event) => [
						'order_item_id' => $event->order_item->get_id(),
					],
					'apply_details' => function( $event, $details ) {
						$event->prepare( \Voxel\Order_Item::get( $details['order_item_id'] ?? null ) );
					},
					'links_to' => fn($event) => $event->order->get_link(),
				],
				'email' => [
					'enabled' => true,
					'subject' => 'Your claim request has been approved',
					'message' => <<<HTML
					Your claim request on <strong>@post(title)</strong> has been approved.<br>
					<a href="@order(link)">View details</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn($event) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => false,
					'subject' => 'Claim request by @customer(display_name) has been approved',
					'details' => fn($event) => [
						'order_item_id' => $event->order_item->get_id(),
					],
					'apply_details' => function( $event, $details ) {
						$event->prepare( \Voxel\Order_Item::get( $details['order_item_id'] ?? null ) );
					},
					'links_to' => fn($event) => $event->order->get_link(),
					'image_id' => fn($event) => $event->customer->get_avatar_id(),
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Claim request by @customer(display_name) has been approved',
					'message' => <<<HTML
					Claim request submitted by <strong>@customer(display_name)</strong> on <strong>@post(title)</strong> has been approved.
					<a href="@order(link)">View details</a>
					HTML,
				],
			],
		];
	}
}
