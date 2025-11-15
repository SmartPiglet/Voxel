<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Timeline_Status_Approved_Event extends Post_Timeline_Status_Created_Event {

	public function get_key(): string {
		return sprintf( 'post-types/%s/status:approved', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Timeline post approved', $this->post_type->get_label() );
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->status->get_post();
					return $post ? $post->get_author() : null;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Timeline post on @post(title) approved',
					'details' => function( $event ) {
						return [
							'status_id' => $event->status->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['status_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->status->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Timeline post on @post(title) approved',
					'message' => <<<HTML
					Your timeline post submitted on <strong>@post(title)</strong>
					has been approved and published.
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
					'subject' => 'Timeline post by @post(title) approved',
					'details' => function( $event ) {
						return [
							'status_id' => $event->status->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['status_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->status->get_link(); },
					'image_id' => function( $event ) { return $event->post->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Timeline post by @post(title) approved',
					'message' => <<<HTML
					A timeline post by <strong>@post(title)</strong> was approved and published.
					<a href="@post(permalink)">Open</a>
					HTML,
				],
			],
		];
	}
}
