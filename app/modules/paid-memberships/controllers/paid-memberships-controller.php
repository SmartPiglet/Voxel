<?php

namespace Voxel\Modules\Paid_Memberships\Controllers;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paid_Memberships_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! \Voxel\get('settings.addons.paid_memberships.enabled');
	}

	protected function dependencies() {
		\Voxel\use_ecommerce();

		new Backend\Backend_Controller;
		new Backend\Plan_Controller;
		new Backend\Member_Controller;

		new Frontend\Cart_Controller;
		new Frontend\Checkout_Controller;
		new Frontend\Order_Controller;
		new Frontend\Auth_Controller;
	}

	protected function hooks() {
		$this->register_product_type();
		$this->filter( 'voxel/post/get_field/voxel:membership_plan', '@register_product_field', 10, 3 );
		$this->filter( 'voxel/app-events/categories', '@register_app_event_categories' );
		$this->filter( 'voxel/app-events/register', '@register_app_events' );
		$this->on( 'elementor/widgets/register', '@register_widgets', 1000 );
		$this->on( 'voxel/paid_memberships/updated_user_plan', '@trigger_app_events', 10, 3 );
	}

	protected function register_product_type() {
		return \Voxel\Product_Type::register_virtual( [
			'settings' => [
				'key' => 'voxel:membership_plan',
				'label' => 'Membership plan',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => 'subscription',
				],
				'supports_marketplace' => false,
			],
			'modules' => [
				'base_price' => [
					'enabled' => true,
					'discount_price' => [
						'enabled' => true,
					],
				],
				'cart' => [
					'enabled' => false,
				],
				'custom_currency' => [
					'enabled' => true,
				],
			],
		] );
	}

	protected function register_product_field( $field, $post, $post_type ) {
		if ( $post_type->get_key() !== '_vx_catalog' ) {
			return null;
		}

		$catalog_category = get_post_meta( $post->get_id(), '_vx_catalog_category', true );
		if ( $catalog_category !== 'paid_memberships_price' ) {
			return null;
		}

		$plan_key = (string) get_post_meta( $post->get_id(), '_vx_plan_key', true );
		$price_key = (string) get_post_meta( $post->get_id(), '_vx_price_key', true );
		if ( empty( $plan_key ) || empty( $price_key ) ) {
			return null;
		}

		try {
			$price = \Voxel\Modules\Paid_Memberships\Price::get( $plan_key, $price_key );
		} catch ( \Exception $e ) {
			return null;
		}

		$field = new \Voxel\Post_Types\Fields\Product_Field( [
			'label' => 'Membership plan',
			'key' => 'voxel:membership_plan',
			'product-types' => [ 'voxel:membership_plan' ],
		] );

		$field->set_post( $post );
		$field->_set_value( [
			'product_type' => 'voxel:membership_plan',
			'enabled' => true,
			'base_price' => [
				'amount' => $price->get_amount(),
				'discount_amount' => $price->get_discount_amount(),
			],
			'subscription' => [
				'frequency' => $price->get_billing_frequency(),
				'unit' => $price->get_billing_interval(),
			],
			'currency' => $price->get_currency(),
		] );

		return $field;
	}

	protected function register_widgets() {
		$manager = \Elementor\Plugin::instance()->widgets_manager;
		$manager->register( new \Voxel\Modules\Paid_Memberships\Widgets\Pricing_Plans_Widget );
		$manager->register( new \Voxel\Modules\Paid_Memberships\Widgets\Current_Plan_Widget );
	}

	protected function register_app_event_categories( array $categories ): array {
		$categories['paid_members'] = [
			'key' => 'paid_members',
			'label' => 'Paid Members',
		];

		return $categories;
	}

	protected function register_app_events( array $events ): array {
		foreach ( [
			Module\App_Events\Plan_Activated_Event::class,
			Module\App_Events\Plan_Renewed_Event::class,
			Module\App_Events\Plan_Switched_Event::class,
			Module\App_Events\Plan_Canceled_Event::class,
		] as $event_class ) {
			$event = new $event_class;
			$events[ $event->get_key() ] = $event;
		}

		return $events;
	}

	protected function trigger_app_events( Membership $plan, Membership $previous_plan, \Voxel\User $user ) {
		if ( $plan->get_type() === 'order' ) {
			if ( $previous_plan->get_type() === 'order' && $plan->get_order_item_id() === $previous_plan->get_order_item_id() ) {
				if ( $plan->is_canceled() && ! $previous_plan->is_canceled() ) {
					(new Module\App_Events\Plan_Canceled_Event)->dispatch( $user, $plan );
				} else {
					$period_start = $plan->get_current_period_start();
					$previous_period_start = $previous_plan->get_current_period_start();
					if (
						$plan->is_active()
						&& $plan->get_current_period_start() !== null
						&& $previous_plan->get_current_period_start() !== null
						&& ( strtotime( $plan->get_current_period_start() ) > strtotime( $previous_plan->get_current_period_start() ) )
					) {
						(new Module\App_Events\Plan_Renewed_Event)->dispatch( $user, $plan );
					}
				}
			} else {
				if ( $plan->is_active() ) {
					if ( $previous_plan->get_type() !== 'order' ) {
						(new Module\App_Events\Plan_Activated_Event)->dispatch( $user, $plan );
					} else {
						if ( $previous_plan->is_canceled() ) {
							(new Module\App_Events\Plan_Activated_Event)->dispatch( $user, $plan );
						} else {
							(new Module\App_Events\Plan_Switched_Event)->dispatch( $user, $plan, $previous_plan );
						}
					}
				}
			}
		} else {
			if ( $previous_plan->get_type() === 'order' && ! $previous_plan->is_canceled() ) {
				(new Module\App_Events\Plan_Canceled_Event)->dispatch( $user, $previous_plan );
			}
		}
	}

}
