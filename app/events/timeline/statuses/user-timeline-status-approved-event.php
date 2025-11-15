<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Timeline_Status_Approved_Event extends User_Timeline_Status_Created_Event {

	public function get_key(): string {
		return 'users/timeline/status:approved';
	}

	public function get_label(): string {
		return 'User timeline: Post approved';
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify author',
				'recipient' => function( $event ) {
					return $event->status->get_author() ?? null;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your post was approved',
					'details' => function( $event ) {
						return [
							'status_id' => $event->status->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['status_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->status->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your timeline post was approved',
					'message' => <<<HTML
					Your timeline post has been approved and published.
					<a href="@status(link)">Open</a>
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
					'subject' => '@author(display_name) posted an update',
					'details' => function( $event ) {
						return [
							'status_id' => $event->status->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['status_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->status->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) posted an update',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> published an update to their timeline.
					<a href="@author(profile_url)">Open</a>
					HTML,
				],
			],
		];
	}
}
