<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product_types/cart_item/validate', '@validate_cart_item' );
		$this->filter( 'voxel/product_types/cart_item/get_title', '@set_cart_item_title', 10, 2 );
		$this->filter( 'voxel/product_types/cart_item/get_link', '@set_cart_item_link', 10, 2 );
		$this->filter( 'voxel/product_types/cart_item/details', '@set_cart_item_details', 10, 2 );
	}

	protected function validate_cart_item( $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_memberships_price' ) ) {
			return;
		}

		$value = $cart_item->get_value();

		$product = $cart_item->get_product();
		$plan_key = (string) get_post_meta( $product->get_id(), '_vx_plan_key', true );
		$price_key = (string) get_post_meta( $product->get_id(), '_vx_price_key', true );

		try {
			$price = \Voxel\Modules\Paid_Memberships\Price::get( $plan_key, $price_key );
			$plan = $price->plan;
		} catch ( \Exception $e ) {
			throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 90 );
		}

		if ( $plan->is_archived() ) {
			throw new \Exception( _x( 'This plan is no longer available.', 'pricing plans', 'voxel' ), 91 );
		}

		$customer = \Voxel\get_current_user();
		if ( $customer ) {
			// prevent purchase of new membership plan if user has an existing active or recoverable subscription
			$membership = $customer->get_membership();
			if ( $membership->get_type() === 'order' && empty( $value['custom_data']['_is_switch'] ?? null ) ) {
				$payment_method = $membership->get_payment_method();
				if ( $payment_method && ! ( $payment_method->is_subscription_canceled() ) ) {
					throw new \Exception( _x( 'You already have a subscription. Cancel it before purchasing another.', 'pricing plans', 'voxel' ), 91 );
				}
			}

			$role = \Voxel\Role::get( $customer->get_role_keys()[0] ?? null );

			/**
			 * Handle requests to switch customer role upon activating a plan.
			 */
			$switch_role_key = $value['custom_data']['switch_role'] ?? null;
			$switch_role = null;
			if ( $switch_role_key !== null ) {
				$switch_role = \Voxel\Role::get( $switch_role_key );

				if ( ! $switch_role ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 100 );
				}

				if ( ! $switch_role->is_switching_enabled() ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 101 );
				}

				$switchable_roles = $customer->get_switchable_roles();
				if ( ! isset( $switchable_roles[ $switch_role->get_key() ] ) ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 102 );
				}

				if ( ! $plan->supports_role( $switch_role->get_key() ) ) {
					throw new \Exception( __( 'Invalid request.', 'voxel' ), 103 );
				}

				if ( $customer->has_role( 'administrator' ) || $customer->has_role( 'editor' ) ) {
					throw new \Exception( _x( 'Switching roles is not allowed for Administrator and Editor accounts.', 'roles', 'voxel' ), 102 );
				}

				// if customer already has this role, process checkout without the role-switch request
				if ( $customer->has_role( $switch_role->get_key() ) ) {
					$switch_role = null;
				}
			}

			// if role-switch is not requested, check if customer has at least one role that supports chosen plan
			if ( $switch_role === null ) {
				if ( ! $plan->supports_user( $customer ) ) {
					throw new \Exception( _x( 'This plan is not supported by your current role.', 'roles', 'voxel' ), 110 );
				}
			}
		}
	}

	protected function set_cart_item_title( $title, $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_memberships_price' ) ) {
			return $title;
		}

		$product = $cart_item->get_product();
		$plan_key = (string) get_post_meta( $product->get_id(), '_vx_plan_key', true );
		$price_key = (string) get_post_meta( $product->get_id(), '_vx_price_key', true );

		try {
			$price = \Voxel\Modules\Paid_Memberships\Price::get( $plan_key, $price_key );
		} catch ( \Exception $e ) {
			return $title;
		}

		return $price->get_label();
	}

	protected function set_cart_item_link( $link, $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_memberships_price' ) ) {
			return $link;
		}

		return null;
	}

	protected function set_cart_item_details( $meta, $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_memberships_price' ) ) {
			return $meta;
		}

		$product = $cart_item->get_product();
		$plan_key = (string) get_post_meta( $product->get_id(), '_vx_plan_key', true );
		$price_key = (string) get_post_meta( $product->get_id(), '_vx_price_key', true );

		$price = \Voxel\Modules\Paid_Memberships\Price::get( $plan_key, $price_key );

		if (
			isset( $meta['subscription'] )
			&& $price->get_trial_days()
			&& is_user_logged_in()
			&& \Voxel\get_current_user()->is_eligible_for_free_trial()
		) {
			$meta['subscription']['trial_days'] = $price->get_trial_days();
		}

		$meta['voxel:membership'] = [
			'plan' => $plan_key,
			'price' => $price_key,
		];

		$value = $cart_item->get_value();

		$switch_role_key = $value['custom_data']['switch_role'] ?? null;
		if ( $switch_role_key !== null ) {
			$meta['voxel:membership']['switch_role'] = $switch_role_key;
		}

		return $meta;
	}
}
