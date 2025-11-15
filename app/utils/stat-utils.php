<?php

namespace Voxel\Stats;

if ( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Post specific stats
 */

function _count_post_views( int $post_id, ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits
			WHERE `post_id` = %d
		SQL, $post_id ) ) );
	} else {
		$where_id = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_id = $wpdb->prepare( " AND id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits
			WHERE `post_id` = %d {$where_id}
		SQL, $post_id ) ) );
	}
}

function _count_post_unique_views( int $post_id, ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(DISTINCT `unique_id`) FROM {$wpdb->prefix}voxel_visits
			WHERE `post_id` = %d
		SQL, $post_id ) ) );
	} else {
		$where_id = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_id = $wpdb->prepare( " AND id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(DISTINCT `unique_id`) FROM {$wpdb->prefix}voxel_visits
			WHERE `post_id` = %d {$where_id}
		SQL, $post_id ) ) );
	}
}

function _get_post_top_referrer_domains( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `ref_domain`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `ref_domain` IS NOT NULL
		GROUP BY `ref_domain`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_domain'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_post_top_referrer_urls( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `ref_url`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `ref_url` IS NOT NULL
		GROUP BY `ref_url`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_url'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_post_top_devices( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `device`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `device` IS NOT NULL
		GROUP BY `device`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['device'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_post_top_browsers( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `browser`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `browser` IS NOT NULL
		GROUP BY `browser`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['browser'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_post_top_platforms( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `os`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `os` IS NOT NULL
		GROUP BY `os`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['os'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_post_top_countries( int $post_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT `country_code`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `post_id` = %d AND `country_code` IS NOT NULL
		GROUP BY `country_code`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $post_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['country_code'] ] = (int) $result['item_count'];
	}

	return $list;
}

function cache_post_view_counts( $post_id ) {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_post_meta( $post_id, 'voxel:view_counts', true ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_post_meta( $post_id, 'voxel:view_counts', wp_slash( wp_json_encode( $previous_stats ) ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'views' => [
			'all' => _count_post_views( $post_id, null ),
			'1d' => _count_post_views( $post_id, new \DateInterval('P1D') ),
			'7d' => _count_post_views( $post_id, new \DateInterval('P7D') ),
			'30d' => _count_post_views( $post_id, new \DateInterval('P30D') ),
		],
		'unique_views' => [
			'all' => _count_post_unique_views( $post_id, null ),
			'1d' => _count_post_unique_views( $post_id, new \DateInterval('P1D') ),
			'7d' => _count_post_unique_views( $post_id, new \DateInterval('P7D') ),
			'30d' => _count_post_unique_views( $post_id, new \DateInterval('P30D') ),
		],
	];

	update_post_meta( $post_id, 'voxel:view_counts', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

function cache_post_tracking_stats( $post_id ) {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_post_meta( $post_id, 'voxel:tracking_stats', true ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_post_meta( $post_id, 'voxel:tracking_stats', wp_slash( wp_json_encode( $previous_stats ) ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'ref_domains' => _get_post_top_referrer_domains( $post_id ),
		'ref_urls' => _get_post_top_referrer_urls( $post_id ),
		'devices' => _get_post_top_devices( $post_id ),
		'browsers' => _get_post_top_browsers( $post_id ),
		'platforms' => _get_post_top_platforms( $post_id ),
		'countries' => _get_post_top_countries( $post_id ),
	];

	update_post_meta( $post_id, 'voxel:tracking_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

/**
 * User specific stats
 */

function _count_user_views( int $user_id, ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits AS v
			LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
			WHERE p.post_author = %d
		SQL, $user_id ) ) );
	} else {
		$where_id = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_id = $wpdb->prepare( " AND v.id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits AS v
			LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
			WHERE p.post_author = %d {$where_id}
		SQL, $user_id ) ) );
	}
}

function _count_user_unique_views( int $user_id, ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(DISTINCT v.unique_id) FROM {$wpdb->prefix}voxel_visits AS v
			LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
			WHERE p.post_author = %d
		SQL, $user_id ) ) );
	} else {
		$where_id = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_id = $wpdb->prepare( " AND v.id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( $wpdb->prepare( <<<SQL
			SELECT COUNT(DISTINCT v.unique_id) FROM {$wpdb->prefix}voxel_visits AS v
			LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
			WHERE p.post_author = %d {$where_id}
		SQL, $user_id ) ) );
	}
}

