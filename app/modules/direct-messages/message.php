<?php

namespace Voxel\Modules\Direct_Messages;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Message {

	private
		$id,
		$sender_type,
		$sender_id,
		$sender_deleted,
		$receiver_type,
		$receiver_id,
		$receiver_deleted,
		$content,
		$details,
		$seen,
		$created_at;

	private static $instances = [];

	public static function get( $id ) {
		if ( is_array( $id ) ) {
			$data = $id;
			$id = $data['id'];
			if ( ! array_key_exists( $id, static::$instances ) ) {
				static::$instances[ $id ] = new static( $data );
			}
		} elseif ( is_numeric( $id ) ) {
			if ( ! array_key_exists( $id, static::$instances ) ) {
				$results = static::query( [ 'id' => $id, 'limit' => 1 ] );
				static::$instances[ $id ] = isset( $results[0] ) ? $results[0] : null;
			}
		}

		return static::$instances[ $id ];
	}

	public function __construct( array $data ) {
		$this->id = absint( $data['id'] );
		$this->sender_type = $data['sender_type'];
		$this->sender_id = absint( $data['sender_id'] );
		$this->sender_deleted = !! ( $data['sender_deleted'] ?? false );
		$this->receiver_type = $data['receiver_type'];
		$this->receiver_id = absint( $data['receiver_id'] );
		$this->receiver_deleted = !! ( $data['receiver_deleted'] ?? false );
		$this->content = $data['content'] ?? null;
		$this->details = is_string( $data['details'] ) ? json_decode( $data['details'], ARRAY_A ) : $data['details'];
		$this->seen = !! ( $data['seen'] ?? false );
		$this->created_at = $data['created_at'];
	}

	public function get_id() {
		return $this->id;
	}

	public function get_sender_type() {
		return $this->sender_type;
	}

	public function get_sender_id() {
		return $this->sender_id;
	}

	public function get_sender() {
		return $this->sender_type === 'post'
			? \Voxel\Post::get( $this->sender_id )
			: \Voxel\User::get( $this->sender_id );
	}

	public function get_sender_name() {
		if ( $sender = $this->get_sender() ) {
			return $sender->get_display_name();
		}
	}

	public function get_sender_avatar() {
		if ( $sender = $this->get_sender() ) {
			return $sender->get_avatar_markup();
		}
	}

	public function get_sender_link() {
		if ( $sender = $this->get_sender() ) {
			return $sender->get_link();
		}
	}

	public function get_receiver_type() {
		return $this->receiver_type;
	}

	public function get_receiver_id() {
		return $this->receiver_id;
	}

	public function get_receiver() {
		return $this->receiver_type === 'post'
			? \Voxel\Post::get( $this->receiver_id )
			: \Voxel\User::get( $this->receiver_id );
	}

	public function get_receiver_name() {
		if ( $receiver = $this->get_receiver() ) {
			return $receiver->get_display_name();
		}
	}

	public function get_receiver_avatar() {
		if ( $receiver = $this->get_receiver() ) {
			return $receiver->get_avatar_markup();
		}
	}

	public function get_receiver_link() {
		if ( $receiver = $this->get_receiver() ) {
			return $receiver->get_link();
		}
	}

	public function get_content() {
		return $this->content;
	}

	public function get_content_for_display() {
		$content = $this->get_content();
		$content = links_add_target( make_clickable( $content ) );
		$content = wpautop( $content );
		return $content;
	}

	public function get_details() {
		return (array) $this->details;
	}

	public function is_seen() {
		return $this->seen;
	}

	public function is_sent_by_current_user() {
		if ( ! ( $sender = $this->get_sender() ) ) {
			return false;
		}

		return (
			( $this->get_sender_type() === 'post' && $sender->get_author_id() === get_current_user_id() )
			|| ( $this->get_sender_type() === 'user' && $sender->get_id() === get_current_user_id() )
		);
	}

	public function is_deleted_by_sender() {
		return $this->sender_deleted;
	}

	public function is_deleted_by_receiver() {
		return $this->receiver_deleted;
	}

	public function get_created_at() {
		return $this->created_at;
	}

	public function get_time_for_display() {
		$from = strtotime( $this->created_at ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );

		if ( date( 'Y-m-d', $from ) === date( 'Y-m-d', $to ) ) {
			return sprintf( _x( 'Today at %s', 'message sent at', 'voxel' ), \Voxel\time_format( $from ) );
		} elseif ( date( 'Y-m-d', $from ) === date( 'Y-m-d', strtotime( '-1 day', $to ) ) ) {
			return sprintf( _x( 'Yesterday at %s', 'message sent at', 'voxel' ), \Voxel\time_format( $from ) );
		} elseif ( $diff < WEEK_IN_SECONDS ) {
			return sprintf( _x( '%s at %s', 'message sent at', 'voxel' ), date_i18n( 'l', $from ), \Voxel\time_format( $from ) );
		} else {
			return \Voxel\datetime_format( $from );
		}
	}

	public function get_time_for_chat_display() {
		$from = strtotime( $this->created_at ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );
		$diff = (int) abs( $to - $from );
		if ( $diff < WEEK_IN_SECONDS ) {
			return sprintf( _x( '%s ago', 'message sent at', 'voxel' ), human_time_diff( $from, $to ) );
		}

		return \Voxel\date_format( $from );
	}

