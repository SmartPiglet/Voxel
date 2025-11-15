<?php

namespace Voxel\Modules\Stripe_Payments;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Client {

	private static $liveClient, $testClient;

	const API_VERSION = '2025-07-30.basil';

	const WEBHOOK_EVENTS = [
		'customer.subscription.created',
		'customer.subscription.updated',
		'customer.subscription.deleted',
		'checkout.session.completed',
		'checkout.session.async_payment_succeeded',
		'checkout.session.async_payment_failed',
		'payment_intent.amount_capturable_updated',
		'payment_intent.canceled',
		'payment_intent.succeeded',
		'charge.captured',
		'charge.refunded',
		'charge.refund.updated',
	];

	const CONNECT_WEBHOOK_EVENTS = [
		'account.updated',
	];

	public static function is_test_mode() {
		return \Voxel\get( 'payments.stripe.mode', 'sandbox' ) === 'sandbox';
	}

	public static function getClient() {
		return static::is_test_mode()
			? static::getTestClient()
			: static::getLiveClient();
	}

	public static function get_client() {
		return static::is_test_mode() ? static::getTestClient() : static::getLiveClient();
	}

	public static function get_live_client() {
		if ( is_null( static::$liveClient ) ) {
			\Voxel\Vendor\Stripe\Stripe::setApiKey( \Voxel\get( 'payments.stripe.live.api_key', '' ) );
			\Voxel\Vendor\Stripe\Stripe::setApiVersion( static::API_VERSION );
			static::$liveClient = new \Voxel\Vendor\Stripe\StripeClient( [
				'api_key' => \Voxel\get( 'payments.stripe.live.api_key', '' ),
				'stripe_version' => static::API_VERSION,
			] );
		}

		return static::$liveClient;
	}

	public static function getLiveClient() {
		return static::get_live_client();
	}

	public static function get_test_client() {
		if ( is_null( static::$testClient ) ) {
			\Voxel\Vendor\Stripe\Stripe::setApiKey( \Voxel\get( 'payments.stripe.sandbox.api_key', '' ) );
			\Voxel\Vendor\Stripe\Stripe::setApiVersion( static::API_VERSION );
			static::$testClient = new \Voxel\Vendor\Stripe\StripeClient( [
				'api_key' => \Voxel\get( 'payments.stripe.sandbox.api_key', '' ),
				'stripe_version' => static::API_VERSION,
			] );
		}

		return static::$testClient;
	}

	public static function getTestClient() {
		return static::get_test_client();
	}

	public static function base_dashboard_url( $path = '' ) {
		$url = 'https://dashboard.stripe.com/';
		$path = ltrim( $path, "/\\" );
		return $url.$path;
	}

	public static function dashboard_url( $path = '' ) {
		$url = static::base_dashboard_url();
		if ( static::is_test_mode() ) {
			$url .= 'test/';
		}

		$path = ltrim( $path, "/\\" );
		return $url.$path;
	}

	public static function get_portal_configuration_id() {
		return \Voxel\Modules\Stripe_Payments\Stripe_Client::is_test_mode()
			? \Voxel\get( 'payments.stripe.sandbox.customer_portal.id' )
			: \Voxel\get( 'payments.stripe.live.customer_portal.id' );
	}

	// @link https://docs.stripe.com/payments/checkout/taxes?tax-calculation=tax-rates#dynamic-tax-rates
	public static function get_supported_countries_for_dynamic_tax_rates(): array {
		return [
			'US', 'GB', 'AU', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK',
			'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT',
			'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
		];
	}
}
