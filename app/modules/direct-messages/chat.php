<?php

namespace Voxel\Modules\Direct_Messages;

use \Voxel\Modules\Direct_Messages as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Chat {

	public $user_id;
	public $data;

	public $p1_latest, $p2_latest, $latest;

	public function __construct( $user_id, array $data ) {
		$this->user_id = $user_id;
		$this->data = $data;

		$message_data = [
			'p1' => [],
			'p2' => [],
			'latest' => []
		];

		foreach ( $data as $key => $value ) {
			if ( str_starts_with( $key, 'p1__' ) ) {
				$message_data['p1'][ substr( $key, 4 ) ] = $value;
			}

			if ( str_starts_with( $key, 'p2__' ) ) {
				$message_data['p2'][ substr( $key, 4 ) ] = $value;
			}

			if ( str_starts_with( $key, 'latest__' ) ) {
				$message_data['latest'][ substr( $key, 8 ) ] = $value;
			}
		}

		if ( ! empty( $message_data['p1']['id'] ) ) {
			$this->p1_latest = new Module\Message( $message_data['p1'] );
		}

		if ( ! empty( $message_data['p2']['id'] ) ) {
			$this->p2_latest = new Module\Message( $message_data['p2'] );
		}

		if ( ! empty( $message_data['latest']['id'] ) ) {
			$this->latest = new Module\Message( $message_data['latest'] );
		}
	}

	public function get_author() {
		if ( $this->latest->get_sender_type() === 'post' ) {
			$post = $this->latest->get_sender();
			return $post && $post->get_author_id() === $this->user_id ? $this->latest->get_sender() : $this->latest->get_receiver();
		}

		return $this->latest->get_sender_id() === $this->user_id ? $this->latest->get_sender() : $this->latest->get_receiver();
	}

	public function get_target() {
		if ( $this->latest->get_sender_type() === 'post' ) {
			$post = $this->latest->get_sender();
			return $post && $post->get_author_id() === $this->user_id ? $this->latest->get_receiver() : $this->latest->get_sender();
		}

		return $this->latest->get_sender_id() === $this->user_id ? $this->latest->get_receiver() : $this->latest->get_sender();
	}

	public function get_author_latest() {
		$author = $this->get_author();
		return (
			$this->p1_latest && $author
			&& $this->p1_latest->get_sender_type() === $author->get_object_type()
			&& $this->p1_latest->get_sender_id() === $author->get_id()
		) ? $this->p1_latest : $this->p2_latest;
	}

	public function get_target_latest() {
		$target = $this->get_target();
		return (
			$this->p1_latest && $target
			&& $this->p1_latest->get_sender_type() === $target->get_object_type()
			&& $this->p1_latest->get_sender_id() === $target->get_id()
		) ? $this->p1_latest : $this->p2_latest;
	}

	public function is_seen() {
		$latest = $this->get_target_latest();
		return ! $latest || $latest->is_seen();
	}

	public function is_new() {
		$last_checked = \Voxel\User::get( $this->user_id )->get_inbox_meta();
		$last_checked_time = strtotime( $last_checked['since'] );
		$latest = $this->get_target_latest();
		return $latest && strtotime( $latest->get_created_at() ) > $last_checked_time;
	}

	public function get_excerpt() {
		$author_latest = $this->get_author_latest();
		$is_author = $author_latest && $author_latest->get_id() === $this->latest->get_id();
		return $this->latest->get_excerpt( $is_author );
	}

	public function get_key() {
		$author = $this->get_author();
		$target = $this->get_target();
		if ( $author && $target ) {
			return join( '-', [ $author->get_object_type(), $author->get_id(), $target->get_object_type(), $target->get_id() ] );
		}
	}

	public function get_link() {
		$inbox = get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/');
		$author = $this->get_author();
		$target = $this->get_target();
		return add_query_arg( 'chat', join( '', [
			$author->get_object_type() === 'post' ? $author->get_id() : '',
			$target->get_object_type() === 'post' ? 'p' : 'u',
			$target->get_id()
		] ), $inbox );
	}

	public static function get_inbox( $user_id, $limit = 10, $offset = 0 ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$offset = absint( $offset );
		$limit = absint( $limit );

		$message_columns = [ 'id', 'sender_type', 'sender_id', 'sender_deleted', 'receiver_type', 'receiver_id', 'receiver_deleted', 'content', 'details', 'seen', 'created_at' ];
		$select = [];
		foreach ( ['p1', 'p2', 'latest'] as $join_key ) {
			foreach ( $message_columns as $column_key ) {
				$select[] = sprintf( '%s.%s AS %s__%s', $join_key, $column_key, $join_key, $column_key );
			}
		}

		$select = join( ', ', $select );

		$sql = <<<SQL
			SELECT chats.*, {$select} FROM (
				(
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					WHERE chats.p1_type = 'user' AND chats.p1_id = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
					ORDER BY chats.last_message_id DESC
					LIMIT {$limit} OFFSET {$offset}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					WHERE chats.p2_type = 'user' AND chats.p2_id = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
					ORDER BY chats.last_message_id DESC
					LIMIT {$limit} OFFSET {$offset}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
						LEFT JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
					WHERE chats.p1_type = 'post' AND p1_post.post_author = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
					ORDER BY chats.last_message_id DESC
					LIMIT {$limit} OFFSET {$offset}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
						LEFT JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
					WHERE chats.p2_type = 'post' AND p2_post.post_author = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
					ORDER BY chats.last_message_id DESC
					LIMIT {$limit} OFFSET {$offset}
				)
			) AS chats
			LEFT JOIN {$wpdb->prefix}voxel_messages as p1 ON ( p1.id = chats.p1_last_message_id )
			LEFT JOIN {$wpdb->prefix}voxel_messages as p2 ON ( p2.id = chats.p2_last_message_id )
			INNER JOIN {$wpdb->prefix}voxel_messages as latest ON ( latest.id = chats.last_message_id )
			ORDER BY chats.last_message_id DESC
			LIMIT {$limit}
		SQL;

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		// @todo: prime post and user cache from $results
		$chats = [];
		foreach ( $results as $result ) {
			$chats[] = new static( $user_id, $result );
		}

		return $chats;
	}

	public static function search_inbox( $user_id, $search_string, $limit = 10, $offset = 0 ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$offset = absint( $offset );
		$limit = absint( $limit );

		$message_columns = [ 'id', 'sender_type', 'sender_id', 'sender_deleted', 'receiver_type', 'receiver_id', 'receiver_deleted', 'content', 'details', 'seen', 'created_at' ];
		$select = [];
		foreach ( ['p1', 'p2', 'latest'] as $join_key ) {
			foreach ( $message_columns as $column_key ) {
				$select[] = sprintf( '%s.%s AS %s__%s', $join_key, $column_key, $join_key, $column_key );
			}
		}

		$select = join( ', ', $select );

		$search_string = sanitize_text_field( $search_string );
		$search_string = \Voxel\prepare_keyword_search( $search_string );
		if ( empty( $search_string ) ) {
			return [];
		}

		$search_string = esc_sql( $search_string );

		$sql = <<<SQL
			SELECT chats.*, {$select} FROM (
				(
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					LEFT JOIN {$wpdb->users} AS p2_user ON ( chats.p2_type = 'user' AND chats.p2_id = p2_user.ID )
					WHERE
						chats.p1_type = 'user' AND chats.p1_id = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
						AND MATCH(p2_user.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					LEFT JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
					WHERE
						chats.p1_type = 'user' AND chats.p1_id = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
						AND MATCH(p2_post.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					LEFT JOIN {$wpdb->users} AS p1_user ON ( chats.p1_type = 'user' AND chats.p1_id = p1_user.ID )
					WHERE
						chats.p2_type = 'user' AND chats.p2_id = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
						AND MATCH(p1_user.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					LEFT JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
					WHERE
						chats.p2_type = 'user' AND chats.p2_id = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
						AND MATCH(p1_post.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					INNER JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
					INNER JOIN {$wpdb->users} AS p2_user ON ( chats.p2_type = 'user' AND chats.p2_id = p2_user.ID )
					WHERE
						chats.p1_type = 'post' AND p1_post.post_author = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
						AND MATCH(p2_user.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					INNER JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
					INNER JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
					WHERE
						chats.p1_type = 'post' AND p1_post.post_author = {$user_id} AND chats.p1_cleared_below < chats.last_message_id
						AND MATCH(p2_post.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					INNER JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
					INNER JOIN {$wpdb->users} AS p1_user ON ( chats.p1_type = 'user' AND chats.p1_id = p1_user.ID )
					WHERE
						chats.p2_type = 'post' AND p2_post.post_author = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
						AND MATCH(p1_user.display_name) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				) UNION (
					SELECT chats.*
					FROM {$wpdb->prefix}voxel_chats AS chats
					INNER JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
					INNER JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
					WHERE
						chats.p2_type = 'post' AND p2_post.post_author = {$user_id} AND chats.p2_cleared_below < chats.last_message_id
						AND MATCH(p1_post.post_title) AGAINST('{$search_string}' IN BOOLEAN MODE)
					LIMIT {$limit}
				)
			) AS chats
			LEFT JOIN {$wpdb->prefix}voxel_messages as p1 ON ( p1.id = chats.p1_last_message_id )
			LEFT JOIN {$wpdb->prefix}voxel_messages as p2 ON ( p2.id = chats.p2_last_message_id )
			INNER JOIN {$wpdb->prefix}voxel_messages as latest ON ( latest.id = chats.last_message_id )
			LIMIT {$limit}
		SQL;

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		// @todo: prime post and user cache from $results
		$chats = [];
		foreach ( $results as $result ) {
			$chats[] = new static( $user_id, $result );
		}

		return $chats;
	}

	public static function mark_as_seen( $party1, $party2 ) {
		global $wpdb;

		$p1_type = $party1 instanceof \Voxel\Post ? 'post' : 'user';
		$p2_type = $party2 instanceof \Voxel\Post ? 'post' : 'user';
		$p1_id = absint( $party1->get_id() );
		$p2_id = absint( $party2->get_id() );

		$sql = <<<SQL
			UPDATE {$wpdb->prefix}voxel_messages AS messages
			SET seen = 1
			WHERE sender_type = '{$p2_type}'
				AND sender_id = {$p2_id}
				AND receiver_type = '{$p1_type}'
				AND receiver_id = {$p1_id}
			ORDER BY id DESC
			LIMIT 1
		SQL;

		// dump_sql( $sql );die;
		$wpdb->query( $sql );
	}

	public static function load_messages( $party1, $party2, $cursor = null, $limit = 10 ) {
		global $wpdb;

		$p1_type = $party1 instanceof \Voxel\Post ? 'post' : 'user';
		$p2_type = $party2 instanceof \Voxel\Post ? 'post' : 'user';
		$p1_id = absint( $party1->get_id() );
		$p2_id = absint( $party2->get_id() );

		$limit = absint( $limit );

		$pagination = '';
		if ( is_numeric( $cursor ) ) {
			$cursor = absint( $cursor );
			$pagination = 'AND id < '.$cursor;
		}

		$cleared_below = '';
		$chat = static::get_chat( $party1, $party2 );
		if ( $chat ) {
			$clear_cursor = absint( $chat->party1_side === 'p1' ? $chat->p1_cleared_below : $chat->p2_cleared_below );
			if ( $clear_cursor > 0 ) {
				$cleared_below = 'AND id > '.$clear_cursor;
			}
		}

		$sql = <<<SQL
			SELECT m.* FROM (
				(
					SELECT * FROM {$wpdb->prefix}voxel_messages
					WHERE
						sender_type = '{$p1_type}'
						AND sender_id = {$p1_id}
						AND receiver_type = '{$p2_type}'
						AND receiver_id = {$p2_id}
						{$pagination} {$cleared_below}
					ORDER BY id DESC
					LIMIT {$limit}
				) UNION (
					SELECT * FROM {$wpdb->prefix}voxel_messages
					WHERE
						sender_type = '{$p2_type}'
						AND sender_id = {$p2_id}
						AND receiver_type = '{$p1_type}'
						AND receiver_id = {$p1_id}
						{$pagination} {$cleared_below}
					ORDER BY id DESC
					LIMIT {$limit}
				)
			) AS m
			ORDER BY id DESC
			LIMIT {$limit}
		SQL;

		// dump_sql( $sql );die;
		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map( '\Voxel\Modules\Direct_Messages\Message::get', $results );
	}

	public static function clear_conversation( $party1, $party2 ) {
		if ( $chat = static::get_chat( $party1, $party2 ) ) {
			global $wpdb;
			$chat_id = absint( $chat->id );
			$p1_column = $chat->party1_side === 'p1' ? 'p1_cleared_below' : 'p2_cleared_below';
			$wpdb->query( "UPDATE {$wpdb->prefix}voxel_chats SET {$p1_column} = last_message_id WHERE id = {$chat_id}" );
		}
	}

	public static function get_chat( $party1, $party2 ) {
		global $wpdb;

		$p1_type = esc_sql( $party1->get_object_type() );
		$p1_id = absint( $party1->get_id() );
		$p2_type = esc_sql( $party2->get_object_type() );
		$p2_id = absint( $party2->get_id() );

		$results = $wpdb->get_results( <<<SQL
			SELECT chats.* FROM ( (
				SELECT * FROM {$wpdb->prefix}voxel_chats
				WHERE p1_type = '{$p1_type}' AND p1_id = {$p1_id} AND p2_type = '{$p2_type}' AND p2_id = {$p2_id}
			) UNION (
				SELECT * FROM {$wpdb->prefix}voxel_chats
				WHERE p1_type = '{$p2_type}' AND p1_id = {$p2_id} AND p2_type = '{$p1_type}' AND p2_id = {$p1_id}
			) ) AS chats
		SQL );

		if ( ! count( $results ) ) {
			return null;
		}

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

		$chat->party1_side = $chat->p1_type === $p1_type && absint( $chat->p1_id ) === $p1_id ? 'p1' : 'p2';
		return $chat;
	}
}
