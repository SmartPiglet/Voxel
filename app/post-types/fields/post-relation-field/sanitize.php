<?php

namespace Voxel\Post_Types\Fields\Post_Relation_Field;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Sanitize {

	/**
	 * Sanitize the field value submitted through the Create Post form.
	 *
	 * @since 1.0
	 */
	public function sanitize( $value ) {
		global $wpdb;

		if ( empty( $this->props['post_types'] ) ) {
			return null;
		}

		$post_ids = [];
		foreach ( (array) $value as $post_id ) {
			if ( ! is_numeric( $post_id ) ) {
				continue;
			}

			$post_ids[] = absint( $post_id );
			if ( in_array( $this->props['relation_type'], [ 'has_one', 'belongs_to_one' ], true ) ) {
				break;
			}
		}

		if ( empty( $post_ids ) ) {
			return null;
		}

		$query_ids = join( ',', $post_ids );
		$query_post_types = '\''.join( '\',\'', array_map( 'esc_sql', $this->props['post_types'] ) ).'\'';
		$author_id = absint( $this->post ? $this->post->get_author_id() : get_current_user_id() );
		if ( ! empty( $this->props['allowed_statuses'] ) ) {
			$query_additional_statuses = "'".join(
				"','",
				array_map( 'esc_sql', (array) $this->props['allowed_statuses'] )
			)."'";
		}

		/**
		 * User can pick posts from any author.
		 */
		if ( $this->props['allowed_authors'] === 'any' ) {
			if ( ! empty( $this->props['allowed_statuses'] ) ) {
				$sql = <<<SQL
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_type IN ({$query_post_types})
						AND ID IN ({$query_ids})
						AND ( post_status = 'publish' OR (
							post_author = {$author_id}
							AND post_status IN ('publish',{$query_additional_statuses})
						) )
					ORDER BY FIELD(ID,{$query_ids})
				SQL;
			} else {
				$sql = <<<SQL
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_status = 'publish'
						AND post_type IN ({$query_post_types})
						AND ID IN ({$query_ids})
					ORDER BY FIELD(ID,{$query_ids})
				SQL;
			}

		/**
		 * User can pick their posts only.
		 */
		} else {
			if ( ! empty( $this->props['allowed_statuses'] ) ) {
				$sql = <<<SQL
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_author = {$author_id}
						AND post_status IN ('publish',{$query_additional_statuses})
						AND post_type IN ({$query_post_types})
						AND ID IN ({$query_ids})
					ORDER BY FIELD(ID,{$query_ids})
				SQL;
			} else {
				$sql = <<<SQL
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_author = {$author_id}
						AND post_status = 'publish'
						AND post_type IN ({$query_post_types})
						AND ID IN ({$query_ids})
					ORDER BY FIELD(ID,{$query_ids})
				SQL;
			}
		}

		$existing_ids = $wpdb->get_col( $sql );

		$existing_ids = array_map( 'absint', $existing_ids );

		if ( empty( $existing_ids ) ) {
			return null;
		}

		return $existing_ids;
	}
}
