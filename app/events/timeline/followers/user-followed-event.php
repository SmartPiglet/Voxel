<?php

namespace Voxel\Events\Timeline\Followers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Followed_Event extends \Voxel\Events\Base_Event {

	public
		$user,
		$follower;

	public function prepare( $user_id, $follower_id ) {
		$user = \Voxel\User::get( $user_id );
		$follower = \Voxel\User::get( $follower_id );
		if ( ! ( $user && $follower ) ) {
			throw new \Exception( 'Missing information.' );
		}

		$this->user = $user;
		$this->follower = $follower;
	}

	public function get_key(): string {
		return 'timeline/followers/user-followed-event';
	}

	public function get_label(): string {
		return 'Follows: User received new follower';
	}

	public function get_category() {
		return 'timeline';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->user;
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@follower(display_name) followed you',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'follower_id' => $event->follower->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null, $details['follower_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->follower->get_link(); },
					'image_id' => function( $event ) { return $event->follower->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@follower(display_name) followed you',
					'message' => <<<HTML
					<strong>@follower(display_name)</strong> followed you
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
					'subject' => '@follower(display_name) followed @user(display_name)',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'follower_id' => $event->follower->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null, $details['follower_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->follower->get_link(); },
					'image_id' => function( $event ) { return $event->follower->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@follower(display_name) followed @user(display_name)',
					'message' => <<<HTML
					<strong>@follower(display_name)</strong> followed <strong>@user(display_name)</strong>
					HTML,
				],
			],
		];
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ? $this->user : \Voxel\User::mock() ),
			'follower' => \Voxel\Dynamic_Data\Group::User( $this->follower ? $this->follower : \Voxel\User::mock() ),
		];
	}
}
