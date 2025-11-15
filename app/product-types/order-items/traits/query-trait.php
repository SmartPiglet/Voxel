<?php

namespace Voxel\Product_Types\Order_Items\Traits;

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
				$data['details'] = (array) json_decode( (string) $data['details'], true );
				$product_mode = $data['details']['type'] ?? null;

				if ( $product_mode === 'regular' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Regular( $data );
				} elseif ( $product_mode === 'variable' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Variable( $data );
				} elseif ( $product_mode === 'booking' ) {
					static::$instances[ $id ] = new \Voxel\Product_Types\Order_Items\Order_Item_Booking( $data );
				} else {
					return null;
				}
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

	public static function query( array $args ) {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'order_id' => null,
			'customer_id' => null,
			'vendor_id' => null,
			'party_id' => null,
			'status' => null,
			'product_type' => null,
			'field_key' => null,
			'offset' => null,
			'limit' => 10,
			'with_order' => true,
		], $args );

		$where_clauses = [];
		$orderby_clauses = [];
		$join_clauses = [];

		if ( ! is_null( $args['id'] ) ) {
			if ( is_numeric( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'items.id = %d', absint( $args['id'] ) );
			} elseif ( is_array( $args['id'] ) ) {
				$where_clauses[] = sprintf( 'items.id IN (%s)', join( ',', array_map( 'absint', $args['id'] ) ) );
			}
		}

		if ( ! is_null( $args['order_id'] ) ) {
			if ( is_numeric( $args['order_id'] ) ) {
				$where_clauses[] = sprintf( 'items.order_id = %d', absint( $args['order_id'] ) );
			} elseif ( is_array( $args['order_id'] ) ) {
				$where_clauses[] = sprintf( 'items.order_id IN (%s)', join( ',', array_map( 'absint', $args['order_id'] ) ) );
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

		if ( ! is_null( $args['product_type'] ) ) {
			if ( is_array( $args['product_type'] ) ) {
				$where_clauses[] = sprintf( "items.product_type IN ('%s')", join( "','", array_map( 'esc_sql', $args['product_type'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'items.product_type = \'%s\'', esc_sql( $args['product_type'] ) );
			}
		}

		if ( ! is_null( $args['field_key'] ) ) {
			if ( is_array( $args['field_key'] ) ) {
				$where_clauses[] = sprintf( "items.field_key IN ('%s')", join( "','", array_map( 'esc_sql', $args['field_key'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'items.field_key = \'%s\'', esc_sql( $args['field_key'] ) );
			}
		}

		if ( ! is_null( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$where_clauses[] = sprintf( "orders.status IN ('%s')", join( "','", array_map( 'esc_sql', $args['status'] ) ) );
			} else {
				$where_clauses[] = sprintf( 'orders.status = \'%s\'', esc_sql( $args['status'] ) );
			}
		}

		$join_clauses[] = "LEFT JOIN {$wpdb->prefix}vx_orders AS orders ON items.order_id = orders.id";
		$where_clauses[] = sprintf( 'orders.testmode IS %s', \Voxel\is_test_mode() ? 'true' : 'false' );

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

		$sql = $wpdb->remove_placeholder_escape( "
			SELECT items.* FROM {$wpdb->prefix}vx_order_items AS items
			{$joins}
			{$wheres} {$orderbys}
			ORDER BY items.id DESC
			{$limit} {$offset}
		" );

		if ( ! empty( $args['__dump_sql'] ) ) {
			dump_sql( $sql );
		}

		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		if ( $args['with_order'] ) {
			$order_ids = array_column( $results, 'order_id' );
			if ( ! empty( $order_ids ) ) {
				$orders = \Voxel\Order::query( [
					'id' => $order_ids,
					'limit' => null,
					'with_items' => false,
				] );
			}
		}

		return array_filter( array_map( function( $item_data ) {
			return static::get( $item_data );
		}, $results ) );
	}
}
