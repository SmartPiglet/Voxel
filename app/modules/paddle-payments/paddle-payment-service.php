<?php

namespace Voxel\Modules\Paddle_Payments;

use \Voxel\Modules\Paddle_Payments as Module;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Payment_Service extends \Voxel\Product_Types\Payment_Services\Base_Payment_Service {

	public function get_key(): string {
		return 'paddle';
	}

	public function get_label(): string {
		return 'Paddle';
	}

	public function get_description(): ?string {
		return 'Sell digital products and subscriptions with Paddle';
	}

	public function is_test_mode(): bool {
		return Module\Paddle_Client::is_test_mode();
	}

	public function get_settings_schema(): Data_Object {
		return Schema::Object( [
			'mode' => Schema::Enum( [ 'live', 'sandbox' ] )->default('sandbox'),
			'currency' => Schema::String()->default('USD'),
			'live' => Schema::Object( [
				'api_key' => Schema::String(),
				'webhook' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
			] ),
			'sandbox' => Schema::Object( [
				'api_key' => Schema::String(),
				'webhook' => Schema::Object( [
					'id' => Schema::String(),
					'secret' => Schema::String(),
				] ),
			] ),
		] );
	}

	public function get_settings_component(): ?array {
		ob_start();
		require locate_template( 'app/modules/paddle-payments/templates/backend/paddle-settings.php' );
		$template = ob_get_clean();

		$src = trailingslashit( get_template_directory_uri() ).'app/modules/paddle-payments/assets/scripts/paddle-settings.esm.js';
		return [
			'src' => add_query_arg( 'v', \Voxel\get_assets_version(), $src ),
			'template' => $template,
			'data' => [],
		];
	}

	public function get_payment_handler(): ?string {
		return 'paddle_payment';
	}

	public function get_subscription_handler(): ?string {
		return 'paddle_subscription';
	}

	public function get_primary_currency(): ?string {
		$currency = \Voxel\get('payments.paddle.currency', 'USD');
		if ( ! is_string( $currency ) || empty( $currency ) ) {
			return 'USD';
		}

		return $currency;
	}

	// @link https://developer.paddle.com/concepts/sell/supported-currencies
	public function get_supported_currencies(): array {
		return [
			'USD', 'EUR', 'GBP', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF',
			'CNY', 'COP', 'CZK', 'DKK', 'HKD', 'HUF', 'ILS', 'INR',
			'JPY', 'KRW', 'MXN', 'NOK', 'NZD', 'PLN', 'RUB', 'SEK',
			'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'VND', 'ZAR',
		];
	}
}
