<?php

namespace Voxel\Timeline;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Status {
	use Status\Status_Repository_Trait;
	use Status\Status_Query_Trait;

	protected
		$id,
		$user_id,
		$post_id,
		$published_as,
		$content,
		$feed,
		$moderation,
		$repost_of,
		$quote_of,
		$created_at,
		$edited_at,
		$review_score,
		$like_count,
		$reply_count,
		$details = [],
		$liked_by_user = false,
		$reposted_by_user = false,
		$last3_liked = [];

	protected
		$friends_reposted = [],
		$friends_liked = [];

	protected function __construct( array $data ) {
		$this->id = absint( $data['id'] ?? null );
		$this->user_id = is_numeric( $data['user_id'] ?? null ) ? absint( $data['user_id'] ) : null;
		$this->post_id = is_numeric( $data['post_id'] ?? null ) ? absint( $data['post_id'] ) : null;
		$this->published_as = is_numeric( $data['published_as'] ?? null ) ? absint( $data['published_as'] ) : null;
		$this->content = is_string( $data['content'] ?? null ) ? $data['content'] : '';
		$this->feed = is_string( $data['feed'] ?? null ) ? $data['feed'] : null;
		$this->moderation = is_numeric( $data['moderation'] ?? null ) ? (int) $data['moderation'] : 0;
		$this->repost_of = is_numeric( $data['repost_of'] ?? null ) ? absint( $data['repost_of'] ) : null;
		$this->quote_of = is_numeric( $data['quote_of'] ?? null ) ? absint( $data['quote_of'] ) : null;
		$this->review_score = is_numeric( $data['review_score'] ?? null ) ? round( floatval( $data['review_score'] ), 2 ) : null;

		if ( $created_at = strtotime( $data['created_at'] ?? '' ) ) {
			$this->created_at = date( 'Y-m-d H:i:s', $created_at );
		}

		if ( $edited_at = strtotime( $data['edited_at'] ?? '' ) ) {
			$this->edited_at = date( 'Y-m-d H:i:s', $edited_at );
		}

		$this->like_count = absint( $data['like_count'] ?? 0 );
		$this->reply_count = absint( $data['reply_count'] ?? 0 );
		$this->liked_by_user = !! ( $data['liked_by_user'] ?? false );
		$this->reposted_by_user = !! ( $data['reposted_by_user'] ?? false );

		if ( is_string( $data['details'] ?? null ) ) {
			$details = json_decode( $data['details'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$this->details = $details;
			}
		} elseif ( is_array( $data['details'] ?? null ) ) {
			$this->details = $data['details'];
		}

		if ( is_string( $data['last3_liked'] ?? null ) ) {
			$last3_liked = json_decode( $data['last3_liked'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$this->last3_liked = $last3_liked;
			}
		}

		if ( ! empty( $data['friends_reposted'] ) && is_string( $data['friends_reposted'] ) ) {
			$this->friends_reposted = array_map( 'absint', explode(',', $data['friends_reposted']) );
		}

		if ( ! empty( $data['friends_liked'] ) && is_string( $data['friends_liked'] ) ) {
			$this->friends_liked = array_map( 'absint', explode(',', $data['friends_liked']) );
		}
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_link(): string {
		return add_query_arg(
			'status_id',
			$this->get_id(),
			get_permalink( \Voxel\get( 'templates.timeline' ) )
		);
	}

	public function get_user_id(): ?int {
		return $this->user_id;
	}

	public function get_user(): ?\Voxel\User {
		return \Voxel\User::get( $this->get_user_id() );
	}

	public function get_post_id(): ?int {
		return $this->post_id;
	}

	public function get_post(): ?\Voxel\Post {
		return \Voxel\Post::get( $this->get_post_id() );
	}

	public function get_published_as_id(): ?int {
		return $this->published_as;
	}

	public function get_published_as(): ?\Voxel\Post {
		return \Voxel\Post::get( $this->get_published_as_id() );
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

	/**
	 * @return \Voxel\Post|\Voxel\User|null
	 */
	public function get_publisher() {
		if ( $this->get_feed() === 'post_timeline' ) {
			return $this->get_post();
		} elseif ( $published_as = $this->get_published_as() ) {
			return $published_as;
		} else {
			return $this->get_user();
		}
	}

	public function get_repost_of_id(): ?int {
		return $this->repost_of;
	}

	public function get_repost_of(): ?self {
		return static::get( $this->get_repost_of_id() );
	}

	public function get_quote_of_id(): ?int {
		return $this->quote_of;
	}

	public function get_quote_of(): ?self {
		return static::get( $this->get_quote_of_id() );
	}

	public function get_content(): string {
		return $this->content;
	}

	public function get_feed(): ?string {
		return $this->feed;
	}

	public function get_moderation_status(): int {
		return $this->moderation;
	}

	public function get_content_for_display(): string {
		return \Voxel\text_formatter()->format( $this->content );
	}

	public function get_details(): array {
		return (array) $this->details;
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

	public function get_link_preview(): ?array {
		$link_preview = $this->details['link_preview'] ?? null;
		if ( ! $link_preview ) {
			return null;
		}

		// Handle YouTube links
		if ( isset( $link_preview['type'] ) && $link_preview['type'] === 'youtube' ) {
			return [
				'type' => 'youtube',
				'url' => $link_preview['url'],
				'video_id' => $link_preview['video_id'],
				'embed_url' => $link_preview['embed_url'],
				'title' => null,
				'image' => null,
			];
		}

		// Handle regular links
		$link_url = $link_preview['url'] ?? null;
		$link_title = $link_preview['title'] ?? null;
		$link_image = $link_preview['image'] ?? null;

		if ( ! ( is_string( $link_title ) && is_string( $link_url ) ) ) {
			return null;
		}

		$domain = parse_url( $link_url, PHP_URL_HOST );
		if ( $domain === null ) {
			return null;
		}

		return [
			'url' => $link_url,
			'domain' => $domain,
			'title' => html_entity_decode( $link_title ),
			'image' => is_string( $link_image ) ? html_entity_decode( $link_image ) : null,
		];
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

	public function get_review_score(): ?float {
		return $this->review_score;
	}

	public function get_review_score_for_display(): ?string {
		if ( $this->review_score === null ) {
			return null;
		}

		$score = \Voxel\clamp( $this->review_score, -2, 2 );
		$score = round( $score + 3, 1 );
		return number_format_i18n( $score, 1 );
	}

	public function get_review_rating(): ?array {
		if ( $this->review_score === null ) {
			return null;
		}

		$rating = $this->details['rating'] ?? null;
		if ( ! is_array( $rating ) ) {
			return null;
		}

		return $rating;
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

	public function is_reposted_by_current_user(): bool {
		return !! $this->reposted_by_user;
	}

	public function _update_reply_count(): void {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}voxel_timeline AS t
				SET t.reply_count = ( SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline_replies AS r WHERE r.status_id = %d AND r.moderation = 1 )
				WHERE t.id = %d",
			$this->get_id(),
			$this->get_id()
		) );
	}

	public function _update_like_count(): void {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}voxel_timeline AS t
				SET t.like_count = ( SELECT COUNT(*) FROM {$wpdb->prefix}voxel_timeline_status_likes AS l WHERE l.status_id = %d )
				WHERE t.id = %d",
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
				"INSERT INTO {$wpdb->prefix}voxel_timeline_status_likes (`user_id`, `status_id`) VALUES (%d, %d)",
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
				"DELETE FROM {$wpdb->prefix}voxel_timeline_status_likes WHERE `user_id` = %d AND `status_id` = %d",
				$user_id,
				$this->get_id()
			) );

			$this->_update_like_count();
		}
	}

