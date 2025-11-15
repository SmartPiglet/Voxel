<?php

namespace Voxel\Modules\Paddle_Payments\Payment_Methods;

use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Create\TransactionCreateItemWithPrice;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogProduct;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\Price\TransactionNonCatalogPriceWithProduct;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\Money;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\CurrencyCode;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxCategory;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\TaxMode;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\CustomData;
use \Voxel\Vendor\Paddle\SDK\Entities\Transaction;
use \Voxel\Vendor\Paddle\SDK\Resources\Transactions\Operations\List\Includes as TransactionIncludes;
use \Voxel\Vendor\Paddle\SDK\Resources\CustomerPortalSessions\Operations\CreateCustomerPortalSession;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\TimePeriod;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\Get\Includes as SubscriptionIncludes;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\PauseSubscription;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\ResumeSubscription;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionResumeEffectiveFrom;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\UpdateSubscription;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionOnResume;
use \Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\Operations\PreviewUpdateSubscription;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\CollectionMode;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionNonCatalogProduct;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionNonCatalogPrice;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionNonCatalogPriceWithProduct;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionItemsWithPrice;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\CatalogType;
use Voxel\Vendor\Paddle\SDK\Entities\Shared\PriceQuantity;
use Voxel\Vendor\Paddle\SDK\Entities\Subscription\SubscriptionProrationBillingMode;
use \Voxel\Product_Types\Cart_Items\Cart_Item;
use \Voxel\Modules\Paddle_Payments as Module;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Paddle_Subscription extends \Voxel\Product_Types\Payment_Methods\Base_Payment_Method {

	public function get_type(): string  {
		return 'paddle_subscription';
	}

	public function get_label(): string {
		return _x( 'Paddle subscription', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		try {
			$paddle = Module\Paddle_Client::get_client();
			$customer = $this->order->get_customer();
			$paddle_customer = $customer->get_or_create_paddle_customer();

			$items = array_map( function( $li ) {
				$order_item = $li['order_item'];

				$trial_period = null;
				if ( is_numeric( $order_item->get_details('subscription.trial_days') ) ) {
					$trial_period = TimePeriod::from( [
						'interval' => 'day',
						'frequency' => $order_item->get_details('subscription.trial_days'),
					] );
				}

				return new TransactionCreateItemWithPrice(
					quantity: $li['quantity'],
					price: new TransactionNonCatalogPriceWithProduct(
						description: sprintf( 'Product #%d (%s)', $li['id'], $li['product']['label'] ),
						taxMode: TaxMode::from('account_setting'),
						unitPrice: new Money(
							$li['amount_in_cents'],
							CurrencyCode::from( strtoupper( $li['currency'] ) ),
						),
						billingCycle: TimePeriod::from( [
							'interval' => $order_item->get_details('subscription.unit'),
							'frequency' => $order_item->get_details('subscription.frequency'),
						] ),
						product: new TransactionNonCatalogProduct(
							taxCategory: TaxCategory::from('standard'),
							name: $li['product']['label'],
							description: $li['product']['description'] ?: null,
							imageUrl: $li['product']['thumbnail_url'] ?: null,
						),
						trialPeriod: $trial_period,
					),
				);
			}, $this->get_line_items() );

			$operation = new CreateTransaction(
				customerId: $paddle_customer->id,
				items: $items,
				currencyCode: CurrencyCode::from( strtoupper( $this->order->get_currency() ) ),
				customData: new CustomData( [
					'voxel:payment_for' => 'order',
					'voxel:order_id' => (string) $this->order->get_id(),
				] ),
			);

			$transaction = $paddle->transactions->create( $operation );

			$totals = $transaction->details->totals;
			$total_order_amount = (int) $totals->total;
			$currency = $totals->currencyCode->getValue();

			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$total_order_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_order_amount );
			$this->order->set_details( 'checkout.transaction_id', $transaction->id );
			$this->order->set_transaction_id( $transaction->id );

			$this->order->save();

			$url = $transaction->checkout->url;
			$url = add_query_arg( 'transaction_id', $transaction->id, $url );

			// dd($transaction, wp_json_encode( $transaction ));

			return [ 'success' => true, 'redirect_url' => $url ];
		} catch ( \Throwable $e ) {
			return [
				'success' => false,
				'message' => _x( 'Something went wrong', 'checkout', 'voxel' ),
				'debug' => [
					'type' => 'paddle_error',
					'code' => $e->getCode(),
					'message' => $e->getMessage(),
				],
			];
		}
	}

	public function subscription_updated( Subscription $subscription ) {
		$this->order->set_transaction_id( $subscription->id );
		$this->order->set_status( sprintf( 'sub_%s', $subscription->status->getValue() ) );

		$details = [
			'id' => $subscription->id,
			'status' => $subscription->status->getValue(),
			'first_billed_at' => $subscription->firstBilledAt?->format('Y-m-d H:i:s'),
			'next_billed_at' => $subscription->nextBilledAt?->format('Y-m-d H:i:s'),
			'started_at' => $subscription->startedAt?->format('Y-m-d H:i:s'),
			'paused_at' => $subscription->pausedAt?->format('Y-m-d H:i:s'),
			'canceled_at' => $subscription->canceledAt?->format('Y-m-d H:i:s'),
			'billing_cycle' => [
				'interval' => $subscription->billingCycle->interval->getValue(),
				'frequency' => $subscription->billingCycle->frequency,
			],
		];

		if ( $scheduled_change = $subscription->scheduledChange ) {
			$details['scheduled_change'] = [
				'action' => $scheduled_change->action->getValue(),
				'effective_at' => $scheduled_change->effectiveAt?->format('Y-m-d H:i:s'),
				'resume_at' => $scheduled_change->resumeAt?->format('Y-m-d H:i:s'),
			];
		}

		if ( $recurring_transaction_details = $subscription->recurringTransactionDetails ) {
			$totals = $recurring_transaction_details->totals;
			$details['recurring_transaction_details'] = [
				'totals' => [
					'subtotal' => $totals->subtotal,
					'discount' => $totals->discount,
					'tax' => $totals->tax,
					'total' => $totals->total,
					'currency_code' => $totals->currencyCode->getValue(),
				],
			];

			// save total
			$currency = $totals->currencyCode->getValue();

			$total_amount = (int) $totals->total;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$total_amount /= 100;
			}

			$this->order->set_details( 'pricing.total', $total_amount );

			// save subtotal
			$subtotal_amount = (int) $totals->subtotal;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$subtotal_amount /= 100;
			}

			$this->order->set_details( 'pricing.subtotal', $subtotal_amount );

			// save tax amount
			$tax_amount = (int) $totals->tax;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$tax_amount /= 100;
			}

			$this->order->set_details( 'pricing.tax', $tax_amount );

			// save discount amount
			$discount_amount = (int) $totals->discount;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$discount_amount /= 100;
			}

			$this->order->set_details( 'pricing.discount', $discount_amount );
		}

		if ( $current_billing_period = $subscription->currentBillingPeriod ) {
			$details['current_billing_period'] = [
				'starts_at' => $current_billing_period->startsAt?->format('Y-m-d H:i:s'),
				'ends_at' => $current_billing_period->endsAt?->format('Y-m-d H:i:s'),
			];
		}

		$this->order->set_details( 'subscription', $details );

		$this->order->save();
	}

	public function should_sync(): bool {
		return ! $this->order->get_details( 'checkout.last_synced_at' );
	}

	public function sync(): void {
		$paddle = Module\Paddle_Client::get_client();
		try {
			$transaction = $paddle->transactions->get( (string) $this->order->get_details('checkout.transaction_id'), [
				TransactionIncludes::from('address'),
				TransactionIncludes::from('business'),
				TransactionIncludes::from('customer'),
				TransactionIncludes::from('adjustment'),
			] );

			if ( ! empty( $transaction->subscriptionId ) ) {
				$this->order->set_transaction_id( $transaction->subscriptionId );
			}

			// save other details details
			$this->order->set_details( 'transaction', [
				'id' => $transaction->id,
				'status' => $transaction->status->getValue(),
				'customerId' => $transaction->customerId,
				'addressId' => $transaction->addressId,
				'businessId' => $transaction->businessId,
			] );

			if ( $address = $transaction->address ) {
				$this->order->set_details( 'address', [
					'firstLine' => $address->firstLine,
					'secondLine' => $address->secondLine,
					'city' => $address->city,
					'postalCode' => $address->postalCode,
					'region' => $address->region,
					'countryCode' => $address->countryCode,
				] );
			}

			if ( $business = $transaction->business ) {
				$this->order->set_details( 'business', [
					'name' => $business->name,
					'companyNumber' => $business->companyNumber,
					'taxIdentifier' => $business->taxIdentifier,
				] );
			}

			if ( $customer = $transaction->customer ) {
				$this->order->set_details( 'customer', [
					'name' => $customer->name,
					'email' => $customer->email,
					'marketingConsent' => $customer->marketingConsent,
				] );
			}

			$this->order->set_details( 'checkout.last_synced_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
			$this->order->save();

			if ( ! empty( $transaction->subscriptionId ) ) {
				$subscription = $paddle->subscriptions->get( $transaction->subscriptionId, [
					SubscriptionIncludes::from('recurring_transaction_details'),
					SubscriptionIncludes::from('next_transaction'),
				] );

				$this->subscription_updated( $subscription );
			}
		} catch ( \Exception $e ) {
			// \Voxel\log($e->getMessage());
		}
	}

	protected function resync_subscription_details(): void {
		if ( $subscription = $this->order->get_details('subscription') ) {
			$paddle = Module\Paddle_Client::get_client();
			$this->subscription_updated( $paddle->subscriptions->get( $subscription['id'], [
				SubscriptionIncludes::from('recurring_transaction_details'),
				SubscriptionIncludes::from('next_transaction'),
			] ) );
		}
	}

	public function get_customer_details(): array {
		$details = [];

		$customer_data = (array) $this->order->get_details( 'customer', [] );

		if ( ! empty( $customer_data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Customer name', 'order customer details', 'voxel' ),
				'content' => $customer_data['name'],
			];
		}

		if ( ! empty( $customer_data['email'] ) ) {
			$details[] = [
				'label' => _x( 'Email', 'order customer details', 'voxel' ),
				'content' => $customer_data['email'],
			];
		}

		$address_data = (array) $this->order->get_details( 'address', [] );

		if ( ! empty( $address_data['countryCode'] ) ) {
			$country_code = $address_data['countryCode'];
			$country = \Voxel\Utils\Country_List::all()[ strtoupper( $country_code ) ] ?? null;

			$details[] = [
				'label' => _x( 'Country', 'order customer details', 'voxel' ),
				'content' => $country['name'] ?? $country_code,
			];
		}

		if ( ! empty( $address_data['firstLine'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 1', 'order customer details', 'voxel' ),
				'content' => $address_data['firstLine'],
			];
		}

		if ( ! empty( $address_data['secondLine'] ) ) {
			$details[] = [
				'label' => _x( 'Address line 2', 'order customer details', 'voxel' ),
				'content' => $address_data['secondLine'],
			];
		}

		if ( ! empty( $address_data['city'] ) ) {
			$details[] = [
				'label' => _x( 'City', 'order customer details', 'voxel' ),
				'content' => $address_data['city'],
			];
		}

		if ( ! empty( $address_data['postalCode'] ) ) {
			$details[] = [
				'label' => _x( 'Postal code', 'order customer details', 'voxel' ),
				'content' => $address_data['postalCode'],
			];
		}

		if ( ! empty( $address_data['region'] ) ) {
			$details[] = [
				'label' => _x( 'State', 'order customer details', 'voxel' ),
				'content' => $address_data['region'],
			];
		}

		$business_data = (array) $this->order->get_details( 'business', [] );

		if ( ! empty( $business_data['name'] ) ) {
			$details[] = [
				'label' => _x( 'Business name', 'order customer details', 'voxel' ),
				'content' => $business_data['name'],
			];
		}

		if ( ! empty( $business_data['companyNumber'] ) ) {
			$details[] = [
				'label' => _x( 'Company number', 'order customer details', 'voxel' ),
				'content' => $business_data['companyNumber'],
			];
		}

		if ( ! empty( $business_data['taxIdentifier'] ) ) {
			$details[] = [
				'label' => _x( 'VAT number', 'order customer details', 'voxel' ),
				'content' => $business_data['taxIdentifier'],
			];
		}

		return $details;
	}

	public function get_customer_actions(): array {
		$actions = [];

		$paddle = Module\Paddle_Client::get_client();
		$state = $this->get_state();
		$subscription = $this->order->get_details('subscription');

		if ( $state['status'] === 'trialing' ) {
			$actions[] = [
				'action' => 'trialing.activate',
				'label' => _x( 'Activate now', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be activated immediately, and your payment method will be charged right away. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->activate( $subscription['id'] );

					$this->resync_subscription_details();

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
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->cancel( $subscription['id'], new CancelSubscription(
						effectiveFrom: SubscriptionEffectiveFrom::from('immediately')
					) );

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'scheduled_cancel' ) {
			$actions[] = [
				'action' => 'scheduled_cancel.resume',
				'label' => _x( 'Resume subscription', 'order customer actions', 'voxel' ),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->update( $subscription['id'], new UpdateSubscription(
						scheduledChange: null,
					) );

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'scheduled_pause' ) {
			$actions[] = [
				'action' => 'scheduled_pause.resume',
				'label' => _x( 'Resume subscription', 'order customer actions', 'voxel' ),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->update( $subscription['id'], new UpdateSubscription(
						scheduledChange: null,
					) );

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		} elseif ( $state['status'] === 'active' ) {
			$actions[] = [
				'action' => 'active.pause',
				'label' => _x( 'Pause subscription', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be paused at the end of the current billing period. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->pause( $subscription['id'], new PauseSubscription(
						effectiveFrom: SubscriptionEffectiveFrom::from('next_billing_period')
					) );

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];

			$actions[] = [
				'action' => 'active.cancel',
				'label' => _x( 'Cancel renewal', 'order customer actions', 'voxel' ),
				'confirm' => _x(
					'Your subscription will be canceled at the end of the current billing period. Do you want to proceed?',
					'order customer actions',
					'voxel'
				),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->cancel( $subscription['id'], new CancelSubscription(
						effectiveFrom: SubscriptionEffectiveFrom::from('next_billing_period')
					) );

					$this->resync_subscription_details();

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
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->cancel( $subscription['id'], new CancelSubscription(
						effectiveFrom: SubscriptionEffectiveFrom::from('immediately')
					) );

					$this->resync_subscription_details();

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
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->resume( $subscription['id'], new ResumeSubscription(
						effectiveFrom: SubscriptionResumeEffectiveFrom::from('immediately'),
						onResume: SubscriptionOnResume::from('start_new_billing_period'),
					) );

					$this->resync_subscription_details();

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
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle->subscriptions->cancel( $subscription['id'], new CancelSubscription(
						effectiveFrom: SubscriptionEffectiveFrom::from('immediately')
					) );

					$this->resync_subscription_details();

					return wp_send_json( [
						'success' => true,
					] );
				},
			];
		}

		if ( in_array( $state['status'], [
			'trialing',
			'scheduled_cancel',
			'scheduled_pause',
			'active',
			'paused',
			'past_due',
		], true ) ) {
			$actions[] = [
				'action' => 'update_payment_method',
				'label' => _x( 'Update payment method', 'order customer actions', 'voxel' ),
				'handler' => function() use ( $subscription, $paddle ) {
					$paddle = Module\Paddle_Client::get_client();
					$subscription = $paddle->subscriptions->get( $subscription['id'] );

					return wp_send_json( [
						'success' => true,
						'redirect_to' => $subscription->managementUrls->updatePaymentMethod,
					] );
				},
			];
		}

		$actions[] = [
			'action' => 'access_portal',
			'label' => _x( 'Customer portal', 'order customer actions', 'voxel' ),
			'handler' => function() use ( $paddle ) {
				$session = $paddle->customerPortalSessions->create(
					\Voxel\current_user()->get_paddle_customer_id(),
					new CreateCustomerPortalSession
				);

				return wp_send_json( [
					'success' => true,
					'redirect_to' => $session->urls->general->overview,
				] );
			},
		];

		return $actions;
	}

	public function get_state(): array {
		if ( $this->order->get_status() !== 'pending_payment' && ( $subscription = $this->order->get_details('subscription') ) ) {
			$state = $subscription['status'];
			$next = $subscription['next_billed_at'] ?? null;
			$change = $subscription['scheduled_change']['action'] ?? null;

			if ( $state === 'trialing' ) {
				return [
					'status' => 'trialing',
					'label' => _x( 'Trial', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
					'class' => 'vx-green',
					'message' => sprintf(
						_x( 'Trial ends on %s', 'subscriptions', 'voxel' ),
						\Voxel\datetime_format( strtotime( $subscription['next_billed_at'] ) )
					),
					'actions' => [
						'payments/paddle_subscription/customers/trialing.activate',
						'payments/paddle_subscription/customers/trialing.cancel',
						'payments/paddle_subscription/customers/update_payment_method',
					],
				];
			} elseif ( $state === 'active' ) {
				if ( $change === 'cancel' ) {
					return [
						'status'  => 'scheduled_cancel',
						'label' => _x( 'Active', 'order status', 'voxel' ),
						'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
						'class' => 'vx-green',
						'message' => sprintf(
							_x( 'Cancels on %s', 'subscriptions', 'voxel' ),
							\Voxel\datetime_format( strtotime( $subscription['scheduled_change']['effective_at'] ) )
						),
						'actions' => [
							'payments/paddle_subscription/customers/scheduled_cancel.resume',
						],
					];
				} elseif ( $change === 'pause' ) {
					return [
						'status' => 'scheduled_pause',
						'label' => _x( 'Active', 'order status', 'voxel' ),
						'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
						'class' => 'vx-green',
						'message' => sprintf(
							_x( 'Pauses on %s', 'subscriptions', 'voxel' ),
							\Voxel\datetime_format( strtotime( $subscription['scheduled_change']['effective_at'] ) )
						),
						'actions' => [
							'payments/paddle_subscription/customers/scheduled_pause.resume',
						],
					];
				} else {
					return [
						'status' => 'active',
						'label' => _x( 'Active', 'order status', 'voxel' ),
						'long_label' => _x( 'Subscription is active', 'order status', 'voxel' ),
						'class' => 'vx-green',
						'message' => sprintf(
							_x( 'Next renewal on %s', 'subscriptions', 'voxel' ),
							\Voxel\datetime_format( strtotime( $subscription['next_billed_at'] ) )
						),
						'actions' => [
							// 'payments/paddle_subscription/customers/update_payment_method',
							'payments/paddle_subscription/customers/active.pause',
							'payments/paddle_subscription/customers/active.cancel',
						],
					];
				}
			} elseif ( $state === 'paused' ) {
				return [
					'status'  => 'paused',
					'label' => _x( 'Paused', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is paused', 'order status', 'voxel' ),
					'class' => 'vx-orange',
					'message' => sprintf(
						_x( 'Paused on %s', 'subscriptions', 'voxel' ),
						\Voxel\datetime_format( strtotime( $subscription['paused_at'] ) )
					),
					'actions' => [
						'payments/paddle_subscription/customers/paused.resume',
						'payments/paddle_subscription/customers/paused.cancel',
						'payments/paddle_subscription/customers/update_payment_method',
					],
				];
			} elseif ( $state === 'past_due' ) {
				return [
					'status'  => 'past_due',
					'label' => _x( 'Past due', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is past due', 'order status', 'voxel' ),
					'class' => 'vx-orange',
					'message' => _x( 'Payment failed — update your card', 'subscriptions', 'voxel' ),
					'actions' => [
						'payments/paddle_subscription/customers/update_payment_method',
					],
				];
			} elseif ( $state === 'canceled' ) {
				return [
					'status'  => 'canceled',
					'label' => _x( 'Canceled', 'order status', 'voxel' ),
					'long_label' => _x( 'Subscription is canceled', 'order status', 'voxel' ),
					'message' => sprintf(
						_x( 'Canceled on %s', 'subscriptions', 'voxel' ),
						\Voxel\datetime_format( strtotime( $subscription['canceled_at'] ) )
					),
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

	public function get_billing_interval(): ?array {
		$interval = $this->order->get_details( 'subscription.billing_cycle.interval' );
		$frequency = $this->order->get_details( 'subscription.billing_cycle.frequency' );

		if ( $interval && $frequency ) {
			return [
				'type' => 'recurring',
				'interval' => $interval,
				'interval_count' => $frequency,
			];
		}

		return null;
	}

	public function get_current_billing_period(): ?array {
		if ( $subscription = $this->order->get_details('subscription') ) {
			$starts_at = $subscription['current_billing_period']['starts_at'] ?? null;
			$ends_at = $subscription['current_billing_period']['ends_at'] ?? null;

			return [
				'start' => $starts_at,
				'end' => $ends_at,
			];
		}

		return null;
	}

	public function is_subscription(): bool {
		return true;
	}

	public function is_subscription_recoverable(): bool {
		return ! in_array( $this->order->get_status(), [
			'sub_canceled',
		], true );
	}

	public function cancel_subscription_immediately() {
		$paddle = $this->order->is_test_mode()
			? Module\Paddle_Client::get_test_client()
			: Module\Paddle_Client::get_live_client();

		$paddle->subscriptions->cancel( $this->order->get_transaction_id(), new CancelSubscription(
			effectiveFrom: SubscriptionEffectiveFrom::from('immediately')
		) );

		$this->resync_subscription_details();
	}

	public function preview_subscription_price_update( Cart_Item $cart_item ) {
		$new_amount_in_cents = $cart_item->get_pricing_summary()['total_amount'];
		$new_interval = $cart_item->get_product_field()->get_value()['subscription'];
		$new_currency = \Voxel\get( 'payments.stripe.currency', 'USD' );

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $new_currency ) ) {
			$new_amount_in_cents = $new_amount_in_cents * 100;
		}

		$paddle = $this->order->is_test_mode()
			? Module\Paddle_Client::get_test_client()
			: Module\Paddle_Client::get_live_client();

		$subscription = $paddle->subscriptions->get( $this->order->get_transaction_id() );

		$preview = $paddle->subscriptions->previewUpdate(
			$this->order->get_transaction_id(),
			new PreviewUpdateSubscription(
				discount: null,
				collectionMode: CollectionMode::from('automatic'),
				// nextBilledAt: new \DateTimeImmutable( 'now', new \DateTimeZone('UTC') ),
				prorationBillingMode: SubscriptionProrationBillingMode::from('prorated_immediately'),
				items: [
					new SubscriptionItemsWithPrice(
						price: new SubscriptionNonCatalogPrice(
							description: 'Subscription update',
							taxMode: TaxMode::from('account_setting'),
							name: null,
							unitPrice: new Money(
								(string) $new_amount_in_cents,
								CurrencyCode::from( strtoupper( $new_currency ) ),
							),
							billingCycle: TimePeriod::from( [
								'interval' => $new_interval['unit'],
								'frequency' => (int) $new_interval['frequency'],
							] ),
							productId: $subscription->items[0]->product->id,
							trialPeriod: null,
							unitPriceOverrides: [],
							quantity: PriceQuantity::from( [
								'minimum' => 1,
								'maximum' => 1,
							] ),
							customData: null,
						),
						quantity: 1,
					),
				],
			)
		);

		$amount_due = $preview->immediateTransaction->details->totals->grandTotal;
		$total = $preview->immediateTransaction->details->totals->total;

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $new_currency ) ) {
			$amount_due /= 100;
			$total /= 100;
		}

		return [
			'amount_due' => $amount_due,
			'total' => $total,
			'currency' => $new_currency,
		];
	}

	public function update_subscription_price( Cart_Item $cart_item ) {
		$pricing_summary = $cart_item->get_pricing_summary();
		$new_amount_in_cents = $pricing_summary['total_amount'];
		$new_interval = $cart_item->get_product_field()->get_value()['subscription'];
		$new_currency = \Voxel\get( 'payments.stripe.currency', 'USD' );

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $new_currency ) ) {
			$new_amount_in_cents = $new_amount_in_cents * 100;
		}

		$paddle = $this->order->is_test_mode()
			? Module\Paddle_Client::get_test_client()
			: Module\Paddle_Client::get_live_client();

		$subscription = $paddle->subscriptions->get( $this->order->get_transaction_id() );

		// update price and collect payment
		$updated_subscription = $paddle->subscriptions->update(
			$this->order->get_transaction_id(),
			new UpdateSubscription(
				discount: null,
				collectionMode: CollectionMode::from('automatic'),
				prorationBillingMode: SubscriptionProrationBillingMode::from('prorated_immediately'),
				items: [
					new SubscriptionItemsWithPrice(
						price: new SubscriptionNonCatalogPrice(
							description: 'Subscription update',
							taxMode: TaxMode::from('account_setting'),
							name: null,
							unitPrice: new Money(
								(string) $new_amount_in_cents,
								CurrencyCode::from( strtoupper( $new_currency ) ),
							),
							billingCycle: TimePeriod::from( [
								'interval' => $new_interval['unit'],
								'frequency' => (int) $new_interval['frequency'],
							] ),
							productId: $subscription->items[0]->product->id,
							trialPeriod: null,
							unitPriceOverrides: [],
							quantity: PriceQuantity::from( [
								'minimum' => 1,
								'maximum' => 1,
							] ),
							customData: null,
						),
						quantity: 1,
					),
				],
			)
		);

		// update billing schedule
		try {
			$now_utc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
			$next_billed_at = match ( strtolower( $new_interval['unit'] ) ) {
				'day'  => $now_utc->add( new \DateInterval('P'.(int) $new_interval['frequency'].'D') ),
				'week' => $now_utc->add( new \DateInterval('P'.(int) $new_interval['frequency'].'W') ),
				'month'=> $now_utc->add( new \DateInterval('P'.(int) $new_interval['frequency'].'M') ),
				'year' => $now_utc->add( new \DateInterval('P'.(int) $new_interval['frequency'].'Y') ),
				default => throw new \Exception('Invalid interval unit')
			};

			$updated_subscription = $paddle->subscriptions->update(
				$this->order->get_transaction_id(),
				new UpdateSubscription(
					nextBilledAt: $next_billed_at,
					prorationBillingMode: SubscriptionProrationBillingMode::from('do_not_bill'),
				)
			);
		} catch ( \Exception $e ) {
			\Voxel\log($e->getMessage());
		}

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

		$this->resync_subscription_details();

		// dd($updated_subscription);
	}
}
