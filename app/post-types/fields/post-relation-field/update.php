<?php

namespace Voxel\Post_Types\Fields\Post_Relation_Field;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Update {

	/**
	 * Saves the sanitized relations to the database.
	 *
	 * @since 1.0
	 */
	public function update( $value ): void {
		global $wpdb;

		$pending_ids = $this->send_approval_requests( $value );

		// pending approval ids should only be saved after approval
		if ( ! empty( $value ) ) {
			$value = array_values( array_diff( $value, $pending_ids ) );
		}

		// delete existing relations
		$delete_column = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
			? 'parent_id'
			: 'child_id';

		$wpdb->delete( $wpdb->prefix.'voxel_relations', [
			$delete_column => $this->post->get_id(),
			'relation_key' => $this->get_relation_key(),
		] );

		// insert new relations
		if ( ! empty( $value ) ) {
			$rows = [];
			foreach ( (array) $value as $index => $post_id ) {
				$parent_id = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
					? $this->post->get_id()
					: $post_id;

				$child_id = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
					? $post_id
					: $this->post->get_id();

				$rows[] = $wpdb->prepare(
					'(%d,%d,%s,%d)',
					$parent_id,
					$child_id,
					$this->get_relation_key(),
					$index
				);
			}

			$imploded_rows = implode( ',', $rows );
			$query = <<<SQL
				INSERT INTO {$wpdb->prefix}voxel_relations
				(`parent_id`, `child_id`, `relation_key`, `order`)
				VALUES {$imploded_rows}
			SQL;

			$wpdb->query( $query );
		}

		$this->clear_cached_value();
	}

	public function approve_relations_from_author( int $author_id, array $specific_post_ids = [] ) {
		global $wpdb;

		if ( ! ( $this->props['allowed_authors'] === 'any' && $this->props['require_author_approval'] === 'always' ) ) {
			return;
		}

		$pending_meta = $this->get_pending_meta();
		if ( empty( $pending_meta[ $author_id ]['ids'] ) ) {
			return;
		}

		$post_ids = array_values( array_map( 'absint', (array) $pending_meta[ $author_id ]['ids'] ) );

		if ( ! empty( $specific_post_ids ) ) {
			$post_ids = array_intersect( $post_ids, $specific_post_ids );
		}

		if ( empty( $post_ids ) ) {
			return;
		}

		$post_id__in = join( ',', $post_ids );

		if ( in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true ) ) {
			$existing = $wpdb->get_results( $wpdb->prepare( <<<SQL
				SELECT child_id FROM {$wpdb->prefix}voxel_relations
				WHERE parent_id = %d AND relation_key = %s AND child_id IN ({$post_id__in})
			SQL, $this->post->get_id(), $this->get_relation_key() ), OBJECT_K );
		} else {
			$existing = $wpdb->get_results( $wpdb->prepare( <<<SQL
				SELECT parent_id FROM {$wpdb->prefix}voxel_relations
				WHERE child_id = %d AND relation_key = %s AND parent_id IN ({$post_id__in})
			SQL, $this->post->get_id(), $this->get_relation_key() ), OBJECT_K );
		}

		$rows = [];
		foreach ( $post_ids as $index => $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || isset( $existing[ $post_id ] ) ) {
				continue;
			}

			$parent_id = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
				? $this->post->get_id()
				: $post_id;

			$child_id = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
				? $post_id
				: $this->post->get_id();

