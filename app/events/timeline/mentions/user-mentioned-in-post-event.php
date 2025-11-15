<?php

namespace Voxel\Events\Timeline\Mentions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Mentioned_In_Post_Event extends \Voxel\Events\Base_Event {

	public
		$user,
		$status,
		$author;

	public function prepare( $user_id, $status_id ) {
		$user = \Voxel\User::get( $user_id );
		$status = \Voxel\Timeline\Status::get( $status_id );
		if ( ! ( $user && $status ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->user = $user;
		$this->status = $status;
		$this->author = $status->get_author();
	}

	public function get_key(): string {
		return 'users/timeline/mentioned-in-post';
	}

	public function get_label(): string {
		return 'User timeline: Mentioned in post';
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
					'subject' => '@author(display_name) mentioned you in a post',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'status_id' => $event->status->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null, $details['status_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->status->get_link(); },
					'image_id' => function( $event ) { return $event->author ? $event->author->get_avatar_id() : null; },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@author(display_name) mentioned you in a post',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> mentioned you in a post.
					<a href="@status(link)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->status = \Voxel\Timeline\Status::mock();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ? $this->user : \Voxel\User::mock() ),
			'status' => \Voxel\Dynamic_Data\Group::Timeline_Status( $this->status ),
			'author' => \Voxel\Dynamic_Data\Group::User( $this->status && $this->status->get_author() ? $this->status->get_author() : \Voxel\User::mock() ),
		];
	}
}
