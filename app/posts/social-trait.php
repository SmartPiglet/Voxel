<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Social_Trait {

	public function get_follow_status( $object_type, $object_id ) {
		global $wpdb;
		$status = $wpdb->get_var( $wpdb->prepare(
			"SELECT `status` FROM {$wpdb->prefix}voxel_followers
				WHERE `object_type` = '%s' AND `object_id` = %d AND `follower_type` = 'post' AND `follower_id` = %d",
			$object_type,
			$object_id,
			$this->get_id()
		) );

		if ( is_null( $status ) ) {
			return null;
		}

		$status = intval( $status );
		if ( ! in_array( $status, [ -1, 0, 1 ], true ) ) {
			return null;
		}

		return $status;
	}

	public function set_follow_status( $object_type, $object_id, $status ) {
		global $wpdb;
		if ( $status === \Voxel\FOLLOW_NONE ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}voxel_followers WHERE `object_type` = '%s' AND `object_id` = %d AND `follower_type` = 'post' AND `follower_id` = %d",
				$object_type,
				$object_id,
				$this->get_id()
			) );
		} else {
			$status = intval( $status );
			if ( ! in_array( $status, [ -1, 0, 1 ], true ) ) {
				return null;
			}

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}voxel_followers (`object_type`, `object_id`, `follower_type`, `follower_id`, `status`)
					VALUES ('%s', %d, 'post', %d, %d) ON DUPLICATE KEY UPDATE `status` = VALUES(`status`)",
				$object_type,
				$object_id,
				$this->get_id(),
				$status
			) );
		}

		\Voxel\cache_post_follow_stats( $this->get_id() );
		$object_type === 'post' ? \Voxel\cache_post_follow_stats( $object_id ) : \Voxel\cache_user_follow_stats( $object_id );
	}

	public function can_send_messages() {
		if ( ! $this->post_type ) {
			return false;
		}

		return $this->post_type->get_setting( 'messages.enabled' );
	}
}