	public function is_editable_by_current_user(): bool {
		$feed = $this->get_feed();

		if ( $feed === 'post_timeline' ) {
			$post = $this->get_post();
			if ( $post ) {
				return $post->is_editable_by_current_user();
			} else {
				return false;
			}
		} else {
			$published_as = $this->get_published_as();
			if ( $published_as !== null ) {
				return $published_as->is_editable_by_current_user();
			}

			return absint( $this->get_user_id() ) === absint( get_current_user_id() );
		}
	}

	public function is_moderatable_by_current_user(): bool {
		if ( current_user_can('administrator') || current_user_can('editor') ) {
			return true;
		}

		$feed = $this->get_feed();

		if ( $feed === 'post_reviews' || $feed === 'post_wall' ) {
			$post = $this->get_post();
			if ( $post && $post->post_type ) {
				$settings = $post->post_type->timeline->get_moderation_settings();
				$post_author_can_edit = !! ( $settings[ $feed ]['moderators']['post_author'] ?? false );
				if ( $post_author_can_edit && $post->is_editable_by_current_user() ) {
					return true;
				}
			}
		}

		return false;
	}

	public function is_moderatable_by_user( \Voxel\User $user ): bool {
		if ( $user->has_cap('administrator') || $user->has_cap('editor') ) {
			return true;
		}

		$feed = $this->get_feed();

		if ( $feed === 'post_reviews' || $feed === 'post_wall' ) {
			$post = $this->get_post();
			if ( $post && $post->post_type ) {
				$settings = $post->post_type->timeline->get_moderation_settings();
				$post_author_can_edit = !! ( $settings[ $feed ]['moderators']['post_author'] ?? false );
				if ( $post_author_can_edit && $post->is_editable_by_user( $user ) ) {
					return true;
				}
			}
		}

		return false;
	}

