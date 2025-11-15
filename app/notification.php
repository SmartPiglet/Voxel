<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Notification {

	const ACTIONS_PER_PAGE = 10;

	private static $instances = [];

	private
		$id,
		$user_id,
		$type,
		$details,
		$seen,
		$created_at;

	private $cache = [];

	public function __construct( array $data ) {
		$this->id = absint( $data['id'] );
		$this->user_id = absint( $data['user_id'] );
		$this->type = $data['type'];
		$this->details = is_string( $data['details'] ) ? json_decode( $data['details'], ARRAY_A ) : $data['details'];
		$this->seen = !! ( $data['seen'] ?? false );
		$this->created_at = date( 'Y-m-d H:i:s', strtotime( $data['created_at'] ) );
	}

	public function get_id() {
		return $this->id;
	}

	public function get_user_id() {
		return $this->user_id;
	}

	public function get_user() {
		return \Voxel\User::get( $this->get_user_id() );
	}

	public function get_type() {
		return $this->type;
	}

	public function get_details() {
		return (array) $this->details;
	}

	public function get_created_at() {
		return $this->created_at;
	}

	public function get_time_for_display() {
		$from = strtotime( $this->created_at ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );
		if ( $diff < WEEK_IN_SECONDS ) {
			return sprintf( _x( '%s ago', 'notification created at', 'voxel' ), human_time_diff( $from, $to ) );
		}

		return \Voxel\datetime_format( $from );
	}

	public function get_destination() {
		return $this->get_details()['destination'] ?? null;
	}

	public function is_seen() {
		return $this->seen;
	}

	public function get_event() {
		if ( array_key_exists( 'event', $this->cache ) ) {
			return $this->cache['event'];
		}

		$events = \Voxel\Events\Base_Event::get_all();
		if ( ! isset( $events[ $this->get_type() ] ) ) {
			$this->cache['event'] = null;
			return $this->cache['event'];
		}

		$this->cache['event'] = clone $events[ $this->get_type() ];
		$this->cache['event']->recipient = $this->get_user();
		return $this->cache['event'];
	}

	public function get_config() {
		if ( array_key_exists( 'config', $this->cache ) ) {
			return $this->cache['config'];
		}

		$event = $this->get_event();
		if ( $event === null ) {
			$this->cache['config'] = null;
			return $this->cache['config'];
		}

		$config = $event->get_notifications()[ $this->get_destination() ] ?? null;
		if ( ! $config ) {
			$this->cache['config'] = null;
			return $this->cache['config'];
		}

		if ( is_callable( $config['inapp']['apply_details'] ?? null ) ) {
			try {
				$config['inapp']['apply_details']( $event, $this->get_details(), $this );
			} catch ( \Exception $e ) {
				// echo $e->getMessage();
				$this->cache['config'] = null;
				return $this->cache['config'];
			}
		}

		$this->cache['config'] = $config;
		return $this->cache['config'];
	}

	public function get_subject() {
		$config = $this->get_config();
		if ( ! $config ) {
			return null;
		}

		return \Voxel\render(
			$config['inapp']['subject'] ?: $config['inapp']['default_subject'],
			$this->get_event()->get_dynamic_tags()
		);
	}

	public function get_links_to() {
		$config = $this->get_config();
		if ( ! $config ) {
			return null;
		}

		if ( ! is_callable( $config['inapp']['links_to'] ?? null ) ) {
			return null;
		}

		return $config['inapp']['links_to']( $this->get_event() );
	}

	public function get_actions( $page = 1 ) {
		$config = $this->get_config();
		if ( ! $config ) {
			return [];
		}

		if ( ! is_callable( $config['inapp']['actions'] ?? null ) ) {
			return [];
		}

		$actions = $config['inapp']['actions']( $page, $this->get_event(), $this->get_details(), $this );

		foreach ( $actions as $i => $action ) {
			if ( $action['type'] === 'list-item' ) {
				if ( is_callable( $action['actions'] ) ) {
					$actions[ $i ]['actions'] = $action['actions']();
				}
			}

			$actions[$i]['_page'] = $page;
		}

		return $actions;
	}

	public function get_actions_page_count(): int {
		$config = $this->get_config();
		if ( ! $config ) {
			return 1;
		}

		if ( ! is_callable( $config['inapp']['actions_page_count'] ?? null ) ) {
			return 1;
		}

		return $config['inapp']['actions_page_count']( $this->get_event(), $this );
	}

	public function get_image_id() {
		$config = $this->get_config();
		if ( ! $config ) {
			return null;
		}

		if ( ! is_callable( $config['inapp']['image_id'] ?? null ) ) {
			return null;
		}

		return $config['inapp']['image_id']( $this->get_event() );
	}

	public function get_image_url() {
		if ( ! ( $image_id = $this->get_image_id() ) ) {
			return null;
		}

		return wp_get_attachment_image_url( $image_id );
	}

	public function is_valid() {
		return $this->get_config() !== null;
	}

	/**
	 * Get a notification based on its id.
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
				$results = static::query( [
					'id' => $id,
					'limit' => 1,
				] );
				static::$instances[ $id ] = isset( $results[0] ) ? $results[0] : null;
			}
		}

		return static::$instances[ $id ] ?? null;
	}

	public static function force_get( $id ) {
		unset( static::$instances[ $id ] );
		return static::get( $id );
	}

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
	}

	public function delete() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}voxel_notifications WHERE id = %d",
			$this->get_id()
		) );
	}

	public static function create( array $data ): \Voxel\Notification {
		global $wpdb;
		$data = array_merge( [
			'id' => null,
			'user_id' => null,
			'type' => null,
			'details' => null,
			'seen' => null,
			'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		], $data );

		$sql = static::_generate_insert_query( $data );
		$wpdb->query( $sql );
		$data['id'] = $wpdb->insert_id;

		return new \Voxel\Notification( $data );
	}

	public static function _generate_insert_query( array $data ) {
		global $wpdb;

		$escaped_data = [];
		foreach ( ['id', 'user_id', 'seen'] as $column_name ) {
			if ( isset( $data[ $column_name ] ) ) {
				$escaped_data[ $column_name ] = absint( $data[ $column_name ] );
			}
		}

		if ( isset( $data['details'] ) && is_array( $data['details'] ) ) {
			$data['details'] = wp_json_encode( $data['details'] );
		}

		foreach ( ['type', 'details', 'created_at'] as $column_name ) {
			if ( isset( $data[ $column_name ] ) ) {
				$escaped_data[ $column_name ] = sprintf( '\'%s\'', esc_sql( $data[ $column_name ] ) );
			}
		}

		$columns = join( ', ', array_map( function( $column_name ) {
			return sprintf( '`%s`', esc_sql( $column_name ) );
		}, array_keys( $escaped_data ) ) );

		$values = join( ', ', $escaped_data );

		$on_duplicate = join( ', ', array_map( function( $column_name ) {
			return sprintf( '`%s`=VALUES(`%s`)', $column_name, $column_name );
		}, array_keys( $escaped_data ) ) );

		$sql = "INSERT INTO {$wpdb->prefix}voxel_notifications ($columns) VALUES ($values)
					ON DUPLICATE KEY UPDATE $on_duplicate";

		return $sql;
	}

	public static function query( array $args ): array {
		global $wpdb;
		$sql = static::_generate_search_query( $args );

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( '\Voxel\Notification::get', $results );
	}

	public static function find( array $args ) {
		$args['limit'] = 1;
		$args['offset'] = null;
		$results = static::query( $args );
		return array_shift( $results );
	}

	public static function _generate_search_query( array $args ) {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'user_id' => null,
			'order_by' => 'created_at',
			'order' => 'desc',
			'offset' => null,
			'limit' => 10,
			'created_at' => null,
		], $args );

		$join_clauses = [];
		$where_clauses = [];
		$orderby_clauses = [];
		$select_clauses = [
			'n.*'
		];

		if ( ! is_null( $args['id'] ) ) {
			$where_clauses[] = sprintf( 'n.id = %d', absint( $args['id'] ) );
		}

		if ( ! is_null( $args['user_id'] ) ) {
			if ( $args['user_id'] < 0 ) {
				$where_clauses[] = sprintf( 'NOT(n.user_id <=> %d)', absint( $args['user_id'] ) );
			} else {
				$where_clauses[] = sprintf( 'n.user_id = %d', absint( $args['user_id'] ) );
			}
		}

		if ( ! is_null( $args['order_by'] ) ) {
			$order = $args['order'] === 'asc' ? 'ASC' : 'DESC';
			if ( $args['order_by'] === 'created_at' ) {
				$orderby_clauses[] = "n.created_at {$order}";
			}
		}

		$orderby_clauses[] = 'n.id DESC';

		if ( ! is_null( $args['created_at'] ) ) {
			$timestamp = strtotime( $args['created_at'] );
			if ( $timestamp ) {
				$where_clauses[] = $wpdb->prepare( "notifications.created_at >= %s", date( 'Y-m-d H:i:s', $timestamp ) );
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
		return <<<SQL
			SELECT {$selects} FROM {$wpdb->prefix}voxel_notifications AS n
			{$joins} {$wheres}
			{$orderbys} {$limit} {$offset}
		SQL;
	}

	public static function get_unread_count( $user_id, $since ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$since = esc_sql( date( 'Y-m-d H:i:s', strtotime( $since ) ) );

		if ( ! ( $user_id && $since ) ) {
			return 0;
		}

		return absint( $wpdb->get_var( <<<SQL
			SELECT COUNT(*) FROM {$wpdb->prefix}voxel_notifications
				WHERE `user_id` = {$user_id} AND created_at >= '{$since}'
		SQL ) );
	}
}
