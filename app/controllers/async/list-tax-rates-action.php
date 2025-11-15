<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class List_Tax_Rates_Action extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.list_tax_rates', '@list_rates' );
	}

	protected function list_rates() {
		try {
			$mode = $_REQUEST['mode'] ?? 'test';
			$stripe = $mode === 'test'
				? \Voxel\Modules\Stripe_Payments\Stripe_Client::getTestClient()
				: \Voxel\Modules\Stripe_Payments\Stripe_Client::getLiveClient();

			$args = [
				'active' => true,
				'limit' => 100,
			];

			if ( ! empty( $_REQUEST['ending_before'] ) ) {
				$args['ending_before'] = $_REQUEST['ending_before'];
			}

			if ( ! empty( $_REQUEST['starting_after'] ) ) {
				$args['starting_after'] = $_REQUEST['starting_after'];
			}

			// $rates = $stripe->taxRates->all( $args );
			$rates = [];

			do {
				$response = $stripe->taxRates->all( $args );
				$rates = array_merge( $rates, $response->data );
				$last_tax_rate = end( $rates );
				$args['starting_after'] = $last_tax_rate ? $last_tax_rate->id : null;
			} while ( $response->has_more );

			if ( ( $_REQUEST['dynamic'] ?? null ) === 'yes' ) {
				$supported_countries = \Voxel\Modules\Stripe_Payments\Stripe_Client::get_supported_countries_for_dynamic_tax_rates();
				$rates = array_values( array_filter( $rates, function( $rate ) use ( $supported_countries ) {
					return in_array( $rate->country, $supported_countries, true );
				} ) );
			}

			$countries = \Voxel\Utils\Country_List::all();

			return wp_send_json( [
				'success' => true,
				'has_more' => false,
				'rates' => array_map( function( $rate ) use ( $countries ) {
					$label = [];
					$label[] = sprintf( 'Type: %s', $rate->display_name );
					$label[] = sprintf( 'Rate: %s%% %s', $rate->percentage, $rate->inclusive ? 'Inclusive' : 'Exclusive' );

					if ( $rate->country ) {
						$label[] = sprintf( 'Region: %s', $countries[ $rate->country ]['name'] ?? $rate->country );
					}

					if ( $rate->state ) {
						$label[] = sprintf( 'State: %s', $rate->state );
					}

					return [
						'id' => $rate->id,
						'display_name' => join( ', ', $label ),
					];
				}, $rates ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
