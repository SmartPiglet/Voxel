<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Index_Posts_Action extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_posts.index_all', '@index_all' );
		$this->on( 'voxel_ajax_posts.recreate_and_index_all', '@recreate_and_index_all' );
		$this->on( 'voxel_ajax_posts.get_index_data', '@get_index_data' );
	}

	protected function index_all() {
		try {
			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Post type not found.', 'voxel-backend' ) );
			}

			$option_key = sprintf( 'post_type_index:%s', $post_type->get_key() );
			$data = (array) json_decode( get_option( $option_key ), ARRAY_A );
			if ( ( $data['status'] ?? null ) === 'processing' ) {
				throw new \Exception( __( 'Another indexing request is in process for this post type.', 'voxel-backend' ) );
			}

			update_option( $option_key, wp_json_encode( [
				'status' => 'processing',
				'offset' => $data['offset'] ?? 0,
			] ) );

			global $wpdb;

			$offset = absint( $data['offset'] ?? 0 );
			$limit = apply_filters( 'voxel/indexing/batch-size', 50, $post_type );
			$status_in = $post_type->repository->get_indexable_status_sql();
			$total = absint( $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ({$status_in})",
				$post_type->get_key(),
			) ) );

			do {
				$post_ids = $wpdb->get_col( $wpdb->prepare( <<<SQL
					SELECT ID FROM {$wpdb->posts}
						WHERE post_type = %s AND post_status IN ({$status_in})
						ORDER BY ID ASC LIMIT %d, %d
					SQL,
					$post_type->get_key(),
					$offset,
					$limit
				) );

				$table = $post_type->get_index_table();
				$table->index( $post_ids );

				// final batch
				if ( count( $post_ids ) < $limit ) {
					delete_option( $option_key );
					return wp_send_json( [
						'success' => true,
						'offset' => $total,
						'total' => $total,
						'has_more' => false,
					] );
				}

				$offset += $limit;
			} while ( ! \Voxel\nearing_resource_limits() );

			update_option( $option_key, wp_json_encode( [
				'status' => 'batch-processed',
				'offset' => $offset,
			] ) );

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

	protected function recreate_and_index_all() {
		$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
		if ( ! $post_type ) {
			throw new \Exception( __( 'Post type not found.', 'voxel-backend' ) );
		}

		delete_option( sprintf( 'post_type_index:%s', $post_type->get_key() ) );
		$post_type->index_table->recreate();
		$this->index_all();
	}

	protected function get_index_data() {
		try {
			$post_type = \Voxel\Post_Type::get( $_GET['post_type'] ?? null );
			if ( ! $post_type ) {
				throw new \Exception( __( 'Post type not found.', 'voxel-backend' ) );
			}

			global $wpdb;

			$status_in = $post_type->repository->get_indexable_status_sql();
			$items_total = absint( $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ({$status_in})",
				$post_type->get_key(),
			) ) );

			$table_name = $post_type->index_table->get_escaped_name();
			$table_exists = $post_type->index_table->exists();
			if ( $table_exists ) {
				$items_indexed = absint( $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" ) );
			}

			return wp_send_json( [
				'success' => true,
				'table_name' => $table_name,
				'table_exists' => $table_exists,
				'items_total' => $items_total,
				'items_indexed' => $items_indexed ?? 0,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
