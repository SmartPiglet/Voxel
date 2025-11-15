<?php

namespace Voxel\Modules\Collections;

if ( ! defined('ABSPATH') ) {
	exit;
}

new Controllers\Collections_Controller;

function has_saved_post( int $user_id, int $post_id ): bool {
	$cache_key = sprintf( 'user_collections:%d', $user_id );
	$cache = wp_cache_get( $cache_key, 'voxel' );
	if ( isset( $cache[ $post_id ] ) ) {
		return !! $cache[ $post_id ];
	} else {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT 1 FROM {$wpdb->prefix}voxel_relations AS r
			INNER JOIN {$wpdb->posts} AS p ON (
				r.relation_key = 'items'
				AND r.parent_id = p.ID
				AND r.child_id = %d
				AND p.post_author = %d
				AND p.post_type = 'collection'
				AND p.post_status = 'publish'
			)
		SQL, $post_id, $user_id ) );

		$is_saved = $result !== null;

		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		$cache[ $post_id ] = $is_saved;
		wp_cache_set( $cache_key, $cache, 'voxel' );

		return $is_saved;
	}
}

function prime_collection_cache( int $user_id, array $post_ids ) {
	static $primed = [];

	$post_ids = array_filter( array_map( 'absint', $post_ids ) );
	if ( empty( $post_ids ) ) {
		return;
	}

	global $wpdb;

	$post_id__in = join( ',', $post_ids );

	if ( isset( $primed[ $user_id ][ $post_id__in ] ) ) {
		return;
	}

	if ( ! isset( $primed[ $user_id ] ) ) {
		$primed[ $user_id ] = [];
	}

	$primed[ $user_id ][ $post_id__in ] = true;

	$results = $wpdb->get_results( $wpdb->prepare( <<<SQL
		SELECT r.child_id FROM {$wpdb->prefix}voxel_relations AS r
		INNER JOIN {$wpdb->posts} AS p ON (
			r.relation_key = 'items'
			AND r.parent_id = p.ID
			AND p.post_author = %d
			AND p.post_type = 'collection'
			AND p.post_status = 'publish'
		)
		WHERE r.child_id IN ({$post_id__in})
	SQL, $user_id ), OBJECT_K );

	$cache_values = [];
	foreach ( $post_ids as $post_id ) {
		$cache_values[ $post_id ] = isset( $results[ $post_id ] );
	}

	$cache_key = sprintf( 'user_collections:%d', $user_id );
	$existing_cache = wp_cache_get( $cache_key, 'voxel' );

	if ( is_array( $existing_cache ) ) {
		$cache_values = array_merge( $existing_cache, $cache_values );
	}

	wp_cache_set( $cache_key, $cache_values, 'voxel' );
}

function get_collection_count( int $user_id ): int {
	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_type = 'collection'
			AND post_author = %d
			AND post_status = 'publish'
	SQL, $user_id ) );

	return absint( $count );
}

function get_collection_limit(): int {
	return absint( \Voxel\get( 'settings.addons.collections.max_count', 10 ) );
}

function user_can_create_collection( int $user_id ): bool {
	// Administrators can always create collections
	if ( user_can( $user_id, 'administrator' ) ) {
		return true;
	}

	$limit = get_collection_limit();
	
	// 0 means unlimited
	if ( $limit === 0 ) {
		return true;
	}

	$current_count = get_collection_count( $user_id );
	return $current_count < $limit;
}