			$rows[] = $wpdb->prepare(
				'(%d,%d,%s,%d)',
				$parent_id,
				$child_id,
				$this->get_relation_key(),
				$index
			);
		}

		if ( empty( $rows ) ) {
			return;
		}

		$imploded_rows = implode( ',', $rows );
		$query = <<<SQL
			INSERT INTO {$wpdb->prefix}voxel_relations
			(`parent_id`, `child_id`, `relation_key`, `order`)
			VALUES {$imploded_rows}
		SQL;

		$wpdb->query( $query );

		// clear pending meta
		if ( ! empty( $specific_post_ids ) ) {
			$remaining_values = array_values( array_diff(
				(array) $pending_meta[ $author_id ]['ids'],
				$specific_post_ids
			) );

			if ( ! empty( $remaining_values ) ) {
				$pending_meta[ $author_id ]['ids'] = $remaining_values;
			} else {
				unset( $pending_meta[ $author_id ] );
			}
		} else {
			unset( $pending_meta[ $author_id ] );
		}

		$this->update_pending_meta( $pending_meta );

		$this->clear_cached_value();
	}

	public function decline_relations_from_author( int $author_id, array $specific_post_ids = [] ) {
		$pending_meta = $this->get_pending_meta();
		if ( empty( $pending_meta[ $author_id ]['ids'] ) ) {
			return;
		}

		if ( ! empty( $specific_post_ids ) ) {
			$remaining_values = array_values( array_diff(
				(array) $pending_meta[ $author_id ]['ids'],
				$specific_post_ids
			) );

			if ( ! empty( $remaining_values ) ) {
				$pending_meta[ $author_id ]['ids'] = $remaining_values;
			} else {
				unset( $pending_meta[ $author_id ] );
			}
		} else {
			unset( $pending_meta[ $author_id ] );
		}

		$this->update_pending_meta( $pending_meta );
	}

	/**
	 * Send approval requests to posts from different authors.
	 *
	 * @since 1.2.9
	 */
	protected function send_approval_requests( $value ) {
		global $wpdb;

		$pending_meta = $this->get_pending_meta();

		delete_post_meta( $this->post->get_id(), $this->get_key().':pending' );

		if ( ! (
			$this->props['allowed_authors'] === 'any'
			&& $this->props['require_author_approval'] === 'always'
			&& ! empty( $value )
			&& ! ( current_user_can('administrator') || current_user_can('editor') ) // admins and editors don't need approval
			&& $this->repeater === null
		) ) {
			$this->recall_approval_requests( $pending_meta );
			return [];
		}

		$previous_value = $this->get_value();
		$new_ids = array_diff( $value, (array) $previous_value );

		// \Voxel\log($previous_value, $value);
		if ( empty( $new_ids ) ) {
			return [];
		}

		$query_id_in = join( ',', array_map( 'absint', $new_ids ) );
		$pending_groups = $wpdb->get_results( $wpdb->prepare( <<<SQL
			SELECT `post_author`, GROUP_CONCAT(`ID`) AS `ids` FROM {$wpdb->posts}
			WHERE ID IN ({$query_id_in}) AND post_author != %d
			GROUP BY post_author
		SQL, $this->post->get_author_id() ) );

		$pending_ids = [];
		$new_pending_meta = [];
		foreach ( $pending_groups as $group ) {
			$author_id = absint( $group->post_author );
			$group_ids = array_map( 'absint', explode( ',', $group->ids ) );

			$new_pending_meta[ $author_id ] = [];
			$new_pending_meta[ $author_id ]['ids'] = $group_ids;
			$new_pending_meta[ $author_id ]['n'] =  $pending_meta[ $author_id ]['n'] ?? null;
			$pending_ids = array_merge( $pending_ids, $group_ids );

			// trigger relation request event if it hasn't been triggered already for posts from this author
			if ( ( $pending_meta[ $author_id ]['ids'] ?? [] ) !== $group_ids ) {
				$event = new \Voxel\Events\Post_Relations\Relation_Requested_Event( $this );
				$event->dispatch( $this->post->get_id(), $author_id, $group_ids );

				if ( $notification = ( $event->_inapp_sent_cache['author'] ?? null ) ) {
					$new_pending_meta[ $author_id ]['n'] = $notification->get_id();

					if ( $previous_notification = \Voxel\Notification::get( $pending_meta[ $author_id ]['n'] ?? null ) ) {
						$previous_notification->delete();
					}
				}
			}

			unset( $pending_meta[ $author_id ] );
		}

		$this->recall_approval_requests( $pending_meta );

		$this->update_pending_meta( $new_pending_meta );

		return $pending_ids;
	}

	/**
	 * User unselected posts from these authors before they
	 * responded, cleanup notifications.
	 *
	 * @since 1.2.9
	 */
	protected function recall_approval_requests( $requests ) {
		global $wpdb;

		$recalled_ids = array_filter( array_map( 'absint', array_column( $requests, 'n' ) ) );
		if ( ! empty( $recalled_ids ) ) {
			$recalled_id__in = join( ',', $recalled_ids );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}voxel_notifications WHERE id IN ({$recalled_id__in})" );
		}
	}

	protected function clear_cached_value() {
		$select_key = in_array( $this->props['relation_type'], [ 'has_one', 'has_many' ], true )
			? 'child_id'
			: 'parent_id';

		$cache_key = sprintf(
			'relations:%s:%d:%s',
			$this->get_relation_key(),
			$this->post->get_id(),
			$select_key
		);

		wp_cache_delete( $cache_key, 'voxel' );

		if ( $this->post_type->get_key() === 'collection' && $this->get_key() === 'items' && $this->post && $this->post->get_author_id() ) {
			wp_cache_delete( sprintf( 'user_collections:%d', $this->post->get_author_id() ), 'voxel' );
		}
	}

	public function get_pending_meta(): array {
		if ( ! $this->post ) {
			return [];
		}

		return (array) json_decode(
			get_post_meta( $this->post->get_id(), $this->get_key().':pending', true ),
			true
		);
	}

	public function get_pending_ids(): array {
		if ( $this->repeater !== null ) {
			return [];
		}

		$pending_ids = [];
		foreach ( $this->get_pending_meta() as $pending_ids_by_author ) {
			$pending_ids = array_merge( $pending_ids, (array) ( $pending_ids_by_author['ids'] ?? [] ) );
		}

		return $pending_ids;
	}

	public function update_pending_meta( array $pending_meta ) {
		if ( ! empty( $pending_meta ) ) {
			update_post_meta(
				$this->post->get_id(),
				$this->get_key().':pending',
				wp_slash( wp_json_encode( $pending_meta ) )
			);
		} else {
			delete_post_meta( $this->post->get_id(), $this->get_key().':pending' );
		}
	}
}
