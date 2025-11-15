<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class List_Shipping_Rates_Action extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.list_shipping_rates', '@list_rates' );
	}

	protected function list_rates() {
		try {
			$mode = $_REQUEST['mode'] ?? 'test';
			$stripe = $mode === 'test'
				? \Voxel\Modules\Stripe_Payments\Stripe_Client::getTestClient()
				: \Voxel\Modules\Stripe_Payments\Stripe_Client::getLiveClient();

			$args = [
				'active' => true,
				'limit' => 10,
			];

			if ( ! empty( $_REQUEST['ending_before'] ) ) {
				$args['ending_before'] = $_REQUEST['ending_before'];
			}

			if ( ! empty( $_REQUEST['starting_after'] ) ) {
				$args['starting_after'] = $_REQUEST['starting_after'];
			}

			$rates = $stripe->shippingRates->all( $args );

			return wp_send_json( [
				'success' => true,
				'has_more' => $rates->has_more,
				'rates' => array_map( function( $rate ) {
					return [
						'id' => $rate->id,
						'display_name' => $rate->display_name,
					];
				}, $rates->data ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