	public function is_repostable_by_current_user(): bool {
		return (
			$this->is_viewable_by_current_user()
			&& $this->get_repost_of_id() === null
			&& $this->get_moderation_status() === \Voxel\MODERATION_APPROVED
		);
	}

	public function is_quotable_by_current_user(): bool {
		return (
			$this->is_viewable_by_current_user()
			&& $this->get_moderation_status() === \Voxel\MODERATION_APPROVED
		);
	}

	public function is_viewable_by_current_user(): bool {
		if ( $this->get_moderation_status() !== \Voxel\MODERATION_APPROVED ) {
			return ( $this->is_editable_by_current_user() || $this->is_moderatable_by_current_user() );
		}

		$current_user = \Voxel\get_current_user();
		$feed = $this->get_feed();

		if ( $feed === 'user_timeline' ) {
			$user = $this->get_user();
			if ( ! $user ) {
				return false;
			}

			$visibility = \Voxel\get( 'settings.timeline.user_timeline.visibility', 'public' );

			if ( $visibility === 'logged_in' ) {
				return !! is_user_logged_in();
			} elseif ( $visibility === 'followers_only' ) {
				if ( ! is_user_logged_in() ) {
					return false;
				}

				if ( $current_user->get_id() === $user->get_id() ) {
					return true;
				}

				return $current_user->follows_user( $user->get_id() );
			} elseif ( $visibility === 'customers_only' ) {
				if ( ! is_user_logged_in() ) {
					return false;
				}

				if ( $current_user->get_id() === $user->get_id() ) {
					return true;
				}

				if ( $user->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
					return $current_user->has_bought_product_from_platform();
				}

				return $current_user->has_bought_product_from_vendor( $user->get_id() );
			} elseif ( $visibility === 'private' ) {
				return is_user_logged_in() && $current_user->get_id() === $user->get_id();
			} else /* $visibility === 'public' */ {
				return true;
			}
		} elseif ( in_array( $feed, [ 'post_timeline', 'post_reviews', 'post_wall' ], true ) ) {
			$post = $this->get_post();
			if ( ! ( $post && $post->post_type ) ) {
				return false;
			}

			if ( $feed === 'post_reviews' ) {
				$visibility = $post->post_type->get_setting( 'timeline.review_visibility' );
			} elseif ( $feed === 'post_wall' ) {
				$visibility = $post->post_type->get_setting( 'timeline.wall_visibility' );
			} else {
				$visibility = $post->post_type->get_setting( 'timeline.visibility' );
			}

			if ( $visibility === 'logged_in' ) {
				return !! is_user_logged_in();
			} elseif ( $visibility === 'followers_only' ) {
				if ( ! is_user_logged_in() ) {
					return false;
				}

				if ( $current_user->get_id() === $post->get_author_id() ) {
					return true;
				}

				return $current_user->follows_post( $post->get_id() );
			} elseif ( $visibility === 'customers_only' ) {
				if ( ! is_user_logged_in() ) {
					return false;
				}

				if ( $current_user->get_id() === $post->get_author_id() ) {
					return true;
				}

				return $current_user->has_bought_product( $post->get_id() );
			} elseif ( $visibility === 'private' ) {
				return is_user_logged_in() && $post->is_editable_by_current_user();
			} else /* $visibility === 'public' */ {
				return true;
			}
		} else {
			// unsupported feed type
			return false;
		}
	}

