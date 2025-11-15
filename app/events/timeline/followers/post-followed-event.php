<?php

namespace Voxel\Events\Timeline\Followers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Followed_Event extends \Voxel\Events\Base_Event {

	public
		$post,
		$follower;

	public function prepare( $post_id, $follower_id ) {
		$post = \Voxel\Post::get( $post_id );
		$follower = \Voxel\User::get( $follower_id );
		if ( ! ( $post && $follower ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->post = $post;
		$this->follower = $follower;
	}

	public function get_key(): string {
		return 'timeline/followers/post-followed-event';
	}

	public function get_label(): string {
		return 'Follows: Post received new follower';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify post author',
				'recipient' => function( $event ) {
					return $event->post->get_author();
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@follower(display_name) followed @post(title)',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
							'follower_id' => $event->follower->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['post_id'] ?? null, $details['follower_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->follower->get_link(); },
					'image_id' => function( $event ) { return $event->follower->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@follower(display_name) followed @post(title)',
					'message' => <<<HTML
					<strong>@follower(display_name)</strong> followed <strong>@post(title)</strong>
					<a href="@follower(profile_url)">View user</a>
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
					'subject' => '@follower(display_name) followed @post(title)',
					'details' => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
							'follower_id' => $event->follower->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['post_id'] ?? null, $details['follower_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->follower->get_link(); },
					'image_id' => function( $event ) { return $event->follower->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@follower(display_name) followed @post(title)',
					'message' => <<<HTML
					<strong>@follower(display_name)</strong> followed <strong>@post(title)</strong>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->post = \Voxel\Post::mock();
	}

	public function dynamic_tags(): array {
		return [
			'post' => \Voxel\Dynamic_Data\Group::Simple_Post( $this->post ),
			'follower' => \Voxel\Dynamic_Data\Group::User( $this->follower ? $this->follower : \Voxel\User::mock() ),
		];
	}
}
