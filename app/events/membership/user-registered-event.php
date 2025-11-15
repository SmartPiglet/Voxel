<?php

namespace Voxel\Events\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Registered_Event extends \Voxel\Events\Base_Event {

	public $user;

	public function prepare( $user_id ) {
		$user = \Voxel\User::get( $user_id );
		if ( ! $user ) {
			throw new \Exception( 'User not found.' );
		}

		$this->user = $user;
	}

	public function get_key(): string {
		return 'membership/user:registered';
	}

	public function get_label(): string {
		return 'Membership: New user registered';
	}

	public function get_category() {
		return 'membership';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->user;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'Your account has been created successfully.',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->user->get_link(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'Your account has been created successfully.',
					'message' => <<<HTML
					Welcome @user(:display_name)
					Your account has been created successfully.
					<a href="@site(login_url)">Login</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => true,
					'subject' => 'New user registered: @user(:display_name)',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null );
					},
					'links_to' => function( $event ) { return $event->user->get_link(); },
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'New user registered: @user(:display_name)',
					'message' => <<<HTML
					A new user has been registered on your site: <strong>@user(:display_name)</strong>
					<a href="@user(:profile_url)">View profile</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->user = \Voxel\User::mock();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ),
		];
	}
}
