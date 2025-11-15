<?php

namespace Voxel\Controllers\Frontend\Timeline;

use \Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Status_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_timeline/v2/status.publish', '@publish_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.edit', '@edit_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.delete', '@delete_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.remove_link_preview', '@remove_link_preview' );
		$this->on( 'voxel_ajax_timeline/v2/status.like', '@like_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.repost', '@repost_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.quote', '@quote_status' );
		$this->on( 'voxel_ajax_timeline/v2/status.mark_approved', '@mark_approved' );
		$this->on( 'voxel_ajax_timeline/v2/status.mark_pending', '@mark_pending' );
	}

	protected function publish_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$current_user = \Voxel\get_current_user();
			if ( $current_user->has_reached_status_rate_limit() ) {
				throw new \Exception( _x( 'You\'re posting too often, try again later.', 'timeline', 'voxel' ), 50 );
			}

			$schema = Schema::Object( [
				'feed' => Schema::Enum( [ 'post_reviews', 'post_wall', 'post_timeline', 'user_timeline' ] ),
				'post_id' => Schema::Int(),
				'content' => Schema::String()->default(''),
				'files' => Schema::List()->default([]),
				'rating' => Schema::Keyed_List()->default([]),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true ) );
			$data = $schema->export();

			// validate content
			$content = \Voxel\trim_spaces( $data['content'] );
			$max_content_length = absint( \Voxel\get( 'settings.timeline.posts.maxlength', 5000 ) );
			if ( mb_strlen( $content ) > $max_content_length ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( 'Content can\'t be longer than @length characters', 'field validation', 'voxel' ), [
						'@length' => $max_content_length,
					]
				) );
			}

			// validate files
			$files = $this->_get_attached_files( $data );

			// validate post_id
			if ( in_array( $data['feed'], [ 'post_timeline', 'post_reviews', 'post_wall' ], true ) ) {
				if ( $data['post_id'] === null ) {
					throw new \Exception( _x( 'Something went wrong.', 'voxel' ), 60 );
				}

				$post = \Voxel\Post::get( $data['post_id'] );
				if ( ! ( $post && $post->post_type && $post->get_status() === 'publish' ) ) {
					throw new \Exception( _x( 'Something went wrong.', 'voxel' ), 61 );
				}
			}

			if ( $data['feed'] === 'post_timeline' ) {
				if ( ! $post->is_editable_by_user( $current_user ) ) {
					throw new \Exception( _x( 'Permission denied.', 'timeline', 'voxel' ), 70 );
				}

				if ( ! $post->post_type->get_setting( 'timeline.enabled' ) ) {
					throw new \Exception( _x( 'Timeline posts are not enabled for this post type.', 'timeline', 'voxel' ), 71 );
				}

				if ( empty( $content ) && empty( $files['list'] ) ) {
					throw new \Exception( _x( 'Post cannot be empty.', 'timeline', 'voxel' ), 72 );
				}

				$details = [
					'posted_by' => $current_user->get_id(),
				];

				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				}

				$status = \Voxel\Timeline\Status::create( [
					'feed' => 'post_timeline',
					'post_id' => $post->get_id(),
					'content' => $content,
					'details' => $details,
					'moderation' => $post->post_type->timeline->timeline_posts_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED,
				], [ 'link_preview' => 'instant' ] );

				// auto-approve posts by moderators
				if ( $status->is_moderatable_by_current_user() && $status->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
					$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
					$status = \Voxel\Timeline\Status::force_get( $status->get_id() );
				}

				( new \Voxel\Events\Timeline\Statuses\Post_Timeline_Status_Created_Event( $post->post_type ) )->dispatch( $status->get_id() );

				if ( $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
					$status->send_mention_notifications();
				}

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
				] );
			} elseif ( $data['feed'] === 'post_wall' ) {
				if ( ! $current_user->can_post_to_wall( $post->get_id() ) ) {
					throw new \Exception( _x( 'You cannot post to this item\'s wall.', 'timeline', 'voxel' ), 80 );
				}

				if ( empty( $content ) && empty( $files['list'] ) ) {
					throw new \Exception( _x( 'Post cannot be empty.', 'timeline', 'voxel' ), 81 );
				}

				$details = [];
				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				}

				$status = \Voxel\Timeline\Status::create( [
					'feed' => 'post_wall',
					'user_id' => $current_user->get_id(),
					'post_id' => $post->get_id(),
					'content' => $content,
					'details' => $details,
					'moderation' => $post->post_type->timeline->wall_posts_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED,
				], [ 'link_preview' => 'instant' ] );

				// auto-approve posts by moderators
				if ( $status->is_moderatable_by_current_user() && $status->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
					$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
					$status = \Voxel\Timeline\Status::force_get( $status->get_id() );
				}

				( new \Voxel\Events\Timeline\Statuses\Post_Wall_Status_Created_Event( $post->post_type ) )->dispatch( $status->get_id() );

				if ( $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
					$status->send_mention_notifications();
				}

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
				] );
			} elseif ( $data['feed'] === 'post_reviews' ) {
				if ( ! $current_user->can_review_post( $post->get_id() ) ) {
					throw new \Exception( _x( 'You\'re not allowed to review this item.', 'timeline', 'voxel' ), 90 );
				}

				if ( $current_user->has_reviewed_post( $post->get_id() ) ) {
					throw new \Exception( _x( 'You have already reviewed this item.', 'timeline', 'voxel' ), 91 );
				}

				$rating = $this->_sanitize_rating( $data['rating'], $post );
				if ( empty( $rating ) ) {
					throw new \Exception( _x( 'Please choose a rating', 'timeline', 'voxel' ), 92 );
				}

				$review_score = array_sum( $rating ) / count( $rating );

				$details = [
					'rating' => $rating,
				];

				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				}

				$options = [];

				if ( apply_filters( 'voxel/timeline/reviews/enable_link_preview', true ) !== false ) {
					$options['link_preview'] = 'instant';
				}

				$status = \Voxel\Timeline\Status::create( [
					'feed' => 'post_reviews',
					'user_id' => $current_user->get_id(),
					'post_id' => $post->get_id(),
					'content' => $content,
					'details' => $details,
					'review_score' => $review_score,
					'moderation' => $post->post_type->timeline->reviews_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED,
				], $options );

				// auto-approve posts by moderators
				if ( $status->is_moderatable_by_current_user() && $status->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
					$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
					$status = \Voxel\Timeline\Status::force_get( $status->get_id() );
				}

				( new \Voxel\Events\Timeline\Statuses\Post_Reviews_Status_Created_Event( $post->post_type ) )->dispatch( $status->get_id() );

				if ( $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
					$status->send_mention_notifications();
				}

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
				] );
			} elseif ( $data['feed'] === 'user_timeline' ) {
				if ( empty( $content ) && empty( $files['list'] ) ) {
					throw new \Exception( _x( 'Post cannot be empty.', 'timeline', 'voxel' ), 100 );
				}

				$details = [];
				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				}

				$status = \Voxel\Timeline\Status::create( [
					'feed' => 'user_timeline',
					'user_id' => $current_user->get_id(),
					'content' => $content,
					'details' => $details,
					'moderation' => $current_user->timeline_posts_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED,
				], [ 'link_preview' => 'instant' ] );

				// auto-approve posts by moderators
				if ( $status->is_moderatable_by_current_user() && $status->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
					$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
					$status = \Voxel\Timeline\Status::force_get( $status->get_id() );
				}

				( new \Voxel\Events\Timeline\Statuses\User_Timeline_Status_Created_Event )->dispatch( $status->get_id() );

				if ( $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
					$status->send_mention_notifications();
				}

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
				] );
			} else {
				throw new \Exception( _x( 'Something went wrong.', 'voxel' ), 113 );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function _get_attached_files( array $data ): array {
		if ( ! \Voxel\get( 'settings.timeline.posts.images.enabled', true ) ) {
			return [
				'list' => [],
			];
		}

		$field = apply_filters( 'voxel/timeline/status-files-field', new \Voxel\Timeline\Fields\Status_Files_Field );
		$files = $field->sanitize( $data['files'] );
		$field->validate( $files );

		return [
			'list' => $files,
			'upload' => function() use ( $field, $files ) {
				return $field->prepare_for_storage( $files );
			},
		];
	}

	protected function _sanitize_rating( array $rating, \Voxel\Post $post ): array {
		$sanitized = [];
		foreach ( $post->post_type->reviews->get_categories() as $key => $category ) {
			$score = $rating[ $key ] ?? null;

			if ( $score === null ) {
				if ( $category['required'] ) {
					throw new \Exception( \Voxel\replace_vars( _x( 'You must choose a rating for @category_label', 'reviews', 'voxel' ), [
						'@category_label' => $category['label'],
					] ), 121 );
				} else {
					// no rating submitted for this category and it isn't required, skip
					continue;
				}
			}

			$score = (int) $score;
			if ( ! in_array( $score, [ -2, -1, 0, 1, 2 ], true ) ) {
				throw new \Exception( \Voxel\replace_vars( _x( 'You must choose a rating for @category_label', 'reviews', 'voxel' ), [
					'@category_label' => $category['label'],
				] ), 122 );
			}

			$sanitized[ $key ] = $score;
		}

		return $sanitized;
	}

	protected function edit_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$current_user = \Voxel\get_current_user();
			$schema = Schema::Object( [
				'status_id' => Schema::Int(),
				'content' => Schema::String()->default(''),
				'files' => Schema::List()->default([]),
				'rating' => Schema::Keyed_List()->default([]),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true ) );
			$data = $schema->export();

			if ( $data['status_id'] === null ) {
				throw new \Exception( _x( 'You cannot edit this post.', 'timeline', 'voxel' ), 100 );
			}

			$status = \Voxel\Timeline\Status::get( $data['status_id'] );
			$editing_allowed = !! \Voxel\get( 'settings.timeline.posts.editable', true );
			if ( ! ( $status && $status->is_editable_by_current_user() && $editing_allowed ) ) {
				throw new \Exception( _x( 'You cannot edit this post.', 'timeline', 'voxel' ) );
			}

			// validate content
			$content = \Voxel\trim_spaces( $data['content'] );
			$max_content_length = absint( \Voxel\get( 'settings.timeline.posts.maxlength', 5000 ) );
			if ( mb_strlen( $content ) > $max_content_length ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( 'Content can\'t be longer than @length characters', 'field validation', 'voxel' ), [
						'@length' => $max_content_length,
					]
				) );
			}

			// validate files
			$files = $this->_get_attached_files( $data );

			if ( $status->get_feed() === 'post_reviews' ) {
				$post = $status->get_post();
				if ( ! $post ) {
					throw new \Exception( _x( 'You cannot edit this post.', 'timeline', 'voxel' ), 101 );
				}

				$rating = $this->_sanitize_rating( $data['rating'], $post );
				if ( empty( $rating ) ) {
					throw new \Exception( _x( 'Please choose a rating', 'timeline', 'voxel' ), 102 );
				}

				$review_score = array_sum( $rating ) / count( $rating );

				$details = $status->get_details();
				$details['rating'] = $rating;

				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				} else {
					unset( $details['files'] );
				}

				$status->update( [
					'content' => $content,
					'details' => $details,
					'review_score' => $review_score,
					'edited_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				$status = \Voxel\Timeline\Status::force_get( $status->get_id() );

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
					'message' => _x( 'Review updated.', 'timeline', 'voxel' ),
				] );
			} else {
				if ( empty( $content ) && empty( $files['list'] ) ) {
					throw new \Exception( _x( 'Post cannot be empty.', 'timeline', 'voxel' ), 103 );
				}

				$details = $status->get_details();

				if ( ! empty( $files['list'] ) ) {
					$details['files'] = $files['upload']();
				} else {
					unset( $details['files'] );
				}

				$status->update( [
					'content' => $content,
					'details' => $details,
					'edited_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				$status = \Voxel\Timeline\Status::force_get( $status->get_id() );

				return wp_send_json( [
					'success' => true,
					'status' => $status->get_frontend_config(),
					'message' => _x( 'Post updated.', 'timeline', 'voxel' ),
				] );
			}
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
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! ( $status && ( $status->is_editable_by_current_user() || $status->is_moderatable_by_current_user() ) ) ) {
				throw new \Exception( _x( 'You cannot delete this post.', 'timeline', 'voxel' ) );
			}

			$status->delete();

			return wp_send_json( [
				'success' => true,
				'message' => _x( 'Post deleted.', 'timeline', 'voxel' ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function remove_link_preview() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! ( $status && $status->is_editable_by_current_user() ) ) {
				throw new \Exception( _x( 'You cannot modify this post.', 'timeline', 'voxel' ) );
			}

			$details = $status->get_details();
			unset( $details['link_preview'] );

			$status->update( 'details', $details );

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

	protected function like_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! $status ) {
				throw new \Exception( _x( 'You cannot like this post.', 'timeline', 'voxel' ), 100 );
			}

			$publisher = $status->get_publisher();
			if ( $publisher instanceof \Voxel\Post ) {
				$author_id = $publisher->get_author_id();
			} elseif ( $publisher instanceof \Voxel\User ) {
				$author_id = $publisher->get_id();
			} else {
				$author_id = null;
			}

			$like_count = $status->get_like_count();
			if ( $status->is_liked_by_current_user() ) {
				$status->unlike();
				$like_count--;
				$action = 'unlike';

				if ( $author_id !== null ) {
					global $wpdb;
					$wpdb->query( $wpdb->prepare( <<<SQL
						DELETE FROM {$wpdb->prefix}voxel_notifications
						WHERE user_id = %d AND type = 'users/timeline/post-liked'
						AND JSON_UNQUOTE( JSON_EXTRACT( details, '$.user_id' ) ) = %d
						LIMIT 1
					SQL, $author_id, get_current_user_id() ) );
				}
			} else {
				if ( ! $status->is_viewable_by_current_user() ) {
					throw new \Exception( _x( 'You cannot like this post.', 'timeline', 'voxel' ), 101 );
				}

				$status->like();
				$like_count++;
				$action = 'like';

				( new \Voxel\Events\Timeline\Statuses\User_Liked_Event )->dispatch( get_current_user_id(), $status->get_id() );
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

	protected function repost_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			if ( ! \Voxel\get( 'settings.timeline.reposts.enabled', true ) ) {
				throw new \Exception( _x( 'You cannot repost this item.', 'timeline', 'voxel' ), 101 );
			}

			$current_user = \Voxel\get_current_user();
			if ( $current_user->has_reached_status_rate_limit() ) {
				throw new \Exception( _x( 'You\'re posting too often, try again later.', 'timeline', 'voxel' ), 50 );
			}

			$repost_of = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! $repost_of ) {
				throw new \Exception( _x( 'You cannot repost this item.', 'timeline', 'voxel' ), 100 );
			}

			$current_repost = \Voxel\Timeline\Status::find( [
				'user_id' => $current_user->get_id(),
				'repost_of' => $repost_of->get_id(),
				'moderation' => 1,
			] );

			if ( $current_repost !== null ) {
				$current_repost->delete();
				return wp_send_json( [
					'success' => true,
					'message' => '',
					'action' => 'unrepost',
				] );
			} else {
				if ( ! ( $repost_of->is_repostable_by_current_user() ) ) {
					throw new \Exception( _x( 'You cannot repost this item.', 'timeline', 'voxel' ), 105 );
				}

				$status = \Voxel\Timeline\Status::create( [
					'feed' => 'user_timeline',
					'user_id' => $current_user->get_id(),
					'repost_of' => $repost_of->get_id(),
					'moderation' => \Voxel\MODERATION_APPROVED,
				] );

				if ( $repost_of->get_author_id() !== $current_user->get_id() ) {
					( new \Voxel\Events\Timeline\Statuses\User_Reposted_Event )->dispatch( $status->get_id() );
				}

				return wp_send_json( [
					'success' => true,
					'message' => '',
					'action' => 'repost',
					'status' => $status->get_frontend_config(),
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function quote_status() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_timeline' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			if ( ! \Voxel\get( 'settings.timeline.reposts.enabled', true ) ) {
				throw new \Exception( _x( 'You cannot quote this item.', 'timeline', 'voxel' ), 101 );
			}

			$current_user = \Voxel\get_current_user();
			if ( $current_user->has_reached_status_rate_limit() ) {
				throw new \Exception( _x( 'You\'re posting too often, try again later.', 'timeline', 'voxel' ), 50 );
			}

			$schema = Schema::Object( [
				'status_id' => Schema::Int(),
				'content' => Schema::String()->default(''),
				'files' => Schema::List()->default([]),
			] );

			$schema->set_value( json_decode( wp_unslash( $_REQUEST['data'] ?? '' ), true ) );
			$data = $schema->export();

			// validate quoted tweet
			$quote_of = \Voxel\Timeline\Status::get( $data['status_id'] );
			if ( ! ( $quote_of && $quote_of->is_quotable_by_current_user() ) ) {
				throw new \Exception( _x( 'You cannot quote this item.', 'timeline', 'voxel' ), 100 );
			}

			// validate content
			$content = \Voxel\trim_spaces( $data['content'] );
			$max_content_length = absint( \Voxel\get( 'settings.timeline.posts.maxlength', 5000 ) );
			if ( mb_strlen( $content ) > $max_content_length ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( 'Content can\'t be longer than @length characters', 'field validation', 'voxel' ), [
						'@length' => $max_content_length,
					]
				) );
			}

			// validate files
			$files = $this->_get_attached_files( $data );

			if ( empty( $content ) && empty( $files['list'] ) ) {
				throw new \Exception( _x( 'Post cannot be empty.', 'timeline', 'voxel' ), 81 );
			}

			$details = [];
			if ( ! empty( $files['list'] ) ) {
				$details['files'] = $files['upload']();
			}

			$status = \Voxel\Timeline\Status::create( [
				'feed' => 'user_timeline',
				'user_id' => $current_user->get_id(),
				'content' => $content,
				'details' => $details,
				'quote_of' => $quote_of->get_id(),
				'moderation' => $current_user->timeline_posts_require_approval() ? \Voxel\MODERATION_PENDING : \Voxel\MODERATION_APPROVED,
			] );

			// auto-approve posts by moderators
			if ( $status->is_moderatable_by_current_user() && $status->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
				$status->update( 'moderation', \Voxel\MODERATION_APPROVED );
				$status = \Voxel\Timeline\Status::force_get( $status->get_id() );
			}

			( new \Voxel\Events\Timeline\Statuses\User_Timeline_Status_Created_Event )->dispatch( $status->get_id() );

			if ( $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) {
				if ( $quote_of->get_author_id() !== $current_user->get_id() ) {
					( new \Voxel\Events\Timeline\Statuses\User_Quoted_Event )->dispatch( $status->get_id() );
				}

				$status->send_mention_notifications();
			}

			return wp_send_json( [
				'success' => true,
				'status' => $status->get_frontend_config(),
				'message' => _x( 'Quote posted to your timeline', 'timeline', 'voxel' ),
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

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! ( $status && $status->is_moderatable_by_current_user() && $status->get_moderation_status() === \Voxel\MODERATION_PENDING ) ) {
				throw new \Exception( _x( 'You cannot modify this post.', 'timeline', 'voxel' ) );
			}

			$status->mark_approved();
			$status = \Voxel\Timeline\Status::force_get( $status->get_id() );

			return wp_send_json( [
				'success' => true,
				'badges' => $status->get_badges(),
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

			$status = \Voxel\Timeline\Status::get( absint( $_REQUEST['status_id'] ?? null ) );
			if ( ! ( $status && $status->is_moderatable_by_current_user() && $status->get_moderation_status() === \Voxel\MODERATION_APPROVED ) ) {
				throw new \Exception( _x( 'You cannot modify this post.', 'timeline', 'voxel' ) );
			}

			$status->update( 'moderation', \Voxel\MODERATION_PENDING );
			$status = \Voxel\Timeline\Status::force_get( $status->get_id() );

			return wp_send_json( [
				'success' => true,
				'badges' => $status->get_badges(),
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
