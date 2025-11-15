<?php

namespace Voxel\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Stats {

	private $cache = [];

	private $user;

	public function __construct( \Voxel\User $user ) {
		$this->user = $user;
	}

	/**
	 * Get view count for this user's posts
	 *
	 * @param $timeframe all|1d|7d|30d
	 * @since 1.3
	 */
	public function get_views( string $timeframe ): int {
		$stats = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:view_counts', true ), ARRAY_A );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_user_view_counts( $this->user->get_id() );
			// \Voxel\log('Caching view counts for user '.$this->user->get_id());
		}

		return absint( $stats['views'][ $timeframe ] ?? 0 );
	}

	/**
	 * Get unique view count for this user's posts
	 *
	 * @param $timeframe all|1d|7d|30d
	 * @since 1.3
	 */
	public function get_unique_views( string $timeframe ): int {
		$stats = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:view_counts', true ), ARRAY_A );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_user_view_counts( $this->user->get_id() );
			// \Voxel\log('Caching unique view counts for user '.$this->user->get_id());
		}

		return absint( $stats['unique_views'][ $timeframe ] ?? 0 );
	}

	/**
	 * Get visitor tracking stats
	 *
	 * @param $stat ref_domains|ref_urls|browsers|devices|platforms|countries|posts
	 * @since 1.3
	 */
	public function get_tracking_stats( string $stat ): array {
		if ( isset( $this->cache[ $stat ] ) ) {
			return $this->cache[ $stat ];
		}

		$stats = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:tracking_stats', true ), ARRAY_A );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_user_tracking_stats( $this->user->get_id() );
			// \Voxel\log('Caching tracking stats for user '.$this->user->get_id());
		}

		$data = [];
		foreach ( (array) ( $stats[ $stat ] ?? [] ) as $item_key => $item_count ) {
			$data[] = [
				'item' => $item_key,
				'count' => $item_count,
			];
		}

		$this->cache[ $stat ] = $data;
		return $data;
	}

	public function get_last_updated_time() {
		$view_counts = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:view_counts', true ), ARRAY_A );
		$view_counts_t = strtotime( $view_counts['t'] ?? '' );

		$tracking_stats = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:tracking_stats', true ), ARRAY_A );
		$tracking_stats_t = strtotime( $tracking_stats['t'] ?? '' );

		if ( $view_counts_t && $tracking_stats_t ) {
			return min( $view_counts_t, $tracking_stats_t );
		}

		return $view_counts_t ?: ( $tracking_stats_t ?: null );
	}

	public function get_views_last_updated_time() {
		$view_counts = (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:view_counts', true ), ARRAY_A );
		return strtotime( $view_counts['t'] ?? '' ) ?: null;
	}

	public function get_chart_cache() {
		return (array) json_decode( get_user_meta( $this->user->get_id(), 'voxel:view_chart_cache', true ), ARRAY_A );
	}

	public function set_chart_cache( $view_type, $timeframe, $data, $t ) {
		$cache = $this->get_chart_cache();
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

		update_user_meta( $this->user->get_id(), 'voxel:view_chart_cache', wp_slash( wp_json_encode( $cache ) ) );
	}
}
