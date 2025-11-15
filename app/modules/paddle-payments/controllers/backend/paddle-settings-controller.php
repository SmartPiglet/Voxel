<?php

namespace Voxel\Modules\Paddle_Payments\Controllers\Backend;

use Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\Operations\CreateNotificationSetting;
use Voxel\Vendor\Paddle\SDK\Entities\NotificationSetting\NotificationSettingType;
use \Voxel\Vendor\Paddle\SDK\Client as PaddleClient;
use \Voxel\Vendor\Paddle\SDK\Environment as PaddleEnv;
use \Voxel\Vendor\Paddle\SDK\Options as PaddleOpts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Settings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_paddle.admin.setup_webhook', '@setup_webhook' );

		$this->on( 'voxel/backend/view_order/details/after', '@order_details' );
		$this->on( 'voxel/backend/view_order/customer_details/after', '@order_customer_details', 10, 2 );
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

			$paddle = new PaddleClient(
				apiKey: $api_key,
				options: new PaddleOpts( $mode === 'live' ? PaddleEnv::PRODUCTION : PaddleEnv::SANDBOX ),
			);

			$webhook = $paddle->notificationSettings->create( new CreateNotificationSetting(
				description: sprintf( 'Primary webhook (%s mode)', $mode ),
				type: NotificationSettingType::from('url'),
				destination: home_url( '/?vx=1&action=paddle.webhooks' ),
				subscribedEvents: \Voxel\Modules\Paddle_Payments\Paddle_Client::get_webhook_events(),
				includeSensitiveFields: true,
			) );

			\Voxel\set( sprintf( 'payments.paddle.%s.webhook', $mode ), [
				'id' => $webhook->id,
				'secret' => $webhook->endpointSecretKey,
			] );

			return wp_send_json( [
				'success' => true,
				'message' => __( 'Endpoint created successfully.', 'voxel-backend' ),
				'id' => $webhook->id,
				'secret' => $webhook->endpointSecretKey,
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

		$paddle_dashboard_url = $order->is_test_mode() ? 'https://sandbox-vendors.paddle.com/' : 'https://vendors.paddle.com/';

		if ( $payment_method->get_type() === 'paddle_payment' ): ?>
			<?php if ( $transaction_id = $order->get_transaction_id() ): ?>
				<tr>
					<th>Transaction ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$paddle_dashboard_url . 'transactions-v2/' . $transaction_id,
							$transaction_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php elseif ( $payment_method->get_type() === 'paddle_subscription' ): ?>
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
							$paddle_dashboard_url . 'subscriptions-v2/' . $transaction_id,
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

		$paddle_dashboard_url = $order->is_test_mode() ? 'https://sandbox-vendors.paddle.com/' : 'https://vendors.paddle.com/';

		if ( in_array( $payment_method->get_type(), [ 'paddle_payment', 'paddle_subscription' ], true ) ): ?>
			<?php if ( $customer_id = $customer->get_paddle_customer_id() ): ?>
				<tr>
					<th>Paddle Customer ID</th>
					<td>
						<?= sprintf(
							'<a href="%s" target="_blank">%s %s</a>',
							$paddle_dashboard_url . 'customers-v2/' . $customer_id,
							$customer_id,
							'<i class="las la-external-link-alt"></i>'
						) ?>
					</td>
				</tr>
			<?php endif ?>
		<?php endif;
	}
}
