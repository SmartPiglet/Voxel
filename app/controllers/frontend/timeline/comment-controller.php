<?php

namespace Voxel\Controllers\Frontend\Timeline;

use \Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_timeline/v2/comment.publish', '@publish_comment' );
		$this->on( 'voxel_ajax_timeline/v2/comment.edit', '@edit_comment' );
		$this->on( 'voxel_ajax_timeline/v2/comment.delete', '@delete_comment' );
		$this->on( 'voxel_ajax_timeline/v2/comment.like', '@like_comment' );
		$this->on( 'voxel_ajax_timeline/v2/comment.mark_approved', '@mark_approved' );
		$this->on( 'voxel_ajax_timeline/v2/comment.mark_pending', '@mark_pending' );
	}

	protected function publish_comment() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$current_user = \Voxel\get_current_user();
			if ( $current_user->has_reached_reply_rate_limit() ) {
				throw new \Exception( _x( 'You\'re commenting too often, try again later.', 'timeline', 'voxel' ), 50 );
			}

			$schema = Schema::Object( [
				'status_id' => Schema::Int(),
				'parent_id' => Schema::Int(),
				'content' => Schema::String()->default(''),
				'files' => Schema::List()->default([]),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true ) );
			$data = $schema->export();

			// validate content
			$content = \Voxel\trim_spaces( $data['content'] );
			$max_content_length = absint( \Voxel\get( 'settings.timeline.replies.maxlength', 2000 ) );
			if ( mb_strlen( $content ) > $max_content_length ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( 'Content can\'t be longer than @length characters', 'field validation', 'voxel' ), [
						'@length' => $max_content_length,
					]
				) );
			}

			// validate files
			$files = $this->_get_attached_files( $data );

			$details = [];
			if ( $data['parent_id'] !== null ) {
				// validate parent comment and status
				$parent = \Voxel\Timeline\Reply::get( $data['parent_id'] );
				if ( ! ( $parent && $parent->get_moderation_status() === \Voxel\MODERATION_APPROVED ) ) {
					throw new \Exception( _x( 'You cannot reply to this comment.', 'timeline', 'voxel' ) );
				}

				$status = $parent->get_status();
				if ( ! ( $status && $status->is_viewable_by_current_user() && $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) ) {
					throw new \Exception( _x( 'You cannot reply to this post.', 'timeline', 'voxel' ), 51 );
				}

				// validate reply depth
				$max_reply_depth = absint( \Voxel\get( 'settings.timeline.replies.max_nest_level', 2 ) );
				if ( $parent->get_depth() >= $max_reply_depth ) {
					$parent = $parent->get_parent();
				}
			} else {
				// top level comment, validate status
				$status = \Voxel\Timeline\Status::get( $data['status_id'] );
				if ( ! ( $status && $status->is_viewable_by_current_user() && $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) ) {
					throw new \Exception( _x( 'You cannot reply to this post.', 'timeline', 'voxel' ), 52 );
				}
			}

			if ( empty( $content ) && empty( $files['list'] ) ) {
				throw new \Exception( _x( 'Comment cannot be empty.', 'timeline', 'voxel' ), 110 );
			}

			if ( ! empty( $files['list'] ) ) {
				$details['files'] = $files['upload']();
			}

			if ( $status->get_feed() === 'user_timeline' ) {
				$moderation = $current_user->timeline_comments_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED;
			} elseif ( in_array( $status->get_feed(), [ 'post_timeline', 'post_wall', 'post_reviews' ], true ) ) {
				$post = $status->get_post();
				if ( ! ( $post && $post->post_type ) ) {
					throw new \Exception( _x( 'You cannot reply to this post.', 'timeline', 'voxel' ), 61 );
				}

				if ( $status->get_feed() === 'post_timeline' ) {
					$moderation = $post->post_type->timeline->timeline_comments_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED;
				} elseif ( $status->get_feed() === 'post_wall' ) {
					$moderation = $post->post_type->timeline->wall_comments_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED;
				} elseif ( $status->get_feed() === 'post_reviews' ) {
					$moderation = $post->post_type->timeline->review_comments_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED;
				} else {
					throw new \Exception( _x( 'You cannot reply to this post.', 'timeline', 'voxel' ), 62 );
				}
			} else {
				throw new \Exception( _x( 'You cannot reply to this post.', 'timeline', 'voxel' ), 59 );
			}

			$comment = \Voxel\Timeline\Reply::create( [
				'user_id' => get_current_user_id(),
				'status_id' => $status->get_id(),
				'parent_id' => isset( $parent ) ? $parent->get_id() : null,
				'content' => $content,
				'details' => $details,
				'moderation' => $moderation,
			] );

			// auto-approve comments by moderators
			if ( $comment->is_moderatable_by_current_user() && $comment->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
				$comment->update( 'moderation', \Voxel\MODERATION_APPROVED );
				$comment = \Voxel\Timeline\Reply::force_get( $comment->get_id() );
			}

			if ( $comment->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
				$status->_update_reply_count();
				if ( isset( $parent ) ) {
					$parent->_update_reply_count();
				}

				$comment->send_mention_notifications();
				if ( isset( $parent ) ) {
					( new \Voxel\Events\Timeline\Comments\Comment_Reply_Approved_Event )->dispatch( $comment->get_id() );
				} else {
					( new \Voxel\Events\Timeline\Comments\Comment_Approved_Event )->dispatch( $comment->get_id() );
				}
			} else {
				if ( isset( $parent ) ) {
					( new \Voxel\Events\Timeline\Comments\Comment_Reply_Submitted_Event )->dispatch( $comment->get_id() );
				} else {
					( new \Voxel\Events\Timeline\Comments\Comment_Submitted_Event )->dispatch( $comment->get_id() );
				}
			}

			return wp_send_json( [
				'success' => true,
				'comment' => $comment->get_frontend_config(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function _get_attached_files( array $data ): array {
		if ( ! \Voxel\get( 'settings.timeline.replies.images.enabled', true ) ) {
			return [
				'list' => [],
			];
		}

		$field = new \Voxel\Timeline\Fields\Comment_Files_Field;
		$files = $field->sanitize( $data['files'] );
		$field->validate( $files );

		return [
			'list' => $files,
			'upload' => function() use ( $field, $files ) {
				return $field->prepare_for_storage( $files );
			},
		];
	}

	protected function edit_comment() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$schema = Schema::Object( [
				'comment_id' => Schema::Int(),
				'content' => Schema::String()->default(''),
				'files' => Schema::List()->default([]),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true ) );
			$data = $schema->export();

			$comment = \Voxel\Timeline\Reply::get( $data['comment_id'] );
			$editing_allowed = !! \Voxel\get( 'settings.timeline.replies.editable', true );
			if ( ! ( $comment && $comment->is_editable_by_current_user() && $editing_allowed ) ) {
				throw new \Exception( _x( 'You cannot edit this comment.', 'timeline', 'voxel' ) );
			}

			// validate content
			$content = \Voxel\trim_spaces( $data['content'] );
			$max_content_length = absint( \Voxel\get( 'settings.timeline.replies.maxlength', 2000 ) );
			if ( mb_strlen( $content ) > $max_content_length ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( 'Content can\'t be longer than @length characters', 'field validation', 'voxel' ), [
						'@length' => $max_content_length,
					]
				) );
			}

			// validate files
			$files = $this->_get_attached_files( $data );

			$details = $comment->get_details();

			if ( empty( $content ) && empty( $files['list'] ) ) {
				throw new \Exception( _x( 'Comment cannot be empty.', 'timeline', 'voxel' ), 110 );
			}

			if ( ! empty( $files['list'] ) ) {
				$details['files'] = $files['upload']();
			} else {
				unset( $details['files'] );
			}

			$comment->update( [
				'content' => $content,
				'details' => $details,
				'edited_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
			] );

			$comment = \Voxel\Timeline\Reply::force_get( $comment->get_id() );

			return wp_send_json( [
				'success' => true,
				'comment' => $comment->get_frontend_config(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function delete_comment() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$comment = \Voxel\Timeline\Reply::get( absint( $_REQUEST['comment_id'] ?? null ) );
			if ( ! ( $comment && ( $comment->is_editable_by_current_user() || $comment->is_moderatable_by_current_user() ) ) ) {
				throw new \Exception( _x( 'You cannot delete this comment.', 'timeline', 'voxel' ) );
			}

			$status = $comment->get_status();
			$parent = $comment->get_parent();
			$comment->delete();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			return wp_send_json( [
				'success' => true,
				'message' => _x( 'Comment deleted.', 'timeline', 'voxel' ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function like_comment() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$comment = \Voxel\Timeline\Reply::get( absint( $_REQUEST['comment_id'] ?? null ) );
			if ( ! ( $comment && $comment->is_viewable_by_current_user() ) ) {
				throw new \Exception( _x( 'You cannot like this comment.', 'timeline', 'voxel' ) );
			}

			$publisher = $comment->get_publisher();
			if ( $publisher instanceof \Voxel\Post ) {
				$author_id = $publisher->get_author_id();
			} elseif ( $publisher instanceof \Voxel\User ) {
				$author_id = $publisher->get_id();
			} else {
				$author_id = null;
			}

			$like_count = $comment->get_like_count();
			if ( $comment->is_liked_by_current_user() ) {
				$comment->unlike();
				$like_count--;
				$action = 'unlike';

				if ( $author_id !== null ) {
					global $wpdb;
					$wpdb->query( $wpdb->prepare( <<<SQL
						DELETE FROM {$wpdb->prefix}voxel_notifications
						WHERE user_id = %d AND type = 'users/timeline/comment-liked'
						AND JSON_UNQUOTE( JSON_EXTRACT( details, '$.user_id' ) ) = %d
						LIMIT 1
					SQL, $author_id, get_current_user_id() ) );
				}
			} else {
				$comment->like();
				$like_count++;
				$action = 'like';

				( new \Voxel\Events\Timeline\Comments\Comment_Liked_Event )->dispatch( get_current_user_id(), $comment->get_id() );
			}

			return wp_send_json( [
				'success' => true,
				'action' => $action,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function mark_approved() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$comment = \Voxel\Timeline\Reply::get( absint( $_REQUEST['comment_id'] ?? null ) );
			if ( ! ( $comment && $comment->is_moderatable_by_current_user() && $comment->get_moderation_status() === \Voxel\MODERATION_PENDING ) ) {
				throw new \Exception( _x( 'You cannot modify this comment.', 'timeline', 'voxel' ) );
			}

			$comment->mark_approved();
			$comment = \Voxel\Timeline\Reply::force_get( $comment->get_id() );

			return wp_send_json( [
				'success' => true,
				'badges' => $comment->get_badges(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function mark_pending() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$comment = \Voxel\Timeline\Reply::get( absint( $_REQUEST['comment_id'] ?? null ) );
			if ( ! ( $comment && $comment->is_moderatable_by_current_user() && $comment->get_moderation_status() === \Voxel\MODERATION_APPROVED ) ) {
				throw new \Exception( _x( 'You cannot modify this comment.', 'timeline', 'voxel' ) );
			}

			$comment->update( 'moderation', \Voxel\MODERATION_PENDING );
			$comment = \Voxel\Timeline\Reply::force_get( $comment->get_id() );

			return wp_send_json( [
				'success' => true,
				'badges' => $comment->get_badges(),
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
