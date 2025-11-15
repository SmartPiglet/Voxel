<?php

namespace Voxel\Controllers\Async;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Purge_Stats_Cache_Action extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.statistics.purge_cache', '@purge_cache' );
	}

	protected function purge_cache() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('voxel:view_counts', 'voxel:tracking_stats', 'voxel:view_chart_cache')" );
			$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('voxel:view_counts', 'voxel:tracking_stats', 'voxel:view_chart_cache')" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ('voxel:view_counts', 'voxel:tracking_stats', 'voxel:view_chart_cache')" );

			return wp_send_json( [
				'success' => true,
				'message' => 'All stats cache has been purged',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}
