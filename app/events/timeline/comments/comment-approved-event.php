<?php

namespace Voxel\Events\Timeline\Comments;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Approved_Event extends Comment_Submitted_Event {

	public function get_key(): string {
		return 'timeline/comment:approved';
	}

	public function get_label(): string {
		return 'Timeline: Comment approved';
	}

	public static function notifications(): array {
		return [
			'comment_author' => [
				'label' => 'Notify comment author',
				'recipient' => function( $event ) {
					return $event->reply->get_author();
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your comment has been published',
					'details' => function( $event ) {
						return [
							'reply_id' => $event->reply->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['reply_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->reply->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your comment has been published',
					'message' => <<<HTML
					Your comment has been approved and published.
					<a href="@comment(link)">Open</a>
					HTML,
				],
			],
			'status_author' => [
				'label' => 'Notify status author',
				'recipient' => function( $event ) {
					if ( $event->status->get_author_id() === $event->reply->get_author_id() ) {
						return null;
					}

					return $event->status->get_author();
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@author(display_name) commented on your post.',
					'details' => function( $event ) {
						return [
							'reply_id' => $event->reply->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['reply_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->reply->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) commented on your post',
					'message' => <<<HTML
					A new comment has been published on your status
					by <strong>@author(display_name)</strong>.
					<a href="@comment(link)">Open</a>
					HTML,
				],
			],
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->status->get_post();
					if ( ! $post ) {
						return null;
					}

					if ( $post->get_author_id() === $event->reply->get_author_id() ) {
						return null;
					}

					return $post->get_author();
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) commented on a status on your post.',
					'details' => function( $event ) {
						return [
							'reply_id' => $event->reply->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['reply_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->reply->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) commented on a status on your post.',
					'message' => <<<HTML
					A new comment has been published
					by <strong>@author(display_name)</strong>.
					<a href="@comment(link)">Open</a>
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
					'subject' => '@author(display_name) commented on a status.',
					'details' => function( $event ) {
						return [
							'reply_id' => $event->reply->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['reply_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->reply->get_link(); },
					'image_id' => function( $event ) { return $event->author->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) commented on a status.',
					'message' => <<<HTML
					A new comment has been published
					by <strong>@author(display_name)</strong>.
					<a href="@comment(link)">Open</a>
					HTML,
				],
			],
		];
	}
}
