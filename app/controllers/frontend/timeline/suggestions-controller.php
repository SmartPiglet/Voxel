<?php

namespace Voxel\Controllers\Frontend\Timeline;

use \Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Suggestions_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_timeline/v2/mentions.search', '@search_mentions' );
	}

	protected function search_mentions() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'GET' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$search_term = mb_substr( sanitize_text_field( $_REQUEST['search'] ?? '' ), 0, 64 );
			if ( mb_strlen( $search_term ) <= 1 ) {
				throw new \Exception( __( 'No search term provided.', 'voxel-backend' ) );
			}

			global $wpdb;

			$like = $wpdb->esc_like( $search_term ).'%';
			$sql = $wpdb->prepare( <<<SQL
				SELECT ID AS id, user_login AS username, display_name FROM {$wpdb->users}
				WHERE user_login LIKE %s OR display_name LIKE %s
				LIMIT 5
			SQL, $like, $like );

			$results = $wpdb->get_results( $sql, ARRAY_A );
			if ( ! is_array( $results ) ) {
				$results = [];
			}

			usort( $results, function($a, $b) use ( $search_term ) {
				return levenshtein( $search_term, $a['username'] ) <=> levenshtein( $search_term, $b['username'] );
			} );

			$users = [];
			foreach ( $results as $result ) {
				$users[] = [
					'id' => (int) $result['id'],
					'username' => $result['username'],
					'display_name' => $result['display_name'],
				];
			}

			return wp_send_json( [
				'success' => true,
				'users' => $users,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
