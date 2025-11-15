<?php

namespace Voxel\Modules\Stripe_Payments\Payment_Methods;

use \Voxel\Modules\Stripe_Payments as Module;
use \Voxel\Product_Types\Cart_Items\Cart_Item;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Subscription extends \Voxel\Product_Types\Payment_Methods\Base_Payment_Method {

	public function get_type(): string {
		return 'stripe_subscription';
	}

	public function get_label(): string {
		return _x( 'Stripe subscription', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		try {
			$customer = $this->order->get_customer();
			$stripe_customer = $customer->get_or_create_stripe_customer();
			$billing_address_collection = \Voxel\get( 'payments.stripe.subscriptions.billing_address_collection', 'auto' );
			$tax_id_collection = !! \Voxel\get( 'payments.stripe.subscriptions.tax_id_collection.enabled', true );

			$tax_collection_method = null;
			if ( \Voxel\get( 'payments.stripe.tax_collection.enabled' ) ) {
				$tax_collection_method = \Voxel\get( 'payments.stripe.tax_collection.collection_method', 'stripe_tax' );
			}

			$trial_period_days = null;

			$args = [
				'client_reference_id' => sprintf( 'order:%d', $this->order->get_id() ),
				'customer' => $stripe_customer->id,
				'mode' => 'subscription',
				'currency' => $this->order->get_currency(),
				'customer_update' => [
					'address' => 'auto',
					'name' => 'auto',
					'shipping' => 'auto',
				],
				'locale' => 'auto',
				'line_items' => array_map( function( $line_item ) use ( $tax_collection_method, &$trial_period_days ) {
					$order_item = $line_item['order_item'];
					$data = [
						'quantity' => $line_item['quantity'],
						'price_data' => [
							'currency' => $line_item['currency'],
							'unit_amount_decimal' => $line_item['amount_in_cents'],
							'product_data' => [
								'name' => $line_item['product']['label'],
							],
						],
					];

					if ( ! empty( $line_item['product']['description'] ) ) {
						$data['price_data']['product_data']['description'] = $line_item['product']['description'];
					}

					if ( ! empty( $line_item['product']['thumbnail_url'] ) ) {
						$data['price_data']['product_data']['images'] = [ $line_item['product']['thumbnail_url'] ];
					}

					if ( $tax_collection_method === 'stripe_tax' ) {
						$tax_behavior = \Voxel\get( sprintf(
							'payments.stripe.tax_collection.stripe_tax.product_types.%s.tax_behavior',
							$order_item->get_product_type_key()
						), 'default' );

						if ( in_array( $tax_behavior, [ 'inclusive', 'exclusive' ], true ) ) {
							$data['price_data']['tax_behavior'] = $tax_behavior;
						}

						$tax_code = \Voxel\get( sprintf(
							'payments.stripe.tax_collection.stripe_tax.product_types.%s.tax_code',
							$order_item->get_product_type_key()
						) );

						if ( ! empty( $tax_code ) ) {
							$data['price_data']['product_data']['tax_code'] = $tax_code;
						}
					} elseif ( $tax_collection_method === 'tax_rates' ) {
						$tax_calculation_method = \Voxel\get( sprintf(
							'payments.stripe.tax_collection.tax_rates.product_types.%s.calculation_method',
							$order_item->get_product_type_key()
						), 'fixed' );

						if ( $tax_calculation_method === 'fixed' ) {
							$tax_rates = \Voxel\get( sprintf(
								'payments.stripe.tax_collection.tax_rates.product_types.%s.fixed_rates.%s',
								$order_item->get_product_type_key(),
								Module\Stripe_Client::is_test_mode() ? 'test_mode' : 'live_mode'
							), [] );

							if ( ! empty( $tax_rates ) ) {
								$data['tax_rates'] = $tax_rates;
							}
						} elseif ( $tax_calculation_method === 'dynamic' ) {
							$dynamic_tax_rates = \Voxel\get( sprintf(
								'payments.stripe.tax_collection.tax_rates.product_types.%s.dynamic_rates.%s',
								$order_item->get_product_type_key(),
								Module\Stripe_Client::is_test_mode() ? 'test_mode' : 'live_mode'
							), [] );

							if ( ! empty( $dynamic_tax_rates ) ) {
								$data['dynamic_tax_rates'] = $dynamic_tax_rates;
							}
						}
					}

					if ( is_numeric( $order_item->get_details('subscription.trial_days') ) ) {
						$trial_period_days = $order_item->get_details('subscription.trial_days');
					}

					$data['price_data']['recurring'] = [
						'interval' => $order_item->get_details('subscription.unit'),
						'interval_count' => $order_item->get_details('subscription.frequency'),
					];

					return $data;
				}, $this->get_line_items() ),
				'subscription_data' => [
					'metadata' => [
						'voxel:payment_for' => 'order',
						'voxel:order_id' => $this->order->get_id(),
					],
					'trial_period_days' => $trial_period_days,
				],
				'success_url' => $this->get_success_url(),
				'cancel_url' => $this->get_cancel_url(),
				'metadata' => [
					'voxel:payment_for' => 'order',
					'voxel:order_id' => $this->order->get_id(),
				],
				'billing_address_collection' => $billing_address_collection === 'required' ? 'required' : 'auto',
				'tax_id_collection' => [
					'enabled' => $tax_id_collection,
				],
				'allow_promotion_codes' => !! \Voxel\get( 'payments.stripe.subscriptions.promotion_codes.enabled', false ),
			];

			if ( $tax_collection_method === 'stripe_tax' ) {
				$args['automatic_tax'] = [
					'enabled' => true,
				];
			}

			if ( \Voxel\get( 'payments.stripe.subscriptions.phone_number_collection.enabled' ) ) {
				$args['phone_number_collection'] = [
					'enabled' => true,
				];
			}

			$vendor = $this->order->get_vendor();
			if ( $vendor !== null && $vendor->is_active_vendor() ) {
				if ( \Voxel\get('payments.stripe.stripe_connect.subscriptions.charge_type') === 'destination_charges' ) {
					$args['subscription_data']['application_fee_percent'] = $this->get_application_fee_percent();
					$args['subscription_data']['transfer_data'] = [
						'destination' => $vendor->get_stripe_vendor_id(),
					];

					$args['allow_promotion_codes'] = false;

					if ( \Voxel\get('payments.stripe.stripe_connect.subscriptions.settlement_merchant') === 'vendor' ) {
						$args['subscription_data']['on_behalf_of'] = $vendor->get_stripe_vendor_id();

						if ( $tax_collection_method === 'stripe_tax' ) {
							$args['automatic_tax'] = [
								'enabled' => true,
								'liability' => [
									'type' => 'self',
								],
							];

							$args['subscription_data']['invoice_settings'] = [
								'issuer' => [
									'type' => 'self',
								],
							];
						}
					}

					$this->order->set_details( 'multivendor.mode', 'destination_charges' );
					$this->order->set_details( 'multivendor.vendor_fees', $vendor->get_vendor_fees() );
				}
			}

			$session = \Voxel\Vendor\Stripe\Checkout\Session::create( $args );

			$total_order_amount = $session->amount_total;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
			$this->order->set_details( 'checkout.session_id', $session->id );

			$this->order->save();

			return [
				'success' => true,
				'redirect_url' => $session->url,
			];
		} catch ( \Voxel\Vendor\Stripe\Exception\ApiErrorException | \Voxel\Vendor\Stripe\Exception\InvalidArgumentException $e ) {
			return [
				'success' => false,
				'message' => _x( 'Something went wrong', 'checkout', 'voxel' ),
				'debug' => [
					'type' => 'stripe_error',
					'code' => method_exists( $e, 'getStripeCode' ) ? $e->getStripeCode() : $e->getCode(),
					'message' => $e->getMessage(),
				],
			];
		}
	}

	protected function get_success_url() {
		return add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_subscriptions.checkout.success',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
		], home_url('/') );
	}

	protected function get_cancel_url() {
		$redirect_url = wp_get_referer() ?: home_url('/');
		$redirect_url = add_query_arg( 't', time(), $redirect_url );

		return add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_subscriptions.checkout.cancel',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
			'redirect_to' => rawurlencode( $redirect_url ),
		], home_url('/') );
	}

	public function subscription_updated(
		\Voxel\Vendor\Stripe\Subscription $subscription,
		\Voxel\Vendor\Stripe\Checkout\Session $session = null
	) {
		$stripe = Module\Stripe_Client::getClient();

		$this->order->set_status( sprintf( 'sub_%s', $subscription->status ) );
		$this->order->set_transaction_id( $subscription->id );

		$this->order->set_details( 'subscription', [
			'id' => $subscription->id,
			'cancel_at_period_end' => $subscription->cancel_at_period_end,
			'currency' => $subscription->currency,
			'customer' => $subscription->customer,
			'status' => $subscription->status,
			'cancel_at' => $subscription->cancel_at,
			'canceled_at' => $subscription->canceled_at,
			'cancellation_details' => [
				'reason' => $subscription->cancellation_details->reason,
			],
			'ended_at' => $subscription->ended_at,
			'pause_collection' => $subscription->pause_collection,
			'livemode' => $subscription->livemode,
			'trial_end' => $subscription->trial_end,
			'application_fee_percent' => $subscription->application_fee_percent,
			'transfer_data' => [
				'destination' => $subscription->transfer_data->destination ?? null,
			],
			'items' => array_map( function( $item ) {
				$price = $item->price;
				return [
					'id' => $item->id,
					'price' => [
						'currency' => $price->currency,
						'recurring' => [
							'interval' => $price->recurring->interval,
							'interval_count' => $price->recurring->interval_count,
						],
						'unit_amount' => $price->unit_amount,
					],
					'current_period_end' => $item->current_period_end ?? null,
					'current_period_start' => $item->current_period_start ?? null,
				];
			}, $subscription->items->data ),
			'latest_invoice' => null,
		] );

		if ( $subscription->latest_invoice !== null ) {
			if ( $subscription->latest_invoice instanceof \Voxel\Vendor\Stripe\Invoice ) {
				$latest_invoice = $subscription->latest_invoice;
			} else {
				$latest_invoice = $stripe->invoices->retrieve( $subscription->latest_invoice, [] );
			}
		}

		if ( $latest_invoice instanceof \Voxel\Vendor\Stripe\Invoice ) {
			$this->order->set_details( 'subscription.latest_invoice', [
				'id' => $latest_invoice->id,
				'currency' => $latest_invoice->currency,
				'status' => $latest_invoice->status,
				'total' => $latest_invoice->total,
				'billing_reason' => $latest_invoice->billing_reason,
				'application_fee_amount' => $latest_invoice->application_fee_amount ?? null,
				'transfer_data' => [
					'destination' => $latest_invoice->transfer_data->destination ?? null,
				],
				'_discount' => array_sum( array_column( $latest_invoice->total_discount_amounts, 'amount' ) ),
			] );
		}

		$subscription_item = $subscription->items->data[0] ?? null;
		if ( $subscription_item ) {
			$price = $subscription_item->price;

			$total_order_amount = $price->unit_amount;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $price->currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
		}

		$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );

		if ( $session ) {
			$this->order->set_details( 'checkout.session_details', [
				'customer_details' => [
					'address' => [
						'city' => $session->customer_details->address->city ?? null,
						'country' => $session->customer_details->address->country ?? null,
						'line1' => $session->customer_details->address->line1 ?? null,
						'line2' => $session->customer_details->address->line2 ?? null,
						'postal_code' => $session->customer_details->address->postal_code ?? null,
						'state' => $session->customer_details->address->state ?? null,
					],
					'email' => $session->customer_details->email ?? null,
					'name' => $session->customer_details->name ?? null,
					'phone' => $session->customer_details->phone ?? null,
				],
			] );
		}

		$this->order->save();
	}

	public function should_sync(): bool {
		return ! $this->order->get_details( 'checkout.last_synced_at' );
	}

	public function sync(): void {
		$stripe = Module\Stripe_Client::getClient();
		if ( $transaction_id = $this->order->get_transaction_id() ) {
			$subscription = $stripe->subscriptions->retrieve( $transaction_id );
			$this->subscription_updated( $subscription );
		} elseif ( $checkout_session_id = $this->order->get_details( 'checkout.session_id' ) ) {
			$session = $stripe->checkout->sessions->retrieve( $checkout_session_id, [
				'expand' => [ 'subscription' ],
			] );

			$subscription = $session->subscription;
			if ( $subscription !== null ) {
				$this->subscription_updated( $subscription, $session );
			}
		} else {
			//
		}
	}

	protected function get_application_fee_percent() {
		$currency = $this->order->get_currency();
		$subtotal_in_cents = $this->order->get_subtotal();
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$subtotal_in_cents *= 100;
		}

		$application_fee_amount = 0;
		foreach ( $this->order->get_vendor()->get_vendor_fees() as $fee ) {
			if ( $fee['type'] === 'fixed' ) {
				$fee_amount_in_cents = $fee['fixed_amount'];
				if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
					$fee_amount_in_cents *= 100;
				}

				$application_fee_amount += $fee_amount_in_cents;
			} elseif ( $fee['type'] === 'percentage' ) {
				$pct = $fee['percentage_amount'];
				$application_fee_amount += ( $subtotal_in_cents * ( $pct / 100 ) );
			}
		}

		if ( $subtotal_in_cents <= 0 || $subtotal_in_cents < $application_fee_amount ) {
			return 0;
		}

		$percentage = abs( ( $application_fee_amount / $subtotal_in_cents ) * 100 );

		return round( $percentage, 2 );
	}

	public function get_vendor_fees_summary(): array {
		if ( $this->order->get_details('multivendor.mode') === 'destination_charges' ) {
			$currency = $this->order->get_currency();
			$application_fee_amount = $this->order->get_details( 'subscription.latest_invoice.application_fee_amount' );
			if ( ! is_numeric( $application_fee_amount ) ) {
				return [];
			}

			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$application_fee_amount /= 100;
			}

			$details = [
				'total' => $application_fee_amount,
				'breakdown' => [],
			];

			foreach ( (array) $this->order->get_details('multivendor.vendor_fees', []) as $fee ) {
				if ( ( $fee['type'] ?? null ) === 'fixed' ) {
					if ( ! is_numeric( $fee['fixed_amount'] ?? null ) && $fee['fixed_amount'] > 0 ) {
						continue;
					}

					$subtotal = $this->order->get_subtotal();
					if ( ! is_numeric( $subtotal ) || $subtotal <= 0 || $subtotal < $fee['fixed_amount'] ) {
						continue;
					}

					$pct = round( abs( ( $fee['fixed_amount'] / $subtotal ) * 100 ), 2 );

					$details['breakdown'][] = [
						'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
						// 'content' => \Voxel\currency_format( $fee['fixed_amount'], $currency, false ),
						'content' => $pct.'%',
					];
				} elseif ( ( $fee['type'] ?? null ) === 'percentage' ) {
					if ( ! is_numeric( $fee['percentage_amount'] ?? null ) && $fee['percentage_amount'] > 0 && $fee['percentage_amount'] <= 100 ) {
						continue;
					}

					$details['breakdown'][] = [
						'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
						'content' => round( $fee['percentage_amount'], 2 ).'%',
					];
				}
			}

			return $details;
		} else {
			return [];
		}
	}

	public function get_billing_interval(): ?array {
		$interval = $this->order->get_details( 'subscription.items.0.price.recurring.interval' );
		$interval_count = $this->order->get_details( 'subscription.items.0.price.recurring.interval_count' );

		if ( $interval && $interval_count ) {
			return [
				'type' => 'recurring',
				'interval' => $interval,
				'interval_count' => $interval_count,
			];
		} else {
			foreach ( $this->order->get_items() as $item ) {
				$interval = $item->get_details( 'subscription.unit' );
				$interval_count = $item->get_details( 'subscription.frequency' );

				if ( $interval && $interval_count ) {
					return [
						'type' => 'recurring',
						'interval' => $interval,
						'interval_count' => $interval_count,
					];
				}
			}
		}

		return null;
	}

	public function get_current_billing_period(): ?array {
		if ( $subscription = $this->order->get_details('subscription') ) {
			$start_timestamp = $subscription['items'][0]['current_period_start'] ?? null;
			$end_timestamp = $subscription['items'][0]['current_period_end'] ?? null;

			return [
				'start' => is_numeric( $start_timestamp ) ? date( 'Y-m-d H:i:s', $start_timestamp ) : null,
				'end' => is_numeric( $end_timestamp ) ? date( 'Y-m-d H:i:s', $end_timestamp ) : null,
			];
		}

		return null;
	}

	public function get_state(): array {
		if ( $this->order->get_status() !== 'pending_payment' && ( $subscription = $this->order->get_details('subscription') ) ) {
			$state = $subscription['status'];
			$pause_collection = $subscription['pause_collection'] ?? null;
			$cancel_at_period_end = $subscription['cancel_at_period_end'] ?? null;

			$current_period_end = $subscription['current_period_end'] ?? null;
			if ( ! $current_period_end ) {
				$current_period_end = $subscription['items'][0]['current_period_end'] ?? null;
			}

			if ( $state === 'incomplete_expired' ) {
				return [
					'status' => 'incomplete_expired',
					'label' => _x( 'Expired', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription expired', 'order status', 'voxel' ),
					'message' => _x( 'Subscription expired after failed payment', 'subscriptions', 'voxel' ),
					'class' => 'vx-red',
					'actions' => null,
				];
			} elseif ( $state === 'incomplete' ) {
				return [
					'status' => 'incomplete',
					'label' => _x( 'Incomplete', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription incomplete', 'order status', 'voxel' ),
					'message' => _x( 'First payment required to activate', 'subscriptions', 'voxel' ),
					'class' => 'vx-orange',
					'actions' => [
						'payments/stripe_subscription/customers/incomplete.pay_now',
						'payments/stripe_subscription/customers/incomplete.cancel',
						'payments/stripe_subscription/customers/access_portal',
					],
				];
			} elseif ( $state === 'trialing' ) {
				return [
					'status' => 'trialing',
					'label' => _x( 'Active', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
					'message' => sprintf(
						_x( 'Trial ends on %s', 'subscriptions', 'voxel' ),
						\Voxel\datetime_format( $subscription['trial_end'] )
					),
					'class' => 'vx-green',
					'actions' => [
						'payments/stripe_subscription/customers/trialing.activate',
						'payments/stripe_subscription/customers/trialing.cancel',
						// 'payments/stripe_subscription/customers/access_portal',
					],
				];
			} elseif ( $state === 'paused' ) {
				return [
					'status' => 'paused',
					'label' => _x( 'Paused', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is paused', 'order status', 'voxel' ),
					'class' => 'vx-orange',
					'actions' => [
						'payments/stripe_subscription/customers/paused.resume',
						'payments/stripe_subscription/customers/paused.cancel',
						// 'payments/stripe_subscription/customers/access_portal',
					],
				];
			} elseif ( $state === 'active' ) {
				if ( $cancel_at_period_end ) {
					return [
						'status' => 'scheduled_cancel',
						'label' => _x( 'Active', 'order status', 'voxel' ),
						'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
						'message' => sprintf(
							_x( 'Cancels on %s', 'subscriptions', 'voxel' ),
							\Voxel\datetime_format( $current_period_end )
						),
						'class' => 'vx-green',
						'actions' => [
							'payments/stripe_subscription/customers/scheduled_cancel.resume',
						],
					];
				} else {
					return [
						'status'  => 'active',
						'label' => _x( 'Active', 'order status', 'voxel' ),
						'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
						'class' => 'vx-green',
						'message' => sprintf(
							_x( 'Next renewal on %s', 'subscriptions', 'voxel' ),
							\Voxel\datetime_format( $current_period_end )
						),
						'actions' => [
							// 'payments/stripe_subscription/customers/access_portal',
							'payments/stripe_subscription/customers/active.cancel',
						],
					];
				}
			} elseif ( $state === 'past_due' ) {
				return [
					'status'  => 'past_due',
					'label' => _x( 'Past due', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is past due', 'order status', 'voxel' ),
					'class' => 'vx-orange',
					'message' => _x( 'Payment failed — update your card', 'subscriptions', 'voxel' ),
					'actions' => [
						'payments/stripe_subscription/customers/incomplete.pay_now',
						'payments/stripe_subscription/customers/incomplete.cancel',
						'payments/stripe_subscription/customers/access_portal',
					],
				];
			} elseif ( $state === 'unpaid' ) {
				return [
					'status'  => 'unpaid',
					'label' => _x( 'Unpaid', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription unpaid', 'order status', 'voxel' ),
					'class' => 'vx-orange',
					'message' => _x( 'Subscription has been deactivated due to failed renewal attempts.', 'subscriptions', 'voxel' ),
					'actions' => [
						'payments/stripe_subscription/customers/incomplete.pay_now',
						'payments/stripe_subscription/customers/incomplete.cancel',
						'payments/stripe_subscription/customers/access_portal',
					],
				];
			} elseif ( $state === 'canceled' ) {
				return [
					'status' => 'canceled',
					'label' => _x( 'Canceled', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription canceled', 'order status', 'voxel' ),
					'message' => $this->order->get_status_last_updated_for_display(),
					'class' => 'vx-red',
					'actions' => null,
				];
			} else {
				return [
					'status' => null,
				];
			}
		} else {
			return [
				'status' => null,
			];
		}
	}

	protected function resync_subscription_details(): void {
		if ( $subscription = $this->order->get_details('subscription') ) {
			$stripe = Module\Stripe_Client::get_client();
			$this->subscription_updated( $stripe->subscriptions->retrieve( $subscription['id'] ) );
		}
	}

	public function get_customer_actions(): array {
		$actions = [];

		$stripe = Module\Stripe_Client::get_client();
		$state = $this->get_state();
		$subscription = $this->order->get_details('subscription');

		if ( in_array( $state['status'], [ 'incomplete', 'past_due', 'unpaid' ], true ) ) {
			$actions[] = [
				'action' => 'incomplete.pay_now',
				'label' => _x( 'Pay now', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be activated immediately, and your payment method will be charged right away. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$invoice = $stripe->invoices->retrieve( $this->order->get_details( 'subscription.latest_invoice.id' ) );

					if ( $invoice->status === 'draft' ) {
						$stripe->invoices->finalizeInvoice( $invoice->id, [
							'auto_advance' => true,
						] );
					} else {
						if ( $invoice->hosted_invoice_url ) {
							return wp_send_json( [
								'success' => true,
								'redirect_to' => $invoice->hosted_invoice_url,
							] );
						} else {
							$stripe->invoices->pay( $invoice->id );
						}
					}

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'incomplete.cancel',
				'label' => _x( 'Cancel subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled immediately. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'trialing' ) {
			$actions[] = [
				'action' => 'trialing.activate',
				'label' => _x( 'Activate now', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be activated immediately, and your payment method will be charged right away. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->update( $this->order->get_transaction_id(), [
						'trial_end' => 'now',
					] );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'trialing.cancel',
				'label' => _x( 'Cancel subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled immediately. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'paused' ) {
			$actions[] = [
				'action' => 'paused.resume',
				'label' => _x( 'Resume subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will resume immediately, and your payment method will be charged right away. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->resume( $this->order->get_transaction_id() );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'paused.cancel',
				'label' => _x( 'Cancel subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled immediately. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'scheduled_cancel' ) {
			$actions[] = [
				'action' => 'scheduled_cancel.resume',
				'label' => _x( 'Resume subscription', 'order customer actions', 'voxel' ),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->update( $this->order->get_transaction_id(), [
						'cancel_at_period_end' => false,
					] );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'active' ) {
			$actions[] = [
				'action' => 'active.cancel',
				'label' => _x( 'Cancel renewal', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled at the end of the current billing period. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->update( $this->order->get_transaction_id(), [
						'cancel_at_period_end' => true,
					] );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'active.cancel_immediately',
				'label' => _x( 'Cancel subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled immediately. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $stripe ) {
					$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );

					$this->subscription_updated( $subscription );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		$actions[] = [
			'action' => 'access_portal',
			'label' => _x( 'Customer portal', 'order customer actions', 'voxel' ),
			'handler' => function() use ( $stripe ) {
				$session = $stripe->billingPortal->sessions->create( [
					'customer' => \Voxel\current_user()->get_stripe_customer_id(),
					'configuration' => Module\Stripe_Client::get_portal_configuration_id(),
					'return_url' => $this->order->get_link(),
				] );

				return wp_send_json( [
					'success' => true,
					'redirect_to' => $session->url,
				] );
			},
		];

		return $actions;
	}

	public function is_subscription(): bool {
		return true;
	}

	public function is_subscription_recoverable(): bool {
		return ! in_array( $this->order->get_status(), [
			'sub_canceled',
			'sub_incomplete_expired',
		], true );
	}

	public function update_subscription_price( Cart_Item $cart_item ) {
		$pricing_summary = $cart_item->get_pricing_summary();
		$new_amount_in_cents = $pricing_summary['total_amount'];
		$new_interval = $cart_item->get_product_field()->get_value()['subscription'];
		$new_currency = $cart_item->get_currency();

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $new_currency ) ) {
			$new_amount_in_cents = $new_amount_in_cents * 100;
		}

		$stripe = $this->order->is_test_mode()
			? Module\Stripe_Client::get_test_client()
			: Module\Stripe_Client::get_live_client();

		$product = Module\get_subscription_update_product( $this->order->is_test_mode() );
		$subscription = $stripe->subscriptions->retrieve( $this->order->get_transaction_id() );
		$subscription_item = $subscription->items->data[0];

		$tax_collection_method = null;
		if ( \Voxel\get( 'payments.stripe.tax_collection.enabled' ) ) {
			$tax_collection_method = \Voxel\get( 'payments.stripe.tax_collection.collection_method', 'stripe_tax' );
		}

		$args = [
			'items' => [],
			'payment_behavior' => 'allow_incomplete',
			'proration_behavior' => 'always_invoice',
			'billing_cycle_anchor' => 'now',
			'discounts' => '',
			'trial_end' => 'now',
		];

		$line_item = [
			'id' => $subscription_item->id,
			'price_data' => [
				'product' => $product->id,
				'currency' => $new_currency,
				'recurring' => [
					'interval' => $new_interval['unit'],
					'interval_count' => $new_interval['frequency'],
				],
				'unit_amount_decimal' => $new_amount_in_cents,
			],
			'quantity' => 1,
		];

		if ( $tax_collection_method === 'stripe_tax' ) {
			$args['automatic_tax'] = [
				'enabled' => true,
			];

			$tax_behavior = \Voxel\get( sprintf(
				'payments.stripe.tax_collection.stripe_tax.product_types.%s.tax_behavior',
				$cart_item->get_product_type_key()
			), 'default' );

			if ( in_array( $tax_behavior, [ 'inclusive', 'exclusive' ], true ) ) {
				$line_item['price_data']['tax_behavior'] = $tax_behavior;
			}

			// tax code can be set in the Stripe dashboard (product category: subscription_update)
		} elseif ( $tax_collection_method === 'tax_rates' ) {
			$tax_calculation_method = \Voxel\get( sprintf(
				'payments.stripe.tax_collection.tax_rates.product_types.%s.calculation_method',
				$cart_item->get_product_type_key()
			), 'fixed' );

			if ( $tax_calculation_method === 'fixed' ) {
				$tax_rates = \Voxel\get( sprintf(
					'payments.stripe.tax_collection.tax_rates.product_types.%s.fixed_rates.%s',
					$cart_item->get_product_type_key(),
					$this->order->is_test_mode() ? 'test_mode' : 'live_mode'
				), [] );

				if ( ! empty( $tax_rates ) ) {
					$line_item['tax_rates'] = $tax_rates;
				}
			} elseif ( $tax_calculation_method === 'dynamic' ) {
				// dynamic_tax_rates not supported on upgrades
			}
		}

		$args['items'][] = $line_item;

		$updated_subscription = $stripe->subscriptions->update( $subscription->id, $args );

		// update order meta
		$this->order->set_details( 'cart.items', [
			$cart_item->get_key() => $cart_item->get_value_for_storage(),
		] );

		$this->order->set_details( 'pricing.currency', $new_currency );
		$this->order->set_details( 'pricing.subtotal', $pricing_summary['total_amount'] );

		$this->order->save();

		// delete old order item
		foreach ( $this->order->get_items() as $order_item ) {
			$order_item->delete();
		}

		// add new order item
		global $wpdb;

		$wpdb->insert( $wpdb->prefix.'vx_order_items', [
			'order_id' => $this->order->get_id(),
			'post_id' => $cart_item->get_post()->get_id(),
			'product_type' => $cart_item->get_product_type()->get_key(),
			'field_key' => $cart_item->get_product_field()->get_key(),
			'details' => wp_json_encode( Schema::optimize_for_storage( $cart_item->get_order_item_config() ) ),
		] );

		// clear order item cache
		$this->order = \Voxel\Product_Types\Orders\Order::force_get( $this->order->get_id() );

		$this->subscription_updated( $updated_subscription );
	}

	public function preview_subscription_price_update( Cart_Item $cart_item ) {
		$new_amount_in_cents = $cart_item->get_pricing_summary()['total_amount'];
		$new_interval = $cart_item->get_product_field()->get_value()['subscription'];
		$new_currency = \Voxel\get( 'payments.stripe.currency', 'USD' );

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $new_currency ) ) {
			$new_amount_in_cents = $new_amount_in_cents * 100;
		}

		$stripe = $this->order->is_test_mode()
			? Module\Stripe_Client::get_test_client()
			: Module\Stripe_Client::get_live_client();

		$product = Module\get_subscription_update_product( $this->order->is_test_mode() );
		$subscription = $stripe->subscriptions->retrieve( $this->order->get_transaction_id() );
		$subscription_item = $subscription->items->data[0];

		$tax_collection_method = null;
		if ( \Voxel\get( 'payments.stripe.tax_collection.enabled' ) ) {
			$tax_collection_method = \Voxel\get( 'payments.stripe.tax_collection.collection_method', 'stripe_tax' );
		}

		$args = [
			'customer' => $subscription->customer,
			'subscription' => $subscription->id,
			'discounts' => '',
			'preview_mode' => 'next',
			'subscription_details' => [
				'billing_cycle_anchor' => 'now',
				'proration_behavior' => 'always_invoice',
				'items' => [],
				'trial_end' => 'now',
			],
		];

		$line_item = [
			'id' => $subscription_item->id,
			'price_data' => [
				'product' => $product->id,
				'currency' => $new_currency,
				'recurring' => [
					'interval' => $new_interval['unit'],
					'interval_count' => $new_interval['frequency'],
				],
				'unit_amount_decimal' => $new_amount_in_cents,
			],
			'quantity' => 1,
		];

		if ( $tax_collection_method === 'stripe_tax' ) {
			$args['automatic_tax'] = [
				'enabled' => true,
			];

			$tax_behavior = \Voxel\get( sprintf(
				'payments.stripe.tax_collection.stripe_tax.product_types.%s.tax_behavior',
				$cart_item->get_product_type_key()
			), 'default' );

			if ( in_array( $tax_behavior, [ 'inclusive', 'exclusive' ], true ) ) {
				$line_item['price_data']['tax_behavior'] = $tax_behavior;
			}

			// tax code can be set in the Stripe dashboard (product category: subscription_update)
		} elseif ( $tax_collection_method === 'tax_rates' ) {
			$tax_calculation_method = \Voxel\get( sprintf(
				'payments.stripe.tax_collection.tax_rates.product_types.%s.calculation_method',
				$cart_item->get_product_type_key()
			), 'fixed' );

			if ( $tax_calculation_method === 'fixed' ) {
				$tax_rates = \Voxel\get( sprintf(
					'payments.stripe.tax_collection.tax_rates.product_types.%s.fixed_rates.%s',
					$cart_item->get_product_type_key(),
					$this->order->is_test_mode() ? 'test_mode' : 'live_mode'
				), [] );

				if ( ! empty( $tax_rates ) ) {
					$line_item['tax_rates'] = $tax_rates;
				}
			} elseif ( $tax_calculation_method === 'dynamic' ) {
				// dynamic_tax_rates not supported on upgrades
			}
		}

		$args['subscription_details']['items'][] = $line_item;

		$preview = $stripe->invoices->createPreview( $args );

		$amount_due = $preview->amount_due;
		$total = $preview->total;

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $preview->currency ) ) {
			$amount_due /= 100;
			$total /= 100;
		}

		return [
			'amount_due' => $amount_due,
			'total' => $total,
			'currency' => $preview->currency,
		];
	}

	public function cancel_subscription_immediately() {
		$stripe = $this->order->is_test_mode()
			? Module\Stripe_Client::get_test_client()
			: Module\Stripe_Client::get_live_client();

		$subscription = $stripe->subscriptions->cancel( $this->order->get_transaction_id() );

		$this->subscription_updated( $subscription );
	}
}
