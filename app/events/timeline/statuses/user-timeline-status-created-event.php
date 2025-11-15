<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Timeline_Status_Created_Event extends \Voxel\Events\Base_Event {

	public $status, $author;

	public function prepare( $status_id ) {
		$status = \Voxel\Timeline\Status::get( $status_id );
		if ( ! ( $status && $status->get_feed() === 'user_timeline' && $status->get_user() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->status = $status;
		$this->author = $status->get_user();
	}

	public function get_key(): string {
		return 'users/timeline/status:created';
	}

	public function get_label(): string {
		return 'User timeline: Post created';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => false,
					'subject' => '@author(display_name) submitted a new post to their timeline',
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
					'subject' => '@author(display_name) submitted a new post to their timeline',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> submitted a new post to their timeline.
					<a href="@author(profile_url)">Open</a>
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
			'status' => \Voxel\Dynamic_Data\Group::Timeline_Status( $this->status ),
			'author' => \Voxel\Dynamic_Data\Group::User( $this->status && $this->status->get_user() ? $this->status->get_user() : \Voxel\User::mock() ),
		];
	}
}
