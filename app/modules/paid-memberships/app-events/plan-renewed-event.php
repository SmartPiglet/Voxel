<?php

namespace Voxel\Modules\Paid_Memberships\App_Events;;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Renewed_Event extends \Voxel\Events\Base_Event {

	public $user, $membership;

	public function prepare( \Voxel\User $user, Membership $membership ) {
		$this->user = $user;
		$this->membership = $membership;
	}

	public function get_key(): string {
		return 'paid_members/plan:renewed';
	}

	public function get_label(): string {
		return 'Paid members: Plan renewed';
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
					'subject' => 'Your @membership(plan.label) subscription has been renewed',
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
					'subject' => 'Your @membership(plan.label) subscription has been renewed',
					'message' => <<<HTML
					Hello @user(display_name)<br>
					Your <b>@membership(plan.label)</b> subscription has been successfully renewed.
					You can continue enjoying all the features and benefits of your plan without interruption.
					<a href="@site(current_plan_url)">Dashboard</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => fn( $event ) => \Voxel\get_main_admin(),
				'inapp' => [
					'enabled' => true,
					'subject' => '@user(display_name) renewed @membership(plan.label) plan (@membership(pricing.formatted))',
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
					'subject' => '@user(display_name) renewed @membership(plan.label) plan (@membership(pricing.formatted))',
					'message' => <<<HTML
					<b>@user(display_name)</b> renewed <b>@membership(plan.label)</b> plan.<br>
					Pricing: @membership(pricing.formatted)
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
