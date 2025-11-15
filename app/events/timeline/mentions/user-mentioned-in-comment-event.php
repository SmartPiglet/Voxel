<?php

namespace Voxel\Events\Timeline\Mentions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Mentioned_In_Comment_Event extends \Voxel\Events\Base_Event {

	public
		$user,
		$comment,
		$author;

	public function prepare( $user_id, $comment_id ) {
		$user = \Voxel\User::get( $user_id );
		$comment = \Voxel\Timeline\Reply::get( $comment_id );
		if ( ! ( $user && $comment ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->user = $user;
		$this->comment = $comment;
		$this->author = $comment->get_author();
	}

	public function get_key(): string {
		return 'users/timeline/mentioned-in-comment';
	}

	public function get_label(): string {
		return 'User timeline: Mentioned in comment';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->user ?? null;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) mentioned you in a comment',
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
					'image_id' => function( $event ) { return $event->author ? $event->author->get_avatar_id() : null; },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) mentioned you in a comment',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> mentioned you in a comment.
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
			'author' => \Voxel\Dynamic_Data\Group::User( $this->comment && $this->comment->get_author() ? $this->comment->get_author() : \Voxel\User::mock() ),
		];
	}
}