	public function generate_link_preview() {
		$details = $this->get_details();
		if ( ! empty( $details['files'] ) ) {
			return null;
		}

		$first_link = \Voxel\text_formatter()->find_first_link( (string) $this->get_content() );
		if ( $first_link === null ) {
			return null;
		}

		$link_details = \Voxel\link_previewer()->preview( $first_link );
		if ( $link_details === null ) {
			return null;
		}

		// For YouTube links, we don't need a title
		if ( $link_details['type'] === 'youtube' ) {
			$this->details['link_preview'] = $details['link_preview'] = [
				'type' => 'youtube',
				'url' => $link_details['url'],
				'video_id' => $link_details['video_id'],
				'embed_url' => $link_details['embed_url'],
				'title' => null,
				'image' => null,
			];
		} else {
			// For regular links, we need a title
			if ( $link_details['title'] === null || $link_details['url'] === null ) {
				return null;
			}

			$this->details['link_preview'] = $details['link_preview'] = [
				'url' => $link_details['url'],
				'title' => $link_details['title'],
				'image' => $link_details['image'],
			];
		}

		$this->update( 'details', $details );
	}

	protected function _maybe_update_stats_cache() {
		$feed = $this->get_feed();
		$post_id = $this->get_post_id();
		if ( $feed === 'post_reviews' ) {
			if ( $post_id !== null ) {
				\Voxel\cache_post_review_stats( $post_id );
			}
		} elseif ( $feed === 'post_wall' ) {
			if ( $post_id !== null ) {
				\Voxel\cache_post_wall_stats( $post_id );
			}
		} elseif ( $feed === 'post_timeline' ) {
			if ( $post_id !== null ) {
				\Voxel\cache_post_timeline_stats( $post_id );
			}
		} elseif ( $feed === 'user_timeline' ) {
			$user_id = $this->get_user_id();
			if ( $user_id !== null ) {
				\Voxel\Timeline\cache_user_timeline_stats( $user_id );
			}
		} else {
			//
		}
	}

	protected function get_annotation( string $timeline_mode ): ?array {
		$text = null;
		$icon = null;
		if ( ! empty( $this->friends_reposted ) && ( count( $this->friends_reposted ) * 2 ) >= count( $this->friends_liked ) ) {
			$list = $this->friends_reposted;
			if ( count( $list ) === 1 ) {
				if ( $user = \Voxel\User::get( $list[0] ) ) {
					$icon = 'icon-repost';
					$text = sprintf( _x( '%s reposted', 'timeline', 'voxel' ), $user->get_display_name() );
				}
			} elseif ( count( $list ) === 2 ) {
				$users = \Voxel\User::query( [
					'id' => [ $list[0], $list[1] ],
				] );
				if ( count( $users ) === 2 ) {
					$icon = 'icon-repost';
					$text = sprintf(
						_x( '%s and %s reposted', 'timeline', 'voxel' ),
						$users[0]->get_display_name(),
						$users[1]->get_display_name()
					);
				}
			} else {
				if ( $user = \Voxel\User::get( $list[0] ) ) {
					$icon = 'icon-repost';
					if ( count( $list ) < 11 ) {
						$text = sprintf(
							_x( '%s and %d others reposted', 'timeline', 'voxel' ),
							$user->get_display_name(),
							count( $list ) - 1
						);
					} else {
						$text = sprintf( _x( '%s and 10+ others reposted', 'timeline', 'voxel' ), $user->get_display_name() );
					}
				}
			}
		} elseif ( ! empty( $this->friends_liked ) ) {
			$list = $this->friends_liked;
			if ( count( $list ) === 1 ) {
				if ( $user = \Voxel\User::get( $list[0] ) ) {
					$icon = 'icon-liked';
					$text = sprintf( _x( '%s liked', 'timeline', 'voxel' ), $user->get_display_name() );
				}
			} elseif ( count( $list ) === 2 ) {
				$users = \Voxel\User::query( [
					'id' => [ $list[0], $list[1] ],
				] );
				if ( count( $users ) === 2 ) {
					$icon = 'icon-liked';
					$text = sprintf(
						_x( '%s and %s liked', 'timeline', 'voxel' ),
						$users[0]->get_display_name(),
						$users[1]->get_display_name()
					);
				}
			} else {
				if ( $user = \Voxel\User::get( $list[0] ) ) {
					$icon = 'icon-liked';
					if ( count( $list ) < 11 ) {
						$text = sprintf(
							_x( '%s and %d others liked', 'timeline', 'voxel' ),
							$user->get_display_name(),
							count( $list ) - 1
						);
					} else {
						$text = sprintf( _x( '%s and 10+ others liked', 'timeline', 'voxel' ), $user->get_display_name() );
					}
				}
			}
		}

		if ( $text === null || $icon === null ) {
			return null;
		}

		return [
			'text' => $text,
			'icon' => $icon,
		];
	}

