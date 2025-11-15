<?php

namespace Voxel\Events\VoxPoints;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Points_Changed_Event extends \Voxel\Events\Base_Event {

	public $user;
	public $delta = 0;
	public $action = '';
	public $point_type = 'general';
	public $description = '';

	public function prepare( $user_id, $delta, $action = '', $point_type = 'general', $description = '' ) {
		$user = \Voxel\User::get( $user_id );
		if ( ! $user ) {
			throw new \Exception( 'Missing user.' );
		}

		$this->user = $user;
		$this->delta = (int) $delta;
		$this->action = is_string( $action ) ? $action : '';
		$this->point_type = is_string( $point_type ) ? $point_type : 'general';
		$this->description = is_string( $description ) ? $description : '';
	}

	public function get_key(): string {
		return 'voxpoints/points:changed';
	}

	public function get_label(): string {
		return 'VoxPoints: Points changed';
	}

	public function get_category() {
		return 'voxpoints';
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
					// Keep subject simple for broad compatibility
					'subject' => 'Your points were updated',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'delta' => (int) $event->delta,
							'action' => (string) $event->action,
							'point_type' => (string) $event->point_type,
							'description' => (string) $event->description,
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare(
							$details['user_id'] ?? null,
							$details['delta'] ?? 0,
							$details['action'] ?? '',
							$details['point_type'] ?? 'general',
							$details['description'] ?? ''
						);
					},
					'links_to' => function( $event ) {
						// Link to user profile or a points page if available
						return $event->user->get_link();
					},
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
				],
			],
		];
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ? $this->user : \Voxel\User::mock() ),
		];
	}
}


