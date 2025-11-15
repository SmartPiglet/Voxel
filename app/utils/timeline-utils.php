<?php

namespace Voxel\Timeline;

if ( ! defined('ABSPATH') ) {
	exit;
}

function user_has_reached_status_rate_limit( int $user_id ): bool {
	global $wpdb;

	$limits = (array) \Voxel\get( 'settings.timeline.posts.rate_limit' );
	$limits = apply_filters( 'voxel/timeline/status-rate-limits', $limits, $user_id );
	$user_id = absint( $user_id );

	$time_between_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) < 1
			FROM {$wpdb->prefix}voxel_timeline tl
			LEFT JOIN {$wpdb->posts} AS p on tl.post_id = p.ID
		WHERE ( tl.user_id = {$user_id} OR ( tl.feed = 'post_timeline' AND p.post_author = {$user_id} ) )
			AND created_at >= %s
		LIMIT 1
	SQL, date( 'Y-m-d H:i:s', strtotime( sprintf( '-%d seconds', absint( $limits['time_between'] ?? 20 ) ) ) ) ) );

	if ( ! $time_between_reached ) {
		return true;
	}

	$hourly_limit = absint( $limits['hourly_limit'] ?? 20 );
	$hourly_limit_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) > {$hourly_limit}
			FROM {$wpdb->prefix}voxel_timeline tl
			LEFT JOIN {$wpdb->posts} AS p on tl.post_id = p.ID
		WHERE ( tl.user_id = {$user_id} OR ( tl.feed = 'post_timeline' AND p.post_author = {$user_id} ) )
			AND created_at >= %s
	SQL, date( 'Y-m-d H:i:s', strtotime('-1 hour') ) ) );

	if ( $hourly_limit_reached ) {
		return true;
	}

	$daily_limit = absint( $limits['daily_limit'] ?? 100 );
	$daily_limit_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) > {$daily_limit}
			FROM {$wpdb->prefix}voxel_timeline tl
			LEFT JOIN {$wpdb->posts} AS p on tl.post_id = p.ID
		WHERE ( tl.user_id = {$user_id} OR ( tl.feed = 'post_timeline' AND p.post_author = {$user_id} ) )
			AND created_at >= %s
	SQL, date( 'Y-m-d H:i:s', strtotime('-1 day') ) ) );

	if ( $daily_limit_reached ) {
		return true;
	}

	return false;
}

function user_has_reached_reply_rate_limit( int $user_id ): bool {
	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	global $wpdb;

	$limits = (array) \Voxel\get( 'settings.timeline.replies.rate_limit' );
	$limits = apply_filters( 'voxel/timeline/reply-rate-limits', $limits, $user_id );
	$user_id = absint( $user_id );

	$time_between_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) < 1
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->posts} AS p on r.published_as = p.ID
		WHERE ( r.user_id = {$user_id} OR p.post_author = {$user_id} )
			AND created_at >= %s
		LIMIT 1
	SQL, date( 'Y-m-d H:i:s', strtotime( sprintf( '-%d seconds', absint( $limits['time_between'] ?? 5 ) ) ) ) ) );

	if ( ! $time_between_reached ) {
		return true;
	}

	$hourly_limit = absint( $limits['hourly_limit'] ?? 100 );
	$hourly_limit_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) > {$hourly_limit}
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->posts} AS p on r.published_as = p.ID
		WHERE ( r.user_id = {$user_id} OR p.post_author = {$user_id} )
			AND created_at >= %s
	SQL, date( 'Y-m-d H:i:s', strtotime('-1 hour') ) ) );

	if ( $hourly_limit_reached ) {
		return true;
	}

	$daily_limit = absint( $limits['daily_limit'] ?? 1000 );
	$daily_limit_reached = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) > {$daily_limit}
			FROM {$wpdb->prefix}voxel_timeline_replies r
			LEFT JOIN {$wpdb->posts} AS p on r.published_as = p.ID
		WHERE ( r.user_id = {$user_id} OR p.post_author = {$user_id} )
			AND created_at >= %s
	SQL, date( 'Y-m-d H:i:s', strtotime('-1 day') ) ) );

	if ( $daily_limit_reached ) {
		return true;
	}

	return false;
}

function cache_user_timeline_stats( int $user_id ): array {
	global $wpdb;

	$stats = [
		'total' => 0,
		'reposted' => 0,
		'quoted' => 0,
	];

	$stats['total'] = absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'user_timeline' AND user_id = %d AND moderation = 1
	SQL, $user_id ) ) );

	$stats['reposted'] = absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'user_timeline' AND user_id = %d AND repost_of IS NOT NULL AND moderation = 1
	SQL, $user_id ) ) );

	$stats['quoted'] = absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
		SELECT COUNT(*) AS total
		FROM {$wpdb->prefix}voxel_timeline
		WHERE feed = 'user_timeline' AND user_id = %d AND quote_of IS NOT NULL AND moderation = 1
	SQL, $user_id ) ) );

	update_user_meta( $user_id, 'voxel:timeline_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}
