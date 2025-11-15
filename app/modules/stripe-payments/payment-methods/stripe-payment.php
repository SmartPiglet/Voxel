<?php

namespace Voxel\Modules\Stripe_Payments\Payment_Methods;

use \Voxel\Modules\Stripe_Payments as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Payment extends \Voxel\Product_Types\Payment_Methods\Base_Payment_Method {

	public function get_type(): string {
		return 'stripe_payment';
	}

	public function get_label(): string {
		return _x( 'Stripe payment', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		try {
			$customer = $this->order->get_customer();
			$stripe_customer = $customer->get_or_create_stripe_customer();
			$billing_address_collection = \Voxel\get( 'payments.stripe.payments.billing_address_collection', 'auto' );

			$args = [
				'client_reference_id' => sprintf( 'order:%d', $this->order->get_id() ),
				'customer' => $stripe_customer->id,
				'mode' => 'payment',
				'currency' => $this->order->get_currency(),
				'customer_update' => [
					'address' => 'auto',
					'name' => 'auto',
					'shipping' => 'auto',
				],
				'locale' => 'auto',
				'line_items' => array_map( function( $line_item ) {
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

					if ( $this->get_tax_collection_method() === 'stripe_tax' ) {
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
					} elseif ( $this->get_tax_collection_method() === 'tax_rates' ) {
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

					return $data;
				}, $this->get_line_items() ),
				'payment_intent_data' => [
					'capture_method' => $this->get_capture_method() === 'automatic' ? 'automatic_async' : 'manual',
					'metadata' => [
						'voxel:payment_for' => 'order',
						'voxel:order_id' => $this->order->get_id(),
					],
				],
				'success_url' => $this->get_success_url(),
				'cancel_url' => $this->get_cancel_url(),
				'submit_type' => $this->get_submit_type(),
				'metadata' => [
					'voxel:payment_for' => 'order',
					'voxel:order_id' => $this->order->get_id(),
				],
				'billing_address_collection' => $billing_address_collection === 'required' ? 'required' : 'auto',
				'tax_id_collection' => [
					'enabled' => !! \Voxel\get( 'payments.stripe.payments.tax_id_collection.enabled', true ),
				],
				'allow_promotion_codes' => !! \Voxel\get( 'payments.stripe.payments.promotion_codes.enabled', false ),
			];

			if ( $this->get_tax_collection_method() === 'stripe_tax' ) {
				$args['automatic_tax'] = [
					'enabled' => true,
				];
			}

			if ( \Voxel\get( 'payments.stripe.payments.phone_number_collection.enabled' ) ) {
				$args['phone_number_collection'] = [
					'enabled' => true,
				];
			}

			if ( $this->order->has_shippable_products() ) {
				$this->_checkout_apply_shipping_rates( $args );
			}

			if ( \Voxel\get( 'payments.stripe.stripe_connect.enabled' ) ) {
				$this->_checkout_apply_vendor_config( $args );
			}

			$session = \Voxel\Vendor\Stripe\Checkout\Session::create( $args );

			$total_order_amount = $session->amount_total;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
				$total_order_amount /= 100;
			}

			if ( $total_order_amount === 0 ) {
				$this->order->set_details( 'checkout.is_zero_amount', true );

				if ( apply_filters( 'voxel/stripe_payments/zero_amount/skip_checkout', false ) === true ) {
					$this->order->set_details( 'checkout.zero_amount.skip_checkout', true );
				}
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
			$this->order->set_details( 'checkout.session_id', $session->id );
			$this->order->set_details( 'checkout.capture_method', $this->get_capture_method() );

			$this->order->save();

			if ( $total_order_amount === 0 && $this->order->get_details( 'checkout.zero_amount.skip_checkout' ) === true ) {
				if ( $this->order->get_details( 'cart.type' ) === 'customer_cart' ) {
					$customer_cart = $customer->get_cart();
					$customer_cart->empty();
					$customer_cart->update();
				}

				$this->zero_amount_checkout_session_updated( $session );

				return [
					'success' => true,
					'redirect_url' => $this->order->get_link(),
				];
			}

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
			'action' => 'stripe_payments.checkout.success',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
		], home_url('/') );
	}

	protected function get_cancel_url() {
		$redirect_url = wp_get_referer() ?: home_url('/');
		$redirect_url = add_query_arg( 't', time(), $redirect_url );

		return add_query_arg( [
			'vx' => 1,
			'action' => 'stripe_payments.checkout.cancel',
			'session_id' => '{CHECKOUT_SESSION_ID}',
			'order_id' => $this->order->get_id(),
			'redirect_to' => rawurlencode( $redirect_url ),
		], home_url('/') );
	}

	protected function get_submit_type(): string {
		foreach ( $this->order->get_items() as $item ) {
			if ( $item->get_type() === 'booking' ) {
				return 'book';
			}
		}

		return 'auto';
	}

	public function get_capture_method(): string {
		if ( count( $this->order->get_items() ) === 1 ) {
			foreach ( $this->order->get_items() as $item ) {
				if ( $item->get_product_field_key() === 'voxel:promotion' ) {
					$approval = \Voxel\get( 'paid_listings.settings.promotions.order_approval', 'automatic' );
					return $approval === 'manual' ? 'manual' : 'automatic';
				} elseif ( $item->get_product_field_key() === 'voxel:listing_plan' ) {
					return 'automatic';
				}
			}
		}

		$approval = \Voxel\get( 'payments.stripe.payments.order_approval' );
		if ( $approval === 'manual' ) {
			return 'manual';
		} elseif ( $approval === 'deferred' ) {
			return 'deferred';
		} else {
			return 'automatic';
		}
	}

	public function get_tax_collection_method() {
		if ( \Voxel\get( 'payments.stripe.tax_collection.enabled' ) ) {
			return \Voxel\get( 'payments.stripe.tax_collection.collection_method', 'stripe_tax' );
		}

		return null;
	}

	public function is_zero_amount(): bool {
		return !! $this->order->get_details( 'checkout.is_zero_amount' );
	}

	public function payment_intent_updated(
		\Voxel\Vendor\Stripe\PaymentIntent $payment_intent,
		\Voxel\Vendor\Stripe\Checkout\Session $session = null
	) {
		if ( $this->order->get_details( 'checkout.capture_method' ) === 'deferred' ) {
			if ( $payment_intent->status === 'requires_capture' ) {
				$cart_is_valid = false;
				try {
					$cart = $this->order->get_cart();
					$cart_is_valid = true;
				} catch ( \Exception $e ) {
					\Voxel\log($e->getMessage(), $e->getCode());
				}

				if ( $cart_is_valid ) {
					$payment_intent = $payment_intent->capture();
				} else {
					$payment_intent = $payment_intent->cancel();
				}
			}
		}

		$order_status = $this->determine_order_status_from_payment_intent( $payment_intent );
		if ( $order_status !== null ) {
			$this->order->set_status( $order_status );
		}

		$this->order->set_details( 'payment_intent', [
			'id' => $payment_intent->id,
			'amount' => $payment_intent->amount,
			'currency' => $payment_intent->currency,
			'customer' => $payment_intent->customer,
			'status' => $payment_intent->status,
			'canceled_at' => $payment_intent->canceled_at,
			'cancellation_reason' => $payment_intent->cancellation_reason,
			'created' => $payment_intent->created,
			'livemode' => $payment_intent->livemode,
			'latest_charge' => is_object( $payment_intent->latest_charge ) ? $payment_intent->latest_charge->id : $payment_intent->latest_charge,
			'capture_method' => $payment_intent->capture_method,
			'application_fee_amount' => $payment_intent->application_fee_amount,
			'transfer_data' => [
				'destination' => $payment_intent->transfer_data->destination ?? null,
			],
			'transfer_group' => $payment_intent->transfer_group,
			'shipping' => [
				'carrier' => $payment_intent->shipping->carrier ?? null,
				'phone' => $payment_intent->shipping->phone ?? null,
				'tracking_number' => $payment_intent->shipping->tracking_number ?? null,
			],
		] );

		$total_order_amount = $payment_intent->amount;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $payment_intent->currency ) ) {
			$total_order_amount /= 100;
		}

		$this->order->set_details( 'pricing.total', $total_order_amount );
		$this->order->set_transaction_id( $payment_intent->id );
		$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );

		if ( $session ) {
			$this->order->set_details( 'checkout.session_details', $this->_get_checkout_session_details_for_storage( $session ) );

			$tax_amount = $this->_get_tax_amount_from_checkout_session( $session );
			$discount_amount = $this->_get_discount_amount_from_checkout_session( $session );
			$shipping_amount = $this->_get_shipping_amount_from_checkout_session( $session );

			if ( $tax_amount !== null ) {
				$this->order->set_details( 'pricing.tax', $tax_amount );
			}

			if ( $discount_amount !== null ) {
				$this->order->set_details( 'pricing.discount', $discount_amount );
			}

			if ( $shipping_amount !== null ) {
				$this->order->set_details( 'pricing.shipping', $shipping_amount );
			}
		}

		$this->order->save();
	}

	protected function _get_checkout_session_details_for_storage( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		return [
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
			'shipping_details' => [
				'address' => [
					'city' => $session->shipping_details->address->city ?? null,
					'country' => $session->shipping_details->address->country ?? null,
					'line1' => $session->shipping_details->address->line1 ?? null,
					'line2' => $session->shipping_details->address->line2 ?? null,
					'postal_code' => $session->shipping_details->address->postal_code ?? null,
					'state' => $session->shipping_details->address->state ?? null,
				],
				'name' => $session->shipping_details->name ?? null,
			],
		];
	}

	protected function _get_tax_amount_from_checkout_session( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		$tax_amount = $session->total_details->amount_tax;
		if ( ! is_numeric( $tax_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
			$tax_amount /= 100;
		}

		if ( $tax_amount === 0 ) {
			return null;
		}

		return $tax_amount;
	}

	protected function _get_discount_amount_from_checkout_session( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		$discount_amount = $session->total_details->amount_discount;
		if ( ! is_numeric( $discount_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
			$discount_amount /= 100;
		}

		if ( $discount_amount === 0 ) {
			return null;
		}

		return $discount_amount;
	}

	protected function _get_shipping_amount_from_checkout_session( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		$shipping_amount = $session->total_details->amount_shipping;
		if ( ! is_numeric( $shipping_amount ) ) {
			return null;
		}

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
			$shipping_amount /= 100;
		}

		if ( $shipping_amount === 0 ) {
			return null;
		}

		return $shipping_amount;
	}

	protected function determine_order_status_from_payment_intent( \Voxel\Vendor\Stripe\PaymentIntent $payment_intent ): ?string {
		if ( in_array( $payment_intent->status, [ 'requires_payment_method', 'requires_confirmation', 'requires_action', 'processing' ], true ) ) {
			return \Voxel\ORDER_PENDING_PAYMENT;
		} elseif ( $payment_intent->status === 'canceled' ) {
			return \Voxel\ORDER_CANCELED;
		} elseif ( $payment_intent->status === 'requires_capture' ) {
			return \Voxel\ORDER_PENDING_APPROVAL;
		} elseif ( $payment_intent->status === 'succeeded' ) {
			$stripe = Module\Stripe_Client::getClient();
			$latest_charge = $stripe->charges->retrieve( $payment_intent->latest_charge, [] );

			// handle refunds
			if ( $latest_charge ) {
				if ( $latest_charge->refunded ) {
					// full refund
					return \Voxel\ORDER_REFUNDED;
				} elseif ( $latest_charge->amount_refunded > 0 ) {
					// partial refund
					return \Voxel\ORDER_REFUNDED;
				}
			}

			return \Voxel\ORDER_COMPLETED;
		} else {
			return null;
		}
	}

	public function zero_amount_checkout_session_updated( \Voxel\Vendor\Stripe\Checkout\Session $session ) {
		$this->order->set_details( 'checkout.session_details', $this->_get_checkout_session_details_for_storage( $session ) );
		$this->order->set_details( 'checkout.is_zero_amount', true );
		$this->order->set_details( 'pricing.total', 0 );

		$tax_amount = $this->_get_tax_amount_from_checkout_session( $session );
		$discount_amount = $this->_get_discount_amount_from_checkout_session( $session );
		$shipping_amount = $this->_get_shipping_amount_from_checkout_session( $session );

		if ( $tax_amount !== null ) {
			$this->order->set_details( 'pricing.tax', $tax_amount );
		}

		if ( $discount_amount !== null ) {
			$this->order->set_details( 'pricing.discount', $discount_amount );
		}

		if ( $shipping_amount !== null ) {
			$this->order->set_details( 'pricing.shipping', $shipping_amount );
		}

		if ( $session->payment_status === 'paid' || $this->order->get_details( 'checkout.zero_amount.skip_checkout' ) === true ) {
			$capture_method = $this->order->get_details( 'checkout.capture_method' );
			if ( $capture_method === 'deferred' ) {
				$cart_is_valid = false;
				try {
					$cart = $this->order->get_cart();
					$cart_is_valid = true;
				} catch ( \Exception $e ) {}

				if ( $cart_is_valid ) {
					$status = \Voxel\ORDER_COMPLETED;
				} else {
					$status = \Voxel\ORDER_CANCELED;
				}
			} elseif ( $capture_method === 'manual' ) {
				$status = \Voxel\ORDER_PENDING_APPROVAL;
			} else {
				$status = \Voxel\ORDER_COMPLETED;
			}

			$this->order->set_status( $status );
			$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
			$this->order->save();
		} else {
			$this->order->set_status( \Voxel\ORDER_CANCELED );
			$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
			$this->order->save();
		}
	}

	public function should_sync(): bool {
		return ! $this->order->get_details( 'checkout.last_synced_at' );
	}

	public function sync(): void {
		$stripe = Module\Stripe_Client::getClient();
		if ( $this->is_zero_amount() ) {
			$session = $stripe->checkout->sessions->retrieve( $this->order->get_details( 'checkout.session_id' ) );
			$this->zero_amount_checkout_session_updated( $session );
		} else {
			if ( $transaction_id = $this->order->get_transaction_id() ) {
				$payment_intent = $stripe->paymentIntents->retrieve( $transaction_id );
				$this->payment_intent_updated( $payment_intent );
			} elseif ( $checkout_session_id = $this->order->get_details( 'checkout.session_id' ) ) {
				$session = $stripe->checkout->sessions->retrieve( $checkout_session_id, [
					'expand' => [ 'payment_intent' ],
				] );

				$payment_intent = $session->payment_intent;
				if ( $payment_intent !== null ) {
					$this->payment_intent_updated( $payment_intent, $session );
				} else {
					// edge case: session exists but no payment intent, the customer likely used
					// a discount code to bring the order totals from a non-zero value to exactly 0
					$total_order_amount = $session->amount_total;
					if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $session->currency ) ) {
						$total_order_amount /= 100;
					}

					if ( $total_order_amount === 0 ) {
						$this->zero_amount_checkout_session_updated( $session );
					}
				}
			} else {
				//
			}
		}
	}

	protected function get_vendor_fee_amount_in_cents( \Voxel\User $vendor, int $subtotal_in_main_unit ): int {
		$currency = $this->order->get_currency();
		$subtotal_in_cents = $subtotal_in_main_unit;
		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$subtotal_in_cents *= 100;
		}

		$application_fee_amount = 0;
		foreach ( $vendor->get_vendor_fees() as $fee ) {
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

		return (int) round( $application_fee_amount );
	}

	public function get_vendor_fees_summary(): array {
		if ( $this->order->get_details('multivendor.mode') === 'destination_charges' ) {
			$currency = $this->order->get_currency();
			$application_fee_amount = $this->order->get_details( 'payment_intent.application_fee_amount' );
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

					$details['breakdown'][] = [
						'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
						'content' => \Voxel\currency_format( $fee['fixed_amount'], $currency, false ),
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

			$shipping_fee_in_cents = $this->order->get_details( 'multivendor.shipping_fee_in_cents' );
			if ( is_numeric( $shipping_fee_in_cents ) ) {
				$shipping_fee = $shipping_fee_in_cents;
				if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
					$shipping_fee /= 100;
				}

				$details['breakdown'][] = [
					'label' => _x( 'Shipping fee', 'vendor fees', 'voxel' ),
					'content' => \Voxel\currency_format( $shipping_fee, $currency, false ),
				];
			}

			return $details;
		} else {
			return [];
		}
	}

	protected function _checkout_apply_vendor_config( array &$args ) {
		$charge_type = \Voxel\get('payments.stripe.stripe_connect.charge_type');

		if ( $charge_type === 'destination_charges' ) {
			$vendor = $this->order->get_vendor();
			if ( $vendor !== null && $vendor->is_active_vendor() ) {
				if ( $this->order->has_shippable_products() ) {
					if ( $this->order->get_details('shipping.method') === 'vendor_rates' ) {
						// shipping is handled by the vendor, apply vendor fees on shipping costs
						$shipping_details = $this->_calculate_shipping( $this->order->get_items(), $this->order->get_shipping_rate_for_vendor( $vendor->get_id() ) );
						$application_fee_amount = $this->get_vendor_fee_amount_in_cents( $vendor, $this->order->get_subtotal() + $shipping_details['amount'] );
					} else {
						// shipping is handled by the platform, add full shipping costs to vendor fees
						$shipping_details = $this->_calculate_shipping( $this->order->get_items(), $this->order->get_shipping_rate() );
						$application_fee_amount = $this->get_vendor_fee_amount_in_cents( $vendor, $this->order->get_subtotal() ) + $shipping_details['amount_in_cents'];
						$this->order->set_details( 'multivendor.shipping_fee_in_cents', $shipping_details['amount_in_cents'] );
					}
				} else {
					$application_fee_amount = $this->get_vendor_fee_amount_in_cents( $vendor, $this->order->get_subtotal() );
				}

				$args['payment_intent_data']['application_fee_amount'] = $application_fee_amount;
				$args['payment_intent_data']['transfer_data'] = [
					'destination' => $vendor->get_stripe_vendor_id(),
				];

				$args['allow_promotion_codes'] = false;

				if ( \Voxel\get('payments.stripe.stripe_connect.settlement_merchant') === 'vendor' ) {
					$args['payment_intent_data']['on_behalf_of'] = $vendor->get_stripe_vendor_id();
					if ( $this->get_tax_collection_method() === 'stripe_tax' ) {
						$args['automatic_tax'] = [
							'enabled' => true,
							'liability' => [
								'type' => 'self',
							],
						];
					}
				}

				$this->order->set_details( 'multivendor.mode', 'destination_charges' );
				$this->order->set_details( 'multivendor.vendor_fees', $vendor->get_vendor_fees() );
			}
		} elseif ( $charge_type === 'separate_charges_and_transfers' ) {
			$items_by_vendor = [];
			foreach ( $this->order->get_items() as $item ) {
				$vendor = $item->get_vendor();
				if ( $vendor !== null && $vendor->is_active_vendor() ) {
					if ( ! isset( $items_by_vendor[ $vendor->get_id() ] ) ) {
						$items_by_vendor[ $vendor->get_id() ] = [
							'vendor' => $vendor,
							'items' => [],
							'subtotal' => 0,
						];
					}

					$items_by_vendor[ $vendor->get_id() ]['items'][] = $item;

					if ( $item->get_subtotal() !== null ) {
						$items_by_vendor[ $vendor->get_id() ]['subtotal'] += $item->get_subtotal();
					}
				}
			}

			if ( ! empty( $items_by_vendor ) ) {
				$transfer_data = [];
				foreach ( $items_by_vendor as $item_group ) {
					$vendor = $item_group['vendor'];
					$order_items = $item_group['items'];
					$subtotal = $item_group['subtotal'];
					$subtotal_in_cents = $subtotal;
					if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $this->order->get_currency() ) ) {
						$subtotal_in_cents *= 100;
					}

					if ( $this->order->has_shippable_products_from_vendor( $vendor->get_id() ) ) {
						if ( $this->order->get_details('shipping.method') === 'vendor_rates' ) {
							// shipping is handled by the vendor, apply vendor fees on shipping costs
							$shipping_details = $this->_calculate_shipping( $item_group['items'], $this->order->get_shipping_rate_for_vendor( $vendor->get_id() ) );
							$fee_in_cents = $this->get_vendor_fee_amount_in_cents( $vendor, $subtotal + $shipping_details['amount'] );
							$shipping_fee_in_cents = null;

							$total_in_cents = $subtotal_in_cents + $shipping_details['amount_in_cents'];
						} else {
							// shipping is handled by the platform, add full shipping costs to vendor fees
							$shipping_details = $this->_calculate_shipping( $item_group['items'], $this->order->get_shipping_rate() );
							$fee_in_cents = $this->get_vendor_fee_amount_in_cents( $vendor, $subtotal ) + $shipping_details['amount_in_cents'];
							$shipping_fee_in_cents = $shipping_details['amount_in_cents'];

							$total_in_cents = $subtotal_in_cents + $shipping_details['amount_in_cents'];
						}
					} else {
						$fee_in_cents = $this->get_vendor_fee_amount_in_cents( $vendor, $subtotal );
						$shipping_fee_in_cents = null;

						$total_in_cents = $subtotal_in_cents;
					}

					$transfer_data[ $vendor->get_id() ] = [
						'vendor_id' => $vendor->get_id(),
						'vendor_fees' => $vendor->get_vendor_fees(),
						'subtotal_in_cents' => round( $subtotal_in_cents ),
						'total_in_cents' => round( $total_in_cents ),
						'fee_in_cents' => round( $fee_in_cents ),
						'shipping_fee_in_cents' => $shipping_fee_in_cents !== null ? round( $shipping_fee_in_cents ) : null,
					];
				}

				$args['allow_promotion_codes'] = false;

				$args['payment_intent_data']['transfer_group'] = sprintf( 'ORDER_%d', $this->order->get_id() );
				$this->order->set_details( 'multivendor.mode', 'separate_charges_and_transfers' );
				$this->order->set_details( 'multivendor.transfer_data', $transfer_data );
			}
		}
	}

	protected function _checkout_apply_shipping_rates( array &$args ) {
		if ( $this->order->get_details('shipping.method') === 'vendor_rates' ) {
			$args['shipping_address_collection'] = [
				'allowed_countries' => [ $this->order->get_shipping_country() ],
			];

			$amounts_by_vendor = [];
			foreach ( (array) $this->order->get_details('shipping.rates_by_vendor', []) as $vendor_key => $vendor_rate ) {
				if ( $vendor_key === 'platform' ) {
					if ( ! ( $shipping_rate = $this->order->get_shipping_rate_for_platform() ) ) {
						continue;
					}

					$order_items = array_filter( $this->order->get_items(), function( $item ) {
						return $item->get_vendor_id() === null;
					} );

					$details = $this->_calculate_shipping( $order_items, $shipping_rate );

					$line_item = [
						'quantity' => 1,
						'price_data' => [
							'currency' => $this->order->get_currency(),
							'unit_amount' => $details['amount_in_cents'],
							'tax_behavior' => $details['tax_behavior'],
							'product_data' => [
								'tax_code' => $details['tax_code'],
								'name' => _x( 'Platform shipping costs', 'cart summary', 'voxel' ),
								'description' => $details['label'],
							],
						],
					];

					if ( $details['delivery_estimate_message'] !== null ) {
						$line_item['price_data']['product_data']['description'] = sprintf( '%s - %s', $details['label'], $details['delivery_estimate_message'] );
					}

					$args['line_items'][] = $line_item;

					$amounts_by_vendor[ $vendor_key ] = [
						'amount_in_cents' => $details['amount_in_cents'],
					];
				} else {
					if ( ! ( $vendor = \Voxel\User::get( str_replace( 'vendor_', '', $vendor_key ) ) ) ) {
						continue;
					}

					if ( ! ( $shipping_rate = $this->order->get_shipping_rate_for_vendor( $vendor->get_id() ) ) ) {
						continue;
					}

					$order_items = array_filter( $this->order->get_items(), function( $item ) use ( $vendor ) {
						return $item->get_vendor_id() === $vendor->get_id();
					} );

					$details = $this->_calculate_shipping( $order_items, $shipping_rate );

					$line_item = [
						'quantity' => 1,
						'price_data' => [
							'currency' => $this->order->get_currency(),
							'unit_amount' => $details['amount_in_cents'],
							'tax_behavior' => $details['tax_behavior'],
							'product_data' => [
								'tax_code' => $details['tax_code'],
								'name' => \Voxel\replace_vars( _x( 'Shipping costs for vendor @vendor_name', 'cart summary', 'voxel' ), [
									'@vendor_name' => $vendor->get_display_name(),
								] ),
								'description' => $details['label'],
								'metadata' => [
									'voxel:vendor_shipping_rate' => $vendor->get_id(),
								],
							],
						],
					];

					if ( $details['delivery_estimate_message'] !== null ) {
						$line_item['price_data']['product_data']['description'] = sprintf( '%s - %s', $details['label'], $details['delivery_estimate_message'] );
					}

					$args['line_items'][] = $line_item;

					$amounts_by_vendor[ $vendor_key ] = [
						'amount_in_cents' => $details['amount_in_cents'],
					];
				}
			}

			$this->order->set_details( 'shipping.amounts_by_vendor', $amounts_by_vendor );
		} else {
			$args['shipping_address_collection'] = [
				'allowed_countries' => [ $this->order->get_shipping_country() ],
			];

			$details = $this->_calculate_shipping( $this->order->get_items(), $this->order->get_shipping_rate() );

			$args['shipping_options'] = [ [
				'shipping_rate_data' => [
					'display_name' => mb_substr( $details['label'], 0, 100 ),
					'type' => 'fixed_amount',
					'fixed_amount' => [
						'amount' => $details['amount_in_cents'],
						'currency' => $this->order->get_currency(),
					],
					'tax_code' => $details['tax_code'],
					'tax_behavior' => $details['tax_behavior'],
					'delivery_estimate' => $details['delivery_estimate'],
				],
			] ];

			$amounts_by_vendor = [];
			foreach ( $this->order->get_items() as $item ) {
				$vendor_key = $item->get_vendor_id() !== null ? sprintf( 'vendor_%d', $item->get_vendor_id() ) : 'platform';
				if ( ! isset( $amounts_by_vendor[ $vendor_key ] ) ) {
					$vendor_items = array_filter( $this->order->get_items(), function( $vendor_item ) use ( $item ) {
						return $vendor_item->get_vendor_id() === $item->get_vendor_id();
					} );

					$vendor_details = $this->_calculate_shipping( $vendor_items, $this->order->get_shipping_rate() );
					$amounts_by_vendor[ $vendor_key ] = [
						'amount_in_cents' => $vendor_details['amount_in_cents'],
					];
				}
			}

			$this->order->set_details( 'shipping.amounts_by_vendor', $amounts_by_vendor );
		}
	}

	protected function _calculate_shipping( array $items, $shipping_rate ): array {
		if ( $shipping_rate->get_type() === 'fixed_rate' ) {
			$amount = 0;
			foreach ( $items as $item ) {
				if ( $item->is_shippable() ) {
					$item_quantity = $item->get_quantity() ?? 1;
					$shipping_class = $item->get_shipping_class();
					if ( $shipping_class !== null ) {
						$amount_per_unit = $shipping_rate->get_amount_per_unit_for_shipping_class( $shipping_class->get_key() );
					} else {
						$amount_per_unit = $shipping_rate->get_default_amount_per_unit();
					}

					$amount += $amount_per_unit * $item_quantity;
				}
			}

			$amount_in_cents = $amount;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $this->order->get_currency() ) ) {
				$amount_in_cents *= 100;
			}

			$amount_in_cents = round( $amount_in_cents );

			$details = [
				'label' => $shipping_rate->get_label(),
				'amount' => $amount,
				'amount_in_cents' => $amount_in_cents,
				'tax_code' => $shipping_rate->get_tax_code() === 'shipping' ? 'txcd_92010001' : 'txcd_00000000',
				'tax_behavior' => null,
				'delivery_estimate' => null,
				'delivery_estimate_message' => null,
			];

			if ( $shipping_rate->has_delivery_estimate() ) {
				$details['delivery_estimate_message'] = $shipping_rate->get_delivery_estimate_message();
				$details['delivery_estimate'] = [
					'minimum' => [
						'unit' => $shipping_rate->get_minimum_delivery_unit(),
						'value' => $shipping_rate->get_minimum_delivery_time(),
					],
					'maximum' => [
						'unit' => $shipping_rate->get_maximum_delivery_unit(),
						'value' => $shipping_rate->get_maximum_delivery_time(),
					],
				];
			}

			if ( in_array( $shipping_rate->get_tax_behavior(), [ 'inclusive', 'exclusive' ], true ) ) {
				$details['tax_behavior'] = $shipping_rate->get_tax_behavior();
			}

			return $details;
		} elseif ( $shipping_rate->get_type() === 'free_shipping' ) {
			$minimum_order_amount = $shipping_rate->get_minimum_order_amount();
			if ( $minimum_order_amount !== null ) {
				$subtotal = 0;
				foreach ( $items as $item ) {
					if ( $item->get_subtotal() !== null ) {
						$subtotal += $item->get_subtotal();
					}
				}

				if ( $subtotal < $minimum_order_amount ) {
					throw new \Exception( \Voxel\replace_vars( _x( 'Shipping via "@shipping_rate" requires a minimum order amount of @amount', 'cart summary', 'voxel' ), [
						'@shipping_rate' => $shipping_rate->get_label(),
						'@amount' => \Voxel\currency_format( $minimum_order_amount, $this->order->get_currency(), false ),
					] ) );
				}
			}

			$details = [
				'label' => $shipping_rate->get_label(),
				'amount' => 0,
				'amount_in_cents' => 0,
				'tax_code' => null,
				'tax_behavior' => null,
				'delivery_estimate' => null,
				'delivery_estimate_message' => null,
			];

			if ( $shipping_rate->has_delivery_estimate() ) {
				$details['delivery_estimate_message'] = $shipping_rate->get_delivery_estimate_message();
				$details['delivery_estimate'] = [
					'minimum' => [
						'unit' => $shipping_rate->get_minimum_delivery_unit(),
						'value' => $shipping_rate->get_minimum_delivery_time(),
					],
					'maximum' => [
						'unit' => $shipping_rate->get_maximum_delivery_unit(),
						'value' => $shipping_rate->get_maximum_delivery_time(),
					],
				];
			}

			return $details;
		}
	}

	public function get_vendor_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'vendor.approve',
				'label' => _x( 'Approve', 'order actions', 'voxel' ),
				'type' => 'primary',
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_COMPLETED );
						$this->order->save();
					} else {
						$stripe = Module\Stripe_Client::getClient();
						$payment_intent = $stripe->paymentIntents->retrieve( $this->order->get_transaction_id() );
						$payment_intent = $payment_intent->capture();

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Vendor_Approved_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'vendor.decline',
				'label' => _x( 'Decline', 'order actions', 'voxel' ),
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_CANCELED );
						$this->order->save();
					} else {
						$stripe = Module\Stripe_Client::getClient();
						$payment_intent = $stripe->paymentIntents->cancel( $this->order->get_transaction_id() );

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Vendor_Declined_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		return $actions;
	}

	public function get_customer_actions(): array {
		$actions = [];
		if ( $this->order->get_status() === \Voxel\ORDER_PENDING_APPROVAL ) {
			$actions[] = [
				'action' => 'customer.cancel',
				'label' => _x( 'Cancel order', 'order customer actions', 'voxel' ),
				'handler' => function() {
					if ( $this->is_zero_amount() ) {
						$this->order->set_status( \Voxel\ORDER_CANCELED );
						$this->order->save();
					} else {
						$stripe = Module\Stripe_Client::getClient();
						$payment_intent = $stripe->paymentIntents->cancel( $this->order->get_transaction_id(), [
							'cancellation_reason' => 'requested_by_customer',
						] );

						$this->payment_intent_updated( $payment_intent );
					}

					( new \Voxel\Events\Products\Orders\Customer_Canceled_Order_Event )->dispatch( $this->order->get_id() );

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		$actions[] = [
			'action' => 'customer.access_portal',
			'label' => _x( 'Customer portal', 'order customer actions', 'voxel' ),
			'handler' => function() {
				$stripe = Module\Stripe_Client::getClient();
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
}
