<?php

namespace Voxel\Modules\Paid_Memberships\App_Events;;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Switched_Event extends \Voxel\Events\Base_Event {

	public $user, $membership, $previous_membership;

	public function prepare( \Voxel\User $user, Membership $membership, Membership $previous_membership ) {
		$this->user = $user;
		$this->membership = $membership;
		$this->previous_membership = $previous_membership;
	}

	public function get_key(): string {
		return 'paid_members/plan:switched';
	}

	public function get_label(): string {
		return 'Paid members: Plan switched';
	}

	public function get_category() {
		return 'paid_members';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => fn( $event ) => $event->user,
				'inapp' => [
					'enabled' => true,
					'subject' => 'You have switched to the @membership(plan.label) (@membership(pricing.formatted)) plan',
					'links_to' => function( $event ) {
						if ( $event->membership->get_type() === 'order' && ( $order = $event->membership->get_order() ) ) {
							return $order->get_link();
						}

						return get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/');
					},
					'details' => fn( $event ) => [
						'user_id' => $event->user->get_id(),
						'membership' => $event->membership->to_array(),
						'previous_membership' => $event->previous_membership->to_array(),
					],
					'apply_details' => function( $event, $details ) {
						$user = \Voxel\User::get( $details['user_id'] ?? null );
						$membership = Membership::from( (array) ( $details['membership'] ?? [] ) );
						$previous_membership = Membership::from( (array) ( $details['previous_membership'] ?? [] ) );
						if ( ! ( $user && $membership && $previous_membership ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $user, $membership, $previous_membership );
					},
				],
				'email' => [
					'enabled' => true,
					'subject' => 'You have switched to the @membership(plan.label) (@membership(pricing.formatted)) plan',
					'message' => <<<HTML
					Hello @user(display_name)<br>
					You've successfully switched from <b>@previous_membership(plan.label) (@previous_membership(pricing.formatted))</b>
					to the <b>@membership(plan.label) (@membership(pricing.formatted))</b> plan.
					<a href="@site(current_plan_url)">Go to Dashboard</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn( $event ) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => true,
					'subject' => '@user(display_name) switched to the @membership(plan.label) (@membership(pricing.formatted)) plan',
					'image_id' => fn( $event ) => $event->user->get_avatar_id(),
					'links_to' => function( $event ) {
						if ( $event->membership->get_type() === 'order' && ( $order = $event->membership->get_order() ) ) {
							return $order->get_link();
						}

						return $event->user->get_link();
					},
					'details' => fn( $event ) => [
						'user_id' => $event->user->get_id(),
						'membership' => $event->membership->to_array(),
						'previous_membership' => $event->previous_membership->to_array(),
					],
					'apply_details' => function( $event, $details ) {
						$user = \Voxel\User::get( $details['user_id'] ?? null );
						$membership = Membership::from( (array) ( $details['membership'] ?? [] ) );
						$previous_membership = Membership::from( (array) ( $details['previous_membership'] ?? [] ) );
						if ( ! ( $user && $membership && $previous_membership ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $user, $membership, $previous_membership );
					},
				],
				'email' => [
					'enabled' => true,
					'subject' => '@user(display_name) switched to the @membership(plan.label) (@membership(pricing.formatted)) plan',
					'message' => <<<HTML
					<b>@user(display_name)</b> switched from <b>@previous_membership(plan.label) (@previous_membership(pricing.formatted))</b>
					to the <b>@membership(plan.label) (@membership(pricing.formatted))</b> plan.
					<a href="@user(profile_url)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->user = \Voxel\User::mock();
		$this->membership = \Voxel\User::mock()->get_membership();
		$this->previous_membership = \Voxel\User::mock()->get_membership();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ),
			'membership' => \Voxel\Dynamic_Data\Group::User_Membership( $this->membership ),
			'previous_membership' => \Voxel\Dynamic_Data\Group::User_Membership( $this->previous_membership ),
		];
	}
}
