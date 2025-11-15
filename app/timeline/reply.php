<?php

namespace Voxel\Timeline;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Reply {
	use Reply\Reply_Query_Trait;
	use Reply\Reply_Repository_Trait;

	private
		$id,
		$user_id,
		$published_as,
		$status_id,
		$parent_id,
		$content,
		$details = [],
		$moderation,
		$created_at,
		$edited_at,
		$like_count,
		$reply_count,
		$liked_by_user,
		$last3_liked = [];

	public function __construct( array $data ) {
		$this->id = absint( $data['id'] ?? null );
		$this->user_id = is_numeric( $data['user_id'] ?? null ) ? absint( $data['user_id'] ) : null;
		$this->published_as = is_numeric( $data['published_as'] ?? null ) ? absint( $data['published_as'] ) : null;
		$this->status_id = absint( $data['status_id'] ?? null );
		$this->parent_id = is_numeric( $data['parent_id'] ?? null ) ? absint( $data['parent_id'] ) : null;
		$this->content = is_string( $data['content'] ?? null ) ? $data['content'] : '';

		if ( is_string( $data['details'] ?? null ) ) {
			$details = json_decode( $data['details'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$this->details = $details;
			}
		} elseif ( is_array( $data['details'] ?? null ) ) {
			$this->details = $data['details'];
		}

		if ( $created_at = strtotime( $data['created_at'] ?? '' ) ) {
			$this->created_at = date( 'Y-m-d H:i:s', $created_at );
		}

		if ( $edited_at = strtotime( $data['edited_at'] ?? '' ) ) {
			$this->edited_at = date( 'Y-m-d H:i:s', $edited_at );
		}

		$this->moderation = is_numeric( $data['moderation'] ?? null ) ? (int) $data['moderation'] : 0;
		$this->like_count = absint( $data['like_count'] ?? 0 );
		$this->reply_count = absint( $data['reply_count'] ?? 0 );
		$this->liked_by_user = !! ( $data['liked_by_user'] ?? false );

		if ( is_string( $data['last3_liked'] ?? null ) ) {
			$last3_liked = json_decode( $data['last3_liked'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$this->last3_liked = $last3_liked;
			}
		}
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_link(): string {
		return add_query_arg( [
			'status_id' => $this->get_status_id(),
			'reply_id' => $this->get_id(),
		], get_permalink( \Voxel\get( 'templates.timeline' ) ) );
	}

	public function get_user_id(): ?int {
		return $this->user_id;
	}

	public function get_user(): ?\Voxel\User {
		return \Voxel\User::get( $this->get_user_id() );
	}

	public function get_published_as_id(): ?int {
		return $this->published_as;
	}

	public function get_published_as(): ?\Voxel\Post {
		return \Voxel\Post::get( $this->get_published_as_id() );
	}

	public function get_status_id(): int {
		return $this->status_id;
	}

	public function get_status(): \Voxel\Timeline\Status {
		return \Voxel\Timeline\Status::get( $this->get_status_id() );
	}

	public function get_parent_id(): ?int {
		return $this->parent_id;
	}

	public function get_parent(): ?self {
		if ( $this->get_parent_id() ) {
			return \Voxel\Timeline\Reply::get( $this->get_parent_id() );
		}

		return null;
	}

	public function get_content(): string {
		return $this->content;
	}

	public function get_content_for_display(): string {
		return \Voxel\text_formatter()->format( $this->content );
	}

	public function get_details(): array {
		return (array) $this->details;
	}

	public function get_moderation_status(): int {
		return $this->moderation;
	}

	public function get_created_at() {
		return $this->created_at;
	}

	public function get_time_for_display() {
		$from = strtotime( $this->created_at ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$to = current_time( 'timestamp' );

		return \Voxel\minimal_time_diff( $from, $to );
	}

	public function get_edit_time_for_display() {
		if ( ! ( $edited_at = strtotime( $this->edited_at ?? '' ) ) ) {
			return null;
		}

		return \Voxel\datetime_format(
			$edited_at + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )
		);
	}

	public function get_like_count(): int {
		return $this->like_count;
	}

	public function get_reply_count(): int {
		return $this->reply_count;
	}

	public function liked_by_user(): bool {
		return !! $this->liked_by_user;
	}

	public function is_liked_by_current_user(): bool {
		return !! $this->liked_by_user;
	}

	public function _update_reply_count(): void {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}voxel_timeline_replies AS r
			JOIN (
				SELECT COUNT(*) AS reply_count FROM {$wpdb->prefix}voxel_timeline_replies
				WHERE parent_id = %d AND moderation = 1
			) AS subquery
			SET r.reply_count = subquery.reply_count
			WHERE r.id = %d",
			$this->get_id(),
			$this->get_id()
		) );
	}

