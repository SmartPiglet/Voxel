<?php

namespace Voxel\Dynamic_Data\Data_Groups\User;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Data_Group extends \Voxel\Dynamic_Data\Data_Groups\Base_Data_Group {
	use Vendor_Data, Visits_Data, Membership_Data;

	public function get_type(): string {
		return 'user';
	}

	protected static $instances = [];
	public static function get( \Voxel\User $user ): self {
		if ( ! array_key_exists( $user->get_id(), static::$instances ) ) {
			static::$instances[ $user->get_id() ] = new static( $user );
		}

		return static::$instances[ $user->get_id() ];
	}

	public static function unset( int $user_id ) {
		unset( static::$instances[ $user_id ] );
	}

	public $user;
	protected function __construct( \Voxel\User $user ) {
		$this->user = $user;
	}

	public function get_user(): \Voxel\User {
		return $this->user;
	}

	protected function properties(): array {
		$properties = [
			'id' => Tag::Number('ID')->render( function() {
				return $this->user->get_id() ?: '';
			} ),
			'username' => Tag::String('Username')->render( function() {
				return $this->user->get_username();
			} ),
			'display_name' => Tag::String('Display name')->render( function() {
				return $this->user->get_display_name();
			} ),
			'email' => Tag::Email('Email')->render( function() {
				return $this->user->get_email();
			} ),
			'avatar' => Tag::Number('Avatar')->render( function() {
				return $this->user->get_avatar_id();
			} ),
			'first_name' => Tag::String('First name')->render( function() {
				return $this->user->get_first_name();
			} ),
			'last_name' => Tag::String('Last name')->render( function() {
				return $this->user->get_last_name();
			} ),
			'profile' => Tag::Object('Profile')->properties( function() {
				$post = $this->user->get_profile() ?? \Voxel\Post::dummy( [ 'post_type' => 'profile' ] );
				return \Voxel\Dynamic_Data\Group::Post( $post );
			} ),
			'profile_id' => Tag::Number('Profile ID')->render( function() {
				return $this->user->get_profile_id();
			} ),
			'profile_url' => Tag::URL('Profile URL')->render( function() {
				return get_author_posts_url( $this->user->get_id() );
			} ),
			'post_types' => $this->get_post_type_data(),
			'plan' => $this->get_membership_plan_data(),
			'followers' => $this->get_followers_data(),
			'following' => $this->get_following_data(),
			'timeline' => $this->get_timeline_data(),
			'vendor' => $this->get_vendor_data(),
		];

		if ( ! empty( \Voxel\get('settings.stats.enabled_post_types') ) ) {
			$properties['visit_stats'] = $this->get_visit_stats();
		}

		return $properties;
	}

	protected function aliases(): array {
		return [
			':id' => 'id',
			':username' => 'username',
			':display_name' => 'display_name',
			':email' => 'email',
			':avatar' => 'avatar',
			':profile_url' => 'profile_url',
			':profile_id' => 'profile_id',
			':first_name' => 'first_name',
			':last_name' => 'last_name',
			':plan' => 'plan',
			':followers' => 'followers',
			':following' => 'following',
			':stats' => 'visit_stats',
		];
	}

