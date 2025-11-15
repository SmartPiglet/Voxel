<?php

namespace Voxel\Controllers\Ecommerce\Payments;

use Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Payments_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 20 );
		$this->on( 'admin_post_voxel_save_payment_settings', '@save_payment_settings' );
	}

	protected function add_menu_page() {
		add_submenu_page(
			'voxel-orders',
			__( 'Payments', 'voxel-backend' ),
			__( 'Payments', 'voxel-backend' ),
			'manage_options',
			'voxel-payments',
			function() {
				$props = [
					'tab' => $_GET['tab'] ?? 'general',
					'providers' => [],
				];

				$product_types = [];
				foreach ( \Voxel\Product_Type::all(true) as $product_type ) {
					$product_types[ $product_type->get_key() ] = [
						'label' => $product_type->get_label(),
						'key' => $product_type->get_key(),
						'supports_marketplace' => $product_type->supports_marketplace(),
					];
				}

				$props['product_types'] = $product_types;

				$schema = $this->get_payment_settings_schema();

				$payment_services = \Voxel\Product_Types\Payment_Services\Base_Payment_Service::get_all();
				foreach ( $payment_services as $payment_service ) {
					$props['providers'][ $payment_service->get_key() ] = [
						'key' => $payment_service->get_key(),
						'label' => $payment_service->get_label(),
						'description' => $payment_service->get_description(),
						'component' => $payment_service->get_settings_component(),
					];
				}

				$schema->set_value( (array) \Voxel\get( 'payments', [] ) );
				$config = $schema->export();

				require locate_template( 'templates/backend/product-types/payments-screen.php' );
			}
		);
	}

	protected function get_payment_settings_schema(): Data_Object {
		$schema = apply_filters( 'voxel/payment-settings/register', Schema::Object( [
			'provider' => Schema::String(),
		] ) );

		$payment_services = \Voxel\Product_Types\Payment_Services\Base_Payment_Service::get_all();
		foreach ( $payment_services as $payment_service ) {
			$schema->set_prop( $payment_service->get_key(), $payment_service->get_settings_schema() );
		}

		return $schema;
	}

	protected function save_payment_settings() {
		check_admin_referer( 'voxel_save_payment_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['config'] ) ) {
			die;
		}

		$submitted_config = json_decode( stripslashes( $_POST['config'] ), true );

		$schema = $this->get_payment_settings_schema();
		$schema->set_value( $submitted_config );

		$config = $schema->export();

		\Voxel\set( 'payments', Schema::optimize_for_storage( $config ) );

		wp_safe_redirect( add_query_arg( 'tab', $_REQUEST['tab'] ?? null, admin_url( 'admin.php?page=voxel-payments' ) ) );
		die;
	}

}