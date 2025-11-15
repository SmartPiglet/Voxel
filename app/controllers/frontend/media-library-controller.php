<?php

namespace Voxel\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Media_Library_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_list_media', '@handle' );
	}

	protected function handle() {
		try {
			global $wpdb;

			$author_id = absint( get_current_user_id() );
			$offset = isset( $_GET['offset'] ) ? absint( $_GET['offset'] ) : 0;
			$per_page = 9;
			$limit = $per_page + 1;

			$query_order_by = 'ID DESC';
			$query_search = '';
			$query_mime_type = '';

			if ( ! empty( $_GET['search'] ) ) {
				$search_string = sanitize_text_field( $_GET['search'] );

				$filetype = wp_check_filetype( '.'.$search_string );
				if ( $filetype['type'] ) {
					$mime_type = esc_sql( $filetype['type'] );
					$query_mime_type = "AND post_mime_type = '{$mime_type}'";
					$search_string = substr( $search_string, 0, -( mb_strlen( $filetype['ext'] ) + 1 ) );
				}

				$search_string = \Voxel\prepare_keyword_search( $search_string );
				if ( ! empty( $search_string ) ) {
					$search_string = esc_sql( $search_string );
					$query_search = "AND MATCH(post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)";
					$query_order_by = "MATCH(post_title) AGAINST('{$search_string}' IN BOOLEAN MODE) DESC";
				}
			}

			$where_author = "AND post_author = {$author_id}";
			if ( apply_filters( 'voxel/media-library/filter-by-author', true ) === false ) {
				$where_author = '';
			}

			$sql = <<<SQL
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
					{$where_author}
					{$query_mime_type}
					{$query_search}
				ORDER BY {$query_order_by}
				LIMIT {$limit} OFFSET {$offset}
			SQL;
			$post_ids = $wpdb->get_col( $sql );
			// dd_sql($sql);
			$has_more = count( $post_ids ) > $per_page;
			if ( $has_more ) {
				array_pop( $post_ids );
			}

			_prime_post_caches( $post_ids );

			$files = [];
			foreach ( $post_ids as $post_id ) {
				if ( $attachment = get_post( $post_id ) ) {
					$files[] = [
						'source' => 'existing',
						'id' => $attachment->ID,
						'name' => wp_basename( get_attached_file( $attachment->ID ) ),
						'type' => $attachment->post_mime_type,
						'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
						'is_private' => $attachment->post_status === 'private',
					];
				}
			}

			return wp_send_json( [
				'success' => true,
				'data' => $files,
				'has_more' => $has_more,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

}
