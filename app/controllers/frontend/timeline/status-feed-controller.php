<?php

namespace Voxel\Controllers\Frontend\Timeline;

use \Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Status_Feed_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_timeline/v2/get_feed', '@get_feed' );
		$this->on( 'voxel_ajax_nopriv_timeline/v2/get_feed', '@get_feed' );
	}

	protected function get_feed() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'GET' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$page = absint( $_REQUEST['page'] ?? 1 );
			$per_page = absint( \Voxel\get( 'settings.timeline.posts.per_page', 10 ) );
			$user_id = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : null;
			$post_id = ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : null;
			$search_query = ! empty( $_REQUEST['search'] ) && is_string( $_REQUEST['search'] ) ? wp_unslash( $_REQUEST['search'] ) : null;
			$filter_by = $_REQUEST['filter_by'] ?? null;

			$user = \Voxel\User::get( $user_id );
			$post = \Voxel\Post::get( $post_id );
			$current_user = \Voxel\get_current_user();

			$args = [
				'limit' => $per_page + 1,
				'with_user_like_status' => true,
				'with_user_repost_status' => true,
				'moderation' => 1,
			];

			if ( $filter_by === 'liked' ) {
				$args['liked_by_user'] = get_current_user_id();
			}

			if ( $search_query !== null ) {
				$args['search'] = $search_query;
			}

			if ( $page > 1 ) {
				$args['offset'] = ( $page - 1 ) * $per_page;
			}

			$empty = function() {
				return wp_send_json( [
					'success' => true,
					'data' => [],
					'has_more' => false,
				] );
			};

			$allowed_modes = [
				'post_reviews' => true,
				'post_wall' => true,
				'post_timeline' => true,
				'author_timeline' => true,
				'user_feed' => true,
				'global_feed' => true,
				'single_status' => true,
			];

			$mode = $_REQUEST['mode'] ?? null;
			if ( $mode === null || ! isset( $allowed_modes[ $mode ] ) ) {
				throw new \Exception( _x( 'Could not load timeline.', 'timeline', 'voxel' ), 70 );
			}

			if ( $mode === 'post_reviews' ) {
				if ( ! ( $post && $post->post_type ) ) {
					throw new \Exception( _x( 'Could not retrieve reviews for post.', 'timeline', 'voxel' ), 71 );
				}

				$args['feed'] = 'post_reviews';
				$args['post_id'] = $post->get_id();
				$args['with_no_reposts'] = true;

				if ( $current_user ) {
					$current_user_review = \Voxel\Timeline\Status::find( [
						'post_id' => $post->get_id(),
						'user_id' => $current_user->get_id(),
						'feed' => 'post_reviews',
						'moderation' => 1,
						'with_user_like_status' => true,
					] );

					if ( $current_user_review !== null ) {
						$args['id'] = -$current_user_review->get_id();
					}
				}

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'post_reviews', [ 'post' => $post ] ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'post_wall' ) {
				if ( ! ( $post && $post->post_type ) ) {
					throw new \Exception( _x( 'Could not retrieve timeline items for post.', 'timeline', 'voxel' ), 72 );
				}

				$args['feed'] = 'post_wall';
				$args['post_id'] = $post->get_id();
				$args['with_no_reposts'] = true;

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'post_wall', [ 'post' => $post ] ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'post_timeline' ) {
				if ( ! ( $post && $post->post_type ) ) {
					throw new \Exception( _x( 'Could not retrieve timeline for post.', 'timeline', 'voxel' ), 73 );
				}

				$args['feed'] = 'post_timeline';
				$args['post_id'] = $post->get_id();
				$args['with_no_reposts'] = true;

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'post_timeline', [ 'post' => $post ] ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'author_timeline' ) {
				if ( ! $user ) {
					throw new \Exception( _x( 'Could not retrieve timeline for user.', 'timeline', 'voxel' ), 74 );
				}

				$args['feed'] = [ 'user_timeline', 'post_wall', 'post_reviews' ];
				$args['user_id'] = $user->get_id();

				if ( ! ( $current_user && $current_user->get_id() === $user->get_id() ) ) {
					$args['with_current_user_visibility_checks'] = true;
				}

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'author_timeline' ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'user_feed' ) {
				if ( ! $current_user ) {
					return $empty();
				}

				$args['follower_type'] = 'user';
				$args['follower_id'] = $current_user->get_id();
				$args['with_current_user_visibility_checks'] = true;

				if ( $search_query === null ) {
					$args['with_annotations'] = true;
				}

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'user_feed' ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'global_feed' ) {
				$args['with_current_user_visibility_checks'] = true;
				$args['with_no_reposts'] = true;

				if ( $filter_by === 'pending' && is_user_logged_in() ) {
					if ( $current_user->can_moderate_timeline_feed( 'global_feed' ) ) {
						$args['moderation'] = 0;
						$args['moderation_strict'] = 1;
					}
				}
			} elseif ( $mode === 'single_status' ) {
				$status_id = ! empty( $_REQUEST['status_id'] ) ? absint( $_REQUEST['status_id'] ) : null;
				if ( $status_id === null ) {
					return $empty;
				}

				$args['id'] = $status_id;
				$args['limit'] = 1;
			} else {
				return $empty();
			}

			// visibility
			if ( in_array( $mode, [ 'post_reviews', 'post_wall', 'post_timeline' ], true ) ) {
				$visibility_key = ( $mode === 'post_wall' ? 'wall_visibility' : ( $mode === 'post_reviews' ? 'review_visibility' : 'visibility' ) );
				$visibility = $post->post_type->get_setting( 'timeline.'.$visibility_key );
				if ( $visibility === 'logged_in' && ! is_user_logged_in() ) {
					return $empty();
				} elseif ( $visibility === 'followers_only' && ! ( is_user_logged_in() && ( \Voxel\current_user()->follows_post( $post->get_id() ) || $post->get_author_id() === get_current_user_id() ) ) ) {
					return $empty();
				} elseif ( $visibility === 'customers_only' && ! ( is_user_logged_in() && ( \Voxel\current_user()->has_bought_product( $post->get_id() ) || $post->get_author_id() === get_current_user_id() ) ) ) {
					return $empty();
				} elseif ( $visibility === 'private' && ! $post->is_editable_by_current_user() ) {
					return $empty();
				}
			} elseif ( $mode === 'author_timeline' ) {
				$visibility = \Voxel\get( 'settings.timeline.user_timeline.visibility', 'public' );

				if ( $visibility === 'logged_in' ) {
					if ( ! is_user_logged_in() ) {
						return $empty();
					}
				} elseif ( $visibility === 'followers_only' ) {
					if ( ! is_user_logged_in() ) {
						return $empty();
					}

					if ( ! ( $current_user->get_id() === $user->get_id() || $current_user->follows_user( $user->get_id() ) ) ) {
						return $empty();
					}
				} elseif ( $visibility === 'customers_only' ) {
					if ( ! is_user_logged_in() ) {
						return $empty();
					}

					if ( ! (
						$current_user->get_id() === $user->get_id()
						|| (
							$user->has_cap('administrator')
							&& apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true
							&& $current_user->has_bought_product_from_platform()
						) || (
							$current_user->has_bought_product_from_vendor( $user->get_id() )
						)
					) ) {
						return $empty();
					}
				} elseif ( $visibility === 'private' ) {
					if ( ! ( is_user_logged_in() && $current_user->get_id() === $user->get_id() ) ) {
						return $empty();
					}
				} else /* $visibility === 'public' */ {
					//
				}
			} elseif ( $mode === 'user_feed' ) {
				//
			} elseif ( $mode === 'global_feed' ) {
				//
			} elseif ( $mode === 'single_status' ) {
				//
			} else {
				return $empty();
			}

			$allowed_orders = [
				'latest' => true,
				'earliest' => true,
				'most_liked' => true,
				'most_discussed' => true,
				'most_popular' => true,
				'best_rated' => true,
				'worst_rated' => true,
			];

			$order = $_REQUEST['order_type'] ?? null;
			if ( $order === null || ! isset( $allowed_orders[ $order ] ) ) {
				throw new \Exception( _x( 'Could not load timeline.', 'timeline', 'voxel' ), 75 );
			}

			if ( $order === 'latest' ) {
				$args['order_by'] = 'created_at';
				$args['order'] = 'desc';
			} elseif ( $order === 'earliest' ) {
				$args['order_by'] = 'created_at';
				$args['order'] = 'asc';
			} elseif ( $order === 'most_liked' ) {
				$args['order_by'] = 'like_count';
				$args['order'] = 'desc';
			} elseif ( $order === 'most_discussed' ) {
				$args['order_by'] = 'reply_count';
				$args['order'] = 'desc';
			} elseif ( $order === 'most_popular' ) {
				$args['order_by'] = 'interaction_count';
				$args['order'] = 'desc';
			} elseif ( $order === 'best_rated' ) {
				$args['order_by'] = 'rating';
				$args['order'] = 'desc';
			} elseif ( $order === 'worst_rated' ) {
				$args['order_by'] = 'rating';
				$args['order'] = 'asc';
			}

			$allowed_times = [
				'today' => true,
				'this_week' => true,
				'this_month' => true,
				'this_year' => true,
				'all_time' => true,
				'custom' => true,
			];
			$time = $_REQUEST['order_time'] ?? null;
			if ( $time === null || ! isset( $allowed_times[ $time ] ) ) {
				throw new \Exception( _x( 'Could not load timeline.', 'timeline', 'voxel' ), 76 );
			}

			if ( $time === 'today' ) {
				$args['created_at'] = \Voxel\utc()->modify( '-24 hours' )->format( 'Y-m-d H:i:s' );
			} elseif ( $time === 'this_week' ) {
				$args['created_at'] = \Voxel\utc()->modify( 'first day of this week' )->format( 'Y-m-d 00:00:00' );
			} elseif ( $time === 'this_month' ) {
				$args['created_at'] = \Voxel\utc()->modify( 'first day of this month' )->format( 'Y-m-d 00:00:00' );
			} elseif ( $time === 'this_month' ) {
				$args['created_at'] = \Voxel\utc()->modify( 'first day of this year' )->format( 'Y-m-d 00:00:00' );
			} elseif ( $time === 'all_time' ) {
				//
			} elseif ( $time === 'custom' ) {
				$custom_time = absint( $_REQUEST['order_time_custom'] ?? null );
				if ( $custom_time ) {
					$args['created_at'] = \Voxel\utc()->modify( sprintf( '-%d days', $custom_time ) )->format( 'Y-m-d 00:00:00' );
				}
			}

			$query = \Voxel\Timeline\Status::query( $args );
			$statuses = $query['items'];
			$has_more = $query['count'] > $per_page;
			if ( $has_more && $query['count'] === count( $query['items'] ) ) {
				array_pop( $statuses );
			}

			if ( isset( $current_user_review ) ) {
				array_unshift( $statuses, $current_user_review );
			}

			$meta = [
				'review_config' => [],
			];

			$loaded_review_config = (array) json_decode( wp_unslash( $_REQUEST['_loaded_review_config'] ?? '' ), true );
			$export_review_config = function( $status ) use ( &$meta, $loaded_review_config, &$export_review_config ) {
				if (
					$status->get_feed() === 'post_reviews'
					&& ( $post = $status->get_post() )
					&& $post->post_type
					&& ! isset( $meta['review_config'][ $post->post_type->get_key() ] )
					&& ! in_array( $post->post_type->get_key(), $loaded_review_config, true )
				) {
					$meta['review_config'][ $post->post_type->get_key() ] = $post->post_type->reviews->get_timeline_config();
				}

				if ( $repost_of = $status->get_repost_of() ) {
					$export_review_config( $repost_of );
				}

				if ( $quote_of = $status->get_quote_of() ) {
					$export_review_config( $quote_of );
				}
			};

			$data = array_map( function( $status ) use ( $export_review_config, $mode ) {
				$export_review_config( $status );

				$config = $status->get_frontend_config();

				// visibility checks for single_status mode
				if ( $mode === 'single_status' && ! $status->is_viewable_by_current_user() ) {
					$config['private'] = true;
					$config['edited_at'] = null;
					$config['content'] = '';
					$config['files'] = [];
					$config['link_preview'] = null;
					$config['annotation'] = null;
					$config['likes']['count'] = 0;
					$config['likes']['last3'] = [];
					$config['replies']['count'] = 0;
					$config['quote_of'] = null;
					$config['review'] = null;
				}

				return $config;
			}, $statuses );

			return wp_send_json( [
				'success' => true,
				'data' => $data,
				'has_more' => $has_more,
				'meta' => $meta,
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
