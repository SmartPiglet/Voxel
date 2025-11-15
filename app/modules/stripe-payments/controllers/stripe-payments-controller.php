<?php

namespace Voxel\Modules\Stripe_Payments\Controllers;

use \Voxel\Modules\Stripe_Payments as Module;
use \Voxel\Modules\Stripe_Payments\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Payments_Controller extends \Voxel\Controllers\Base_Controller {

	protected function dependencies() {
		new Backend\Settings_Controller;

		new Frontend\Webhooks_Controller;
		new Frontend\Order_Controller;
		new Frontend\Payments_Controller;
		new Frontend\Subscriptions_Controller;
	}

	protected function hooks() {
		$this->filter( 'voxel/product-types/payment-services', '@register_payment_service' );
		$this->filter( 'voxel/product-types/payment-methods', '@register_payment_methods' );
		$this->on( 'admin_init', '@migrate_settings' );
	}

	protected function register_payment_service( $payment_services ) {
		$payment_services['stripe'] = new Module\Stripe_Payment_Service;

		return $payment_services;
	}

	protected function register_payment_methods( $payment_methods ) {
		$payment_methods['stripe_payment'] = Payment_Methods\Stripe_Payment::class;
		$payment_methods['stripe_subscription'] = Payment_Methods\Stripe_Subscription::class;

		return $payment_methods;
	}

	protected function migrate_settings() {
		if ( \Voxel\get('payments.stripe.__migrated') ) {
			return;
		}

		$product_settings = \Voxel\get('product_settings');

		if ( ! empty( $product_settings['stripe_payments'] ) ) {
			\Voxel\set( 'payments.stripe.payments', $product_settings['stripe_payments'] );
		}

		if ( ! empty( $product_settings['stripe_subscriptions'] ) ) {
			\Voxel\set( 'payments.stripe.subscriptions', $product_settings['stripe_subscriptions'] );
		}

		if ( ! empty( $product_settings['multivendor'] ) ) {
			\Voxel\set( 'payments.stripe.stripe_connect', $product_settings['multivendor'] );
		}

		if ( ! empty( $product_settings['tax_collection'] ) ) {
			\Voxel\set( 'payments.stripe.tax_collection', $product_settings['tax_collection'] );
		}

		$stripe_settings = \Voxel\get('settings.stripe');
		if ( ! empty( $stripe_settings ) ) {
			\Voxel\set( 'payments.provider', 'stripe' );

			if ( isset( $stripe_settings['test_mode'] ) ) {
				\Voxel\set( 'payments.stripe.mode', $stripe_settings['test_mode'] ? 'sandbox' : 'live' );
			}

			if ( ! empty( $stripe_settings['currency'] ) ) {
				\Voxel\set( 'payments.stripe.currency', $stripe_settings['currency'] );
			}

			if ( ! empty( $stripe_settings['secret'] ) ) {
				\Voxel\set( 'payments.stripe.live.api_key', $stripe_settings['secret'] );
			}

			if ( ! empty( $stripe_settings['test_secret'] ) ) {
				\Voxel\set( 'payments.stripe.sandbox.api_key', $stripe_settings['test_secret'] );
			}

			if ( ! empty( $stripe_settings['webhooks']['live'] ) ) {
				\Voxel\set( 'payments.stripe.live.webhook', $stripe_settings['webhooks']['live'] );
			}

			if ( ! empty( $stripe_settings['webhooks']['test'] ) ) {
				\Voxel\set( 'payments.stripe.sandbox.webhook', $stripe_settings['webhooks']['test'] );
			}

			if ( ! empty( $stripe_settings['webhooks']['local']['enabled'] ) && ! empty( $stripe_settings['webhooks']['local']['secret'] ) ) {
				\Voxel\set( 'payments.stripe.sandbox.webhook.secret', $stripe_settings['webhooks']['local']['secret'] );
			}

			if ( ! empty( $stripe_settings['portal']['live_config_id'] ) ) {
				$portal = $stripe_settings['portal'];
				$portal['id'] = $stripe_settings['portal']['live_config_id'];
				unset( $portal['live_config_id'] );
				unset( $portal['test_config_id'] );

				\Voxel\set( 'payments.stripe.live.customer_portal', $portal );
			}

			if ( ! empty( $stripe_settings['portal']['test_config_id'] ) ) {
				$portal = $stripe_settings['portal'];
				$portal['id'] = $stripe_settings['portal']['test_config_id'];
				unset( $portal['live_config_id'] );
				unset( $portal['test_config_id'] );

				\Voxel\set( 'payments.stripe.sandbox.customer_portal', $portal );
			}

			if ( ! empty( $stripe_settings['webhooks']['live_connect'] ) ) {
				\Voxel\set( 'payments.stripe.stripe_connect.webhook.live', $stripe_settings['webhooks']['live_connect'] );
			}

			if ( ! empty( $stripe_settings['webhooks']['test_connect'] ) ) {
				\Voxel\set( 'payments.stripe.stripe_connect.webhook.sandbox', $stripe_settings['webhooks']['test_connect'] );
			}
		}

		if ( ! empty( \Voxel\get('product_settings.promotions') ) ) {
			\Voxel\set( 'paid_listings.settings.promotions', \Voxel\get('product_settings.promotions') );
			\Voxel\set( 'product_settings.promotions', null );
		}

		\Voxel\set( 'payments.stripe.__migrated', true );

		\Voxel\set( 'product_settings.stripe_payments', null );
		\Voxel\set( 'product_settings.stripe_subscriptions', null );
		\Voxel\set( 'product_settings.multivendor', null );
		\Voxel\set( 'product_settings.tax_collection', null );
		\Voxel\set( 'settings.stripe', null );
		\Voxel\set( 'settings.addons.paid_memberships.enabled', true );
	}

}