	protected function _update_like_count(): void {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}voxel_timeline_replies AS r
				SET r.like_count = ( SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline_reply_likes_v2 AS l WHERE l.reply_id = %d )
				WHERE r.id = %d",
			$this->get_id(),
			$this->get_id()
		) );
	}

	public function like( $user_id = null ) {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}voxel_timeline_reply_likes_v2 (`user_id`, `reply_id`) VALUES (%d, %d)",
				$user_id,
				$this->get_id()
			) );

			$this->_update_like_count();
		}
	}

	public function unlike( $user_id = null ) {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}voxel_timeline_reply_likes_v2 WHERE `user_id` = %d AND `reply_id` = %d",
				$user_id,
				$this->get_id()
			) );

			$this->_update_like_count();
		}
	}

	public function is_editable_by_current_user(): bool {
		$published_as = $this->get_published_as();
		if ( $published_as !== null ) {
			return $published_as->is_editable_by_current_user();
		}

		return absint( $this->get_user_id() ) === absint( get_current_user_id() );
	}

	public function is_moderatable_by_current_user(): bool {
		return $this->get_status()->is_moderatable_by_current_user();
	}

	public function is_moderatable_by_user( \Voxel\User $user ): bool {
		return $this->get_status()->is_moderatable_by_user( $user );
	}

	public function is_viewable_by_current_user(): bool {
		return $this->get_status()->is_viewable_by_current_user();
	}

	/**
	 * Count the number of ancestor replies to determine the reply depth.
	 *
	 * @link https://stackoverflow.com/questions/20215744/how-to-create-a-mysql-hierarchical-recursive-query
	 * @since 1.2.9
	 */
	public function get_depth(): int {
		if ( $this->get_parent_id() === null ) {
			return 1;
		}

		global $wpdb;

		$depth = $wpdb->get_var( $wpdb->prepare( <<<SQL
			WITH RECURSIVE cte ( `parent_id` ) AS (
				SELECT `parent_id`
					FROM `{$wpdb->prefix}voxel_timeline_replies`
					WHERE `id` = %d
				UNION
				SELECT p.`parent_id`
					FROM `{$wpdb->prefix}voxel_timeline_replies` p
					INNER JOIN cte ON p.`id` = cte.`parent_id`
			)
			SELECT COUNT(*) FROM cte;
		SQL, $this->get_id() ) );

		return absint( $depth );
	}

	private function _maybe_update_stats_cache() {
		$status = $this->get_status();
		$feed = $status->get_feed();
		if ( $post_id = $status->get_post_id() ) {
			if ( $feed === 'post_reviews' ) {
				\Voxel\cache_post_review_reply_stats( $post_id );
			} elseif ( $feed === 'post_timeline' ) {
				\Voxel\cache_post_timeline_reply_stats( $post_id );
			} elseif ( $feed === 'post_wall' ) {
				\Voxel\cache_post_wall_reply_stats( $post_id );
			}
		}
	}

	public function get_files(): array {
		$files = [];

		$file_ids = $this->details['files'] ?? null;
		if ( ! is_string( $file_ids ) ) {
			return $files;
		}

		$file_ids = explode( ',', $file_ids );
		$file_ids = array_filter( array_map( 'absint', $file_ids ) );

		foreach ( $file_ids as $file_id ) {
			if ( $file_url = wp_get_attachment_url( $file_id ) ) {
				$display_filename = get_post_meta( $file_id, '_display_filename', true );
				$files[] = [
					'source' => 'existing',
					'id' => $file_id,
					'name' => ! empty( $display_filename ) ? $display_filename : wp_basename( get_attached_file( $file_id ) ),
					'alt' => get_post_meta( $file_id, '_wp_attachment_image_alt', true ),
					'url' => $file_url,
					'preview' => wp_get_attachment_image_url( $file_id, 'large' ),
					'type' => get_post_mime_type( $file_id ),
				];
			}
		}

		return $files;
	}

	/**
	 * @return \Voxel\Post|\Voxel\User|null
	 */
	public function get_publisher() {
		if ( $published_as = $this->get_published_as() ) {
			return $published_as;
		} else {
			return $this->get_user();
		}
	}

	public function get_author(): ?\Voxel\User {
		$publisher = $this->get_publisher();
		if ( $publisher instanceof \Voxel\Post ) {
			return $publisher->get_author();
		} elseif ( $publisher instanceof \Voxel\User ) {
			return $publisher;
		} else {
			return null;
		}
	}

	public function get_author_id(): ?int {
		$author = $this->get_author();
		return $author ? $author->get_id() : null;
	}

	protected function get_mentions(): array {
		return \Voxel\text_formatter()->find_mentions( $this->content );
	}

	public function send_mention_notifications(): void {
		$author = $this->get_author();
		$max_count = absint( apply_filters( 'voxel/timeline/mentions/max-per-comment', 5 ) );
		$mentions = array_slice( $this->get_mentions(), 0, $max_count );
		foreach ( $mentions as $username ) {
			if ( $author !== null && $author->get_username() === $username ) {
				continue;
			}

			$wp_user = get_user_by( 'login', $username );
			if ( ! $wp_user ) {
				continue;
			}

			$user = \Voxel\User::get( $wp_user );
			( new \Voxel\Events\Timeline\Mentions\User_Mentioned_In_Comment_Event )->dispatch( $user->get_id(), $this->get_id() );
		}
	}

	public function get_badges(): array {
		$items = [];

		if ( $this->get_moderation_status() === \Voxel\MODERATION_PENDING ) {
			$items[] = [
				'key' => 'pending_approval',
				'label' => _x( 'Pending', 'timeline', 'voxel' ),
			];
		}

		return $items;
	}

	public function mark_approved(): void {
		if ( $this->get_moderation_status() === \Voxel\MODERATION_PENDING ) {
			$details = $this->get_details();
			$is_first_approval = ! isset( $details['approved_at'] );

			$details['approved_at'] = \Voxel\utc()->format('Y-m-d H:i:s');
			$this->update( [
				'moderation' => \Voxel\MODERATION_APPROVED,
				'details' => $details,
			] );

			$status = $this->get_status();
			$parent = $this->get_parent();

			$status->_update_reply_count();
			if ( $parent ) {
				$parent->_update_reply_count();
			}

			if ( $parent ) {
				( new \Voxel\Events\Timeline\Comments\Comment_Reply_Approved_Event )->dispatch( $this->get_id() );
			} else {
				( new \Voxel\Events\Timeline\Comments\Comment_Approved_Event )->dispatch( $this->get_id() );
			}

			if ( $is_first_approval ) {
				$this->send_mention_notifications();
			}
		}
	}

	public function get_frontend_config( array $options = [] ): array {
		$publisher = $this->get_publisher();
		$details = [
			'id' => $this->get_id(),
			'created_at' => $this->get_time_for_display(),
			'edited_at' => $this->get_edit_time_for_display(),
			'content' => $this->get_content(),
			'link' => $this->get_link(),
			'files' => $this->get_files(),
			'publisher' => $publisher ? $publisher->get_timeline_publisher_config() : [ 'exists' => false ],
			'is_pending' => $this->get_moderation_status() === \Voxel\MODERATION_PENDING,
			'badges' => $this->get_badges(),
			'current_user' => [
				'can_edit' => $this->is_editable_by_current_user(),
				'can_moderate' => $this->is_moderatable_by_current_user(),
				'can_delete' => $this->is_editable_by_current_user() || $this->is_moderatable_by_current_user(),
				'has_liked' => $this->is_liked_by_current_user(),
			],
			'likes' => [
				'count' => $this->get_like_count(),
				'last3' => array_filter( array_map( function( $like ) {
					$liked_by = isset( $like['post_id'] ) ? \Voxel\Post::get( $like['post_id'] ) : \Voxel\User::get( $like['user_id'] ?? null );
					if ( $liked_by !== null ) {
						return [
							'id' => $liked_by->get_id(),
							'type' => $liked_by->get_object_type(),
							'display_name' => $liked_by->get_display_name(),
							'link' => $liked_by->get_link(),
							'avatar_url' => $liked_by->get_avatar_url(),
						];
					} else {
						return null;
					}
				}, $this->last3_liked ) ),
			],
			'replies' => [
				'count' => $this->get_reply_count(),
			],
		];

		return $details;
	}

	public static function mock(): self {
		return new static( [
			'id' => null,
			'user_id' => null,
			'status_id' => null,
			'parent_id' => null,
			'details' => null,
			'created_at' => \Voxel\now()->format('Y-m-d H:i:s'),
			'edited_at' => null,
		] );
	}
}
