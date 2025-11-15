<?php

namespace Voxel\Controllers\Frontend\Create_Post;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Relations_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_create_post.relations.get_posts', '@get_posts_for_relation_field' );
	}

	protected function get_posts_for_relation_field() {
		try {
			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? '' );
			if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ), 100 );
			}

			if ( ! empty( $_GET['field_path'] ?? null ) ) {
				$field = null;
				$field_path = array_filter( explode( '.', (string) ( $_GET['field_path'] ?? '' ) ) );
				$repeater = $post_type->get_field( $field_path[0] );
				array_shift( $field_path );

				foreach ( $field_path as $field_path_key ) {
					if ( $repeater && $repeater->get_type() === 'repeater' ) {
						$repeater = $repeater->get_fields()[ $field_path_key ] ?? null;
					}
				}

				if ( $repeater && $repeater->get_type() === 'repeater' ) {
					$field = $repeater->get_fields()[ $_GET['field_key'] ?? '' ] ?? null;
				}
			} else {
				$field = $post_type->get_field( $_GET['field_key'] ?? '' );
			}

			if ( ! ( $field && $field->get_type() === 'post-relation' ) ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ), 101 );
			}

			$post_types = [];
			foreach ( $field->get_prop('post_types') as $post_type_key ) {
				$post_type = \Voxel\Post_Type::get( $post_type_key );
				if ( $post_type && $post_type->is_managed_by_voxel() ) {
					$post_types[] = $post_type->get_key();
				}
			}

			if ( empty( $post_types ) ) {
				throw new \Exception( __( 'Invalid request', 'voxel' ), 102 );
			}

			$author_id = absint( get_current_user_id() );
			if ( ! empty( $_GET['post_id'] ) ) {
				$post = \Voxel\Post::get( (int) $_GET['post_id'] );
				if ( $post && $post->is_editable_by_current_user() ) {
					$author_id = $post->get_author_id();
				}
			}

			$offset = absint( $_GET['offset'] ?? 0 );
			$per_page = 10;
			$limit = $per_page + 1;

			// generate query
			global $wpdb;

			if ( ! empty( $_GET['exclude'] ) ) {
				$exclude_ids = explode( ',', sanitize_text_field( $_GET['exclude'] ) );
				$exclude_ids = array_filter( array_map( 'absint', $exclude_ids ) );
				if ( ! empty( $exclude_ids ) ) {
					$post__not_in = sprintf( 'AND p.ID NOT IN (%s)', join( ',', $exclude_ids ) );
				}
			} else {
				$post__not_in = '';
			}

			$query_post_types = '\''.join( '\',\'', array_map( 'esc_sql', $post_types ) ).'\'';
			$query_order_by = 'p.post_title ASC, p.ID DESC';
			$query_search = '';

			$joins = [];

			if ( ! empty( $_GET['search'] ) ) {
				$search_string = sanitize_text_field( $_GET['search'] );
				$search_string = \Voxel\prepare_keyword_search( $search_string );
				if ( ! empty( $search_string ) ) {
					$search_string = esc_sql( $search_string );
					$query_search = "AND MATCH(p.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)";
					$query_order_by = "MATCH(p.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE) DESC, p.ID DESC";

					if ( in_array( 'profile', $post_types, true ) ) {
						$joins['users'] = "LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID";

						$query_search = <<<SQL
							AND (
								MATCH(p.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)
								OR MATCH(u.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE)
							)
						SQL;

						$query_order_by = <<<SQL
							MATCH(p.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE) DESC,
							MATCH(u.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE) DESC,
							p.ID DESC
						SQL;
					}
				}
			}

			if ( ! empty( $field->get_prop('allowed_statuses') ) ) {
				$query_additional_statuses = "'".join(
					"','",
					array_map( 'esc_sql', (array) $field->get_prop('allowed_statuses') )
				)."'";
			}

			$joins_sql = join( ' ', array_unique( $joins ) );

			if ( $field->get_prop('allowed_authors') === 'any' ) {
				if ( ! empty( $field->get_prop('allowed_statuses') ) ) {
					$sql = <<<SQL
						SELECT p.ID FROM {$wpdb->posts} p {$joins_sql}
						WHERE p.post_type IN ({$query_post_types})
							AND ( p.post_status = 'publish' OR (
								p.post_author = {$author_id}
								AND p.post_status IN ('publish',{$query_additional_statuses})
							) )
							{$post__not_in}
							{$query_search}
						ORDER BY {$query_order_by}
						LIMIT {$limit} OFFSET {$offset}
					SQL;
				} else {
					$sql = <<<SQL
						SELECT p.ID FROM {$wpdb->posts} p {$joins_sql}
						WHERE p.post_status = 'publish'
							AND p.post_type IN ({$query_post_types})
							{$post__not_in}
							{$query_search}
						ORDER BY {$query_order_by}
						LIMIT {$limit} OFFSET {$offset}
					SQL;
				}
			} else {
				if ( ! empty( $field->get_prop('allowed_statuses') ) ) {
					$sql = <<<SQL
						SELECT p.ID FROM {$wpdb->posts} p {$joins_sql}
						WHERE p.post_author = {$author_id}
							AND p.post_status IN ('publish',{$query_additional_statuses})
							AND p.post_type IN ({$query_post_types})
							{$post__not_in}
							{$query_search}
						ORDER BY {$query_order_by}
						LIMIT {$limit} OFFSET {$offset}
					SQL;
				} else {
					$sql = <<<SQL
						SELECT p.ID FROM {$wpdb->posts} p {$joins_sql}
						WHERE p.post_author = {$author_id}
							AND p.post_status = 'publish'
							AND p.post_type IN ({$query_post_types})
							{$post__not_in}
							{$query_search}
						ORDER BY {$query_order_by}
						LIMIT {$limit} OFFSET {$offset}
					SQL;
				}
			}

			$custom_sql = apply_filters( '_voxel/post-relations/get-available-posts-query', null, $field, [
				'limit' => $limit,
				'offset' => $offset,
				'search' => sanitize_text_field( $_GET['search'] ?? '' ),
				'post_id' => isset( $post ) ? $post->get_id() : null,
				'author_id' => $author_id,
			] );

			if ( ! empty( $custom_sql ) ) {
				$sql = $custom_sql;
			}

			$post_ids = $wpdb->get_col( $sql );
			// dd_sql($sql);
			$has_more = count( $post_ids ) > $per_page;
			if ( $has_more ) {
				array_pop( $post_ids );
			}

			_prime_post_caches( $post_ids );

			$posts = [];
			foreach ( $post_ids as $post_id ) {
				if ( $post = \Voxel\Post::get( $post_id ) ) {
					$posts[] = [
						'id' => $post->get_id(),
						'title' => $post->get_display_name(),
						'logo' => $post->get_avatar_markup(),
						'type' => $post->post_type->get_singular_name(),
						'icon' => \Voxel\get_icon_markup( $post->post_type->get_icon() ),
						'requires_approval' => $post->get_author_id() !== $author_id,
					];
				}
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'data' => $posts,
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
