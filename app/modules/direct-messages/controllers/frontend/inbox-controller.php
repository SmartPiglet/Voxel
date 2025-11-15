<?php

namespace Voxel\Modules\Direct_Messages\Controllers\Frontend;

use \Voxel\Modules\Direct_Messages as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Inbox_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_inbox.list_chats', '@list_chats' );
		$this->on( 'voxel_ajax_inbox.search_chats', '@search_chats' );
		$this->on( 'voxel_ajax_inbox.load_chat', '@load_chat' );
		$this->on( 'voxel_ajax_inbox.send_message', '@send_message' );
		$this->on( 'voxel_ajax_inbox.block_chat', '@block_chat' );
		$this->on( 'voxel_ajax_inbox.clear_conversation', '@clear_conversation' );
		$this->on( 'voxel_ajax_inbox.delete_message', '@delete_message' );
	}

	protected function list_chats() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			$page = absint( $_REQUEST['pg'] ?? 1 );
			$per_page = 10;
			$last_checked = \Voxel\current_user()->get_inbox_meta();
			$last_checked_time = strtotime( $last_checked['since'] );

			$default_chat = $this->_get_default_chat();
			$default_chat_loaded = false;

			$chats = Module\Chat::get_inbox( get_current_user_id(), $per_page + 1, ( $page - 1 ) * $per_page );

			$has_more = count( $chats ) > $per_page;
			if ( $has_more ) {
				array_pop( $chats );
			}

			$list = [];
			foreach ( $chats as $chat ) {
				$author = $chat->get_author();
				$target = $chat->get_target();
				if ( ! ( $author && $target ) ) {
					continue;
				}

				if (
					$default_chat && $default_chat['author']['type'] === $author->get_object_type()
					&& $default_chat['target']['type'] === $target->get_object_type()
					&& $default_chat['author']['id'] === $author->get_id()
					&& $default_chat['target']['id'] === $target->get_id()
				) {
					$list[] = $default_chat;
					$default_chat_loaded = true;
				} else {
					$config = [
						'key' => $chat->get_key(),
						'author' => [
							'type' => $author->get_object_type(),
							'id' => $author->get_id(),
							'name' => $author->get_display_name(),
							'link' => $author->get_link(),
							'avatar' => $author->get_avatar_markup(),
						],
						'target' => [
							'type' => $target->get_object_type(),
							'id' => $target->get_id(),
							'name' => $target->get_display_name(),
							'link' => $target->get_link(),
							'avatar' => $target->get_avatar_markup(),
						],
						'time' => $chat->latest->get_time_for_chat_display(),
						'seen' => $chat->is_seen(),
						'is_new' => $chat->is_new(),
						'link' => $chat->get_link(),
						'excerpt' => $chat->get_excerpt(),
						'messages' => [
							'list' => null,
							'hasMore' => false,
							'loading' => true,
							'loadingMore' => false,
						],
						'state' => [
							'content' => '',
							'files' => '',
						],
						'autoload' => false,
						'last_id' => $chat->latest->get_id(),
						'follow_status' => [
							'author' => null,
							'target' => null,
						],
					];

					$list[] = $config;
				}
			}

			// if the default request chat is not present in the $list above, it means
				// a) the chat is in the second page or lower
				// b) the chat does not exist at all (no messages exchanged yet)
			if ( $default_chat && ! $default_chat_loaded ) {
				$default_chat_config = $default_chat;
			}

			if ( $page === 1 ) {
				\Voxel\current_user()->update_inbox_meta( [
					'unread' => false,
					'since' => date( 'Y-m-d H:i:s', time() ),
				] );
				\Voxel\current_user()->set_inbox_activity(false);
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'list' => $list,
				'default_chat' => $default_chat_config ?? null,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function search_chats() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			$search_string = trim( sanitize_text_field( $_GET['search'] ?? '' ) );
			if ( empty( $search_string ) || mb_strlen( $search_string ) <= 2 ) {
				throw new \Exception( _x( 'No search term provided.', 'messages', 'voxel' ) );
			}

			$per_page = 10;
			$chats = Module\Chat::search_inbox( get_current_user_id(), $search_string, $per_page );

			$list = [];
			foreach ( $chats as $chat ) {
				$author = $chat->get_author();
				$target = $chat->get_target();
				if ( ! ( $author && $target ) ) {
					continue;
				}

				$list[] = [
					'key' => $chat->get_key(),
					'author' => [
						'type' => $author->get_object_type(),
						'id' => $author->get_id(),
						'name' => $author->get_display_name(),
						'link' => $author->get_link(),
						'avatar' => $author->get_avatar_markup(),
					],
					'target' => [
						'type' => $target->get_object_type(),
						'id' => $target->get_id(),
						'name' => $target->get_display_name(),
						'link' => $target->get_link(),
						'avatar' => $target->get_avatar_markup(),
					],
					'time' => $chat->latest->get_time_for_display(),
					'seen' => $chat->is_seen(),
					'is_new' => $chat->is_new(),
					'excerpt' => $chat->get_excerpt(),
					'messages' => [
						'list' => null,
						'hasMore' => false,
						'loading' => true,
						'loadingMore' => false,
					],
					'state' => [
						'content' => '',
						'files' => '',
					],
					'autoload' => false,
					'last_id' => $chat->latest->get_id(),
					'follow_status' => [
						'author' => null,
						'target' => null,
					],
				];
			}

			return wp_send_json( [
				'success' => true,
				'list' => $list,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function load_chat() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );

			$cursor = is_numeric( $_REQUEST['cursor'] ?? null ) ? absint( $_REQUEST['cursor'] ) : null;
			$per_page = 15;

			$author_type = \Voxel\from_list( $_REQUEST['author_type'] ?? null, [ 'user', 'post' ] );
			$author_id = absint( $_REQUEST['author_id'] ?? null );

			$target_type = \Voxel\from_list( $_REQUEST['target_type'] ?? null, [ 'user', 'post' ] );
			$target_id = absint( $_REQUEST['target_id'] ?? null );

			if ( ! ( $author_type && $author_id && $target_type && $target_id ) ) {
				throw new \Exception( _x( 'Chat not found.', 'messages', 'voxel' ) );
			}

			$author = $author_type === 'post' ? \Voxel\Post::get( $author_id ) : \Voxel\User::get( $author_id );
			$target = $target_type === 'post' ? \Voxel\Post::get( $target_id ) : \Voxel\User::get( $target_id );
			if ( ! ( $author && $target ) ) {
				throw new \Exception( _x( 'Chat not found.', 'messages', 'voxel' ) );
			}

			if ( $author_type === 'user' && $author->get_id() !== get_current_user_id() ) {
				throw new \Exception( _x( 'Chat not available: user not found', 'messages', 'voxel' ) );
			}

			if ( $author_type === 'post' && $author->get_author_id() !== get_current_user_id() ) {
				throw new \Exception( _x( 'Chat not available: post not found', 'messages', 'voxel' ) );
			}

			$messages = Module\Chat::load_messages( $author, $target, $cursor, $per_page + 1 );

			$has_more = count( $messages ) > $per_page;
			if ( $has_more ) {
				array_pop( $messages );
			}

			$list = [];
			$latest_received_message = false;
			foreach ( $messages as $message ) {
				$sent_by = $message->get_sender_id() === $author->get_id() ? 'author' : 'target';
				$list[] = [
					'id' => $message->get_id(),
					'sent_by' => $sent_by,
					'time' => $message->get_time_for_display(),
					'seen' => $message->is_seen(),
					'has_content' => ! empty( $message->get_content() ),
					'content' => $message->get_content_for_display(),
					'excerpt' => $message->get_excerpt( $sent_by === 'author' ),
					'files' => (new Module\Fields\Attachments_Field)->prepare_for_display( $message->get_details()['files'] ?? '' ),
					'is_deleted' => $message->is_deleted_by_sender(),
					'is_hidden' => $sent_by === 'target' ? $message->is_deleted_by_receiver() : false,
				];

				if ( ! $latest_received_message && $message->get_sender_id() !== $author->get_id()  ) {
					$latest_received_message = $message;
				}
			}

			// signal seen to message author
			if ( $latest_received_message && ! $latest_received_message->is_seen() ) {
				if ( \Voxel\get( 'settings.messages.enable_seen', true ) ) {
					$latest_received_message->get_sender()->set_inbox_activity(true);
				}
				Module\Chat::mark_as_seen( $author, $target );
			}

			return wp_send_json( [
				'success' => true,
				'has_more' => $has_more,
				'list' => $list,
				'follow_status' => [
					'author' => $author->get_follow_status( $target_type, $target->get_id() ),
					'target' => $target->get_follow_status( $author_type, $author->get_id() ),
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function send_message() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			// determine sender
			if ( ( $_REQUEST['sender_type'] ?? null ) === 'post' ) {
				$sender_post = \Voxel\Post::get( $_REQUEST['sender_id'] ?? null );
				if ( ! ( $sender_post && $sender_post->get_author_id() === get_current_user_id() && $sender_post->can_send_messages() ) ) {
					throw new \Exception( _x( 'You cannot send messages as this post.', 'messages', 'voxel' ) );
				}

				$sender_type = 'post';
				$sender_id = $sender_post->get_id();
				$sender = $sender_post;
			} else {
				$sender_type = 'user';
				$sender_id = get_current_user_id();
				$sender = \Voxel\current_user();
			}

			// determine receiver
			if ( ( $_REQUEST['receiver_type'] ?? null ) === 'post' ) {
				$receiver_post = \Voxel\Post::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! ( $receiver_post && $receiver_post->can_send_messages() ) ) {
					throw new \Exception( _x( 'You cannot message this post.', 'messages', 'voxel' ) );
				}

				$receiver_type = 'post';
				$receiver_id = $receiver_post->get_id();
				$receiver = $receiver_post;
			} else {
				$receiver_user = \Voxel\User::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! $receiver_user ) {
					throw new \Exception( _x( 'You cannot message this user.', 'messages', 'voxel' ) );
				}

				$receiver_type = 'user';
				$receiver_id = $receiver_user->get_id();
				$receiver = $receiver_user;
			}

			// check if users have blocked each other
			if ( $sender->get_follow_status( $receiver_type, $receiver->get_id() ) === -1 || $receiver->get_follow_status( $sender_type, $sender->get_id() ) === -1 ) {
				throw new \Exception( _x( 'You cannot message this user.', 'messages', 'voxel' ) );
			}

			$fields = json_decode( stripslashes( $_REQUEST['fields'] ?? '' ), true );

			try {
				$content_field = new Module\Fields\Content_Field;
				$content = $content_field->sanitize( $fields['content'] ?? '' );
				$content_field->validate( $content );

				if ( \Voxel\get( 'settings.messages.files.enabled', true ) ) {
					$attachments_field = new Module\Fields\Attachments_Field( [
						'upload_dir' => sprintf(
							'voxel-messages/%s/%s',
							substr( md5( wp_json_encode( [ $sender_type, $sender_id, $receiver_type, $receiver_id ] ) ), 0, 10 ),
							strtolower( \Voxel\random_string(4) )
						),
						'skip_subdir' => true,
					] );

					$files = $attachments_field->sanitize( $fields['files'] ?? [] );
					$attachments_field->validate( $files );
					$file_ids = $attachments_field->prepare_for_storage( $files );
				}

				if ( empty( $content ) && empty( $file_ids ) ) {
					throw new \Exception( _x( 'Message cannot be empty.', 'messages', 'voxel' ) );
				}
			} catch ( \Exception $e ) {
				return wp_send_json( [
					'success' => false,
					'message' => $e->getMessage(),
					'error_type' => 'validation',
				] );
			}

			$details = [];
			if ( ! empty( $file_ids ) ) {
				$details['files'] = $file_ids;
			}

			$message = Module\Message::create( [
				'sender_type' => $sender_type,
				'sender_id' => $sender_id,
				'sender_deleted' => 0,
				'receiver_type' => $receiver_type,
				'receiver_id' => $receiver_id,
				'receiver_deleted' => 0,
				'content' => $content,
				'details' => ! empty( $details ) ? $details : null,
				'seen' => 0,
			] );

			$receiver->set_inbox_activity(true);
			$receiver_author = $receiver->get_object_type() === 'post' ? $receiver->get_author() : $receiver;
			if ( $receiver_author ) {
				$receiver_author->update_inbox_meta( [
					'unread' => true,
				] );
			}

			$message->update_chat();

			global $wpdb;
			$has_recently_received_message = !! $wpdb->get_var( $wpdb->prepare( <<<SQL
				SELECT id FROM {$wpdb->prefix}voxel_messages
				WHERE
					sender_type = %s AND sender_id = %d
					AND receiver_type = %s AND receiver_id = %d
					AND created_at > %s
					AND id != %d
				LIMIT 1
			SQL, $sender_type, $sender_id, $receiver_type, $receiver_id, date( 'Y-m-d H:i:s', time() - ( 15 * MINUTE_IN_SECONDS ) ), $message->get_id() ) );

			if ( ! $has_recently_received_message ) {
				( new \Voxel\Events\Direct_Messages\User_Received_Message_Event )->dispatch( $message->get_id() );
			}

			( new \Voxel\Events\Direct_Messages\User_Received_Message_Unthrottled_Event )->dispatch( $message->get_id() );

			return wp_send_json( [
				'success' => true,
				'message' => [
					'id' => $message->get_id(),
					'sent_by' => 'author',
					'time' => $message->get_time_for_display(),
					'chat_time' => $message->get_time_for_chat_display(),
					'seen' => $message->is_seen(),
					'has_content' => ! empty( $message->get_content() ),
					'content' => $message->get_content_for_display(),
					'excerpt' => $message->get_excerpt(true),
					'files' => (new Module\Fields\Attachments_Field)->prepare_for_display( $message->get_details()['files'] ?? '' ),
					'is_deleted' => false,
					'is_hidden' => false,
				],
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function _get_default_chat() {
		$str = $_REQUEST['load'] ?? null;
		if ( ! is_string( $str ) || empty( $str ) ) {
			return null;
		}

		preg_match( '/(?P<sender_id>\d+)?(?P<receiver_type>p|u)(?P<receiver_id>\d+)/', $str, $matches );

		if ( is_numeric( $matches['sender_id'] ?? null ) ) {
			$post = \Voxel\Post::get( $matches['sender_id'] );
			if ( $post && $post->get_author_id() === get_current_user_id() && $post->can_send_messages() ) {
				$author_type = 'post';
				$author = $post;
			} else {
				return null;
			}
		} else {
			$author_type = 'user';
			$author = \Voxel\current_user();
		}

		if ( ! is_numeric( $matches['receiver_id'] ?? null ) ) {
			return null;
		}

		if ( ( $matches['receiver_type'] ?? null ) === 'u' ) {
			$user = \Voxel\User::get( $matches['receiver_id'] );
			if ( $user ) {
				$target_type = 'user';
				$target = $user;
			} else {
				return null;
			}
		}

		if ( ( $matches['receiver_type'] ?? null ) === 'p' ) {
			$post = \Voxel\Post::get( $matches['receiver_id'] );
			if ( $post && $post->can_send_messages() ) {
				$target_type = 'post';
				$target = $post;
			} else {
				return null;
			}
		}

		if ( ! ( $author && $target ) ) {
			return null;
		}

		$per_page = 15;
		$messages = Module\Chat::load_messages( $author, $target, null, $per_page + 1 );

		$has_more = count( $messages ) > $per_page;
		if ( $has_more ) {
			array_pop( $messages );
		}

		$list = [];
		$latest_received_message = false;
		foreach ( $messages as $message ) {
			$sent_by = $message->get_sender_id() === $author->get_id() ? 'author' : 'target';
			$list[] = [
				'id' => $message->get_id(),
				'sent_by' => $sent_by,
				'time' => $message->get_time_for_display(),
				'seen' => $message->is_seen(),
				'has_content' => ! empty( $message->get_content() ),
				'content' => $message->get_content_for_display(),
				'excerpt' => $message->get_excerpt( $sent_by === 'author' ),
				'files' => (new Module\Fields\Attachments_Field)->prepare_for_display( $message->get_details()['files'] ?? '' ),
				'is_deleted' => $message->is_deleted_by_sender(),
				'is_hidden' => $sent_by === 'target' ? $message->is_deleted_by_receiver() : false,
			];

			if ( ! $latest_received_message && $message->get_sender_id() !== $author->get_id()  ) {
				$latest_received_message = $message;
			}
		}

		// signal seen to message author
		if ( $latest_received_message && ! $latest_received_message->is_seen() ) {
			if ( \Voxel\get( 'settings.messages.enable_seen', true ) ) {
				$latest_received_message->get_sender()->set_inbox_activity(true);
			}
			Module\Chat::mark_as_seen( $author, $target );
		}

		$latest_message = $messages[0] ?? null;

		return [
			'key' => join( '-', [ $author_type, $author->get_id(), $target_type, $target->get_id() ] ),
			'author' => [
				'type' => $author->get_object_type(),
				'id' => $author->get_id(),
				'name' => $author->get_display_name(),
				'link' => $author->get_link(),
				'avatar' => $author->get_avatar_markup(),
			],
			'target' => [
				'type' => $target->get_object_type(),
				'id' => $target->get_id(),
				'name' => $target->get_display_name(),
				'link' => $target->get_link(),
				'avatar' => $target->get_avatar_markup(),
			],
			'time' => $latest_message ? $latest_message->get_time_for_chat_display() : null,
			'seen' => true,
			'is_new' => false,
			'excerpt' => $latest_message ? $latest_message->get_excerpt( $latest_message->is_sent_by_current_user() ) : '',
			'messages' => [
				'list' => $list,
				'hasMore' => $has_more,
				'loading' => false,
				'loadingMore' => false,
			],
			'state' => [
				'content' => '',
				'files' => '',
			],
			'autoload' => true,
			'last_id' => $latest_message ? $latest_message->get_id() : 0,
			'follow_status' => [
				'author' => $author->get_follow_status( $target_type, $target->get_id() ),
				'target' => $target->get_follow_status( $author_type, $author->get_id() ),
			],
		];
	}

	protected function block_chat() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			// determine sender
			if ( ( $_REQUEST['sender_type'] ?? null ) === 'post' ) {
				$sender_post = \Voxel\Post::get( $_REQUEST['sender_id'] ?? null );
				if ( ! ( $sender_post && $sender_post->get_author_id() === get_current_user_id() && $sender_post->can_send_messages() ) ) {
					throw new \Exception( _x( 'You cannot block this post.', 'messages', 'voxel' ) );
				}

				$sender_type = 'post';
				$sender_id = $sender_post->get_id();
				$sender = $sender_post;
			} else {
				$sender_type = 'user';
				$sender_id = get_current_user_id();
				$sender = \Voxel\current_user();
			}

			// determine receiver
			if ( ( $_REQUEST['receiver_type'] ?? null ) === 'post' ) {
				$receiver_post = \Voxel\Post::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! ( $receiver_post && $receiver_post->can_send_messages() ) ) {
					throw new \Exception( _x( 'You cannot block this post.', 'messages', 'voxel' ) );
				}

				$receiver_type = 'post';
				$receiver_id = $receiver_post->get_id();
				$receiver = $receiver_post;
			} else {
				$receiver_user = \Voxel\User::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! $receiver_user ) {
					throw new \Exception( _x( 'You cannot message this user.', 'messages', 'voxel' ) );
				}

				$receiver_type = 'user';
				$receiver_id = $receiver_user->get_id();
				$receiver = $receiver_user;
			}

			$status = ( $_REQUEST['unblock'] ?? null ) === 'yes' ? \Voxel\FOLLOW_NONE : \Voxel\FOLLOW_BLOCKED;
			$sender->set_follow_status( $receiver_type, $receiver->get_id(), $status );

			return wp_send_json( [
				'success' => true,
				'status' => $status,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function clear_conversation() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			// determine sender
			if ( ( $_REQUEST['sender_type'] ?? null ) === 'post' ) {
				$sender_post = \Voxel\Post::get( $_REQUEST['sender_id'] ?? null );
				if ( ! ( $sender_post && $sender_post->get_author_id() === get_current_user_id() ) ) {
					throw new \Exception( _x( 'You cannot send messages as this post.', 'messages', 'voxel' ) );
				}

				$sender = $sender_post;
			} else {
				$sender = \Voxel\current_user();
			}

			// determine receiver
			if ( ( $_REQUEST['receiver_type'] ?? null ) === 'post' ) {
				$receiver_post = \Voxel\Post::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! $receiver_post ) {
					throw new \Exception( _x( 'You cannot message this post.', 'messages', 'voxel' ) );
				}

				$receiver = $receiver_post;
			} else {
				$receiver_user = \Voxel\User::get( $_REQUEST['receiver_id'] ?? null );
				if ( ! $receiver_user ) {
					throw new \Exception( _x( 'You cannot message this user.', 'messages', 'voxel' ) );
				}

				$receiver = $receiver_user;
			}

			Module\Chat::clear_conversation( $sender, $receiver );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function delete_message() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_chat' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			if ( ! is_numeric( $_REQUEST['message_id'] ?? null ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			// determine deleter
			if ( ( $_REQUEST['deleter_type'] ?? null ) === 'post' ) {
				$deleter_post = \Voxel\Post::get( $_REQUEST['deleter_id'] ?? null );
				if ( ! ( $deleter_post && $deleter_post->get_author_id() === get_current_user_id() ) ) {
					throw new \Exception( __( 'Not allowed.', 'voxel' ) );
				}

				$deleter = $deleter_post;
			} else {
				$deleter = \Voxel\current_user();
			}

			$message = Module\Message::find( [ 'id' => absint( $_REQUEST['message_id'] ) ] );
			if ( ! $message ) {
				throw new \Exception( __( 'Not allowed.', 'voxel' ) );
			}

			$is_sender = $message->get_sender_type() === $deleter->get_object_type() && $message->get_sender_id() === $deleter->get_id();
			$is_receiver = $message->get_receiver_type() === $deleter->get_object_type() && $message->get_receiver_id() === $deleter->get_id();
			if ( ! ( $is_sender || $is_receiver ) ) {
				throw new \Exception( __( 'Not allowed.', 'voxel' ) );
			}

			global $wpdb;

			$message_id = absint( $message->get_id() );

			if ( $is_receiver && ! $message->is_deleted_by_receiver() ) {
				$wpdb->query( "UPDATE {$wpdb->prefix}voxel_messages SET receiver_deleted = 1 WHERE id = {$message_id} LIMIT 1" );
				return wp_send_json( [
					'success' => true,
					'is_deleted' => false,
					'is_hidden' => true,
				] );
			} elseif ( $is_sender && ! $message->is_deleted_by_sender() ) {
				$wpdb->query( "UPDATE {$wpdb->prefix}voxel_messages SET sender_deleted = 1, content = NULL, details = NULL WHERE id = {$message_id} LIMIT 1" );
				$message->get_receiver()->set_inbox_activity(true);
				return wp_send_json( [
					'success' => true,
					'is_deleted' => true,
					'is_hidden' => false,
				] );
			} else {
				throw new \Exception( __( 'Not allowed.', 'voxel' ) );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

}
