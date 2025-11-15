<?php

namespace Voxel\Modules\Stripe_Payments\Controllers\Backend;

use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_stripe.admin.setup_webhook', '@setup_webhook' );
		$this->on( 'voxel_ajax_stripe.admin.setup_connect_webhook', '@setup_connect_webhook' );
		$this->on( 'voxel_ajax_stripe.admin.setup_customer_portal', '@setup_customer_portal' );

		$this->on( 'voxel/backend/view_order/details/after', '@order_details' );
		$this->on( 'voxel/backend/view_order/customer_details/after', '@order_customer_details', 10, 2 );
		$this->on( 'voxel/backend/view_order/vendor_details/after', '@order_vendor_details', 10, 2 );
	}

	protected function setup_webhook() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$mode = $_POST['mode'] ?? null;
			if ( ! in_array( $mode, [ 'live', 'sandbox' ], true ) ) {
				throw new \Exception( 'Invalid request' );
			}

			$api_key = $_REQUEST['api_key'] ?? null;
			if ( empty( $api_key ) || ! is_string( $api_key ) ) {
				throw new \Exception( 'Missing API key' );
			}

			$stripe = new \Voxel\Vendor\Stripe\StripeClient( [
				'api_key' => $api_key,
				'stripe_version' => \Voxel\Modules\Stripe_Payments\Stripe_Client::API_VERSION,
			] );

			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.webhooks' ),
				'enabled_events' => \Voxel\Modules\Stripe_Payments\Stripe_Client::WEBHOOK_EVENTS,
			] );

			\Voxel\set( sprintf( 'payments.stripe.%s.webhook', $mode ), [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );

			return wp_send_json( [
				'success' => true,
				'message' => __( 'Endpoint created successfully.', 'voxel-backend' ),
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function setup_customer_portal() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$mode = $_POST['mode'] ?? null;
			if ( ! in_array( $mode, [ 'live', 'sandbox' ], true ) ) {
				throw new \Exception( 'Invalid request' );
			}

			$api_key = $_REQUEST['api_key'] ?? null;
			if ( empty( $api_key ) || ! is_string( $api_key ) ) {
				throw new \Exception( 'Missing API key' );
			}

			$stripe = new \Voxel\Vendor\Stripe\StripeClient( [
				'api_key' => $api_key,
				'stripe_version' => \Voxel\Modules\Stripe_Payments\Stripe_Client::API_VERSION,
			] );

			$schema = Schema::Object( [
				'id' => Schema::String(),
				'invoice_history' => Schema::Bool()->default(true),
				'customer_update' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'allowed_updates' => Schema::List()
						->allowed_values( [ 'email', 'address', 'phone', 'shipping', 'tax_id', 'name' ] )
						->default( [ 'name', 'email', 'address', 'phone' ] ),
				] ),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['customer_portal'] ?? '' ), true ) );

			$settings = $schema->export();

			$configuration_params = [
				'business_profile' => [
					'headline' => get_bloginfo( 'name' ),
					'privacy_policy_url' => get_permalink( \Voxel\get( 'templates.privacy_policy' ) ) ?: home_url('/'),
					'terms_of_service_url' => get_permalink( \Voxel\get( 'templates.terms' ) ) ?: home_url('/'),
				],
				'features' => [
					'payment_method_update' => [
						'enabled' => true,
					],
					'customer_update' => [
						'allowed_updates' => $settings['customer_update']['allowed_updates'],
						'enabled' => $settings['customer_update']['enabled'],
					],
					'invoice_history' => [
						'enabled' => $settings['invoice_history'],
					],
				],
			];

			if ( empty( $settings['id'] ) ) {
				$configuration = $stripe->billingPortal->configurations->create( $configuration_params );

				$settings['id'] = $configuration->id;

				\Voxel\set( sprintf( 'payments.stripe.%s.customer_portal', $mode ), $settings );

				return wp_send_json( [
					'success' => true,
					'message' => _x( 'Portal configuration saved.', 'stripe', 'voxel-backend' ),
					'customer_portal' => $settings,
				] );
			} else {
				$stripe->billingPortal->configurations->update( $settings['id'], $configuration_params );

				\Voxel\set( sprintf( 'payments.stripe.%s.customer_portal', $mode ), $settings );

				return wp_send_json( [
					'success' => true,
					'message' => _x( 'Portal configuration updated.', 'stripe', 'voxel-backend' ),
					'customer_portal' => $settings,
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}


	protected function setup_connect_webhook() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$mode = $_POST['mode'] ?? null;
			if ( ! in_array( $mode, [ 'live', 'sandbox' ], true ) ) {
				throw new \Exception( 'Invalid request' );
			}

			$api_key = $_REQUEST['api_key'] ?? null;
			if ( empty( $api_key ) || ! is_string( $api_key ) ) {
				throw new \Exception( 'Missing API key' );
			}

			$stripe = new \Voxel\Vendor\Stripe\StripeClient( [
				'api_key' => $api_key,
				'stripe_version' => \Voxel\Modules\Stripe_Payments\Stripe_Client::API_VERSION,
			] );

			$endpoint = $stripe->webhookEndpoints->create( [
				'url' => home_url( '/?vx=1&action=stripe.connect_webhooks' ),
				'connect' => true,
				'enabled_events' => \Voxel\Modules\Stripe_Payments\Stripe_Client::CONNECT_WEBHOOK_EVENTS,
			] );

			\Voxel\set( sprintf( 'payments.stripe.stripe_connect.webhook.%s', $mode ), [
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );

			return wp_send_json( [
				'success' => true,
				'message' => __( 'Endpoint created successfully.', 'voxel-backend' ),
				'id' => $endpoint->id,
				'secret' => $endpoint->secret,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function order_details( \Voxel\Order $order ) {
		$payment_method = $order->get_payment_method();
		if ( ! $payment_method ) {
			return;
		}

		$stripe_dashboard_url = $order->is_test_mode() ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';

		if ( $payment_method->get_type() === 'stripe_payment' ): ?>
			<?php if ( $transaction_id = $order->get_transaction_id() ): ?>
				<tr>
					<th>Transaction ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$stripe_dashboard_url . 'payments/' . $transaction_id,
							$transaction_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php elseif ( $payment_method->get_type() === 'stripe_subscription' ): ?>
			<?php if ( $billing_interval = $order->get_billing_interval() ): ?>
				<tr>
					<th>Billing interval</th>
					<td>
						<?= ucwords( \Voxel\interval_format(
							$billing_interval['interval'],
							$billing_interval['interval_count'],
						) ) ?>
					</td>
				</tr>
			<?php endif ?>

			<?php if ( $transaction_id = $order->get_transaction_id() ): ?>
				<tr>
					<th>Subscription ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$stripe_dashboard_url . 'subscriptions/' . $transaction_id,
							$transaction_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php elseif ( $payment_method->get_type() === 'stripe_transfer' ): ?>
			<?php if ( $transaction_id = $order->get_transaction_id() ): ?>
				<tr>
					<th>Transaction ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$stripe_dashboard_url . 'transfers/' . $transaction_id,
							$transaction_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php endif;
	}

	protected function order_customer_details( \Voxel\Order $order, \Voxel\User $customer ) {
		$payment_method = $order->get_payment_method();
		if ( ! $payment_method ) {
			return;
		}

		$stripe_dashboard_url = $order->is_test_mode() ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';

		if ( in_array( $payment_method->get_type(), [ 'stripe_payment', 'stripe_subscription' ], true ) ): ?>
			<?php if ( $customer_id = $customer->get_stripe_customer_id() ): ?>
				<tr>
					<th>Stripe Customer ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$stripe_dashboard_url . 'customers/' . $customer_id,
							$customer_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php endif;
	}

	protected function order_vendor_details( \Voxel\Order $order, \Voxel\User $vendor ) {
		$payment_method = $order->get_payment_method();
		if ( ! $payment_method ) {
			return;
		}

		$stripe_dashboard_url = $order->is_test_mode() ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';

		if ( in_array( $payment_method->get_type(), [ 'stripe_payment', 'stripe_subscription', 'stripe_transfer' ], true ) ): ?>
			<?php if ( $vendor_id = $vendor->get_stripe_vendor_id() ): ?>
				<tr>
					<th>Stripe Vendor ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$stripe_dashboard_url . 'connect/accounts/' . $vendor_id,
							$vendor_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php endif;
	}
}
