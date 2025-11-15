<?php

namespace Voxel\Modules\Collections\Controllers\Frontend;

use Voxel\Modules\Collections as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Frontend_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_user.collections.toggle_item', '@toggle_collection_item' );
		$this->on( 'voxel_ajax_user.collections.list', '@list_collections' );
		$this->on( 'voxel_ajax_user.collections.create', '@create_collection' );
	}

	protected function toggle_collection_item() {
		try {
			global $wpdb;

			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$collection = \Voxel\Post::get( $_POST['collection_id'] ?? null );
			if ( ! (
				$collection
				&& $collection->post_type
				&& $collection->get_status() === 'publish'
				&& $collection->post_type->get_key() === 'collection'
				&& absint( $collection->get_author_id() ) === absint( get_current_user_id() )
			) ) {
				throw new \Exception( _x( 'Collection not found.', 'collections', 'voxel' ) );
			}

			$post = \Voxel\Post::get( $_POST['post_id'] ?? null );
			if ( ! ( $post && $post->post_type ) ) {
				throw new \Exception( _x( 'Post not found.', 'collections', 'voxel' ) );
			}

			$field = $collection->get_field('items');
			$current_user = \Voxel\get_current_user();

			if ( ! in_array( $post->post_type->get_key(), (array) $field->get_prop('post_types'), true ) ) {
				throw new \Exception( _x( 'This post cannot be added to this collection.', 'collections', 'voxel' ) );
			}

			$allowed_statuses = array_merge( [ 'publish' ], (array) $field->get_prop('allowed_statuses') );
			if ( ! in_array( $post->get_status(), $allowed_statuses, true ) ) {
				throw new \Exception( _x( 'This post cannot be added to this collection.', 'collections', 'voxel' ) );
			}

			$toggle = ( $_POST['toggle'] ?? null ) === 'add' ? 'add' : 'remove';

			if ( $toggle === 'add' ) {
				$max_count = $field->get_prop('max_count');
				if ( ! empty( $max_count ) ) {
					$current_count = absint( $wpdb->get_var( <<<SQL
						SELECT COUNT(*) FROM {$wpdb->prefix}voxel_relations
						WHERE parent_id = {$collection->get_id()} AND relation_key = 'items'
					SQL ) );

					if ( absint( $current_count ) >= absint( $max_count ) ) {
						throw new \Exception( _x( 'You cannot add any more items to this collection.', 'collections', 'voxel' ) );
					}
				}

				$exists = !! $wpdb->get_var( <<<SQL
					SELECT id FROM {$wpdb->prefix}voxel_relations
					WHERE parent_id = {$collection->get_id()}
						AND child_id = {$post->get_id()}
						AND relation_key = 'items'
					LIMIT 1
				SQL );

				if ( ! $exists ) {
					$wpdb->query( <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_relations (`parent_id`, `child_id`, `relation_key`, `order`)
						VALUES ({$collection->get_id()}, {$post->get_id()}, 'items', 0)
					SQL );
				}

				$cache_key = sprintf( 'user_collections:%d', $current_user->get_id() );
				$cache = wp_cache_get( $cache_key, 'voxel' );
				if ( ! is_array( $cache ) ) {
					$cache = [];
				}

				$cache[ $post->get_id() ] = true;
				wp_cache_set( $cache_key, $cache, 'voxel' );

				return wp_send_json( [
					'success' => true,
					'status' => 'added',
					'is_saved' => true,
					'message' => sprintf( 'Saved to %s', $collection->get_title() ),
					'message' => \Voxel\replace_vars( _x( 'Saved to @collection', 'collections', 'voxel' ), [
						'@collection' => $collection->get_title(),
					] ),
					'actions' => [ [
						'label' => _x( 'View collection', 'collections', 'voxel' ),
						'link' => $collection->get_link(),
					] ],
				] );
			} else {
				$wpdb->query( <<<SQL
					DELETE FROM {$wpdb->prefix}voxel_relations
					WHERE parent_id = {$collection->get_id()}
						AND child_id = {$post->get_id()}
						AND relation_key = 'items'
				SQL );

				$cache_key = sprintf( 'user_collections:%d', $current_user->get_id() );
				$cache = wp_cache_get( $cache_key, 'voxel' );
				if ( isset( $cache[ $post->get_id() ] ) ) {
					unset( $cache[ $post->get_id() ] );
					wp_cache_set( $cache_key, $cache, 'voxel' );
				}

				return wp_send_json( [
					'success' => true,
					'status' => 'removed',
					'is_saved' => Module\has_saved_post( $current_user->get_id(), $post->get_id() ),
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function list_collections() {
		try {
			$post_id = absint( $_GET['post_id'] ?? null );
			if ( ! $post_id ) {
				throw new \Exception( _x( 'Could not retrieve collections.', 'collections', 'voxel' ) );
			}

			$page = absint( $_GET['pg'] ?? 1 );
			$per_page = 10;

			$user_id = absint( get_current_user_id() );
			$limit = $per_page + 1;
			$offset = ( $page - 1 ) * $per_page;

			global $wpdb;

			$results = $wpdb->get_results( <<<SQL
				SELECT posts.ID AS post_id, posts.post_title AS title, relations.id AS is_selected
					FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->prefix}voxel_relations AS relations ON (
					relations.parent_id = posts.ID
					AND relations.child_id = {$post_id}
					AND relations.relation_key = 'items'
				)
				WHERE posts.post_type = 'collection'
					AND posts.post_author = {$user_id}
					AND posts.post_status = 'publish'
				ORDER BY posts.post_title ASC
				LIMIT {$limit} OFFSET {$offset}
			SQL );

			$has_more = count( $results ) > $per_page;
			if ( $has_more ) {
				array_pop( $results );
			}

			$list = [];
			foreach ( $results as $collection ) {
				$list[] = [
					'id' => absint( $collection->post_id ),
					'title' => $collection->title,
					'selected' => !! $collection->is_selected,
				];
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'list' => $list,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function create_collection() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$user = \Voxel\current_user();
			if ( ! $user->can_create_post( 'collection' ) ) {
				throw new \Exception( _x( 'You have reached the collection limit.', 'collections', 'voxel' ) );
			}

			$post_type = \Voxel\Post_Type::get( 'collection' );
			$field = $post_type->get_field('title');
			$field->set_prop( 'required', true );

			$title = $field->sanitize( $_POST['title'] ?? null );
			$field->check_validity( $title );

			$post_id = wp_insert_post( [
				'post_type' => 'collection',
				'post_title' => $title,
				'post_name' => sanitize_title( $title ),
				'post_status' => 'publish',
				'post_author' => $user->get_id(),
			] );

			if ( is_wp_error( $post_id ) ) {
				throw new \Exception( _x( 'Could not create collection.', 'collections', 'voxel' ) );
			}

			return wp_send_json( [
				'success' => true,
				'item' => [
					'id' => $post_id,
					'title' => $title,
					'selected' => false,
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
