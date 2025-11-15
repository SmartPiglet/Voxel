<?php

namespace Voxel\Controllers\Frontend\Statistics;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Tracking_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_tracking.save_visit', '@save_visit' );
		$this->on( 'voxel_ajax_nopriv_tracking.save_visit', '@save_visit' );

		$this->on( 'wp_footer', '@load_tracker' );
	}

	protected function save_visit() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( 'Invalid request.', 100 );
			}

			$post = \Voxel\Post::get( $_REQUEST['post_id'] ?? null );
			if ( ! ( $post && $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( 'Invalid request.', 101 );
			}

			if ( ! ( $post->post_type->is_tracking_enabled() && $post->get_status() === 'publish' ) ) {
				throw new \Exception( 'Invalid request.', 105 );
			}

			global $wpdb;

			$visitor = \Voxel\Visitor::get();
			$ip_address = $visitor->get_ip();

			if ( $ip_address === null ) {
				throw new \Exception( 'Invalid request.', 102 );
			}

			$ref_url = null;
			$ref_domain = null;
			if ( ! empty( $_REQUEST['ref_url'] ) ) {
				$ref_parts = parse_url( $_REQUEST['ref_url'] );
				if ( ! ( $ref_parts === false || empty( $ref_parts['host'] ) ) ) {
					$ref_url = esc_url_raw( $_REQUEST['ref_url'] );
					$ref_domain = $ref_parts['host'];
				}
			}

			$country_code = null;
			$country_list = \Voxel\Utils\Country_List::all();

			if ( is_string( $_REQUEST['country_code'] ?? null ) && isset( $country_list[ strtoupper( $_REQUEST['country_code'] ) ] ) ) {
				$country_code = $country_list[ strtoupper( $_REQUEST['country_code'] ) ]['alpha-2'];
			} elseif ( $country = $visitor->get_country() ) {
				$country_code = $country['alpha-2'];
			}

			$unique_id = $visitor->get_unique_id();

			$data = [
				'post_id' => $post->get_id(),
				'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				'unique_id' => $unique_id,
				'ip_address' => $ip_address,
				'ref_url' => $ref_url,
				'ref_domain' => $ref_domain,
				'os' => $visitor->get_os(),
				'browser' => $visitor->get_browser(),
				'device' => wp_is_mobile() ? 'mobile' : 'desktop',
				'country_code' => $country_code,
			];

			$should_throttle = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT 1 FROM {$wpdb->prefix}voxel_visits
			WHERE unique_id = %s AND post_id = %d AND created_at >= %s
			LIMIT 1
			SQL, $unique_id, $post->get_id(), \Voxel\utc()->sub( new \DateInterval('PT5S') )->format( 'Y-m-d H:i:s' ) ) );

			if ( ! $should_throttle ) {
				$wpdb->insert( $wpdb->prefix.'voxel_visits', $data );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function load_tracker() {
		if ( ! ( is_singular() || is_author() ) ) {
			return;
		}

		$post = \Voxel\get_current_post();
		if ( ! ( $post && $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
			return;
		}

		if ( ! ( $post->post_type->is_tracking_enabled() && $post->get_status() === 'publish' ) ) {
			return;
		}

		if ( apply_filters( '_voxel/disable-visit-tracking', false ) === true ) {
			return;
		}

		$config = [
			'done' => false,
			'post_type' => $post->post_type->get_key(),
			'post_id' => $post->get_id(),
			'providers' => [],
			'index' => 0,
		];

		$available_providers = \Voxel\get_ipgeo_providers();

		foreach ( (array) \Voxel\get( 'settings.ipgeo.providers' ) as $provider ) {
			foreach ( $available_providers as $available_provider ) {
				if ( $available_provider['key'] === $provider['key'] ) {
					$geocode_url = $available_provider['geocode_url'];

					if ( ! empty( $provider['api_key'] ) && ! empty( $available_provider['api_key_param'] ) ) {
						$geocode_url = add_query_arg( $available_provider['api_key_param'], $provider['api_key'], $geocode_url );
					}

					$config['providers'][] = [
						'url' => $geocode_url,
						'prop' => $available_provider['country_code_key'],
					];

					break;
				}
			}
		}

		wp_add_inline_script( 'vx:visit-tracker.js', sprintf( 'window.VX_Track = %s', wp_json_encode( $config ) ), 'before' );
		wp_enqueue_script( 'vx:visit-tracker.js' );
	}
}
