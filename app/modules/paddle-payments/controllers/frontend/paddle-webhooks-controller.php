<?php

namespace Voxel\Modules\Paddle_Payments\Controllers\Frontend;

use \Voxel\Modules\Paddle_Payments as Module;
use \Voxel\Vendor\Nyholm\Psr7\Stream;
use \Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory;
use \Voxel\Vendor\Nyholm\Psr7\ServerRequest;
use \Voxel\Vendor\Paddle\SDK\Notifications\Secret;
use \Voxel\Vendor\Paddle\SDK\Notifications\Verifier;
use \Voxel\Vendor\Paddle\SDK\Entities\Transaction;
use \Voxel\Vendor\Paddle\SDK\Entities\Adjustment;
use \Voxel\Vendor\Paddle\SDK\Entities\Subscription;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Webhooks_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_paddle.webhooks', '@handle_webhooks' );
		$this->on( 'voxel_ajax_nopriv_paddle.webhooks', '@handle_webhooks' );
	}

	protected function handle_webhooks() {
		$payload = @file_get_contents('php://input');
		$signature = $_SERVER['HTTP_PADDLE_SIGNATURE'] ?? '';

		$factory = new Psr17Factory();
		$uri = $factory->createUri( $_SERVER['REQUEST_URI'] ?? '/' );

		$request = new ServerRequest(
			$_SERVER['REQUEST_METHOD'] ?? 'POST',
			$uri,
			[ 'paddle-signature' => $signature ],
			Stream::create( $payload )
		);

		$mode = Module\Paddle_Client::is_test_mode() ? 'sandbox' : 'live';
		$secret = new Secret( \Voxel\get( sprintf( 'payments.paddle.%s.webhook.secret', $mode ) ) );

		(new Verifier())->verify( $request, $secret );

		$event = json_decode( $payload, true );

		$event_type = $event['event_type'] ?? null;
		if ( in_array( $event_type, [ 'transaction.completed', 'transaction.canceled' ], true ) ) {
			$transaction = Transaction::from( $event['data'] );
			$order = \Voxel\Product_Types\Orders\Order::find( [
				'payment_method' => 'paddle_payment',
				'transaction_id' => $transaction->id,
			] );

			if ( $order ) {
				\Voxel\Utils\Async_Requests\Sync_Order::instance()->data( [ 'order_id' => $order->get_id() ] )->dispatch();
			}
		} elseif ( in_array( $event_type, [ 'adjustment.created', 'adjustment.updated' ], true ) ) {
			$adjustment = Adjustment::from( $event['data'] );
			$order = \Voxel\Product_Types\Orders\Order::find( [
				'payment_method' => 'paddle_payment',
				'transaction_id' => $adjustment->transactionId,
			] );

			if ( $order ) {
				\Voxel\Utils\Async_Requests\Sync_Order::instance()->data( [ 'order_id' => $order->get_id() ] )->dispatch();
			}
		} elseif ( in_array( $event_type, [
			'subscription.activated',
			'subscription.canceled',
			'subscription.created',
			'subscription.imported',
			'subscription.past_due',
			'subscription.paused',
			'subscription.resumed',
			'subscription.trialing',
			'subscription.updated',
		], true ) ) {
			$subscription = Subscription::from( $event['data'] );
			$order = \Voxel\Product_Types\Orders\Order::find( [
				'payment_method' => 'paddle_subscription',
				'transaction_id' => $subscription->id,
			] );

			if ( $order ) {
				\Voxel\Utils\Async_Requests\Sync_Order::instance()->data( [ 'order_id' => $order->get_id() ] )->dispatch();
			}
		}

		http_response_code(200);
		exit;
	}
}
