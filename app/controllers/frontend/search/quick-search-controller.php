<?php

namespace Voxel\Controllers\Frontend\Search;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Quick_Search_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_quick_search', '@quick_search' );
		$this->on( 'voxel_ajax_nopriv_quick_search', '@quick_search' );
	}

	protected function quick_search() {
		try {
			global $wpdb;

			$keywords = \Voxel\prepare_keyword_search_for_index_query( sanitize_text_field( wp_unslash( $_GET['search'] ?? '' ) ) );
			if ( empty( $keywords ) ) {
				return wp_send_json( [
					'data' => [],
					'success' => true,
				] );
			}

			$requested_post_types = (array) json_decode( wp_unslash( $_GET['post_types'] ?? null ), true );
			$post_type_queries = [];
			$taxonomies = [];
			$results = [];
			$dev = [];

			foreach ( $requested_post_types as $post_type_key => $data ) {
				if ( isset( $post_type_queries[ $post_type_key ] ) ) {
					continue;
				}

				$post_type = \Voxel\Post_Type::get( $post_type_key );
				if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
					continue;
				}

				$filter = $post_type->get_filter( $data['filter_key'] ?? null );
				if ( $filter && $filter->get_type() === 'keywords' ) {
					$escaped_table_name = $post_type->get_index_table()->get_escaped_name();
					$escaped_filter_key = esc_sql( $filter->db_key() );
					$post_type_queries[ $post_type_key ] = $wpdb->prepare( <<<SQL
						SELECT post_id, MATCH (`{$escaped_filter_key}`) AGAINST ('%s' IN BOOLEAN MODE) AS `relevance`
						FROM `{$escaped_table_name}`
						WHERE post_status = 'publish' AND MATCH (`{$escaped_filter_key}`) AGAINST ('%s' IN BOOLEAN MODE)
						ORDER BY `relevance` DESC
						LIMIT 5
					SQL, $keywords, $keywords );
				}

				$allowed_taxonomies = $post_type->get_taxonomies();
				foreach ( (array) ( $data['taxonomies'] ?? [] ) AS $taxonomy_key ) {
					$taxonomy = $allowed_taxonomies[ $taxonomy_key ] ?? null;
					if ( $taxonomy && $taxonomy->is_publicly_queryable() ) {
						$taxonomies[ $taxonomy->get_key() ] = $taxonomy->get_key();
					}
				}
			}

			$_query_start_time = microtime( true );
			if ( count( $post_type_queries ) > 1 ) {
				$unions = sprintf( '(%s)', join( ') UNION ALL (', $post_type_queries ) );
				$query = <<<SQL
					SELECT posts.post_id FROM ( {$unions} ) AS posts
					ORDER BY posts.relevance DESC
					LIMIT 10
				SQL;

				$post_ids = $wpdb->get_col( $query );
			} elseif ( count( $post_type_queries ) === 1 ) {
				$query = join( '', $post_type_queries );
				$post_ids = $wpdb->get_col( $query );
			} else {
				$post_ids = [];
			}

			$dev['post_query_time'] = round( ( microtime( true ) - $_query_start_time ) * 1000, 1 ).'ms';
			$dev['post_query'] = $query ?? '(none)';

			if ( ! empty( $post_ids ) ) {
				_prime_post_caches( $post_ids );
				foreach ( $post_ids as $post_id ) {
					if ( $post = \Voxel\Post::get( $post_id ) ) {
						$results[] = [
							'type' => 'post',
							'link' => $post->get_link(),
							'title' => wp_specialchars_decode( $post->quick_search->get_text() ),
							'logo' => $post->quick_search->get_thumbnail(),
							'icon' => \Voxel\get_icon_markup_optimized( $post->quick_search->get_default_icon() ),
							'key' => sprintf( 'post:%d', $post->get_id() ),
						];
					}
				}
			}

			if ( ! empty( $taxonomies ) ) {
				$taxonomy__in = "'".join( "','", array_map( 'esc_sql', $taxonomies ) )."'";
				$query = $wpdb->prepare( "
					SELECT t.term_id, MATCH (t.name) AGAINST (%s IN BOOLEAN MODE) AS relevance
					FROM {$wpdb->terms} AS t
					INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
					WHERE tt.taxonomy IN ({$taxonomy__in}) AND MATCH (t.name) AGAINST (%s IN BOOLEAN MODE)
					ORDER BY relevance DESC
					LIMIT 5
				", $keywords, $keywords );

				$_query_start_time = microtime( true );
				$term_ids = $wpdb->get_col( $query );
				$dev['term_query_time'] = round( ( microtime( true ) - $_query_start_time ) * 1000, 1 ).'ms';
				$dev['term_query'] = $query ?? '(none)';

				if ( ! empty( $term_ids ) ) {
					_prime_term_caches( $term_ids );
					foreach ( $term_ids as $term_id ) {
						if ( $term = \Voxel\Term::get( $term_id ) ) {
							$results[] = [
								'type' => 'term',
								'link' => $term->get_link(),
								'title' => $term->get_label(),
								'icon' => \Voxel\get_icon_markup( $term->get_icon() ),
								'key' => sprintf( 'term:%d', $term->get_id() ),
							];
						}
					}
				}
			}

			return wp_send_json( [
				'data' => $results,
				'success' => true,
				'dev' => \Voxel\is_dev_mode() ? $dev : null,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
