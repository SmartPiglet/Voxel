<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Liked_Event extends \Voxel\Events\Base_Event {

	public $user, $status;

	public function prepare( $user_id, $status_id ) {
		$user = \Voxel\User::get( $user_id );
		$status = \Voxel\Timeline\Status::get( $status_id );
		if ( ! ( $user && $status ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->user = $user;
		$this->status = $status;
	}

	public function get_key(): string {
		return 'users/timeline/post-liked';
	}

	public function get_label(): string {
		return 'User timeline: Post liked';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					$publisher = $event->status->get_publisher();
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
					'subject' => '@user(display_name) liked your post',
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
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@user(display_name) liked your post',
					'message' => <<<HTML
					<strong>@user(display_name)</strong> liked one of your posts.
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
		];
	}
}
