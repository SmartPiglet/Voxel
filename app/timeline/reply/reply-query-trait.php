<?php

namespace Voxel\Timeline\Reply;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Reply_Query_Trait {

	protected static $instances = [];

	public static function get( $id ) {
		if ( is_array( $id ) ) {
			$data = $id;
			$id = $data['id'];
			if ( ! array_key_exists( $id, static::$instances ) ) {
				static::$instances[ $id ] = new static( $data );
			}
		} elseif ( is_numeric( $id ) ) {
			if ( ! array_key_exists( $id, static::$instances ) ) {
				$result = static::find( [
					'id' => $id,
					'limit' => 1,
					'with_user_like_status' => true,
				] );

				static::$instances[ $id ] = $result ?? null;
			}
		} elseif ( $id === null ) {
			return null;
		}

		return static::$instances[ $id ];
	}

	public static function force_get( $id ) {
		unset( static::$instances[ $id ] );
		return static::get( $id );
	}

	public static function find( array $args ): ?self {
		$args['limit'] = 1;
		$args['offset'] = null;
		$query = static::query( $args );
		return array_shift( $query['items'] );
	}

	public static function query( array $args ): array {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'user_id' => null,
			'status_id' => null,
			'parent_id' => null,
			'order_by' => 'created_at',
			'order' => 'desc',
			// 'order' => 'earliest', // earliest|latest|popular
			'offset' => null,
			'limit' => 10,
			'with_user_like_status' => false,
			'search' => null,
			'moderation' => null,
			'moderation_strict' => false,
			'_get_total_count' => false,
		], $args );

		$join_clauses = [];
		$where_clauses = [];
		$orderby_clauses = [];
		$select_clauses = [
			'DISTINCT replies.id AS reply_id',
			'replies.*',
		];

		if ( ! is_null( $args['id'] ) ) {
			$where_clauses[] = sprintf( 'replies.id = %d', absint( $args['id'] ) );
		}

		if ( ! is_null( $args['user_id'] ) ) {
			$where_clauses[] = sprintf( 'replies.user_id = %d', absint( $args['user_id'] ) );
		}

		if ( ! is_null( $args['status_id'] ) ) {
			$where_clauses[] = sprintf( 'replies.status_id = %d', absint( $args['status_id'] ) );
		}

		if ( ! is_null( $args['parent_id'] ) ) {
			if ( $args['parent_id'] === 0 ) {
				$where_clauses[] = 'replies.parent_id IS NULL';
			} else {
				$where_clauses[] = sprintf( 'replies.parent_id = %d', absint( $args['parent_id'] ) );
			}
		}

		if ( is_string( $args['search'] ) && ! empty( $args['search'] ) ) {
			$keywords = \Voxel\text_formatter()->prepare_for_fulltext_search( $args['search'] );
			if ( ! empty( $keywords ) ) {
				$where_clauses[] = $wpdb->prepare( 'MATCH (replies._index) AGAINST (%s IN BOOLEAN MODE)', $keywords );
			} else {
				// if the search query becomes empty after preparation (invalid query provided), return no results
				$where_clauses[] = '1=0';
			}
		}

		if ( is_numeric( $args['moderation'] ) ) {
			if ( $args['moderation_strict'] ) {
				$where_clauses[] = $wpdb->prepare( "replies.moderation = %d", (int) $args['moderation'] );
			} else {
				if ( is_user_logged_in() ) {
					$where_clauses[] = $wpdb->prepare( <<<SQL
						( replies.moderation = %d OR replies.user_id = %d )
					SQL, (int) $args['moderation'], get_current_user_id() );
				} else {
					$where_clauses[] = $wpdb->prepare( "replies.moderation = %d", (int) $args['moderation'] );
				}
			}
		}

		if ( ! is_null( $args['order_by'] ) ) {
			$order = $args['order'] === 'asc' ? 'ASC' : 'DESC';

			if ( $args['order_by'] === 'created_at' ) {
				$orderby_clauses[] = "replies.created_at {$order}";
			} elseif ( $args['order_by'] === 'like_count' ) {
				$orderby_clauses[] = "replies.like_count {$order}";
			} elseif ( $args['order_by'] === 'reply_count' ) {
				$orderby_clauses[] = "replies.reply_count {$order}";
			} elseif ( $args['order_by'] === 'interaction_count' ) {
				$select_clauses[] = "(replies.like_count + replies.reply_count) AS interaction_count";
				$orderby_clauses[] = "interaction_count {$order}";
			}
		}

		if ( $args['with_user_like_status'] ) {
			$user_to_check = get_current_user_id();
			if ( $user_to_check >= 1 ) {
				$select_clauses[] = $wpdb->prepare( "( SELECT 1 FROM {$wpdb->prefix}voxel_timeline_reply_likes_v2 l
					WHERE l.reply_id = replies.id AND l.user_id = %d LIMIT 1 ) AS liked_by_user", $user_to_check );
			}
		}

		// generate sql string
		$joins = join( " \n ", $join_clauses );
		$wheres = '';
		if ( ! empty( $where_clauses ) ) {
			$wheres = sprintf( 'WHERE %s', join( ' AND ', $where_clauses ) );
		}

		$orderbys = '';
		if ( ! empty( $orderby_clauses ) ) {
			$orderbys = sprintf( 'ORDER BY %s', join( ", ", $orderby_clauses ) );
		}

		$limit = '';
		if ( ! is_null( $args['limit'] ) ) {
			$limit = sprintf( 'LIMIT %d', absint( $args['limit'] ) );
		}

		$offset = '';
		if ( ! is_null( $args['offset'] ) ) {
			$offset = sprintf( 'OFFSET %d', absint( $args['offset'] ) );
		}

		$selects = join( ', ', $select_clauses );
		$sql = <<<SQL
			SELECT {$selects} FROM {$wpdb->prefix}voxel_timeline_replies AS replies
			{$joins} {$wheres}
			{$orderbys}
			{$limit} {$offset}
		SQL;

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		$count = count( $results );

		if ( ! is_array( $results ) ) {
			return [
				'items' => [],
				'count' => 0,
				'_total_count' => 0,
			];
		}

		$grouped_results = [];
		foreach ( $results as $result ) {
			$grouped_results[ $result['id'] ] = $result;
		}

		if ( ! empty( $grouped_results ) ) {
			$__result_id_in = join( ',', array_map( 'absint', array_keys( $grouped_results ) ) );

			$last3_liked_sql = <<<SQL
				SELECT replies.id AS reply_id, JSON_ARRAYAGG( JSON_OBJECT('user_id', l.user_id, 'post_id', l.post_id) ) AS last3_liked
				FROM {$wpdb->prefix}voxel_timeline_replies replies
				JOIN (
					SELECT l.reply_id, l.user_id, l.post_id,
						ROW_NUMBER() OVER (
							PARTITION BY l.reply_id
							ORDER BY l.id DESC
						) AS row_num
					FROM {$wpdb->prefix}voxel_timeline_reply_likes_v2 l
					WHERE l.reply_id IN ({$__result_id_in})
				) l ON l.reply_id = replies.id AND l.row_num <= 3
				GROUP BY replies.id;
			SQL;

			$last3_liked = $wpdb->get_results( $last3_liked_sql, ARRAY_A );

			foreach ( $last3_liked as $details ) {
				if ( isset( $grouped_results[ $details['reply_id'] ] ) ) {
					$grouped_results[ $details['reply_id'] ]['last3_liked'] = $details['last3_liked'];
				}
			}
		}

		$replies = array_map( '\Voxel\Timeline\Reply::get', array_values( $grouped_results ) );

		$_total_count = null;
		if ( $args['_get_total_count'] ) {
			$_total_count = absint( $wpdb->get_var( <<<SQL
				SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline_replies AS replies
				{$joins} {$wheres}
				{$limit} {$offset}
			SQL ) );
		}

		return [
			'items' => $replies,
			'count' => $count,
			'_total_count' => $_total_count,
		];
	}
}