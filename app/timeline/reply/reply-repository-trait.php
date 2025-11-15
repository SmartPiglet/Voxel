<?php

namespace Voxel\Timeline\Reply;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Reply_Repository_Trait {

	public function update( $data_or_key, $value = null ) {
		global $wpdb;

		if ( is_array( $data_or_key ) ) {
			$data = $data_or_key;
		} else {
			$data = [];
			$data[ $data_or_key ] = $value;
		}

		$data['id'] = $this->get_id();
		$wpdb->query( static::_generate_insert_query( $data ) );
		$this->_maybe_update_stats_cache();
	}

	public function delete() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}voxel_timeline_replies WHERE id = %d",
			$this->get_id()
		) );
		$this->_maybe_update_stats_cache();
	}

	public static function create( array $data ): \Voxel\Timeline\Reply {
		global $wpdb;
		$data = array_merge( [
			'id' => null,
			'user_id' => null,
			'status_id' => null,
			'parent_id' => null,
			'content' => null,
			'details' => null,
			'moderation' => null,
			'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
			'edited_at' => null,
		], $data );

		if ( $data['user_id'] === null || $data['status_id'] === null ) {
			throw new \Exception( _x( 'Couldn\'t create reply: missing information.', 'timeline', 'voxel' ) );
		}

		$sql = static::_generate_insert_query( $data );
		$wpdb->query( $sql );
		$data['id'] = $wpdb->insert_id;
		$reply = new \Voxel\Timeline\Reply( $data );
		$reply->_maybe_update_stats_cache();

		return $reply;
	}

	public static function _generate_insert_query( array $data ) {
		global $wpdb;

		$escaped_data = [];
		foreach ( ['id', 'user_id', 'status_id', 'parent_id'] as $column_name ) {
			if ( isset( $data[ $column_name ] ) ) {
				$escaped_data[ $column_name ] = absint( $data[ $column_name ] );
			}
		}

		if ( is_array( $data['details'] ?? null ) ) {
			$data['details'] = wp_json_encode( $data['details'] );
		}

		foreach ( ['content', 'details', 'created_at', 'edited_at'] as $column_name ) {
			if ( isset( $data[ $column_name ] ) ) {
				$escaped_data[ $column_name ] = sprintf( '\'%s\'', esc_sql( $data[ $column_name ] ) );

				if ( $column_name === 'content' ) {
					$_index = \Voxel\text_formatter()->prepare_for_fulltext_indexing( $data[ $column_name ] );
					$escaped_data['_index'] = sprintf( '\'%s\'', esc_sql( $_index ) );
				}
			}
		}

		if ( isset( $data['moderation'] ) && is_numeric( $data['moderation'] ) ) {
			$escaped_data['moderation'] = (int) $data['moderation'];
		}

		$columns = join( ', ', array_map( function( $column_name ) {
			return sprintf( '`%s`', esc_sql( $column_name ) );
		}, array_keys( $escaped_data ) ) );

		$values = join( ', ', $escaped_data );

		$on_duplicate = join( ', ', array_map( function( $column_name ) {
			return sprintf( '`%s`=VALUES(`%s`)', $column_name, $column_name );
		}, array_keys( $escaped_data ) ) );

		$sql = "INSERT INTO {$wpdb->prefix}voxel_timeline_replies ($columns) VALUES ($values)
					ON DUPLICATE KEY UPDATE $on_duplicate";

		return $sql;
	}

}
