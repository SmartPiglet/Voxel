<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Wall_Status_Approved_Event extends Post_Wall_Status_Created_Event {

	public function get_key(): string {
		return sprintf( 'post-types/%s/wall-post:approved', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Wall post approved', $this->post_type->get_label() );
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->status->get_author() ?? null;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your wall post has been approved',
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
					'subject' => 'Your status has been approved',
					'message' => <<<HTML
					Your wall post on @post(title) has been approved and published.
					<a href="@status(link)">Open</a>
					HTML,
				],
			],

			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->status->get_post();
					return $post ? $post->get_author() : null;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@author(display_name) published a wall post on @post(title)',
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
					'subject' => '@author(display_name) published a wall post on @post(title)',
					'message' => <<<HTML
					A new wall post has been published on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
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
					'subject' => '@author(display_name) published a wall post on @post(title)',
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
					'subject' => '@author(display_name) published a wall post on @post(title)',
					'message' => <<<HTML
					A new wall post has been published on <strong>@post(title)</strong>
					by <strong>@author(display_name)</strong>.
					<a href="@post(permalink)">Open</a>
					HTML,
				],
			],
		];
	}
}
