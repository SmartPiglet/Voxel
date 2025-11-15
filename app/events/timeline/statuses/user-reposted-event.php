<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Reposted_Event extends \Voxel\Events\Base_Event {

	public $status, $author, $repost_of;

	public function prepare( $status_id ) {
		$status = \Voxel\Timeline\Status::get( $status_id );
		if ( ! ( $status && $status->get_repost_of() && $status->get_user() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->status = $status;
		$this->author = $status->get_user();
		$this->repost_of = $status->get_repost_of();
	}

	public function get_key(): string {
		return 'users/timeline/post-reposted';
	}

	public function get_label(): string {
		return 'User timeline: Post reposted';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					$publisher = $event->repost_of->get_publisher();
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
					'subject' => '@author(display_name) reposted your post',
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
					'subject' => '@author(display_name) reposted your post',
					'message' => <<<HTML
					<strong>@author(display_name)</strong> reposted one of your posts.
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
			'author' => \Voxel\Dynamic_Data\Group::User( $this->author ? $this->author : \Voxel\User::mock() ),
			'status' => \Voxel\Dynamic_Data\Group::Timeline_Status( $this->status ),
		];
	}
}
