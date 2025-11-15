<?php

namespace Voxel\Product_Types\Orders\Traits;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Query_Trait {

	protected static $instances = [];

	public static function get( $id ) {
		if ( is_array( $id ) ) {
			$data = $id;
			$id = absint( $data['id'] );
			if ( ! array_key_exists( $id, static::$instances ) ) {
				static::$instances[ $id ] = new static( $data );
			}
		} elseif ( is_numeric( $id ) ) {
			$id = absint( $id );
			if ( ! array_key_exists( $id, static::$instances ) ) {
				$results = static::query( [
					'id' => $id,
					'limit' => 1,
				] );

				static::$instances[ $id ] = isset( $results[0] ) ? $results[0] : null;
			}
		} else {
			return null;
		}

		return static::$instances[ $id ];
	}

	public static function force_get( $id ) {
		unset( static::$instances[ $id ] );
		return static::get( $id );
	}

	public static function find( array $args ) {
		$args['limit'] = 1;
		$args['offset'] = null;
		$results = static::query( $args );
		return array_shift( $results );
	}

	public static function count( array $args ): int {
		$args['calculate_count'] = true;
		return static::query( $args );
	}

	public static function query( array $args ) {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'customer_id' => null,
			'vendor_id' => null,
			'party_id' => null,
			'status' => null,
			'status_not_in' => null,
			'shipping_status' => null,
			'shipping_status_not_in' => null,
			'payment_method' => null,
			'transaction_id' => null,
			'parent_id' => null,
			'offset' => null,
			'limit' => 10,
			'order_by' => 'id',
			'order' => 'desc',
			'search' => null,
			'search_customer' => null,
			'search_vendor' => null,
			'product_type' => null,
			'with_items' => true,
			'with_child_orders' => false,
			'calculate_count' => false,
		], $args );

		$where_clauses = [];
		$orderby_clauses = [];

		$join_clauses = [];
		$join_posts = false;
		$join_vendors = false;
		$join_customers = false;

		if ( ! is_null( $args['id'] ) ) {
			if ( is_numeric( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'orders.id = %d', absint( $args['id'] ) );
			} elseif ( is_array( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'orders.id IN (%s)', join( ',', array_map( 'absint', $args['id'] ) ) );
			}
		}

		if ( ! is_null( $args['customer_id'] ) ) {
			$where_clauses[] = sprintf( 'orders.customer_id = %d', absint( $args['customer_id'] ) );
		}

		if ( ! is_null( $args['vendor_id'] ) ) {
			$where_clauses[] = sprintf( 'orders.vendor_id = %d', absint( $args['vendor_id'] ) );
		}

		if ( ! is_null( $args['party_id'] ) ) {
			$where_clauses[] = sprintf(
				'( orders.customer_id = %d OR orders.vendor_id = %d )',
				absint( $args['party_id'] ),
				absint( $args['party_id'] )
			);
		}

		if ( ! is_null( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$where_clauses[] = sprintf( "orders.status IN ('%s')", join( "','", array_map( 'esc_sql', $args['status'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.status = \'%s\'', esc_sql( $args['status'] ) );
			}
		}

		if ( ! is_null( $args['status_not_in'] ) ) {
			if ( is_array( $args['status_not_in'] ) ) {
				$where_clauses[] = sprintf( "orders.status NOT IN ('%s')", join( "','", array_map( 'esc_sql', $args['status_not_in'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.status <> \'%s\'', esc_sql( $args['status_not_in'] ) );
			}
		}

		if ( ! is_null( $args['shipping_status'] ) ) {
			if ( is_array( $args['shipping_status'] ) ) {
				$where_clauses[] = sprintf( "orders.shipping_status IN ('%s')", join( "','", array_map( 'esc_sql', $args['shipping_status'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.shipping_status = \'%s\'', esc_sql( $args['shipping_status'] ) );
			}
		}

		if ( ! is_null( $args['shipping_status_not_in'] ) ) {
			if ( is_array( $args['shipping_status_not_in'] ) ) {
				$where_clauses[] = sprintf( "orders.shipping_status NOT IN ('%s')", join( "','", array_map( 'esc_sql', $args['shipping_status_not_in'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.shipping_status <> \'%s\'', esc_sql( $args['shipping_status_not_in'] ) );
			}
		}

		if ( ! is_null( $args['payment_method'] ) ) {
			if ( is_array( $args['payment_method'] ) ) {
				$where_clauses[] = sprintf( "orders.payment_method IN ('%s')",  join( "','", array_map( 'esc_sql', $args['payment_method'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.payment_method = \'%s\'', esc_sql( $args['payment_method'] ) );
			}
		}

		if ( ! is_null( $args['transaction_id'] ) ) {
			$where_clauses[] = sprintf( 'orders.transaction_id = \'%s\'', esc_sql( $args['transaction_id'] ) );
		}

		if ( ! is_null( $args['parent_id'] ) ) {
			if ( $args['parent_id'] === 0 ) {
				$where_clauses[] = 'orders.parent_id IS NULL';
			} elseif ( is_numeric( $args['parent_id'] ) ) {
				$where_clauses[] = sprintf( 'orders.parent_id = %d', absint( $args['parent_id'] ) );
			} elseif ( is_array( $args['parent_id'] ) ) {
				$where_clauses[] = sprintf( 'orders.parent_id IN (%s)', join( ',', array_map( 'absint', $args['parent_id'] ) ) );
			}
		}

		if ( ! is_null( $args['search_customer'] ) ) {
			$join_customers = true;
			$like = '%'.$wpdb->esc_like( $args['search_customer'] ).'%';
			$where_clauses[] = $wpdb->prepare( <<<SQL
				( customers.user_login = %s OR customers.user_email = %s OR customers.ID = %s OR customers.display_name LIKE %s )
			SQL, $args['search_customer'], $args['search_customer'], $args['search_customer'], $like );
		}

		if ( ! is_null( $args['search_vendor'] ) ) {
			$join_vendors = true;
			$like = '%'.$wpdb->esc_like( $args['search_vendor'] ).'%';
			$where_clauses[] = $wpdb->prepare( <<<SQL
				( vendors.user_login = %s OR vendors.user_email = %s OR vendors.ID = %s OR vendors.display_name LIKE %s )
			SQL, $args['search_vendor'], $args['search_vendor'], $args['search_vendor'], $like );
		}

		if ( ! is_null( $args['product_type'] ) ) {
			if ( is_array( $args['product_type'] ) ) {
				$where_clauses[] = sprintf( "items.product_type IN ('%s')", join( "','", array_map( 'esc_sql', $args['product_type'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'items.product_type = \'%s\'', esc_sql( $args['product_type'] ) );
			}
		}

		$where_clauses[] = sprintf( 'orders.testmode IS %s', \Voxel\is_test_mode() ? 'true' : 'false' );

		if ( ! is_null( $args['search'] ) ) {
			$join_posts = true;
			$join_vendors = true;
			$join_customers = true;
			$search_string = sanitize_text_field( $args['search'] );
			$search_string = \Voxel\prepare_keyword_search( $search_string );

			if ( ! empty( $search_string ) ) {
				$where_clauses[] = $wpdb->prepare( <<<SQL
					(
						orders.id = %s
						OR MATCH(posts.post_title) AGAINST(%s IN BOOLEAN MODE)
						OR MATCH(vendors.display_name) AGAINST(%s IN BOOLEAN MODE)
						OR MATCH(customers.display_name) AGAINST(%s IN BOOLEAN MODE)
					)
				SQL, $args['search'], $search_string, $search_string, $search_string );
			} elseif ( ! empty( $args['search'] ) && is_numeric( $args['search'] ) ) {
				$where_clauses[] = $wpdb->prepare( "orders.id = %d", $args['search'] );
			}
		}

		if ( ! is_null( $args['order_by'] ) ) {
			$order = $args['order'] === 'asc' ? 'ASC' : 'DESC';

			if ( $args['order_by'] === 'created_at' ) {
				$orderby_clauses[] = "orders.created_at {$order}";
			} else {
				$orderby_clauses[] = "orders.id {$order}";
			}
		}

		if ( $join_posts ) {
			$join_clauses[] = "LEFT JOIN {$wpdb->posts} AS posts ON items.post_id = posts.ID";
		}

		if ( $join_vendors ) {
			$join_clauses[] = "LEFT JOIN {$wpdb->users} AS vendors ON orders.vendor_id = vendors.ID";
		}

		if ( $join_customers ) {
			$join_clauses[] = "LEFT JOIN {$wpdb->users} AS customers ON orders.customer_id = customers.ID";
		}

		// generate sql string
		$joins = join( " \n ", $join_clauses );

		$wheres = '';
		if ( ! empty( $where_clauses ) ) {
			$wheres = sprintf( 'WHERE %s', join( ' AND ', $where_clauses ) );
		}

		$limit = '';
		if ( ! is_null( $args['limit'] ) ) {
			$limit = sprintf( 'LIMIT %d', absint( $args['limit'] ) );
		}

		$offset = '';
		if ( ! is_null( $args['offset'] ) ) {
			$offset = sprintf( 'OFFSET %d', absint( $args['offset'] ) );
		}

		$orderbys = '';
		if ( ! empty( $orderby_clauses ) ) {
			$orderbys = sprintf( 'ORDER BY %s', join( ", ", $orderby_clauses ) );
		}

		if ( $args['calculate_count'] ) {
			return absint( $wpdb->get_var( "
				SELECT COUNT(DISTINCT orders.id) AS total_count
				FROM {$wpdb->prefix}vx_orders AS orders
				LEFT JOIN {$wpdb->prefix}vx_order_items AS items ON orders.id = items.order_id
				LEFT JOIN {$wpdb->prefix}vx_orders AS child_orders ON orders.id = child_orders.parent_id
				{$joins}
				{$wheres}
			" ) );
		}

		$sql = "
			SELECT orders.*, GROUP_CONCAT(items.id) AS items, GROUP_CONCAT(child_orders.id) AS child_orders
				FROM {$wpdb->prefix}vx_orders AS orders
			LEFT JOIN {$wpdb->prefix}vx_order_items AS items ON orders.id = items.order_id
			LEFT JOIN {$wpdb->prefix}vx_orders AS child_orders ON orders.id = child_orders.parent_id
			{$joins}
			{$wheres}
			GROUP BY orders.id
			{$orderbys} {$limit} {$offset}
		";

		if ( ! empty( $args['__dump_sql'] ) ) {
			dump_sql( $sql );
		}

		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		if ( $args['with_items'] ) {
			$order_ids = array_column( $results, 'id' );
			$order_items = \Voxel\Order_Item::query( [
				'order_id' => ! empty( $order_ids ) ? $order_ids : [0],
				'limit' => null,
				'with_order' => false,
			] );
		}

		if ( $args['with_child_orders'] ) {
			$order_ids = array_column( $results, 'id' );
			static::query( [
				'parent_id' => ! empty( $order_ids ) ? $order_ids : [0],
				'limit' => null,
			] );
		}

		return array_map( function( $order_data ) {
			return static::get( $order_data );
		}, $results );
	}

}