	protected function get_followers_data(): Base_Data_Type {
		return Tag::Object('Followers')->properties( function() {
			return [
				'accepted' => Tag::Number('Follow count', 'Number of users that are following this user')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_ACCEPTED ] ?? 0 );
				} ),
				/*'requested' => Tag::Number('Follow requested count', 'Number of users that have requested to follow this user')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_REQUESTED ] ?? 0 );
				} ),*/
				'blocked' => Tag::Number('Block count', 'Number of users that have been blocked by this user')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['followed'][ \Voxel\FOLLOW_BLOCKED ] ?? 0 );
				} ),
			];
		} );
	}

	protected function get_following_data(): Base_Data_Type {
		return Tag::Object('Following')->properties( function() {
			return [
				'accepted' => Tag::Number('Follow count', 'Number of users/posts this user is following')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['following'][ \Voxel\FOLLOW_ACCEPTED ] ?? 0 );
				} ),
				'requested' => Tag::Number('Follow requested count', 'Number of users/posts this user has requested to follow')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['following'][ \Voxel\FOLLOW_REQUESTED ] ?? 0 );
				} ),
				'blocked' => Tag::Number('Block count', 'Number of users/posts this user has been blocked by')->render( function() {
					$stats = $this->user->get_follow_stats();
					return absint( $stats['following'][ \Voxel\FOLLOW_BLOCKED ] ?? 0 );
				} ),
				'by_post_type' => Tag::Object('By post type')->properties( function() {
					$properties = [];
					foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
						$properties[ $post_type->get_key() ] = Tag::Object( $post_type->get_label() )->properties( function() use ( $post_type ) {
							return [
								'accepted' => Tag::Number('Follow count')->render( function() use ( $post_type ) {
									$stats = $this->user->get_follow_stats();
									return absint( $stats['following_by_post_type'][ $post_type->get_key() ][ \Voxel\FOLLOW_ACCEPTED ] ?? 0 );
								} ),
								'requested' => Tag::Number('Follow requested count')->render( function() use ( $post_type ) {
									$stats = $this->user->get_follow_stats();
									return absint( $stats['following_by_post_type'][ $post_type->get_key() ][ \Voxel\FOLLOW_REQUESTED ] ?? 0 );
								} ),
								'blocked' => Tag::Number('Block count')->render( function() use ( $post_type ) {
									$stats = $this->user->get_follow_stats();
									return absint( $stats['following_by_post_type'][ $post_type->get_key() ][ \Voxel\FOLLOW_BLOCKED ] ?? 0 );
								} ),
							];
						} );
					}

					return $properties;
				} ),
			];
		} );
	}

	protected function get_post_type_data(): Base_Data_Type {
		return Tag::Object('Post types')->properties( function() {
			$properties = [];

			foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
				$properties[ $post_type->get_key() ] = Tag::Object( $post_type->get_label() )->properties( function() use ( $post_type ) {
					return [
						'published' => Tag::Number('Published count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['publish'] ?? 0;
						} ),
						'pending' => Tag::Number('Pending count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['pending'] ?? 0;
						} ),
						'unpublished' => Tag::Number('Unpublished count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['unpublished'] ?? 0;
						} ),
						'expired' => Tag::Number('Expired count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['expired'] ?? 0;
						} ),
						'rejected' => Tag::Number('Rejected count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['rejected'] ?? 0;
						} ),
						'draft' => Tag::Number('Draft count')->render( function() use ( $post_type ) {
							$stats = $this->user->get_post_stats();
							return $stats[ $post_type->get_key() ]['draft'] ?? 0;
						} ),
						'archive' => Tag::URL('Archive link')->render( function() use ( $post_type ) {
							$filters = $post_type->get_filters();
							$key = 'user';
							foreach ( $filters as $filter ) {
								if ( $filter->get_type() === 'user' ) {
									$key = $filter->get_key();
								}
							}

							return add_query_arg( $key, $this->user->get_id(), $post_type->get_archive_link() );
						} ),
					];
				} );
			}

			return $properties;
		} );
	}

	public function methods(): array {
		return [
			'meta' => \Voxel\Dynamic_Data\Modifiers\Group_Methods\User_Meta_Method::class,
		];
	}

	public static function mock(): self {
		return new static( \Voxel\User::dummy() );
	}

	protected function get_timeline_data(): Base_Data_Type {
		return Tag::Object('User timeline')->properties( function() {
			return [
				'total' => Tag::Number('Total count')->render( function() {
					$stats = $this->user->get_timeline_stats();
					return absint( $stats['total'] ?? 0 );
				} ),
				'reposted' => Tag::Number('Repost count')->render( function() {
					$stats = $this->user->get_timeline_stats();
					return absint( $stats['reposted'] ?? 0 );
				} ),
				'quoted' => Tag::Number('Quote count')->render( function() {
					$stats = $this->user->get_timeline_stats();
					return absint( $stats['quoted'] ?? 0 );
				} ),
			];
		} );
	}
}
