<?php

namespace Voxel\Taxonomies;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term_Post_Counts {

	private $term;

	public function __construct( \Voxel\Term $term ) {
		$this->term = $term;
	}

	public function get_counts(): array {
		$stats = json_decode( get_term_meta( $this->term->get_id(), 'voxel:post_counts', true ), ARRAY_A );
		if ( ! is_array( $stats ) ) {
			$stats = $this->update_cache();
		}

		return $stats;
	}

	public function get_count_for_post_type( $post_type_key ): int {
		$counts = $this->get_counts();
		return absint( $counts[ $post_type_key ] ?? 0 );
	}

	public function update_cache(): array {
		global $wpdb;

		$post_counts = [];

		$sql = $wpdb->prepare( <<<SQL
			SELECT p.post_type AS `post_type`, COUNT(*) AS `count` FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->term_relationships} AS tr ON ( p.ID = tr.object_id )
			WHERE p.post_status = 'publish' AND tr.term_taxonomy_id = %d
			GROUP BY p.post_type
		SQL, $this->term->get_term_taxonomy_id() );

		// dd_sql($sql);
		$results = $wpdb->get_results( $sql );

		foreach ( $results as $result ) {
			$post_counts[ $result->post_type ] = absint( $result->count );
		}

		if ( empty( $post_counts ) ) {
			delete_term_meta( $this->term->get_id(), 'voxel:post_counts' );
		} else {
			update_term_meta( $this->term->get_id(), 'voxel:post_counts', wp_slash( wp_json_encode( $post_counts ) ) );
		}

		return $post_counts;
	}
}
