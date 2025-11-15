<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Frontend;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Checkout_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_paid_memberships.choose_plan', '@choose_plan' );
		$this->on( 'voxel_ajax_nopriv_paid_memberships.choose_plan', '@choose_plan_guest_user' );
	}

	protected function choose_plan() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_choose_plan' );

			if ( ( $_GET['plan'] ?? '' ) === 'default' ) {
				$plan = Module\Plan::get_or_create_default_plan();
				$price = null;
			} else {
				$price = Module\Price::from_checkout_key( sanitize_text_field( $_GET['plan'] ?? '' ) );
				$plan = $price->plan;
			}

			if ( $plan->is_archived() ) {
				throw new \Exception( _x( 'This plan is no longer available.', 'pricing plans', 'voxel' ) );
			}

			$customer = \Voxel\get_current_user();
			$membership = $customer->get_membership();
			$role = \Voxel\Role::get( $customer->get_role_keys()[0] ?? null );

			/**
			 * Handle requests to switch customer role upon activating a plan.
			 */
			$switch_role_key = $_REQUEST['switch_to_role'] ?? null;
			$switch_role = null;
			if ( $switch_role_key !== null ) {
				$switch_role = \Voxel\Role::get( $switch_role_key );

				if ( ! $switch_role ) {
					throw new \Exception( __( 'Could not process request, please try later.', 'voxel' ), 100 );
				}

				if ( ! $switch_role->is_switching_enabled() ) {
					throw new \Exception( __( 'Could not process request, please try later.', 'voxel' ), 101 );
				}

				$switchable_roles = $customer->get_switchable_roles();
				if ( ! isset( $switchable_roles[ $switch_role->get_key() ] ) ) {
					throw new \Exception( __( 'Could not process request, please try later.', 'voxel' ), 102 );
				}

				if ( ! $plan->supports_role( $switch_role->get_key() ) ) {
					throw new \Exception( __( 'Could not process request, please try later.', 'voxel' ), 103 );
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

			// support custom redirect
			$redirect_to = null;
			if ( ! empty( $_REQUEST['redirect_to'] ) && wp_validate_redirect( $_REQUEST['redirect_to'] ) ) {
				$redirect_to = wp_validate_redirect( $_REQUEST['redirect_to'] );
			}

			if ( $plan->get_key() === 'default' ) {
				$payment_method = $membership->get_type() === 'order' ? $membership->get_payment_method() : null;

				if ( $payment_method && ( ! $payment_method->is_subscription_canceled() ) ) {
					if ( ! empty( $_REQUEST['confirm_cancel'] ) ) {
						\Voxel\verify_nonce( $_REQUEST['confirm_cancel_nonce'] ?? '', 'voxel_plans_confirm_cancel' );

						$payment_method->cancel_subscription_immediately();

						Module\update_user_plan( $customer->get_id(), [
							'plan' => 'default',
							'type' => 'default',
						] );

						// switch role if requested
						if ( $switch_role !== null ) {
							$customer->set_role( $switch_role->get_key() );
						}

						return wp_send_json( [
							'success' => true,
							'type' => 'redirect',
							'redirect_to' => $redirect_to ?? ( get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/') ),
						] );
					} else {
						return wp_send_json( [
							'success' => true,
							'type' => 'dialog',
							'dialog' => [
								'type' => 'warning',
								'timeout' => 9000,
								'message' => _x( 'Your existing subscription will be canceled immediately. Do you want to proceed?', 'pricing plans', 'voxel' ),
								'actions' => [
									[
										'label' => _x( 'Proceed', 'pricing plans', 'voxel' ),
										'link' => add_query_arg( [
											'confirm_cancel' => true,
											'confirm_cancel_nonce' => wp_create_nonce( 'voxel_plans_confirm_cancel' ),
										], \Voxel\get_current_url() ),
										'confirm_cancel' => true,
									],
								],
							],
						] );
					}
				} else {
					Module\update_user_plan( $customer->get_id(), [
						'plan' => 'default',
						'type' => 'default',
					] );

					// switch role if requested
					if ( $switch_role !== null ) {
						$customer->set_role( $switch_role->get_key() );
					}

					return wp_send_json( [
						'success' => true,
						'type' => 'redirect',
						'redirect_to' => $redirect_to ?? ( get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/') ),
					] );
				}
			} else {
				$order = $membership->get_type() === 'order' ? $membership->get_order() : null;
				$payment_method = $membership->get_type() === 'order' ? $membership->get_payment_method() : null;

				// current plan, handle role switch (if requested)
				if ( $payment_method && ! $payment_method->is_subscription_canceled() && $membership->get_price_key() === $price->get_key() ) {
					// switch role if requested
					if ( $switch_role !== null ) {
						$customer->set_role( $switch_role->get_key() );
					}

					return wp_send_json( [
						'success' => true,
						'type' => 'redirect',
						'redirect_to' => $redirect_to ?? $order->get_link(),
					] );
				}

				if ( $payment_method && $payment_method->is_subscription_active() ) {
					// if user has an active subscription, modify and prorate

					$product = $price->get_product();
					$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( [
						'product' => [
							'post_id' => $product->get_id(),
							'field_key' => 'voxel:membership_plan',
						],
						'custom_data' => [
							'switch_role' => $switch_role ? $switch_role->get_key() : null,
							'_is_switch' => true, // skip check for existing subscription during switch
						],
					] );

					$cart_item->validate();

					// subscription updates are only possible for matching payment services and currencies
					if (
						( strtoupper( $order->get_currency() ) !== strtoupper( $cart_item->get_currency() ) )
						|| ( $payment_method->get_type() !== $cart_item->get_payment_method() )
					) {
						return wp_send_json( [
							'success' => true,
							'type' => 'dialog',
							'dialog' => [
								'type' => 'warning',
								'timeout' => 12000,
								'message' => _x( 'Cancel your existing plan to proceed.', 'pricing plans', 'voxel' ),
								'actions' => [
									[
										'label' => _x( 'Manage plan', 'pricing plans', 'voxel' ),
										'link' => $order->get_link(),
									],
								],
							],
						] );
					}

					try {
						if ( ! empty( $_REQUEST['confirm_switch'] ) ) {
							\Voxel\verify_nonce( $_REQUEST['confirm_switch_nonce'] ?? '', 'voxel_plans_confirm_switch' );

							$payment_method->update_subscription_price( $cart_item );

							// switch role if requested
							if ( $switch_role !== null ) {
								$customer->set_role( $switch_role->get_key() );
							}

							return wp_send_json( [
								'success' => true,
								'redirect_to' => $redirect_to ?? $membership->get_order()->get_link(),
							] );
						} else {
							$preview = $payment_method->preview_subscription_price_update( $cart_item );

							if ( $preview['amount_due'] > 0 ) {
								return wp_send_json( [
									'success' => true,
									'type' => 'dialog',
									'dialog' => [
										'type' => 'warning',
										'timeout' => 12000,
										'message' => sprintf(
											_x( 'Switching to this plan will incur an immediate charge of %s. Would you like to proceed?', 'pricing plans', 'voxel' ),
											\Voxel\currency_format( $preview['amount_due'], $preview['currency'], false )
										),
										'actions' => [
											[
												'label' => _x( 'Proceed', 'pricing plans', 'voxel' ),
												'link' => add_query_arg( [
													'confirm_switch' => true,
													'confirm_switch_nonce' => wp_create_nonce( 'voxel_plans_confirm_switch' ),
												], \Voxel\get_current_url() ),
												'confirm_switch' => true,
											],
										],
										'closeLabel' => _x( 'Cancel', 'pricing plans', 'voxel' ),
									],
								] );
							} elseif ( $preview['total'] < 0 ) {
								if ( apply_filters( 'voxel/paid_memberships/enable_downgrades', true ) === false ) {
									return wp_send_json( [
										'success' => true,
										'type' => 'dialog',
										'dialog' => [
											'type' => 'warning',
											'timeout' => 12000,
											'message' => _x( 'Cancel your existing plan to proceed.', 'pricing plans', 'voxel' ),
											'actions' => [
												[
													'label' => _x( 'Manage plan', 'pricing plans', 'voxel' ),
													'link' => $order->get_link(),
												],
											],
										],
									] );
								}

								return wp_send_json( [
									'success' => true,
									'type' => 'dialog',
									'dialog' => [
										'type' => 'warning',
										'timeout' => 12000,
										'message' => sprintf(
											_x( 'Switching to this plan will cost nothing today and you\'ll receive a %s credit that will be applied to future invoices. Would you like to proceed?', 'pricing plans', 'voxel' ),
											\Voxel\currency_format( abs( $preview['total'] ), $preview['currency'], false )
										),
										'actions' => [
											[
												'label' => _x( 'Proceed', 'pricing plans', 'voxel' ),
												'link' => add_query_arg( [
													'confirm_switch' => true,
													'confirm_switch_nonce' => wp_create_nonce( 'voxel_plans_confirm_switch' ),
												], \Voxel\get_current_url() ),
												'confirm_switch' => true,
											],
										],
										'closeLabel' => _x( 'Cancel', 'pricing plans', 'voxel' ),
									],
								] );
							} else {
								return wp_send_json( [
									'success' => true,
									'type' => 'dialog',
									'dialog' => [
										'type' => 'warning',
										'timeout' => 12000,
										'message' => _x( 'Your plan will be updated immediately without additional charge. Would you like to proceed?', 'pricing plans', 'voxel' ),
										'actions' => [
											[
												'label' => _x( 'Proceed', 'pricing plans', 'voxel' ),
												'link' => add_query_arg( [
													'confirm_switch' => true,
													'confirm_switch_nonce' => wp_create_nonce( 'voxel_plans_confirm_switch' ),
												], \Voxel\get_current_url() ),
												'confirm_switch' => true,
											],
										],
										'closeLabel' => _x( 'Cancel', 'pricing plans', 'voxel' ),
									],
								] );
							}
						}
					} catch ( \Exception $e ) {
						// dd($e->getMessage());
						throw new \Exception( _x( 'Could not process subscription, please try later.', 'pricing plans', 'voxel' ) );
					}
				} elseif ( $payment_method && $payment_method->is_subscription_recoverable() ) {
					throw new \Exception( _x( 'Cancel your existing subscription to proceed.', 'pricing plans', 'voxel' ) );
				} else {
					$product = $price->get_product();

					$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( [
						'product' => [
							'post_id' => $product->get_id(),
							'field_key' => 'voxel:membership_plan',
						],
						'custom_data' => [
							'switch_role' => $switch_role ? $switch_role->get_key() : null,
						],
					] );

					$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
					$cart->add_item( $cart_item );

					if ( apply_filters( 'voxel/paid_memberships/skip_cart_summary', true ) !== false ) {
						$order = \Voxel\Product_Types\Orders\Order::create_from_cart( $cart, [
							'meta' => [
								'redirect_to' => $redirect_to,
							],
						] );

						$payment_method = $order->get_payment_method();
						if ( $payment_method === null ) {
							throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
						}

						return wp_send_json( $payment_method->process_payment() );
					} else {
						$checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');
						$checkout_link = add_query_arg( 'checkout_item', $cart_item->get_key(), $checkout_link );

						if ( $redirect_to !== null ) {
							$checkout_link = add_query_arg( 'redirect_to', $redirect_to, $checkout_link );
						}

						return wp_send_json( [
							'success' => true,
							'type' => 'checkout',
							'item' => $cart_item->get_frontend_config(),
							'checkout_link' => $checkout_link,
						] );
					}
				}
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function choose_plan_guest_user() {
		try {
			if ( ( $_GET['plan'] ?? '' ) === 'default' ) {
				$plan = Module\Plan::get_or_create_default_plan();
				$price = null;
			} else {
				$price = Module\Price::from_checkout_key( sanitize_text_field( $_GET['plan'] ?? '' ) );
				$plan = $price->plan;
			}

			if ( $plan->is_archived() ) {
				throw new \Exception( _x( 'This plan is no longer available.', 'pricing plans', 'voxel' ) );
			}

			$register_role = '';
			if ( $plan->config('settings.supported_roles') === 'custom' ) {
				foreach ( (array) $plan->config('settings.supported_roles_custom') as $role_key ) {
					$role = \Voxel\Role::get( $role_key );
					if ( $role && $role->is_registration_enabled() ) {
						$register_role = $role->get_key();
						break;
					}
				}
			}

			$redirect_to = add_query_arg( [
				'register' => $register_role,
				'plan' => $_GET['plan'] ?? '',
			], \Voxel\get_auth_url() );

			return wp_send_json( [
				'success' => true,
				'type' => 'redirect',
				'redirect_to' => $redirect_to,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
