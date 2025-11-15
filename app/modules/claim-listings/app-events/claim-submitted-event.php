<?php

namespace Voxel\Modules\Claim_Listings\App_Events;

use \Voxel\Modules\Claim_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Submitted_Event extends \Voxel\Events\Base_Event {
	use Claim_Event_Commons;

	public function get_key(): string {
		return 'claim_listings/claim:submitted';
	}

	public function get_label(): string {
		return 'Claim request submitted';
	}

	public static function notifications(): array {
		return [
			'customer' => [
				'label' => 'Notify customer',
				'recipient' => fn($event) => $event->customer,
				'inapp' => [
					'enabled' => false,
					'subject' => 'Your claim request has been submitted',
					'details' => fn($event) => [
						'order_item_id' => $event->order_item->get_id(),
					],
					'apply_details' => function( $event, $details ) {
						$event->prepare( \Voxel\Order_Item::get( $details['order_item_id'] ?? null ) );
					},
					'links_to' => fn($event) => $event->order->get_link(),
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your claim request has been submitted',
					'message' => <<<HTML
					Your claim request on <strong>@post(title)</strong> has been submitted.<br>
					<a href="@order(link)">View details</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn($event) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => true,
					'subject' => 'New claim request submitted by @customer(display_name)',
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
					'enabled' => true,
					'subject' => 'New claim request submitted by @customer(display_name)',
					'message' => <<<HTML
					New claim request submitted by <strong>@customer(display_name)</strong> on <strong>@post(title)</strong><br>
					<a href="@order(link)">View details</a>
					HTML,
				],
			],
		];
	}
}
