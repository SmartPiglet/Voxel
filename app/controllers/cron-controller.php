<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cron_Controller extends Base_Controller {

	protected function hooks() {
		$this->on( 'init', '@schedule_cron_jobs' );
		$this->on( 'init', '@register_background_processes' );
		$this->on( 'voxel/schedule:daily', '@cleanup_notifications' );
		$this->on( 'voxel/schedule:daily', '@cleanup_orders' );
		$this->on( 'voxel/schedule:daily', '@cleanup_auth_codes' );
		$this->on( 'voxel/schedule:daily', '@cleanup_visits' );
		$this->on( 'voxel/schedule:cleanup_messages', '@cleanup_messages' );
		$this->on( 'voxel/schedule:check_for_expired_posts', '@check_for_expired_posts' );
		$this->on( 'voxel/schedule:check_for_expired_promotions', '@check_for_expired_promotions' );
	}

	protected function schedule_cron_jobs() {
		if ( ! wp_next_scheduled( 'voxel/schedule:daily' ) ) {
			wp_schedule_event( time(), 'daily', 'voxel/schedule:daily' );
		}

		if ( ! wp_next_scheduled( 'voxel/schedule:cleanup_messages' ) ) {
			wp_schedule_event( time(), 'daily', 'voxel/schedule:cleanup_messages' );
		}

		if ( ! wp_next_scheduled( 'voxel/schedule:check_for_expired_posts' ) ) {
			wp_schedule_event(
				time(),
				apply_filters( 'voxel/check_for_expired_posts/frequency', 'twicedaily' ),
				'voxel/schedule:check_for_expired_posts'
			);
		}

		if ( ! wp_next_scheduled( 'voxel/schedule:check_for_expired_promotions' ) ) {
			wp_schedule_event(
				time(),
				apply_filters( 'voxel/check_for_expired_promotions/frequency', 'twicedaily' ),
				'voxel/schedule:check_for_expired_promotions'
			);
		}
	}

	protected function register_background_processes() {
		\Voxel\Utils\Async_Requests\Async_Email::instance();
		\Voxel\Utils\Async_Requests\Email_Queue::instance();
		\Voxel\Utils\Async_Requests\Sync_Order::instance();
	}

	protected function cleanup_notifications() {
		global $wpdb;

		$persist_days = absint( \Voxel\get( 'settings.notifications.inapp_persist_days', 30 ) );
		if ( $persist_days ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}voxel_notifications WHERE created_at < %s",
				date( 'Y-m-d H:i:s', strtotime( '-'.$persist_days.' days' ) )
			) );
		}
	}

	protected function cleanup_orders() {
		global $wpdb;

		$persist_hours = absint( apply_filters( 'voxel/orders/pending-payment-persist-hours', 14 * 24 ) );
		$wpdb->query( $wpdb->prepare( <<<SQL
			DELETE FROM {$wpdb->prefix}vx_orders
			WHERE `status` = 'pending_payment'
				AND `transaction_id` IS NULL
				AND `created_at` < %s
		SQL, date( 'Y-m-d H:i:s', strtotime( '-'.$persist_hours.' hours' ) ) ) );
	}

	protected function cleanup_auth_codes() {
		global $wpdb;

		$persist_hours = 12;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}voxel_auth_codes WHERE `created_at` < %s",
			date( 'Y-m-d H:i:s', strtotime( '-'.$persist_hours.' hours' ) )
		) );
	}

	protected function cleanup_visits() {
		global $wpdb;

		$persist_days = absint( \Voxel\get( 'settings.stats.db_ttl', 90 ) );
		if ( $persist_days >= 1 ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}voxel_visits WHERE created_at < %s",
				date( 'Y-m-d H:i:s', strtotime( '-'.$persist_days.' days' ) )
			) );
		}
	}

	protected function cleanup_messages() {
		global $wpdb;

		// remove old messages
		$persist_days = absint( \Voxel\get( 'settings.messages.persist_days', 365 ) );
		if ( $persist_days ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}voxel_messages WHERE created_at < %s",
				date( 'Y-m-d H:i:s', strtotime( '-'.$persist_days.' days' ) )
			) );
		}

		// remove messages with a null sender/receiver
		$wpdb->query( <<<SQL
			DELETE messages FROM {$wpdb->prefix}voxel_messages AS messages
				LEFT JOIN {$wpdb->users} AS sender_user ON ( messages.sender_type = 'user' AND messages.sender_id = sender_user.ID )
				LEFT JOIN {$wpdb->users} AS receiver_user ON ( messages.receiver_type = 'user' AND messages.receiver_id = receiver_user.ID )
				LEFT JOIN {$wpdb->posts} AS sender_post ON ( messages.sender_type = 'post' AND messages.sender_id = sender_post.ID )
				LEFT JOIN {$wpdb->posts} AS receiver_post ON ( messages.receiver_type = 'post' AND messages.receiver_id = receiver_post.ID )
			WHERE
				( messages.sender_type = 'post' AND sender_post.ID IS NULL )
				OR ( messages.receiver_type = 'post' AND receiver_post.ID IS NULL )
				OR ( messages.sender_type = 'user' AND sender_user.ID IS NULL )
				OR ( messages.receiver_type = 'user' AND receiver_user.ID IS NULL )
		SQL );

		// remove chats with a null sender/receiver
		$wpdb->query( <<<SQL
			DELETE chats FROM {$wpdb->prefix}voxel_chats AS chats
				LEFT JOIN {$wpdb->users} AS p1_user ON ( chats.p1_type = 'user' AND chats.p1_id = p1_user.ID )
				LEFT JOIN {$wpdb->users} AS p2_user ON ( chats.p2_type = 'user' AND chats.p2_id = p2_user.ID )
				LEFT JOIN {$wpdb->posts} AS p1_post ON ( chats.p1_type = 'post' AND chats.p1_id = p1_post.ID )
				LEFT JOIN {$wpdb->posts} AS p2_post ON ( chats.p2_type = 'post' AND chats.p2_id = p2_post.ID )
			WHERE
				( chats.p1_type = 'post' AND p1_post.ID IS NULL )
				OR ( chats.p2_type = 'post' AND p2_post.ID IS NULL )
				OR ( chats.p1_type = 'user' AND p1_user.ID IS NULL )
				OR ( chats.p2_type = 'user' AND p2_user.ID IS NULL )
		SQL );

		// remove follows with a null sender/receiver
		$wpdb->query( <<<SQL
			DELETE followers FROM {$wpdb->prefix}voxel_followers AS followers
				LEFT JOIN {$wpdb->users} AS user ON ( followers.object_type = 'user' AND followers.object_id = user.ID )
				LEFT JOIN {$wpdb->users} AS follower_user ON ( followers.follower_type = 'user' AND followers.follower_id = follower_user.ID )
				LEFT JOIN {$wpdb->posts} AS post ON ( followers.object_type = 'post' AND followers.object_id = post.ID )
				LEFT JOIN {$wpdb->posts} AS follower_post ON ( followers.follower_type = 'post' AND followers.follower_id = follower_post.ID )
			WHERE
				( followers.object_type = 'post' AND post.ID IS NULL )
				OR ( followers.follower_type = 'post' AND follower_post.ID IS NULL )
				OR ( followers.object_type = 'user' AND user.ID IS NULL )
				OR ( followers.follower_type = 'user' AND follower_user.ID IS NULL )
		SQL );
	}

	protected function check_for_expired_posts() {
		global $wpdb;

		$expired_posts_map = [];

		// posts expired by custom expiry date
		$expired_ids = $wpdb->get_col(
			$wpdb->prepare( <<<SQL
				SELECT ID FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON ( p.ID = pm.post_id )
				WHERE post_status IN ('publish','unpublished')
					AND pm.meta_key IN ('voxel:expiry_date')
					AND pm.meta_value < %s
			SQL, current_time( 'mysql' ) )
		);

		foreach ( $expired_ids as $post_id ) {
			$expired_posts_map[ absint( $post_id ) ] = absint( $post_id );
		}

		// run through post type expiration rules
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$rules = $post_type->repository->get_expiration_rules();
			if ( empty( $rules ) ) {
				continue;
			}

			foreach ( $rules as $rule ) {
				if ( $rule['type'] === 'fixed' ) {
					$expired_ids = $wpdb->get_col(
						$wpdb->prepare( <<<SQL
							SELECT p.ID FROM {$wpdb->posts} AS p
							LEFT JOIN {$wpdb->postmeta} AS pm_expiry ON ( p.ID = pm_expiry.post_id AND pm_expiry.meta_key = 'voxel:expiry_date' )
							WHERE p.post_type = %s AND p.post_status IN ('publish','unpublished') AND pm_expiry.meta_value IS NULL
								AND ( DATE_ADD( p.post_date_gmt, INTERVAL %d DAY ) < %s )
						SQL, $post_type->get_key(), absint( $rule['amount'] ), date( 'Y-m-d H:i:s', time() ) )
					);

					foreach ( $expired_ids as $post_id ) {
						$expired_posts_map[ absint( $post_id ) ] = absint( $post_id );
					}
				} elseif ( $rule['type'] === 'field' ) {
					$field = $post_type->get_field( $rule['field'] );

					if ( $field->get_type() === 'date' ) {
						$expired_ids = $wpdb->get_col(
							$wpdb->prepare( <<<SQL
								SELECT p.ID FROM {$wpdb->posts} AS p
								LEFT JOIN {$wpdb->postmeta} AS pm_expiry ON ( p.ID = pm_expiry.post_id AND pm_expiry.meta_key = 'voxel:expiry_date' )
								INNER JOIN {$wpdb->postmeta} AS pm ON ( p.ID = pm.post_id )
								WHERE p.post_type = %s AND p.post_status IN ('publish','unpublished') AND pm_expiry.meta_value IS NULL
									AND pm.meta_key = %s AND pm.meta_value < %s
							SQL, $post_type->get_key(), $field->get_key(), current_time( 'mysql' ) )
						);

						foreach ( $expired_ids as $post_id ) {
							$expired_posts_map[ absint( $post_id ) ] = absint( $post_id );
						}
					} elseif ( $field->get_type() === 'recurring-date' ) {
						$expired_ids = $wpdb->get_col(
							$wpdb->prepare( <<<SQL
								SELECT r.post_id, MAX( IFNULL( r.until, r.end ) ) AS finish_date
								FROM {$wpdb->prefix}voxel_recurring_dates AS r
								LEFT JOIN {$wpdb->posts} AS p ON ( r.post_id = p.ID )
								LEFT JOIN {$wpdb->postmeta} AS pm_expiry ON ( r.post_id = pm_expiry.post_id AND pm_expiry.meta_key = 'voxel:expiry_date' )
								WHERE r.post_type = %s AND r.field_key = %s AND p.post_status IN ('publish','unpublished') AND pm_expiry.meta_value IS NULL
								GROUP BY r.post_id
								HAVING finish_date < %s
							SQL, $post_type->get_key(), $field->get_key(), current_time( 'mysql' ) )
						);

						foreach ( $expired_ids as $post_id ) {
							$expired_posts_map[ absint( $post_id ) ] = absint( $post_id );
						}
					}
				}
			}
		}

		// run through all found post ids and change status to expired
		foreach ( $expired_posts_map as $post_id ) {
			wp_update_post( [
				'ID' => $post_id,
				'post_status' => 'expired',
			] );
		}
	}

	protected function check_for_expired_promotions() {
		global $wpdb;

		$now = \Voxel\now()->format( 'Y-m-d H:i:s' );

		// grab affected post ids first
		$post_ids = $wpdb->get_col( $wpdb->prepare( <<<SQL
			SELECT post_id FROM {$wpdb->postmeta}
			WHERE meta_key = 'voxel:promotion'
			AND JSON_UNQUOTE( JSON_EXTRACT( meta_value, '$.end_date' ) ) < %s
		SQL, $now ) );

		// delete promotion meta
		$wpdb->query( $wpdb->prepare( <<<SQL
			DELETE FROM {$wpdb->postmeta}
			WHERE meta_key = 'voxel:promotion'
			AND JSON_UNQUOTE( JSON_EXTRACT( meta_value, '$.end_date' ) ) < %s
		SQL, $now ) );

		// update index
		$post_ids = array_map( 'absint', $post_ids );
		if ( ! empty( $post_ids ) ) {
			_prime_post_caches( $post_ids );
			foreach ( $post_ids as $post_id ) {
				$post = \Voxel\Post::get( $post_id );
				if ( $post ) {
					$post->should_index() ? $post->index() : $post->unindex();
				}
			}
		}
	}
}
