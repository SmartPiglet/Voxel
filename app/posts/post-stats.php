<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Stats {

	private $cache = [];

	protected $post;

	public function __construct( \Voxel\Post $post ) {
		$this->post = $post;
	}

	/**
	 * Get post view count.
	 *
	 * @param $timeframe all|1d|7d|30d
	 * @since 1.3
	 */
	public function get_views( string $timeframe ): int {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:view_counts', true ), true );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_post_view_counts( $this->post->get_id() );
			// \Voxel\log('Caching view counts for post '.$this->post->get_id());
		}

		return absint( $stats['views'][ $timeframe ] ?? 0 );
	}

	/**
	 * Get post unique view count.
	 *
	 * @param $timeframe all|1d|7d|30d
	 * @since 1.3
	 */
	public function get_unique_views( string $timeframe ): int {
		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:view_counts', true ), true );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_post_view_counts( $this->post->get_id() );
			// \Voxel\log('Caching unique view counts for post '.$this->post->get_id());
		}

		return absint( $stats['unique_views'][ $timeframe ] ?? 0 );
	}

	/**
	 * Get visitor tracking stats
	 *
	 * @param $stat ref_domains|ref_urls|browsers|devices|platforms|countries
	 * @since 1.3
	 */
	public function get_tracking_stats( string $stat ): array {
		if ( isset( $this->cache[ $stat ] ) ) {
			return $this->cache[ $stat ];
		}

		$stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:tracking_stats', true ), true );
		$last_updated = strtotime( $stats['t'] ?? '' );

		if ( ! $last_updated || ( time() - $last_updated ) > \Voxel\Stats\get_cache_refresh_interval() ) {
			$stats = \Voxel\Stats\cache_post_tracking_stats( $this->post->get_id() );
			// \Voxel\log('Caching tracking stats for post '.$this->post->get_id());
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
		$view_counts = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:view_counts', true ), true );
		$view_counts_t = strtotime( $view_counts['t'] ?? '' );

		$tracking_stats = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:tracking_stats', true ), true );
		$tracking_stats_t = strtotime( $tracking_stats['t'] ?? '' );

		if ( $view_counts_t && $tracking_stats_t ) {
			return min( $view_counts_t, $tracking_stats_t );
		}

		return $view_counts_t ?: ( $tracking_stats_t ?: null );
	}

	public function get_views_last_updated_time() {
		$view_counts = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:view_counts', true ), true );
		return strtotime( $view_counts['t'] ?? '' ) ?: null;
	}

	public function get_chart_cache() {
		return (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:view_chart_cache', true ), true );
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

		update_post_meta( $this->post->get_id(), 'voxel:view_chart_cache', wp_slash( wp_json_encode( $cache ) ) );
	}
}
