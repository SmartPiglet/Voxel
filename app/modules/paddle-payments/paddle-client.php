<?php

namespace Voxel\Modules\Paddle_Payments;

use \Voxel\Vendor\Paddle\SDK\Client as PaddleClient;
use \Voxel\Vendor\Paddle\SDK\Environment as PaddleEnv;
use \Voxel\Vendor\Paddle\SDK\Options as PaddleOpts;
use \Voxel\Vendor\Paddle\SDK\Entities\Address;
use \Voxel\Vendor\Paddle\SDK\Entities\Business;
use \Voxel\Vendor\Paddle\SDK\Resources\Addresses\Operations\ListAddresses;
use \Voxel\Vendor\Paddle\SDK\Resources\Businesses\Operations\ListBusinesses;
use \Voxel\Vendor\Paddle\SDK\Entities\Shared\Status;
use \Voxel\Vendor\Paddle\SDK\Resources\Shared\Operations\List\Pager;
use \Voxel\Vendor\Paddle\SDK\Resources\Shared\Operations\List\OrderBy;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Client {

	private static $live_client, $test_client;

	public static function is_test_mode() {
		$mode = \Voxel\get( 'payments.paddle.mode', 'sandbox' );
		return $mode === 'live' ? false : true;
	}

	public static function get_client() {
		return static::is_test_mode() ? static::get_test_client() : static::get_live_client();
	}

	public static function get_api_key() {
		return static::is_test_mode()
			? \Voxel\get( 'payments.paddle.sandbox.api_key' )
			: \Voxel\get( 'payments.paddle.live.api_key' );
	}

	public static function get_live_client() {
		if ( is_null( static::$live_client ) ) {
			static::$live_client = new PaddleClient(
				apiKey: \Voxel\get( 'payments.paddle.live.api_key' ),
				options: new PaddleOpts( PaddleEnv::PRODUCTION ),
			);
		}

		return static::$live_client;
	}

	public static function get_test_client() {
		if ( is_null( static::$test_client ) ) {
			static::$test_client = new PaddleClient(
				apiKey: \Voxel\get( 'payments.paddle.sandbox.api_key' ),
				options: new PaddleOpts( PaddleEnv::SANDBOX ),
			);
		}

		return static::$test_client;
	}

	public static function get_webhook_events(): array {
		return [
			'customer.created',
			'customer.imported',
			'customer.updated',

			'subscription.activated',
			'subscription.canceled',
			'subscription.created',
			'subscription.imported',
			'subscription.past_due',
			'subscription.paused',
			'subscription.resumed',
			'subscription.trialing',
			'subscription.updated',

			'transaction.completed',
			'transaction.canceled',
			// 'transaction.billed',
			// 'transaction.created',
			// 'transaction.paid',
			// 'transaction.past_due',
			// 'transaction.payment_failed',
			// 'transaction.ready',
			// 'transaction.revised',
			// 'transaction.updated',

			'adjustment.created',
			'adjustment.updated',
		];
	}

	public static function get_latest_active_address( string $customer_id ): ?Address {
		$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
		$addresses = $paddle->addresses->list(
			$customer_id,
			new ListAddresses(
				pager: new Pager(
					perPage: 1,
					orderBy: OrderBy::idDescending(),
				),
				statuses: [ Status::from('active') ],
			)
		);

		$address = $addresses->current();
		return $address instanceof Address ? $address : null;
	}

	public static function get_latest_active_business( string $customer_id ): ?Business {
		$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
		$businesses = $paddle->businesses->list(
			$customer_id,
			new ListBusinesses(
				pager: new Pager(
					perPage: 1,
					orderBy: OrderBy::idDescending(),
				),
				statuses: [ Status::from('active') ],
			)
		);

		$business = $businesses->current();
		return $business instanceof Business ? $business : null;
	}
}
