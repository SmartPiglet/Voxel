<?php

namespace Voxel\Events\Timeline\Comments;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Reply_Approved_Event extends Comment_Reply_Submitted_Event {

	public function get_key(): string {
		return 'timeline/comment-reply:approved';
	}

	public function get_label(): string {
		return 'Timeline: Comment reply approved';
	}

	public static function notifications(): array {
		return [
			'reply_author' => [
				'label' => 'Notify reply author',
				'recipient' => function( $event ) {
					return $event->reply->get_author();
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'Your reply has been published',
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
					'subject' => 'Your reply has been published',
					'message' => <<<HTML
					Your reply has been approved and published.
					<a href="@reply(link)">Open</a>
					HTML,
				],
			],
			'comment_author' => [
				'label' => 'Notify comment author',
				'recipient' => function( $event ) {
					if ( $event->comment->get_author_id() === $event->reply->get_author_id() ) {
						return null;
					}

					return $event->comment->get_author();
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@author(display_name) replied to your comment.',
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
					'subject' => '@author(display_name) replied to your comment.',
					'message' => <<<HTML
					You have received a new reply from <strong>@author(display_name)</strong>.
					<a href="@reply(link)">Open</a>
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
					'enabled' => false,
					'subject' => '@author(display_name) replied to a comment on your post.',
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
					'subject' => '@author(display_name) replied to a comment on your post.',
					'message' => <<<HTML
					New reply received by <strong>@author(display_name)</strong>.
					<a href="@reply(link)">Open</a>
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
					'subject' => '@author(display_name) replied to a comment on your post.',
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
					'subject' => '@author(display_name) replied to a comment on your post.',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> replied to a comment on your post.
					<a href="@reply(link)">Open</a>
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
					'subject' => '@author(display_name) replied to a comment.',
					'message' => <<<HTML
					New reply received by <strong>@author(display_name)</strong>.
					<a href="@reply(link)">Open</a>
					HTML,
				],
			],
		];
	}

}
