<?php

namespace Voxel\Controllers\Timeline;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Timeline_Actions_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'edit_others_posts' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.timeline.status.approve', '@approve_status' );
		$this->on( 'voxel_ajax_backend.timeline.status.decline', '@decline_status' );
		$this->on( 'voxel_ajax_backend.timeline.status.unapprove', '@unapprove_status' );
		$this->on( 'voxel_ajax_backend.timeline.status.delete', '@delete_status' );
		$this->on( 'voxel_ajax_backend.timeline.status.index_all_statuses', '@index_all_statuses' );

		$this->on( 'voxel_ajax_backend.timeline.reply.approve', '@approve_reply' );
		$this->on( 'voxel_ajax_backend.timeline.reply.decline', '@decline_reply' );
		$this->on( 'voxel_ajax_backend.timeline.reply.unapprove', '@unapprove_reply' );
		$this->on( 'voxel_ajax_backend.timeline.reply.delete', '@delete_reply' );
		$this->on( 'voxel_ajax_backend.timeline.reply.index_all_replies', '@index_all_replies' );
	}

	protected function approve_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$status_id = $_REQUEST['status_id'] ?? null;
			$status = \Voxel\Timeline\Status::get( $status_id );
			if ( ! $status ) {
				throw new \Exception( __( 'Post not found.', 'voxel-backend' ) );
			}

			$status->mark_approved();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function unapprove_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$status_id = $_REQUEST['status_id'] ?? null;
			$status = \Voxel\Timeline\Status::get( $status_id );
			if ( ! $status ) {
				throw new \Exception( __( 'Post not found.', 'voxel-backend' ) );
			}

			$status->update( 'moderation', \Voxel\MODERATION_PENDING );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function decline_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$status_id = $_REQUEST['status_id'] ?? null;
			$status = \Voxel\Timeline\Status::get( $status_id );
			if ( ! $status ) {
				throw new \Exception( __( 'Post not found.', 'voxel-backend' ) );
			}

			$status->delete();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function delete_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$status_id = $_REQUEST['status_id'] ?? null;
			$status = \Voxel\Timeline\Status::get( $status_id );
			if ( ! $status ) {
				throw new \Exception( __( 'Post not found.', 'voxel-backend' ) );
			}

			$status->delete();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function index_all_statuses() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$option_key = 'timeline_index';
			$data = (array) json_decode( get_option( $option_key ), ARRAY_A );
			// if ( ( $data['status'] ?? null ) === 'processing' ) {
			// 	throw new \Exception( __( 'Another indexing request is running.', 'voxel-backend' ) );
			// }

			update_option( $option_key, wp_json_encode( [
				'status' => 'processing',
				'offset' => $data['offset'] ?? 0,
			] ) );

			global $wpdb;

			$offset = absint( $data['offset'] ?? 0 );
			$limit = 500;
			$current_batch = 0;
			$total = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline" ) );

			do {
				$items = $wpdb->get_results( $wpdb->prepare( <<<SQL
					SELECT id, content FROM {$wpdb->prefix}voxel_timeline
						ORDER BY id ASC LIMIT %d, %d
					SQL, $offset, $limit ) );

				$values = [];
				$ids = [];

				foreach ( $items as $item ) {
					$id = absint( $item->id );
					$ids[] = $id;
					$_index = '';
					if ( is_string( $item->content ) ) {
						$_index = \Voxel\text_formatter()->prepare_for_fulltext_indexing( $item->content );
					}

					$values[] = $wpdb->prepare( '(%d,%s)', $id, $_index );
				}

				if ( ! empty( $values ) ) {
					$_joined_values = join( ',', $values );
					$update_sql = <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_timeline (id,_index)
						VALUES {$_joined_values}
						ON DUPLICATE KEY UPDATE id=VALUES(id), _index=VALUES(_index)
					SQL;

					$wpdb->query( $update_sql );
				}

				if ( ! empty( $ids ) ) {
					$_joined_ids = join( ',', $ids );

					// recalculate likes
					$wpdb->query( <<<SQL
						UPDATE {$wpdb->prefix}voxel_timeline AS t
						SET t.like_count = (
							SELECT COUNT(*)
							FROM {$wpdb->prefix}voxel_timeline_status_likes AS l
							WHERE l.status_id = t.id
						)
						WHERE t.id IN ({$_joined_ids});
					SQL );

					// recalculate replies
					$wpdb->query( <<<SQL
						UPDATE {$wpdb->prefix}voxel_timeline AS t
						SET t.reply_count = (
							SELECT COUNT(*)
							FROM {$wpdb->prefix}voxel_timeline_replies AS r
							WHERE r.status_id = t.id AND r.moderation = 1
						)
						WHERE t.id IN ({$_joined_ids});
					SQL );
				}

				// final batch
				if ( count( $items ) < $limit ) {
					delete_option( $option_key );
					return wp_send_json( [
						'success' => true,
						'offset' => $total,
						'total' => $total,
						'has_more' => false,
					] );
				}

				$offset += $limit;
				$current_batch += $limit;
			} while ( ! \Voxel\nearing_resource_limits() && $current_batch < 5000 );

			update_option( $option_key, wp_json_encode( [
				'status' => 'batch-processed',
				'offset' => $offset,
			] ) );

			return wp_send_json( [
				'success' => true,
				'offset' => $offset,
				'total' => $total,
				'has_more' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function index_all_replies() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$option_key = 'timeline_reply_index';
			$data = (array) json_decode( get_option( $option_key ), ARRAY_A );
			// if ( ( $data['status'] ?? null ) === 'processing' ) {
			// 	throw new \Exception( __( 'Another indexing request is running.', 'voxel-backend' ) );
			// }

			update_option( $option_key, wp_json_encode( [
				'status' => 'processing',
				'offset' => $data['offset'] ?? 0,
			] ) );

			global $wpdb;

			$offset = absint( $data['offset'] ?? 0 );
			$limit = 500;
			$current_batch = 0;
			$total = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline_replies" ) );

			do {
				$items = $wpdb->get_results( $wpdb->prepare( <<<SQL
					SELECT id, content FROM {$wpdb->prefix}voxel_timeline_replies
						ORDER BY id ASC LIMIT %d, %d
					SQL, $offset, $limit ) );

				$values = [];
				$ids = [];

				foreach ( $items as $item ) {
					$id = absint( $item->id );
					$ids[] = $id;
					$_index = '';
					if ( is_string( $item->content ) ) {
						$_index = \Voxel\text_formatter()->prepare_for_fulltext_indexing( $item->content );
					}

					$values[] = $wpdb->prepare( '(%d,%s)', $id, $_index );
				}

				if ( ! empty( $values ) ) {
					$_joined_values = join( ',', $values );
					$update_sql = <<<SQL
						INSERT INTO {$wpdb->prefix}voxel_timeline_replies (id,_index)
						VALUES {$_joined_values}
						ON DUPLICATE KEY UPDATE id=VALUES(id), _index=VALUES(_index)
					SQL;

					$wpdb->query( $update_sql );
				}

				if ( ! empty( $ids ) ) {
					$_joined_ids = join( ',', $ids );

					// recalculate likes
					$wpdb->query( <<<SQL
						UPDATE {$wpdb->prefix}voxel_timeline_replies AS r
						SET r.like_count = (
							SELECT COUNT(*)
							FROM {$wpdb->prefix}voxel_timeline_reply_likes_v2 AS l
							WHERE l.reply_id = r.id
						)
						WHERE r.id IN ({$_joined_ids});
					SQL );

					// recalculate replies
					$wpdb->query( <<<SQL
						UPDATE {$wpdb->prefix}voxel_timeline_replies AS r
						LEFT JOIN (
							SELECT parent_id, COUNT(*) AS total_replies
							FROM {$wpdb->prefix}voxel_timeline_replies
							WHERE moderation = 1 AND parent_id IS NOT NULL
							GROUP BY parent_id
						) AS agg ON r.id = agg.parent_id
						SET r.reply_count = IFNULL(agg.total_replies, 0)
						WHERE r.id IN ({$_joined_ids});
					SQL );
				}

				// final batch
				if ( count( $items ) < $limit ) {
					delete_option( $option_key );
					return wp_send_json( [
						'success' => true,
						'offset' => $total,
						'total' => $total,
						'has_more' => false,
					] );
				}

				$offset += $limit;
				$current_batch += $limit;
			} while ( ! \Voxel\nearing_resource_limits() && $current_batch < 5000 );

			update_option( $option_key, wp_json_encode( [
				'status' => 'batch-processed',
				'offset' => $offset,
			] ) );

			return wp_send_json( [
				'success' => true,
				'offset' => $offset,
				'total' => $total,
				'has_more' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function approve_reply() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$reply_id = $_REQUEST['reply_id'] ?? null;
			$reply = \Voxel\Timeline\Reply::get( $reply_id );
			if ( ! $reply ) {
				throw new \Exception( __( 'Reply not found.', 'voxel-backend' ) );
			}

			$reply->update( 'moderation', \Voxel\MODERATION_APPROVED );

			$status = $reply->get_status();
			$parent = $reply->get_parent();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			if ( $parent ) {
				( new \Voxel\Events\Timeline\Comments\Comment_Reply_Approved_Event )->dispatch( $reply->get_id() );
			} else {
				( new \Voxel\Events\Timeline\Comments\Comment_Approved_Event )->dispatch( $reply->get_id() );
			}

			$reply->send_mention_notifications();

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function unapprove_reply() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$reply_id = $_REQUEST['reply_id'] ?? null;
			$reply = \Voxel\Timeline\Reply::get( $reply_id );
			if ( ! $reply ) {
				throw new \Exception( __( 'Reply not found.', 'voxel-backend' ) );
			}

			$reply->update( 'moderation', \Voxel\MODERATION_PENDING );

			$status = $reply->get_status();
			$parent = $reply->get_parent();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function decline_reply() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$reply_id = $_REQUEST['reply_id'] ?? null;
			$reply = \Voxel\Timeline\Reply::get( $reply_id );
			if ( ! $reply ) {
				throw new \Exception( __( 'Reply not found.', 'voxel-backend' ) );
			}

			$reply->delete();

			$status = $reply->get_status();
			$parent = $reply->get_parent();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function delete_reply() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline_backend' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$reply_id = $_REQUEST['reply_id'] ?? null;
			$reply = \Voxel\Timeline\Reply::get( $reply_id );
			if ( ! $reply ) {
				throw new \Exception( __( 'Reply not found.', 'voxel-backend' ) );
			}

			$reply->delete();

			$status = $reply->get_status();
			$parent = $reply->get_parent();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