	protected function get_last3_likes_frontend_config(): array {
		return array_filter( array_map( function( $like ) {
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
		}, $this->last3_liked ) );
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

	protected function get_mentions(): array {
		return \Voxel\text_formatter()->find_mentions( $this->content );
	}

	public function send_mention_notifications(): void {
		$author = $this->get_author();
		$max_count = absint( apply_filters( 'voxel/timeline/mentions/max-per-post', 5 ) );
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
			( new \Voxel\Events\Timeline\Mentions\User_Mentioned_In_Post_Event )->dispatch( $user->get_id(), $this->get_id() );
		}
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

			$post = $this->get_post();
			$feed = $this->get_feed();

			if ( $feed === 'post_reviews' ) {
				if ( $post ) {
					( new \Voxel\Events\Timeline\Statuses\Post_Reviews_Status_Approved_Event( $post->post_type ) )->dispatch( $this->get_id() );
				}
			} elseif ( $feed === 'post_wall' ) {
				if ( $post ) {
					( new \Voxel\Events\Timeline\Statuses\Post_Wall_Status_Approved_Event( $post->post_type ) )->dispatch( $this->get_id() );
				}
			} elseif ( $feed === 'post_timeline' ) {
				if ( $post ) {
					( new \Voxel\Events\Timeline\Statuses\Post_Timeline_Status_Approved_Event( $post->post_type ) )->dispatch( $this->get_id() );
				}
			} elseif ( $feed === 'user_timeline' ) {
				( new \Voxel\Events\Timeline\Statuses\User_Timeline_Status_Approved_Event )->dispatch( $this->get_id() );

				if ( $this->get_quote_of_id() !== null ) {
					$quote_of = $this->get_quote_of();
					if ( $quote_of && $quote_of->get_author_id() !== $this->get_author_id() ) {
						( new \Voxel\Events\Timeline\Statuses\User_Quoted_Event )->dispatch( $this->get_id() );
					}
				}
			}

			if ( $is_first_approval ) {
				$this->send_mention_notifications();
			}
		}
	}

	public function get_frontend_config( array $options = [] ): array {
		$reposted_by = $options['reposted_by'] ?? null;
		$publisher = $this->get_publisher();
		$post = $this->get_post();
		$details = [
			'id' => $this->get_id(),
			'created_at' => $this->get_time_for_display(),
			'edited_at' => $this->get_edit_time_for_display(),
			'content' => $this->get_content(),
			'feed' => $this->get_feed(),
			'files' => $this->get_files(),
			'link_preview' => $this->get_link_preview(),
			'link' => $this->get_link(),
			'is_pending' => $this->get_moderation_status() === \Voxel\MODERATION_PENDING,
			'annotation' => $this->get_annotation( (string) ( $options['timeline_mode'] ?? '' ) ),
			'likes' => [
				'count' => $this->get_like_count(),
				'last3' => $this->get_last3_likes_frontend_config(),
			],
			'replies' => [
				'count' => $this->get_reply_count(),
			],
			'current_user' => [
				'can_edit' => $this->is_editable_by_current_user(),
				'can_moderate' => $this->is_moderatable_by_current_user(),
				'can_delete' => $this->is_editable_by_current_user() || $this->is_moderatable_by_current_user(),
				'has_liked' => $this->is_liked_by_current_user(),
				'has_reposted' => $this->is_reposted_by_current_user(),
			],
			'publisher' => $publisher ? $publisher->get_timeline_publisher_config() : [ 'exists' => false ],
			'badges' => $this->get_badges(),
			'post' => $post ? [
				'title' => $post->get_display_name(),
				'link' => $post->get_link(),
			] : null,
		];

		if ( $reposted_by !== null ) {
			$details['likes']['last3'] = $reposted_by->get_last3_likes_frontend_config();
		}

		if ( $this->get_repost_of_id() !== null && ( $options['load_repost_of'] ?? true ) !== false ) {
			$repost_of = $this->get_repost_of();
			$details['repost_of'] = $repost_of->get_frontend_config( [ 'load_repost_of' => false, 'reposted_by' => $this ] );
			if ( ! $repost_of->is_viewable_by_current_user() ) {
				$details['repost_of']['private'] = true;
				$details['repost_of']['edited_at'] = null;
				$details['repost_of']['content'] = '';
				$details['repost_of']['files'] = [];
				$details['repost_of']['link_preview'] = null;
				$details['repost_of']['likes']['count'] = 0;
				$details['repost_of']['likes']['last3'] = [];
				$details['repost_of']['replies']['count'] = 0;
				$details['repost_of']['quote_of'] = null;
				$details['repost_of']['review'] = null;
				$details['repost_of']['annotation'] = null;
			}
		}

		if ( $this->get_quote_of_id() !== null ) {
			$quote_of = $this->get_quote_of();
			if ( $quote_of === null ) {
				$details['quote_of'] = [
					'exists' => false,
				];
			} else {
				$quote_publisher = $quote_of->get_publisher();

				if ( ! $quote_of->is_viewable_by_current_user() ) {
					$details['quote_of'] = [
						'exists' => true,
						'private' => true,
						'id' => $quote_of->get_id(),
						'created_at' => $quote_of->get_time_for_display(),
						'edited_at' => null,
						'content' => '',
						'files' => [],
						'link' => $quote_of->get_link(),
						'publisher' => $quote_publisher ? $quote_publisher->get_timeline_publisher_config() : [ 'exists' => false ],
					];
				} else {
					$quote_post = $quote_of->get_post();
					$details['quote_of'] = [
						'exists' => true,
						'id' => $quote_of->get_id(),
						'created_at' => $quote_of->get_time_for_display(),
						'edited_at' => $quote_of->get_edit_time_for_display(),
						'content' => $quote_of->get_content(),
						'files' => $quote_of->get_files(),
						'link' => $quote_of->get_link(),
						'publisher' => $quote_publisher ? $quote_publisher->get_timeline_publisher_config() : [ 'exists' => false ],
						'post' => $quote_post ? [
							'title' => $quote_post->get_display_name(),
							'link' => $quote_post->get_link(),
						] : null
					];

					if ( $quote_of->get_feed() === 'post_reviews' && ( $quote_of_post = $quote_of->get_post() ) && $quote_of_post->post_type ) {
						$details['quote_of']['review'] = [
							'post_type' => $quote_of_post->post_type->get_key(),
							'score' => $quote_of->get_review_score(),
							'formatted_score' => $quote_of->get_review_score_for_display(),
							'rating' => $quote_of->get_review_rating(),
						];
					}
				}
			}
		}

		if ( $this->get_feed() === 'post_reviews' && ( $post = $this->get_post() ) && $post->post_type ) {
			$details['review'] = [
				'post_type' => $post->post_type->get_key(),
				'score' => $this->get_review_score(),
				'formatted_score' => $this->get_review_score_for_display(),
				'rating' => $this->get_review_rating(),
			];
		}

		return $details;
	}

	public static function mock(): self {
		return new static( [
			'id' => null,
			'user_id' => null,
			'post_id' => null,
			'published_as' => null,
			'details' => null,
			'created_at' => \Voxel\now()->format('Y-m-d H:i:s'),
			'edited_at' => null,
		] );
	}
}
