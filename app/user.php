<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User {
	use \Voxel\Users\Security_Trait;
	use \Voxel\Users\Vendor_Trait;
	use \Voxel\Users\Customer_Trait;
	use \Voxel\Users\Social_Trait;
	use \Voxel\Users\Member_Trait;

	protected
		$wp_user,
		$account_details,
		$vendor_stats;

	public $stats;

	protected static $instances = [];
	public static function get( $user ) {
		if ( is_numeric( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( ! $user instanceof \WP_User ) {
			return null;
		}

		if ( ! array_key_exists( $user->ID, static::$instances ) ) {
			static::$instances[ $user->ID ] = new static( $user );
		}

		return static::$instances[ $user->ID ];
	}

	public static function force_get( int $user_id ) {
		if ( isset( static::$instances[ $user_id ] ) ) {
			unset( static::$instances[ $user_id ] );
		}

		\Voxel\Dynamic_Data\Data_Groups\User\User_Data_Group::unset( $user_id );

		return static::get( $user_id );
	}

	public static function query( array $args ): array {
		if ( isset( $args['id'] ) ) {
			$args['include'] = $args['id'];
		}

		$args['count_total'] = false;
		$args['number'] = -1;
		$users = get_users( $args );
		return array_map( '\Voxel\User::get', $users );
	}

	public static function get_by_profile_id( $profile_id ) {
		$results = get_users( [
			'meta_key' => \Voxel\get_site_specific_user_meta_key('voxel:profile_id'),
			'meta_value' => $profile_id,
			'number' => 1,
			'fields' => 'ID',
		] );

		return \Voxel\User::get( array_shift( $results ) );
	}

	protected function __construct( \WP_User $user ) {
		$this->wp_user = $user;
		$this->stats = new \Voxel\Users\User_Stats( $this );
	}

	public function get_id() {
		return $this->wp_user->ID;
	}

	public function get_link() {
		return get_author_posts_url( $this->get_id() );
	}

	public function get_display_name() {
		$display_name = $this->wp_user->display_name;
		return ! empty( $display_name ) ? $display_name : $this->get_username();
	}

	public function get_email() {
		return $this->wp_user->user_email;
	}

	public function get_username() {
		return $this->wp_user->user_login;
	}

	public function get_first_name() {
		return $this->wp_user->first_name;
	}

	public function get_last_name() {
		return $this->wp_user->last_name;
	}

	public function get_role_keys() {
		return $this->wp_user->roles;
	}

	/**
	 * Get list of roles assigned to this user.
	 *
	 * @since 1.2
	 * @return \Voxel\Role[]
	 */
	public function get_roles(): array {
		return array_filter( array_map( function( $role_key ) {
			return \Voxel\Role::get( $role_key );
		}, $this->get_role_keys() ) );
	}

	/**
	 * Get list of roles that this user is allowed to switch to
	 * through the frontend interface.
	 *
	 * @since 1.2
	 * @return \Voxel\Role[]
	 */
	public function get_switchable_roles(): array {
		return array_filter( \Voxel\Role::get_voxel_roles(), function( $role ) {
			if ( ! $role->is_switching_enabled() ) {
				return false;
			}

			if ( in_array( $role->get_key(), $this->get_role_keys() ) ) {
				return false;
			}

			/**
			 * Edge case: Users with a paid plan are not allowed to switch to a role
			 * that does not support any plans. They need to cancel their current plan
			 * for the role to become available.
			 */
			$membership = $this->get_membership();
			if ( $membership->get_active_plan()->get_key() !== 'default' && ! $role->has_plans_enabled() ) {
				return false;
			}

			return true;
		} );
	}

	public function has_role( $role ): bool {
		return in_array( $role, $this->get_role_keys(), true );
	}

	public function set_role( $role_key, $force = false ) {
		// safeguard admin and editor roles
		if ( ! $force && in_array( $role_key, [ 'administrator', 'editor' ], true ) ) {
			return;
		}

		$this->wp_user->set_role( $role_key );
	}

	public function get_avatar_id() {
		$avatar_id = get_user_meta( $this->get_id(), 'voxel:avatar', true );
		if ( $avatar_id ) {
			return $avatar_id;
		}

		$field = \Voxel\Post_Type::get('profile')->get_field('voxel:avatar');
		$default = $field ? $field->get_prop('default') : null;
		if ( $default ) {
			return $default;
		}

		return null;
	}

	public function get_avatar_markup( $size = 96 ) {
		return get_avatar( $this->get_id(), $size, '', '', [
			'class' => 'ts-status-avatar',
		] );
	}

	public function get_avatar_url() {
		return get_avatar_url( $this->get_id() );
	}

	public function get_edit_link() {
		return get_edit_user_link( $this->get_id() );
	}

	public function can_create_post( string $post_type_key ): bool {
		if ( current_user_can('administrator') || current_user_can('editor') ) {
			return true;
		}

		$post_type = \Voxel\Post_Type::get( $post_type_key );
		if ( ! $post_type ) {
			return false;
		}

		$can_create_post = !! $post_type->get_setting('submissions.enabled');

		return apply_filters( 'voxel/user/can_create_post', $can_create_post, $this, $post_type );
	}

	public function get_profile_id(): ?int {
		$profile_id = \Voxel\get_site_specific_user_meta( $this->get_id(), 'voxel:profile_id', true );
		if ( ! is_numeric( $profile_id ) ) {
			return null;
		}

		return absint( $profile_id );
	}

	public function get_profile(): ?\Voxel\Post {
		$profile_id = $this->get_profile_id();
		if ( $profile_id === null ) {
			return null;
		}

		return \Voxel\Post::find( [
			'post_type' => 'profile',
			'p' => $profile_id,
			'author' => $this->get_id(),
		] );
	}

	public function get_or_create_profile() {
		$profile = $this->get_profile();
		if ( $profile ) {
			return $profile;
		}

		$unassigned_profile = \Voxel\Post::find( [
			'post_type' => 'profile',
			'author' => $this->get_id(),
			'post_status' => 'publish',
		] );

		if ( $unassigned_profile !== null ) {
			\Voxel\update_site_specific_user_meta( $this->get_id(), 'voxel:profile_id', $unassigned_profile->get_id() );
			return $unassigned_profile;
		}

		$profile_id = wp_insert_post( [
			'post_type' => 'profile',
			'post_author' => $this->get_id(),
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $profile_id ) ) {
			return null;
		}

		\Voxel\update_site_specific_user_meta( $this->get_id(), 'voxel:profile_id', $profile_id );
		return \Voxel\Post::get( $profile_id );
	}

	public function is_verified(): bool {
		$profile = $this->get_profile();
		return $profile ? $profile->is_verified() : false;
	}

	public function get_post_stats() {
		$stats = json_decode( get_user_meta( $this->get_id(), 'voxel:post_stats', true ), true );
		if ( ! is_array( $stats ) ) {
			$stats = \Voxel\cache_user_post_stats( $this->get_id() );
		}

		return $stats;
	}

	public function get_timeline_stats() {
		$stats = json_decode( get_user_meta( $this->get_id(), 'voxel:timeline_stats', true ), true );
		if ( ! is_array( $stats ) ) {
			$stats = \Voxel\Timeline\cache_user_timeline_stats( $this->get_id() );
		}

		return $stats;
	}

	public function get_wp_user_object() {
		return $this->wp_user;
	}

	public function get_object_type() {
		return 'user';
	}

	public function has_cap( $capability, ...$args ) {
		return user_can( $this->wp_user, $capability, ...$args );
	}

	public static function dummy() {
		return static::mock();
	}

	public static function mock() {
		return static::get( new \WP_User( (object) [ 'ID' => 0 ] ) );
	}

	public function get_timeline_publisher_config(): array {
		return [
			'exists' => true,
			'type' => 'user',
			'id' => $this->get_id(),
			'username' => \Voxel\get('settings.timeline.author.show_username', true) ? $this->get_username() : null,
			'display_name' => $this->get_display_name(),
			'avatar_url' => $this->get_avatar_url(),
			'link' => $this->get_link(),
			'is_verified' => $this->is_verified(),
		];
	}
}