function _get_user_top_referrer_domains( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.ref_domain, COUNT(*) AS item_count
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.ref_domain IS NOT NULL
		GROUP BY v.ref_domain
		ORDER BY item_count DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_domain'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_referrer_urls( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.ref_url, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.ref_url IS NOT NULL
		GROUP BY v.ref_url
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_url'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_devices( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.device, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.device IS NOT NULL
		GROUP BY v.device
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['device'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_browsers( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.browser, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.browser IS NOT NULL
		GROUP BY v.browser
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['browser'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_platforms( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.os, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.os IS NOT NULL
		GROUP BY v.os
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['os'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_countries( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.country_code, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d AND v.country_code IS NOT NULL
		GROUP BY v.country_code
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['country_code'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_user_top_posts( int $user_id ) {
	global $wpdb;

	$list = [];
	$sql = $wpdb->prepare( <<<SQL
		SELECT v.post_id, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits AS v
		LEFT JOIN {$wpdb->posts} AS p ON ( v.post_id = p.ID )
		WHERE p.post_author = %d
		GROUP BY v.post_id
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL, $user_id );

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['post_id'] ] = (int) $result['item_count'];
	}

	return $list;
}

function cache_user_view_counts( int $user_id ) {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_user_meta( $user_id, 'voxel:view_counts', true ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_user_meta( $user_id, 'voxel:view_counts', wp_slash( wp_json_encode( $previous_stats ) ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'views' => [
			'all' => _count_user_views( $user_id, null ),
			'1d' => _count_user_views( $user_id, new \DateInterval('P1D') ),
			'7d' => _count_user_views( $user_id, new \DateInterval('P7D') ),
			'30d' => _count_user_views( $user_id, new \DateInterval('P30D') ),
		],
		'unique_views' => [
			'all' => _count_user_unique_views( $user_id, null ),
			'1d' => _count_user_unique_views( $user_id, new \DateInterval('P1D') ),
			'7d' => _count_user_unique_views( $user_id, new \DateInterval('P7D') ),
			'30d' => _count_user_unique_views( $user_id, new \DateInterval('P30D') ),
		],
	];

	update_user_meta( $user_id, 'voxel:view_counts', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

function cache_user_tracking_stats( int $user_id ) {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_user_meta( $user_id, 'voxel:tracking_stats', true ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_user_meta( $user_id, 'voxel:tracking_stats', wp_slash( wp_json_encode( $previous_stats ) ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'ref_domains' => _get_user_top_referrer_domains( $user_id ),
		'ref_urls' => _get_user_top_referrer_urls( $user_id ),
		'devices' => _get_user_top_devices( $user_id ),
		'browsers' => _get_user_top_browsers( $user_id ),
		'platforms' => _get_user_top_platforms( $user_id ),
		'countries' => _get_user_top_countries( $user_id ),
		'posts' => _get_user_top_posts( $user_id ),
	];

	update_user_meta( $user_id, 'voxel:tracking_stats', wp_slash( wp_json_encode( $stats ) ) );
	return $stats;
}

/**
 * Site-wide stats
 */

function _count_sitewide_views( ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits" ) );
	} else {
		$where_clause = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_clause = $wpdb->prepare( "WHERE id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}voxel_visits {$where_clause}" ) );
	}
}

function _count_sitewide_unique_views( ?\DateInterval $interval = null ): int {
	global $wpdb;

	if ( $interval === null ) {
		return absint( $wpdb->get_var( "SELECT COUNT(DISTINCT unique_id) FROM {$wpdb->prefix}voxel_visits" ) );
	} else {
		$where_clause = '';
		$last_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT id FROM {$wpdb->prefix}voxel_visits
			WHERE created_at < %s
			ORDER BY created_at DESC
			LIMIT 1
		", \Voxel\utc()->sub( $interval )->format( 'Y-m-d H:i:s' ) ) );

		if ( is_numeric( $last_id ) ) {
			$where_clause = $wpdb->prepare( "WHERE id > %d", absint( $last_id ) );
		}

		return absint( $wpdb->get_var( "SELECT COUNT(DISTINCT unique_id) FROM {$wpdb->prefix}voxel_visits {$where_clause}" ) );
	}
}

function _get_sitewide_top_referrer_domains() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT ref_domain, COUNT(*) AS item_count
		FROM {$wpdb->prefix}voxel_visits
		WHERE ref_domain IS NOT NULL
		GROUP BY ref_domain
		ORDER BY item_count DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_domain'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_referrer_urls() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT ref_url, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE ref_url IS NOT NULL
		GROUP BY ref_url
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['ref_url'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_devices() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT device, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE device IS NOT NULL
		GROUP BY device
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['device'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_browsers() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT browser, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE browser IS NOT NULL
		GROUP BY browser
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['browser'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_platforms() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT os, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE os IS NOT NULL
		GROUP BY os
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['os'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_countries() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT `country_code`, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		WHERE `country_code` IS NOT NULL
		GROUP BY `country_code`
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['country_code'] ] = (int) $result['item_count'];
	}

	return $list;
}

function _get_sitewide_top_posts() {
	global $wpdb;

	$list = [];
	$sql = <<<SQL
		SELECT post_id, COUNT(*) AS `item_count`
		FROM {$wpdb->prefix}voxel_visits
		GROUP BY post_id
		ORDER BY `item_count` DESC
		LIMIT 10
	SQL;

	$results = $wpdb->get_results( $sql, ARRAY_A );
	foreach ( $results as $result ) {
		$list[ $result['post_id'] ] = (int) $result['item_count'];
	}

	return $list;
}

function cache_sitewide_view_counts() {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_option( 'voxel:view_counts' ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_option( 'voxel:view_counts', wp_json_encode( $previous_stats ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'views' => [
			'all' => _count_sitewide_views( null ),
			'1d' => _count_sitewide_views( new \DateInterval('P1D') ),
			'7d' => _count_sitewide_views( new \DateInterval('P7D') ),
			'30d' => _count_sitewide_views( new \DateInterval('P30D') ),
		],
		'unique_views' => [
			'all' => _count_sitewide_unique_views( null ),
			'1d' => _count_sitewide_unique_views( new \DateInterval('P1D') ),
			'7d' => _count_sitewide_unique_views( new \DateInterval('P7D') ),
			'30d' => _count_sitewide_unique_views( new \DateInterval('P30D') ),
		],
	];

	update_option( 'voxel:view_counts', wp_json_encode( $stats ) );
	return $stats;
}

function cache_sitewide_tracking_stats() {
	global $wpdb;

	// update cache time right away to minimize concurrency issues
	$previous_stats = (array) json_decode( get_option( 'voxel:tracking_stats' ), true );
	$previous_stats['t'] = \Voxel\utc()->format( 'Y-m-d H:i:s' );
	update_option( 'voxel:tracking_stats', wp_json_encode( $previous_stats ) );

	// update stats
	$stats = [
		't' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		'ref_domains' => _get_sitewide_top_referrer_domains(),
		'ref_urls' => _get_sitewide_top_referrer_urls(),
		'devices' => _get_sitewide_top_devices(),
		'browsers' => _get_sitewide_top_browsers(),
		'platforms' => _get_sitewide_top_platforms(),
		'countries' => _get_sitewide_top_countries(),
		'posts' => _get_sitewide_top_posts(),
	];

	update_option( 'voxel:tracking_stats', wp_json_encode( $stats ) );
	return $stats;
}

function get_sitewide_views( string $timeframe ): int {
	$stats = (array) json_decode( get_option( 'voxel:view_counts' ), true );
	$last_updated = strtotime( $stats['t'] ?? '' );

	if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
		$stats = \Voxel\Stats\cache_sitewide_view_counts();
		// \Voxel\log('Caching sitewide view counts');
	}

	return absint( $stats['views'][ $timeframe ] ?? 0 );
}

function get_sitewide_unique_views( string $timeframe ): int {
	$stats = (array) json_decode( get_option( 'voxel:view_counts' ), true );
	$last_updated = strtotime( $stats['t'] ?? '' );

	if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
		$stats = \Voxel\Stats\cache_sitewide_view_counts();
		// \Voxel\log('Caching sitewide unique view counts');
	}

	return absint( $stats['unique_views'][ $timeframe ] ?? 0 );
}

function get_sitewide_tracking_stats( string $stat ): array {
	static $cache;
	if ( is_null( $cache ) ) {
		$cache = [];
	}

	if ( isset( $cache[ $stat ] ) ) {
		return $cache[ $stat ];
	}

	$stats = (array) json_decode( get_option( 'voxel:tracking_stats' ), true );
	$last_updated = strtotime( $stats['t'] ?? '' );

	if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
		$stats = \Voxel\Stats\cache_sitewide_tracking_stats();
		// \Voxel\log('Caching sitewide tracking stats');
	}

	$data = [];
	foreach ( (array) ( $stats[ $stat ] ?? [] ) as $item_key => $item_count ) {
		$data[] = [
			'item' => $item_key,
			'count' => $item_count,
		];
	}

	$cache[ $stat ] = $data;
	return $data;
}

function get_sitewide_last_updated_time() {
	$view_counts = (array) json_decode( get_option( 'voxel:view_counts' ), true );
	$view_counts_t = strtotime( $view_counts['t'] ?? '' );

	$tracking_stats = (array) json_decode( get_option( 'voxel:tracking_stats' ), true );
	$tracking_stats_t = strtotime( $tracking_stats['t'] ?? '' );

	if ( $view_counts_t && $tracking_stats_t ) {
		return min( $view_counts_t, $tracking_stats_t );
	}

	return $view_counts_t ?: ( $tracking_stats_t ?: null );
}

function get_sitewide_views_last_updated_time() {
	$view_counts = (array) json_decode( get_option( 'voxel:view_counts' ), ARRAY_A );
	return strtotime( $view_counts['t'] ?? '' ) ?: null;
}

function get_sitewide_chart_cache() {
	return (array) json_decode( get_option( 'voxel:view_chart_cache' ), ARRAY_A );
}

function set_sitewide_chart_cache( $view_type, $timeframe, $data, $t ) {
	$cache = get_sitewide_chart_cache();
	if ( ! isset( $cache[ $view_type ] ) ) {
		$cache[ $view_type ] = [];
	}

	if ( ! isset( $cache[ $view_type ][ $timeframe ] ) ) {
		$cache[ $view_type ][ $timeframe ] = [];
	}

	$cache[ $view_type ][ $timeframe ] = [
		'data' => $data,
		't' => $t,
	];

	update_option( 'voxel:view_chart_cache', wp_json_encode( $cache ) );
}

/* Helpers */

function get_cache_refresh_interval(): int {
	$value = absint( \Voxel\get( 'settings.stats.cache_ttl.value', 24 ) );
	$unit = \Voxel\get( 'settings.stats.cache_ttl.unit', 'hours' );

	if ( $unit === 'days' ) {
		$interval = $value * DAY_IN_SECONDS;
	} elseif ( $unit === 'hours' ) {
		$interval = $value * HOUR_IN_SECONDS;
	} else /* $unit === 'minutes' */ {
		$interval = $value * MINUTE_IN_SECONDS;
	}

	if ( $interval < 60 ) {
		$interval = 60;
	}

	return $interval;
}

function get_device_label( $key ): string {
	static $labels;

	if ( is_null( $labels ) ) {
		$labels = [
			'mobile' => _x( 'Mobile', 'device type', 'voxel' ),
			'desktop' => _x( 'Desktop', 'device type', 'voxel' ),
		];
	}

	return $labels[ $key ] ?? '';
}

function get_browser_label( $key ): string {
	static $labels;

	if ( is_null( $labels ) ) {
		$labels = [
			'chrome' => _x( 'Chrome', 'browser type', 'voxel' ),
			'firefox' => _x( 'Firefox', 'browser type', 'voxel' ),
			'safari' => _x( 'Safari', 'browser type', 'voxel' ),
			'edge' => _x( 'Edge', 'browser type', 'voxel' ),
			'opera' => _x( 'Opera', 'browser type', 'voxel' ),
			'ie' => _x( 'Internet Explorer', 'browser type', 'voxel' ),
		];
	}

	return $labels[ $key ] ?? '';
}

function get_platform_label( $key ): string {
	static $labels;

	if ( is_null( $labels ) ) {
		$labels = [
			'windows' => _x( 'Windows', 'platform type', 'voxel' ),
			'macos' => _x( 'macOS', 'platform type', 'voxel' ),
			'linux' => _x( 'Linux', 'platform type', 'voxel' ),
			'ubuntu' => _x( 'Ubuntu', 'platform type', 'voxel' ),
			'ios' => _x( 'iOS', 'platform type', 'voxel' ),
			'android' => _x( 'Android', 'platform type', 'voxel' ),
			'webos' => _x( 'webOS', 'platform type', 'voxel' ),
		];
	}

	return $labels[ $key ] ?? '';
}
