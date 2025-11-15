<?php

namespace Voxel\Timeline\Status;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Status_Query_Trait {

	protected static $instances = [];

	/**
	 * Get a status based on its id.
	 *
	 * @since 1.0
	 */
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
					'with_user_repost_status' => true,
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

	public static function query( array $args ): array {
		return static::_query_results( $args );
	}

	public static function find( array $args ): ?self {
		$args['limit']  = 1;
		$args['offset'] = null;
		$query = static::query( $args );
		return array_shift( $query['items'] );
	}

	public static function _query_results( array $args ): array {
		global $wpdb;

		/* ---- defaults -------------------------------------------------- */
		$args = array_merge( [
			// filters
			'id'                         => null,
			'user_id'                    => null,
			'post_id'                    => null,
			'published_as'               => null,
			'repost_of'                  => null,
			'quote_of'                   => null,
			'feed'                       => null,
			'search'                     => null,
			'moderation'                 => null,
			'moderation_strict'          => false,
			'with_no_reposts'            => false,
			'created_at'                 => null,
			// extras
			'with_current_user_visibility_checks' => false,
			'with_user_like_status'      => false,
			'with_user_repost_status'    => false,
			'liked_by_user'              => null,
			'follower_type'              => null,
			'follower_id'                => null,
			// ordering / window
			'order_by'                   => 'created_at',
			'order'                      => 'desc',
			'limit'                      => 10,
			'offset'                     => null,
			// output
			'_get_total_count'           => false,
			'with_annotations'           => false,
		], $args );

		$viewer_user_id = is_user_logged_in() ? (int) get_current_user_id() : 0;

		/* ---- frequently reused EXISTS snippets ------------------------ */
		$exists_user_follow = $viewer_user_id
			? $wpdb->prepare( "EXISTS (
					SELECT 1
					FROM {$wpdb->prefix}voxel_followers uf
					WHERE uf.follower_type = 'user' AND uf.follower_id = %d
					  AND uf.object_type = 'user' AND uf.status = 1
					  AND uf.object_id = statuses.user_id
				)", $viewer_user_id )
			: '0';

		$exists_post_follow = $viewer_user_id
			? $wpdb->prepare( "EXISTS (
					SELECT 1
					FROM {$wpdb->prefix}voxel_followers pf
					WHERE pf.follower_type = 'user' AND pf.follower_id = %d
					  AND pf.object_type = 'post' AND pf.status = 1
					  AND pf.object_id = statuses.post_id
				)", $viewer_user_id )
			: '0';

		/* ---- SELECT / JOIN / WHERE / ORDER holders --------------------- */
		$select_columns = [ 'DISTINCT statuses.id AS status_id', 'statuses.*' ];
		$join_clauses   = [];
		$where_clauses  = [];
		$order_clauses  = [];

		/* ---- scalar (id / user_id / post_id) --------------------------- */
		$scalar_map = [
			'id'      => 'statuses.id',
			'user_id' => 'statuses.user_id',
			'post_id' => 'statuses.post_id',
		];
		foreach ( $scalar_map as $arg_key => $column ) {
			if ( $args[ $arg_key ] !== null ) {
				$value = absint( $args[ $arg_key ] );
				if ( $args[ $arg_key ] < 0 ) {
					$where_clauses[] = "NOT( {$column} <=> {$value} )";
				} else {
					$where_clauses[] = "{$column} = {$value}";
				}
			}
		}

		/* ---- full-text search ----------------------------------------- */
		if ( is_string( $args['search'] ) && $args['search'] !== '' ) {
			$keywords = \Voxel\text_formatter()->prepare_for_fulltext_search( $args['search'] );
			$where_clauses[] = $keywords === ''
				? '1=0'
				: $wpdb->prepare( 'MATCH (statuses._index) AGAINST (%s IN BOOLEAN MODE)', $keywords );
		}

		/* ---- context columns (published_as, repost_of, quote_of) ------- */
		foreach ( [ 'published_as', 'repost_of', 'quote_of' ] as $ctx_key ) {
			if ( $args[ $ctx_key ] !== null ) {
				$ctx_value = absint( $args[ $ctx_key ] );
				$column    = 'statuses.' . esc_sql( $ctx_key );
				if ( $args[ $ctx_key ] < 0 ) {
					$where_clauses[] = "NOT( {$column} <=> {$ctx_value} )";
				} else {
					$where_clauses[] = "{$column} = {$ctx_value}";
				}
			}
		}

		/* ---- moderation ------------------------------------------------ */
		if ( is_numeric( $args['moderation'] ) ) {
			$moderation_value = (int) $args['moderation'];

			if ( $args['moderation_strict'] ) {
				$where_clauses[] = $wpdb->prepare( 'statuses.moderation = %d', $moderation_value );
			} else {
				if ( $viewer_user_id ) {
					$join_clauses[] = "LEFT JOIN {$wpdb->posts} AS posts ON statuses.post_id = posts.ID";
					$where_clauses[] = $wpdb->prepare( '(
						statuses.moderation = %d
						OR statuses.user_id = %d
						OR ( statuses.feed = "post_timeline" AND posts.post_author = %d )
					)', $moderation_value, $viewer_user_id, $viewer_user_id );
				} else {
					$where_clauses[] = $wpdb->prepare( 'statuses.moderation = %d', $moderation_value );
				}
			}
		}

		/* ---- feed filter ---------------------------------------------- */
		if ( $args['feed'] !== null ) {
			if ( is_array( $args['feed'] ) ) {
				$in = "'" . join( "','", array_map( 'esc_sql', $args['feed'] ) ) . "'";
				$where_clauses[] = "statuses.feed IN ({$in})";
			} else {
				$where_clauses[] = sprintf( "statuses.feed = '%s'", esc_sql( $args['feed'] ) );
			}
		}

		/* ---- like filter + flag (single derived table if IDs match) ---- */
		$liked_by_user_id      = is_numeric( $args['liked_by_user'] ) ? absint( $args['liked_by_user'] ) : null;
		$need_flag_for_viewer  = $args['with_user_like_status'] && $viewer_user_id;
		$reuse_one_table       = $need_flag_for_viewer && $liked_by_user_id === $viewer_user_id;

		if ( $liked_by_user_id !== null || $need_flag_for_viewer ) {
			// viewer ID and/or liked_by_user filter require a join
			if ( $reuse_one_table ) {
				$alias = 'ulikes';
				$join_clauses[] = $wpdb->prepare( "
					LEFT JOIN (
						SELECT DISTINCT status_id
						FROM {$wpdb->prefix}voxel_timeline_status_likes
						WHERE user_id = %d
					) AS {$alias} ON {$alias}.status_id = statuses.id", $viewer_user_id );

				if ( $liked_by_user_id !== null ) {
					$where_clauses[] = "{$alias}.status_id IS NOT NULL";
				}
				if ( $need_flag_for_viewer ) {
					$select_columns[] = "IF({$alias}.status_id IS NULL, NULL, 1) AS liked_by_user";
				}
			} else {
				if ( $liked_by_user_id !== null ) {
					$alias = 'liked_filter';
					$join_clauses[] = $wpdb->prepare( "
						INNER JOIN (
							SELECT DISTINCT status_id
							FROM {$wpdb->prefix}voxel_timeline_status_likes
							WHERE user_id = %d
						) AS {$alias} ON {$alias}.status_id = statuses.id", $liked_by_user_id );
				}
				if ( $need_flag_for_viewer ) {
					$alias = 'liked_flag';
					$join_clauses[] = $wpdb->prepare( "
						LEFT JOIN (
							SELECT DISTINCT status_id
							FROM {$wpdb->prefix}voxel_timeline_status_likes
							WHERE user_id = %d
						) AS {$alias} ON {$alias}.status_id = statuses.id", $viewer_user_id );
					$select_columns[] = "IF({$alias}.status_id IS NULL, NULL, 1) AS liked_by_user";
				}
			}
		}

		/* ---- repost flag for viewer ----------------------------------- */
		if ( $args['with_user_repost_status'] && $viewer_user_id ) {
			$alias = 'reposted_by_viewer';
			$join_clauses[] = $wpdb->prepare( "
				LEFT JOIN (
					SELECT DISTINCT repost_of AS status_id
					FROM {$wpdb->prefix}voxel_timeline
					WHERE user_id = %d AND repost_of IS NOT NULL
				) AS {$alias} ON {$alias}.status_id = statuses.id", $viewer_user_id );
			$select_columns[] = "IF({$alias}.status_id IS NULL, NULL, 1) AS reposted_by_user";
		}

		/* ---- follower filter (explicit) -------------------------------- */
		if ( $args['follower_type'] !== null && $args['follower_id'] !== null ) {
			$follower_id = absint( $args['follower_id'] );

			if ( $args['follower_type'] === 'post' ) {
				$where_clauses[] = $wpdb->prepare( '
					(
						statuses.post_id = %d
						OR EXISTS (
							SELECT 1
							FROM '.$wpdb->prefix.'voxel_followers f
							WHERE f.follower_type = "post"
							  AND f.follower_id = %d
							  AND f.status = 1
							  AND (
									(f.object_type = "user" AND f.object_id = statuses.user_id)
								 OR (f.object_type = "post" AND f.object_id = statuses.post_id)
							  )
						)
					)', $follower_id, $follower_id );
			} else {
				$where_clauses[] = $wpdb->prepare( '
					(
						statuses.user_id = %d
						OR EXISTS (
							SELECT 1
							FROM '.$wpdb->prefix.'voxel_followers f
							WHERE f.follower_type = "user"
							  AND f.follower_id = %d
							  AND f.status = 1
							  AND (
									(f.object_type = "user" AND f.object_id = statuses.user_id)
								 OR (f.object_type = "post" AND f.object_id = statuses.post_id)
							  )
						)
					)', $follower_id, $follower_id );
			}
		}

		/* ---- visibility rules (unchanged logic, no switch) ------------- */
		if ( $args['with_current_user_visibility_checks'] ) {
			$visibility_or = [];

			/* user_timeline ------------------------------------------------ */
			$user_tl_vis = \Voxel\get( 'settings.timeline.user_timeline.visibility', 'public' );

			if ( $user_tl_vis === 'public' ) {
				$visibility_or[] = '(statuses.feed = "user_timeline")';
			} elseif ( $user_tl_vis === 'logged_in' ) {
				$visibility_or[] = $viewer_user_id
					? '(statuses.feed = "user_timeline")'
					: '(statuses.feed = "user_timeline" AND 1=0)';
			} elseif ( $user_tl_vis === 'followers_only' ) {
				if ( $viewer_user_id ) {
					$visibility_or[] = $wpdb->prepare( '
						(statuses.feed = "user_timeline" AND (
							statuses.user_id = %d OR '.$exists_user_follow.'
						))', $viewer_user_id );
				} else {
					$visibility_or[] = '(statuses.feed = "user_timeline" AND 1=0)';
				}
			} elseif ( $user_tl_vis === 'customers_only' ) {
				if ( $viewer_user_id ) {
					$join_clauses[] = $wpdb->prepare( '
						LEFT JOIN '.$wpdb->prefix.'vx_orders AS uo
						ON (uo.customer_id = %d AND uo.status IN ("completed","sub_active"))', $viewer_user_id );
					$main_admin = \Voxel\get_main_admin() ? \Voxel\get_main_admin()->get_id() : -1;
					$visibility_or[] = $wpdb->prepare( '
						(statuses.feed = "user_timeline" AND (
							statuses.user_id = %d
							OR uo.vendor_id = statuses.user_id
							OR (statuses.user_id = %d AND uo.vendor_id IS NULL)
						))', $viewer_user_id, $main_admin );
				} else {
					$visibility_or[] = '(statuses.feed = "user_timeline" AND 1=0)';
				}
			} else { // private
				$visibility_or[] = $viewer_user_id
					? $wpdb->prepare( '(statuses.feed = "user_timeline" AND statuses.user_id = %d)', $viewer_user_id )
					: '(statuses.feed = "user_timeline" AND 1=0)';
			}

			/* post_* feeds ------------------------------------------------- */
			$join_clauses[] = "LEFT JOIN {$wpdb->posts} AS posts ON statuses.post_id = posts.ID";

			$feed_to_setting = [
				'post_timeline' => 'timeline',
				'post_wall'     => 'wall',
				'post_reviews'  => 'reviews',
			];

			foreach ( $feed_to_setting as $feed_key => $setting_key ) {

				/* bucket post types by visibility -------------------------- */
				$vis_buckets = [
					'public'          => [],
					'logged_in'       => [],
					'followers_only'  => [],
					'customers_only'  => [],
					'private'         => [],
				];

				foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
					$enabled = $post_type->get_setting( "timeline.{$setting_key}" );
					if ( $enabled === 'disabled' ) {
						continue;
					}

					if ( $setting_key === 'timeline' ) {
						$vis_value = $post_type->get_setting( 'timeline.visibility' );
					} elseif ( $setting_key === 'wall' ) {
						$vis_value = $post_type->get_setting( 'timeline.wall_visibility' );
					} else { // reviews
						$vis_value = $post_type->get_setting( 'timeline.review_visibility' );
					}

					if ( ! isset( $vis_buckets[ $vis_value ] ) ) {
						$vis_value = 'public';
					}
					$vis_buckets[ $vis_value ][] = $post_type->get_key();
				}

				/* build OR clauses for each visibility -------------------- */
				foreach ( $vis_buckets as $vis_key => $type_keys ) {
					if ( empty( $type_keys ) ) {
						continue;
					}

					$type_in = "'" . join( "','", array_map( 'esc_sql', $type_keys ) ) . "'";

					if ( $vis_key === 'public' ) {
						$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}))";

					} elseif ( $vis_key === 'logged_in' ) {
						if ( $viewer_user_id ) {
							$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}))";
						} else {
							$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}) AND 1=0)";
						}

					} elseif ( $vis_key === 'followers_only' ) {
						if ( $viewer_user_id ) {
							$visibility_or[] = $wpdb->prepare( '
								(statuses.feed = "'.$feed_key.'" AND posts.post_type IN ('.$type_in.') AND (
									posts.post_author = %d
									OR '.$exists_post_follow.'
								))', $viewer_user_id );
						} else {
							$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}) AND 1=0)";
						}

					} elseif ( $vis_key === 'customers_only' ) {
						if ( $viewer_user_id ) {
							$join_clauses[] = $wpdb->prepare( '
								LEFT JOIN '.$wpdb->prefix.'vx_orders AS po
								ON (po.customer_id = %d AND po.status IN ("completed","sub_active"))', $viewer_user_id );
							$join_clauses[] = 'LEFT JOIN '.$wpdb->prefix.'vx_order_items AS poi ON (poi.order_id = po.id)';
							$visibility_or[] = $wpdb->prepare( '
								(statuses.feed = "'.$feed_key.'" AND posts.post_type IN ('.$type_in.') AND (
									posts.post_author = %d
									OR poi.post_id = statuses.post_id
								))', $viewer_user_id );
						} else {
							$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}) AND 1=0)";
						}

					} else { // private
						if ( $viewer_user_id ) {
							$visibility_or[] = $wpdb->prepare( '
								(statuses.feed = "'.$feed_key.'" AND posts.post_type IN ('.$type_in.') AND posts.post_author = %d)', $viewer_user_id );
						} else {
							$visibility_or[] = "(statuses.feed = '{$feed_key}' AND posts.post_type IN ({$type_in}) AND 1=0)";
						}
					}
				}
			}

			if ( $visibility_or ) {
				$where_clauses[] = '(' . join( ' OR ', $visibility_or ) . ')';
			}
		}

		/* ---- misc filters ---------------------------------------------- */
		if ( $args['with_no_reposts'] ) {
			$where_clauses[] = 'statuses.repost_of IS NULL';
		}

		if ( $args['created_at'] && strtotime( $args['created_at'] ) ) {
			$where_clauses[] = $wpdb->prepare(
				'statuses.created_at >= %s',
				date( 'Y-m-d H:i:s', strtotime( $args['created_at'] ) )
			);
		}

		/* ---- ORDER BY --------------------------------------------------- */
		$order_dir = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		if ( $args['order_by'] === 'like_count' ) {
			$order_clauses[] = "statuses.like_count {$order_dir}";
		} elseif ( $args['order_by'] === 'reply_count' ) {
			$order_clauses[] = "statuses.reply_count {$order_dir}";
		} elseif ( $args['order_by'] === 'interaction_count' ) {
			$select_columns[] = '(statuses.like_count + statuses.reply_count) AS interaction_count';
			$order_clauses[]  = "interaction_count {$order_dir}";
		} elseif ( $args['order_by'] === 'rating' ) {
			$order_clauses[] = "statuses.review_score {$order_dir}";
		} else { // created_at
			$order_clauses[] = "statuses.created_at {$order_dir}";
		}

		/* ---- LIMIT / OFFSET -------------------------------------------- */
		$limit_sql  = $args['limit']  !== null ? 'LIMIT ' . absint( $args['limit'] )  : '';
		$offset_sql = $args['offset'] !== null ? 'OFFSET ' . absint( $args['offset'] ) : '';

		/* ---- build final SQL ------------------------------------------- */
		$sql = 'SELECT ' . join( ', ', array_unique( $select_columns ) ) . '
			FROM ' . $wpdb->prefix . 'voxel_timeline AS statuses
			' . join( ' ', array_unique( $join_clauses ) ) . '
			' . ( $where_clauses ? 'WHERE ' . join( ' AND ', $where_clauses ) : '' ) . '
			' . ( $order_clauses ? 'ORDER BY ' . join( ', ', $order_clauses ) : '' ) . '
			' . $limit_sql . ' ' . $offset_sql;

		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [ 'items' => [], 'count' => 0, '_total_count' => 0 ];
		}

		$count_results = count( $results );

		/* ---- collapse reposts (unless excluded) ------------------------ */
		$grouped = [];
		foreach ( $results as $row ) {
			if ( !$args['with_no_reposts'] && is_numeric( $row['repost_of'] ) ) {
				$grouped_key = $row['repost_of'];
			} else {
				$grouped_key = $row['id'];
			}
			if ( ! isset( $grouped[ $grouped_key ] ) ) {
				$grouped[ $grouped_key ] = $row;
			}
		}

		/* ---- last-3 likes ---------------------------------------------- */
		if ( $grouped ) {
			$ids_in = join( ',', array_map( 'intval', array_keys( $grouped ) ) );
			$last3  = $wpdb->get_results( <<<SQL
				SELECT statuses.id AS status_id,
					   JSON_ARRAYAGG( JSON_OBJECT('user_id', l.user_id, 'post_id', l.post_id) ) AS last3_liked
				FROM {$wpdb->prefix}voxel_timeline statuses
				JOIN (
					SELECT l.status_id, l.user_id, l.post_id,
						   ROW_NUMBER() OVER (PARTITION BY l.status_id ORDER BY l.id DESC) AS rn
					FROM {$wpdb->prefix}voxel_timeline_status_likes l
					WHERE l.status_id IN ({$ids_in})
				) l ON l.status_id = statuses.id AND l.rn <= 3
				GROUP BY statuses.id
			SQL, ARRAY_A );

			foreach ( $last3 as $row ) {
				$grouped[ $row['status_id'] ]['last3_liked'] = $row['last3_liked'];
			}
		}

		/* ---- annotations (friends liked / reposted) -------------------- */
		if ( $args['with_annotations'] && $viewer_user_id && $grouped ) {
			$ids_in = join( ',', array_map( 'intval', array_keys( $grouped ) ) );

			// friends who reposted
			$friends_reposted = $wpdb->get_results( $wpdb->prepare( <<<SQL
				WITH rep AS (
					SELECT tl.repost_of, tl.user_id,
						   ROW_NUMBER() OVER (PARTITION BY tl.repost_of) AS rn
					FROM {$wpdb->prefix}voxel_timeline tl
					JOIN (
						SELECT object_id
						FROM {$wpdb->prefix}voxel_followers
						WHERE follower_type = 'user' AND follower_id = %d
						  AND object_type = 'user' AND status = 1
						LIMIT 11
					) f ON f.object_id = tl.user_id
					WHERE tl.repost_of IN ({$ids_in})
				)
				SELECT repost_of, GROUP_CONCAT(user_id SEPARATOR ',') AS user_ids
				FROM rep WHERE rn <= 11 GROUP BY repost_of
			SQL, $viewer_user_id ), ARRAY_A );

			foreach ( $friends_reposted as $row ) {
				$grouped[ $row['repost_of'] ]['friends_reposted'] = $row['user_ids'];
			}

			// friends who liked
			$friends_liked = $wpdb->get_results( $wpdb->prepare( <<<SQL
				WITH lk AS (
					SELECT likes.status_id, likes.user_id,
						   ROW_NUMBER() OVER (PARTITION BY likes.status_id) AS rn
					FROM {$wpdb->prefix}voxel_timeline_status_likes likes
					JOIN (
						SELECT object_id
						FROM {$wpdb->prefix}voxel_followers
						WHERE follower_type = 'user' AND follower_id = %d
						  AND object_type = 'user' AND status = 1
						LIMIT 11
					) f ON f.object_id = likes.user_id
					WHERE likes.status_id IN ({$ids_in}) AND likes.user_id <> %d
				)
				SELECT status_id, GROUP_CONCAT(user_id SEPARATOR ',') AS user_ids
				FROM lk WHERE rn <= 11 GROUP BY status_id
			SQL, $viewer_user_id, $viewer_user_id ), ARRAY_A );

			foreach ( $friends_liked as $row ) {
				$grouped[ $row['status_id'] ]['friends_liked'] = $row['user_ids'];
			}
		}

		/* ---- to Status objects ---------------------------------------- */
		$status_items = array_map( '\\Voxel\\Timeline\\Status::get', array_values( $grouped ) );

		/* ---- total count (old semantics = after limit/offset) ---------- */
		$total_count = null;
		if ( $args['_get_total_count'] ) {
			$count_sql = '
				SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'voxel_timeline AS statuses
				' . join( ' ', array_unique( $join_clauses ) ) . '
				' . ( $where_clauses ? 'WHERE ' . join( ' AND ', $where_clauses ) : '' );
			$total_count = (int) $wpdb->get_var( $count_sql );
		}

		return [
			'items'        => $status_items,
			'count'        => $count_results,
			'_total_count' => $total_count,
		];
	}
}
