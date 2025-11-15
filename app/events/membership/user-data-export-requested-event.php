<?php

namespace Voxel\Events\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Data_Export_Requested_Event extends \Voxel\Events\Base_Event {

	public $user;

	public function prepare( $user_id ) {
		$user = \Voxel\User::get( $user_id );
		if ( ! $user ) {
			throw new \Exception( 'User not found.' );
		}

		$this->user = $user;
	}

	public function get_key(): string {
		return 'membership/user:data-export-requested';
	}

	public function get_label(): string {
		return 'Membership: Personal data export requested';
	}

	public function get_category() {
		return 'membership';
	}

	public static function notifications(): array {
		return [
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@user(display_name) sent a personal data export request',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['user_id'] ?? null );
					},
					'links_to' => function( $event ) { return admin_url('/export-personal-data.php'); },
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@user(display_name) sent a personal data export request',
					'message' => <<<HTML
					<strong>@user(display_name)</strong> sent a personal data export request
					<a href="@site(admin_url)/export-personal-data.php">View request</a>
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
