<?php

namespace Voxel\Modules\Stripe_Payments\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Webhooks_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_stripe.webhooks', '@handle_webhooks' );
		$this->on( 'voxel_ajax_nopriv_stripe.webhooks', '@handle_webhooks' );

		$this->on( 'voxel_ajax_stripe.connect_webhooks', '@handle_connect_webhooks' );
		$this->on( 'voxel_ajax_nopriv_stripe.connect_webhooks', '@handle_connect_webhooks' );
	}

	protected function handle_webhooks() {
		$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();

		if ( \Voxel\get( 'payments.stripe.mode' ) === 'sandbox' ) {
			$endpoint_secret = \Voxel\get( 'payments.stripe.sandbox.webhook.secret' );
		} else {
			$endpoint_secret = \Voxel\get( 'payments.stripe.live.webhook.secret' );
		}

		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
		$event = null;

		try {
			$event = \Voxel\Vendor\Stripe\Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch( \UnexpectedValueException $e ) {
			// Invalid payload
			http_response_code(400);
			exit();
		} catch( \Voxel\Vendor\Stripe\Exception\SignatureVerificationException $e ) {
			// Invalid signature
			http_response_code(400);
			exit();
		}

		try {

			// checkout session events
			foreach ( [
				'checkout.session.completed',
				'checkout.session.async_payment_succeeded',
				'checkout.session.async_payment_failed',
			] as $event_type ) {
				if ( $event->type === $event_type ) {
					$session = $event->data->object;
					$payment_for = $session->metadata['voxel:payment_for'];

					if ( $payment_for === 'order' ) {
						$order_id = $session->metadata['voxel:order_id'];
						$order_id = is_numeric( $order_id ) ? absint( $order_id ) : null;
						$order = \Voxel\Product_Types\Orders\Order::get( $order_id );

						if ( $order ) {
							if ( $session->mode === 'payment' ) {
								if ( $session->payment_intent === null && $order->get_details( 'checkout.is_zero_amount' ) ) {
									do_action( 'voxel/stripe_payments/zero_amount/event:'.$event_type, $event, $session, $order );
								} else {
									$payment_intent = $stripe->paymentIntents->retrieve( $session->payment_intent );
									do_action( 'voxel/stripe_payments/event:'.$event_type, $event, $session, $payment_intent, $order );
								}
							} elseif ( $session->mode === 'subscription' ) {
								$subscription = $stripe->subscriptions->retrieve( $session->subscription );
								do_action( 'voxel/stripe_subscriptions/event:'.$event_type, $event, $session, $subscription, $order );
							}
						}
					}
				}
			}

			// charge events
			foreach ( [
				'charge.captured',
				'charge.refunded',
			] as $event_type ) {
				if ( $event->type === $event_type ) {
					$charge = $event->data->object;
					if ( $charge->payment_intent ) {
						$order = \Voxel\Product_Types\Orders\Order::find( [
							'payment_method' => 'stripe_payment',
							'transaction_id' => $charge->payment_intent,
						] );

						if ( $order ) {
							$payment_intent = $stripe->paymentIntents->retrieve( $charge->payment_intent );
							do_action( 'voxel/stripe_payments/event:'.$event_type, $event, $charge, $payment_intent, $order );
						}
					}
				}
			}

			// refund updated
			if ( $event->type === 'charge.refund.updated' ) {
				$refund = $event->data->object;
				if ( $refund->payment_intent ) {
					$order = \Voxel\Product_Types\Orders\Order::find( [
						'payment_method' => 'stripe_payment',
						'transaction_id' => $refund->payment_intent,
					] );

					if ( $order ) {
						$payment_intent = $stripe->paymentIntents->retrieve( $refund->payment_intent );
						do_action( 'voxel/stripe_payments/event:charge.refund.updated', $event, $refund, $payment_intent, $order );
					}
				}
			}

			// refund updated
			if ( $event->type === 'payment_intent.canceled' ) {
				$payment_intent = $event->data->object;
				$order = \Voxel\Product_Types\Orders\Order::find( [
					'payment_method' => 'stripe_payment',
					'transaction_id' => $payment_intent->id,
				] );

				if ( $order ) {
					do_action( 'voxel/stripe_payments/event:charge.refund.updated', $event, $payment_intent, $payment_intent, $order );
				}
			}

			// subscriptions
			foreach ( [
				'customer.subscription.created',
				'customer.subscription.updated',
				'customer.subscription.deleted',
			] as $event_type ) {
				if ( $event->type === $event_type ) {
					$subscription = $event->data->object;
					$order = \Voxel\Product_Types\Orders\Order::find( [
						'payment_method' => 'stripe_subscription',
						'transaction_id' => $subscription->id,
					] );

					if ( $order ) {
						do_action( 'voxel/stripe_subscriptions/event:'.$event_type, $event, $subscription, $order );
					}
				}
			}
		} catch ( \Exception $e ) {
			\Voxel\log( $e->getMessage() );

			http_response_code(400);
			exit();
		}

		http_response_code(200);
	}

	protected function handle_connect_webhooks() {
		$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();

		if ( \Voxel\get( 'payments.stripe.mode' ) === 'sandbox' ) {
			$endpoint_secret = \Voxel\get( 'payments.stripe.stripe_connect.webhook.sandbox.secret' );
		} else {
			$endpoint_secret = \Voxel\get( 'payments.stripe.stripe_connect.webhook.live.secret' );
		}

		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
		$event = null;

		try {
			$event = \Voxel\Vendor\Stripe\Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch( \UnexpectedValueException $e ) {
			// Invalid payload
			http_response_code(400);
			exit();
		} catch( \Voxel\Vendor\Stripe\Exception\SignatureVerificationException $e ) {
			// Invalid signature
			http_response_code(400);
			exit();
		}

		try {
			// vendor account updated
			if ( $event->type === 'account.updated' ) {
				$account = $event->data->object;
				do_action( 'voxel/stripe_connect/event:'.$event->type, $event, $account );
			}
		} catch ( \Exception $e ) {
			\Voxel\log( $e->getMessage() );

			http_response_code(400);
			exit();
		}

		http_response_code(200);
	}
}
