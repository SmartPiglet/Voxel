<?php

namespace Voxel\Modules\Paid_Memberships\App_Events;;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Canceled_Event extends \Voxel\Events\Base_Event {

	public $user, $membership;

	public function prepare( \Voxel\User $user, Membership $membership ) {
		$this->user = $user;
		$this->membership = $membership;
	}

	public function get_key(): string {
		return 'paid_members/plan:canceled';
	}

	public function get_label(): string {
		return 'Paid members: Plan canceled';
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
					'subject' => 'Your @membership(plan.label) plan has been canceled',
					'links_to' => function( $event ) {
						if ( $event->membership->get_type() === 'order' && ( $order = $event->membership->get_order() ) ) {
							return $order->get_link();
						}

						return get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/');
					},
					'details' => fn( $event ) => [
						'user_id' => $event->user->get_id(),
						'membership' => $event->membership->to_array(),
					],
					'apply_details' => function( $event, $details ) {
						$user = \Voxel\User::get( $details['user_id'] ?? null );
						$membership = Membership::from( (array) ( $details['membership'] ?? [] ) );
						if ( ! ( $user && $membership ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $user, $membership );
					},
				],
				'email' => [
					'enabled' => true,
					'subject' => 'Your @membership(plan.label) plan has been canceled',
					'message' => <<<HTML
					Hello @user(display_name)<br>
					Your <b>@membership(plan.label)</b> plan has been canceled.
					Access to all features included in this plan has ended, and no further charges will apply.
					<a href="@site(current_plan_url)">Explore Plans</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn( $event ) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => true,
					'subject' => '@user(display_name) canceled their @membership(plan.label) plan',
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
					],
					'apply_details' => function( $event, $details ) {
						$user = \Voxel\User::get( $details['user_id'] ?? null );
						$membership = Membership::from( (array) ( $details['membership'] ?? [] ) );
						if ( ! ( $user && $membership ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $user, $membership );
					},
				],
				'email' => [
					'enabled' => true,
					'subject' => '@user(display_name) canceled their @membership(plan.label) plan',
					'message' => <<<HTML
					<b>@user(display_name)</b> canceled their <b>@membership(plan.label)</b> (@membership(pricing.formatted)) plan.<br>
					<a href="@user(profile_url)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->user = \Voxel\User::mock();
		$this->membership = \Voxel\User::mock()->get_membership();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ),
			'membership' => \Voxel\Dynamic_Data\Group::User_Membership( $this->membership ),
		];
	}
}
