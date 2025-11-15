<?php

namespace Voxel\Events\Timeline\Comments;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Reply_Submitted_Event extends \Voxel\Events\Base_Event {

	public $reply, $comment, $status, $author;

	public function prepare( $reply_id ) {
		$reply = \Voxel\Timeline\Reply::get( $reply_id );
		if ( ! ( $reply && $reply->get_status() && $reply->get_author() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$parent = $reply->get_parent();
		if ( ! $parent ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->reply = $reply;
		$this->status = $reply->get_status();
		$this->author = $reply->get_author();
		$this->comment = $parent;
	}

	public function get_key(): string {
		return 'timeline/comment-reply:submitted';
	}

	public function get_label(): string {
		return 'Timeline: Comment reply submitted';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'post_author' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					$post = $event->status->get_post();
					if ( ! ( $post && $post->get_author() ) ) {
						return null;
					}

					if ( $post->get_author_id() === $event->reply->get_author_id() ) {
						return null;
					}

					if ( ! $event->reply->is_moderatable_by_user( $post->get_author() ) ) {
						return null;
					}

					return $post->get_author();
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) submitted a new reply on your post',
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
					'subject' => '@author(display_name) submitted a new reply on your post',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> submitted a new reply on your post.
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
					'subject' => '@author(display_name) submitted a new reply',
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
					'subject' => '@author(display_name) submitted a new reply',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> submitted a new reply.
					<a href="@reply(link)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->reply = \Voxel\Timeline\Reply::mock();
		$this->author = \Voxel\User::mock();
		$this->comment = \Voxel\Timeline\Reply::mock();
		$this->status = \Voxel\Timeline\Status::mock();
	}

	public function dynamic_tags(): array {
		return [
			'reply' => \Voxel\Dynamic_Data\Group::Timeline_Reply( $this->reply ),
			'author' => \Voxel\Dynamic_Data\Group::User( $this->author ),
			'comment' => \Voxel\Dynamic_Data\Group::Timeline_Reply( $this->comment ),
			'status' => \Voxel\Dynamic_Data\Group::Timeline_Status( $this->status ),
		];
	}
}
