<?php

namespace Voxel\Controllers\Taxonomies;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term_Post_Cache_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.terms.cache_post_counts', '@cache_post_counts' );
		$this->filter( 'admin_footer', '@add_cache_button', 100 );
	}

	protected function cache_post_counts() {
		try {
			$taxonomy = \Voxel\Taxonomy::get( $_GET['taxonomy'] ?? null );
			if ( ! $taxonomy || empty( $taxonomy->get_post_types() ) ) {
				throw new \Exception( __( 'Taxonomy not found.', 'voxel-backend' ) );
			}

			global $wpdb;

			$offset = absint( $_GET['offset'] ?? 0 );
			$limit = max( 1, apply_filters( 'voxel/terms/cache-counts/batch-size', 50, $taxonomy ) );
			$total = absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
					SELECT COUNT(*) FROM {$wpdb->terms} AS t
					LEFT JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
					WHERE tt.taxonomy = %s
				SQL,
				$taxonomy->get_key(),
			) ) );

			do {
				$term_ids = $wpdb->get_col( $wpdb->prepare(
					<<<SQL
						SELECT t.term_id FROM {$wpdb->terms} AS t
						LEFT JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
						WHERE tt.taxonomy = %s
						ORDER BY t.term_id ASC LIMIT %d, %d
					SQL,
					$taxonomy->get_key(),
					$offset,
					$limit
				) );

				$handled_ids = [];

				if ( ! empty( $term_ids ) ) {
					$term_id__in = join( ',', array_map( 'absint', $term_ids ) );

					$joins = [];
					$counts = [];
					foreach ( $taxonomy->get_post_types() as $post_type_key ) {
						$escaped_key = esc_sql( $post_type_key );
						$join_key = sprintf( '`post_type__%s`', esc_sql( $post_type_key ) );

						$counts[] = "'{$escaped_key}', COUNT({$join_key}.post_type)";
						$joins[] = <<<SQL
							LEFT JOIN {$wpdb->posts} AS {$join_key} ON (
								tr.object_id = {$join_key}.ID
								AND {$join_key}.post_type = '{$escaped_key}'
								AND {$join_key}.post_status = 'publish'
							)
						SQL;
					}

					$_join_clauses = join( ' ', $joins );
					$_count_clauses = join( ', ', $counts );
					$sql = <<<SQL
						SELECT tt.term_id, JSON_OBJECT( {$_count_clauses} ) AS post_counts
						FROM {$wpdb->term_relationships} AS tr
						LEFT JOIN {$wpdb->term_taxonomy} AS tt ON ( tr.term_taxonomy_id = tt.term_taxonomy_id )
						{$_join_clauses}
						WHERE tt.term_id IN ({$term_id__in})
						GROUP BY tt.term_id
					SQL;

					$results = $wpdb->get_results( $sql );
					foreach ( $results as $term ) {
						if ( ! is_numeric( $term->term_id ) || $term->term_id <= 1 ) {
							continue;
						}

						$post_counts = (array) json_decode( $term->post_counts, true );
						$post_counts = array_filter( $post_counts, function( $post_count ) {
							return is_numeric( $post_count ) && $post_count > 0;
						} );

						if ( empty( $post_counts ) ) {
							delete_term_meta( $term->term_id, 'voxel:post_counts' );
						} else {
							update_term_meta( $term->term_id, 'voxel:post_counts', wp_slash( wp_json_encode( $post_counts ) ) );
						}

						$handled_ids[ (int) $term->term_id ] = true;
					}

					// these terms were not present in the wp_term_relationships table at all,
					// so their count is implicitly zero
					foreach ( $term_ids as $term_id ) {
						if ( ! isset( $handled_ids[ (int) $term_id ] ) ) {
							delete_term_meta( (int) $term_id, 'voxel:post_counts' );
						}
					}
				}

				// final batch
				if ( count( $term_ids ) < $limit ) {
					return wp_send_json( [
						'success' => true,
						'offset' => $total,
						'total' => $total,
						'has_more' => false,
					] );
				}

				$offset += $limit;
			} while ( ! \Voxel\nearing_resource_limits() );

			return wp_send_json( [
				'success' => true,
				'offset' => $offset,
				'total' => $total,
				'has_more' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function add_cache_button() {
		if ( get_current_screen()->base !== 'edit-tags' ) {
			return;
		}

		$taxonomy = \Voxel\Taxonomy::get( get_current_screen()->taxonomy ?? null );
		if ( ! $taxonomy ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery('.tablenav.top .bulkactions').append(
				jQuery('<a></a>')
					.addClass('button ts-terms-cache-post-counts')
					.attr('href', '#')
					.attr('title', 'Refresh the post count cache for all terms in this taxonomy.')
					.attr('data-taxonomy', <?= wp_json_encode( $taxonomy->get_key() ) ?>)
					.css( { margin: '0 3px' } )
					.text('Refresh post counts')
			);
		</script>
		<?php
	}
}
