<?php

namespace Voxel\Events\Timeline\Statuses;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Timeline_Status_Created_Event extends \Voxel\Events\Base_Event {

	public $post_type;

	public $status, $post;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function prepare( $status_id ) {
		$status = \Voxel\Timeline\Status::get( $status_id );
		if ( ! ( $status && $status->get_feed() === 'post_timeline' && $status->get_post() ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->status = $status;
		$this->post = $status->get_post();
	}

	public function get_key(): string {
		return sprintf( 'post-types/%s/status:created', $this->post_type->get_key() );
	}

	public function get_label(): string {
		return sprintf( '%s: Timeline post submitted', $this->post_type->get_label() );
	}

	public function get_category() {
		return sprintf( 'post-type:%s', $this->post_type->get_key() );
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
					'subject' => '@post(title) posted an update',
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
					'subject' => '@post(title) posted an update',
					'message' => <<<HTML
					<strong>@post(title)</strong> posted an updated on their timeline.
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
			'status' => \Voxel\Dynamic_Data\Group::Timeline_Status( $this->status ),
			'post' => \Voxel\Dynamic_Data\Group::Post( $this->status && $this->status->get_post() ? $this->status->get_post() : \Voxel\Post::mock( [ 'post_type' => $this->post_type->get_key() ] ) ),
		];
	}
}
