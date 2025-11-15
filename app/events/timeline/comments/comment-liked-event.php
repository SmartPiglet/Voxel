<?php

namespace Voxel\Events\Timeline\Comments;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Liked_Event extends \Voxel\Events\Base_Event {

	public $user, $comment;

	public function prepare( $user_id, $comment_id ) {
		$user = \Voxel\User::get( $user_id );
		$comment = \Voxel\Timeline\Reply::get( $comment_id );
		if ( ! ( $user && $comment ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->user = $user;
		$this->comment = $comment;
	}

	public function get_key(): string {
		return 'users/timeline/comment-liked';
	}

	public function get_label(): string {
		return 'User: Comment liked';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					$publisher = $event->comment->get_publisher();
					if ( $publisher instanceof \Voxel\Post ) {
						return $publisher->get_author();
					} elseif ( $publisher instanceof \Voxel\User ) {
						return $publisher;
					} else {
						return null;
					}
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@user(display_name) liked your comment',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'comment_id' => $event->comment->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null, $details['comment_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->comment->get_link(); },
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@user(display_name) liked your post',
					'message' => <<<HTML
					<strong>@user(display_name)</strong> liked one of your posts.
					<a href="@comment(link)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->comment = \Voxel\Timeline\Reply::mock();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ? $this->user : \Voxel\User::mock() ),
			'comment' => \Voxel\Dynamic_Data\Group::Timeline_Reply( $this->comment ),
		];
	}
}