	public function get_excerpt( $is_author = false ) {
		$content = $this->get_content();
		if ( ! empty( $content ) ) {
			return $content;
		}

		$files = $this->get_details()['files'] ?? '';
		$file_ids = explode( ',', (string) $files );
		$file_ids = array_filter( array_map( 'absint', $file_ids ) );
		if ( ! empty( $file_ids ) ) {
			if ( $is_author ) {
				return count( $file_ids ) === 1
					? _x( 'You sent a file', 'messages', 'voxel' )
					: \Voxel\replace_vars( _x( 'You sent @amount files', 'messages', 'voxel' ), [
						'@amount' => count( $file_ids ),
					] );
			}

			return count( $file_ids ) === 1
				? _x( 'Sent a file', 'messages', 'voxel' )
				: \Voxel\replace_vars( _x( 'Sent @amount files', 'messages', 'voxel' ), [
					'@amount' => count( $file_ids ),
				] );
		}

		return _x( 'Sent a message', 'messages', 'voxel' );
	}

	public function update_chat() {
		global $wpdb;

		$sender_type = esc_sql( $this->get_sender_type() );
		$sender_id = absint( $this->get_sender_id() );
		$receiver_type = esc_sql( $this->get_receiver_type() );
		$receiver_id = absint( $this->get_receiver_id() );
		$message_id = absint( $this->get_id() );

		$results = $wpdb->get_results( <<<SQL
			SELECT id, p1_type, p1_id, p2_type, p2_id FROM {$wpdb->prefix}voxel_chats
			WHERE ( p1_type = '{$sender_type}' AND p1_id = {$sender_id} AND p2_type = '{$receiver_type}' AND p2_id = {$receiver_id} )
				OR ( p1_type = '{$receiver_type}' AND p1_id = {$receiver_id} AND p2_type = '{$sender_type}' AND p2_id = {$sender_id} )
		SQL );

		if ( count( $results ) ) {
			$chat = array_shift( $results );

			// only 1 chat can exist between same two parties
			if ( ! empty( $results ) ) {
				$ids = array_map( function( $result ) {
					return absint( $result->id );
				}, $results );
				$delete_ids = join( ',', array_filter( $ids ) );
				if ( ! empty( $delete_ids ) ) {
					$wpdb->query( "DELETE FROM {$wpdb->prefix}voxel_chats WHERE id IN ({$delete_ids})" );
				}
			}

			$sender_column = $chat->p1_type === $sender_type && absint( $chat->p1_id ) === $sender_id ? 'p1_last_message_id' : 'p2_last_message_id';

			// update
			$chat_id = absint( $chat->id );
			$wpdb->query( <<<SQL
				UPDATE {$wpdb->prefix}voxel_chats
					SET last_message_id = {$message_id}, {$sender_column} = {$message_id}
					WHERE id = {$chat_id}
			SQL );
		} else {
			// this is the first message between these users, create new chat
			$wpdb->query( <<<SQL
				INSERT INTO {$wpdb->prefix}voxel_chats (p1_type, p1_id, p1_last_message_id, p2_type, p2_id, p2_last_message_id, last_message_id)
					VALUES ('{$sender_type}', $sender_id, $message_id, '{$receiver_type}', $receiver_id, 0, {$message_id})
			SQL );
		}
	}

	public static function create( array $data ): static {
		global $wpdb;
		$data = array_merge( [
			'sender_type' => null,
			'sender_id' => null,
			'sender_deleted' => null,
			'receiver_type' => null,
			'receiver_id' => null,
			'receiver_deleted' => null,
			'content' => null,
			'details' => null,
			'seen' => null,
			'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
		], $data );

		if ( in_array( null, [ $data['sender_type'], $data['sender_id'], $data['receiver_type'], $data['receiver_id'] ], true ) ) {
			throw new \Exception( _x( 'Couldn\'t insert message: missing data.', 'messages', 'voxel' ) );
		}

		$escaped_data = [];
		foreach ( [ 'id', 'sender_id', 'receiver_id', 'sender_deleted', 'receiver_deleted', 'seen' ] as $column_name ) {
			if ( isset( $data[ $column_name ] ) ) {
				$escaped_data[ $column_name ] = absint( $data[ $column_name ] );
			}
		}

		if ( ! is_null( $data['details'] ) ) {
			if ( is_array( $data['details'] ) ) {
				$data['details'] = wp_json_encode( $data['details'] );
			}

			$escaped_data[ 'details' ] = sprintf( '\'%s\'', esc_sql( $data[ 'details' ] ) );
		}

		foreach ( [ 'sender_type', 'receiver_type', 'content', 'details', 'created_at'] as $column_name ) {
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

		$sql = "INSERT INTO {$wpdb->prefix}voxel_messages ($columns) VALUES ($values)
					ON DUPLICATE KEY UPDATE $on_duplicate";

		$wpdb->query( $sql );
		$data['id'] = $wpdb->insert_id;

		$message = new static( $data );

		return $message;
	}

	public function delete() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}voxel_messages WHERE id = %d",
			$this->get_id()
		) );
	}

	public static function query( array $args ): array {
		global $wpdb;
		$args = array_merge( [
			'id' => null,
			'offset' => null,
			'limit' => null,
		], $args );

		$where_clauses = [];
		$join_clauses = [];

		if ( ! is_null( $args['id'] ) ) {
			$where_clauses[] = sprintf( 'messages.id = %d', absint( $args['id'] ) );
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

		$sql = "
			SELECT messages.* FROM {$wpdb->prefix}voxel_messages AS messages
			{$joins} {$wheres} ORDER BY messages.created_at ASC {$limit} {$offset}
		";

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( '\Voxel\Modules\Direct_Messages\Message::get', $results );
	}

	public static function find( array $args ) {
		$args['limit'] = 1;
		$args['offset'] = null;
		$results = static::query( $args );
		return array_shift( $results );
	}

	public static function mock(): self {
		return new static( [
			'id' => null,
			'sender_type' => null,
			'sender_id' => null,
			'receiver_type' => null,
			'receiver_id' => null,
			'details' => null,
			'created_at' => null,
		] );
	}
}
