<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post {
	use \Voxel\Posts\Post_Singleton_Trait;
	use \Voxel\Posts\Social_Trait;

	private $fields;

	private $wp_post;

	public
		$post_type,
		$repository,
		$stats,
		$promotions,
		$quick_search;

	private function __construct( \WP_Post $post ) {
		$this->wp_post = $post;
		$this->post_type = \Voxel\Post_Type::get( $post->post_type );
		$this->repository = new \Voxel\Posts\Post_Repository( $this );
		$this->stats = new \Voxel\Posts\Post_Stats( $this );
		$this->promotions = new \Voxel\Posts\Post_Promotions( $this );
		$this->quick_search = new \Voxel\Posts\Post_Quick_Search( $this );
	}

	public function get_id(): int {
		return (int) $this->wp_post->ID;
	}

	public function get_title() {
		return $this->wp_post->post_title;
	}

	public function get_content() {
		return $this->wp_post->post_content;
	}

	public function get_excerpt() {
		return get_the_excerpt( $this->get_wp_post_object() );
	}

	public function get_date() {
		return $this->wp_post->post_date_gmt;
	}

	public function get_modified_date() {
		return $this->wp_post->post_modified_gmt;
	}

	public function get_slug() {
		return $this->wp_post->post_name;
	}

	public function get_status() {
		return $this->wp_post->post_status;
	}

	public function get_link() {
		if ( $this->post_type && $this->post_type->get_key() === 'profile' && ( $author = $this->get_author() ) ) {
			return $author->get_link();
		}

		return get_permalink( $this->wp_post );
	}

	public function get_edit_link() {
		if ( ! $this->post_type ) {
			return null;
		}

		return add_query_arg(
			'post_id',
			$this->get_id(),
			get_permalink( $this->post_type->get_templates()['form'] )
		);
	}

	public function get_backend_edit_link() {
		return admin_url( sprintf( 'post.php?post=%d&action=edit', $this->get_id() ) );
	}

	public function is_managed_by_voxel(): bool {
		return $this->post_type && $this->post_type->is_managed_by_voxel();
	}

	public function is_built_with_elementor(): bool {
		return !! get_post_meta( $this->get_id(), '_elementor_edit_mode', true );
	}

	public function get_author_id(): int {
		return (int) $this->wp_post->post_author;
	}

	public function get_author() {
		return \Voxel\User::get( $this->get_author_id() );
	}

	public function get_display_name() {
		if ( $this->post_type && $this->post_type->get_key() === 'profile' && ( $author = $this->get_author() ) ) {
			return $author->get_display_name();
		}

		return $this->get_title();
	}

	public function get_avatar_markup() {
		if ( $this->post_type && $this->post_type->get_key() === 'profile' && ( $author = $this->get_author() ) ) {
			return $author->get_avatar_markup();
		}

		return $this->get_logo_markup();
	}

	public function get_avatar_id() {
		if ( $this->post_type && $this->post_type->get_key() === 'profile' && ( $author = $this->get_author() ) ) {
			return $author->get_avatar_id();
		}

		return $this->get_logo_id();
	}

	public function get_avatar_url() {
		return wp_get_attachment_image_url( $this->get_logo_id() );
	}

	public function set_inbox_activity( $has_activity ) {
		if ( $author = $this->get_author() ) {
			$author->set_inbox_activity( $has_activity );
		}
	}

	public function get_fields() {
		return $this->repository->get_fields();
	}

	public function get_field( $field_key ) {
		return $this->repository->get_field( $field_key );
	}

	public function get_logo_id() {
		$field = $this->get_field('logo');
		if ( ! $field ) {
			return null;
		}

		$value = $field->get_value();
		if ( ! empty( $value[0] ) ) {
			return $value[0];
		}

		$default = $field->get_prop('default');
		if ( $default ) {
			return $default;
		}

		return null;
	}

	public function get_logo_markup() {
		return wp_get_attachment_image( $this->get_logo_id(), 'thumbnail', false, [
			'class' => 'ts-status-avatar',
		] );
	}

	public function get_timezone() {
		$field = $this->get_field( 'timezone' );
		if ( $field && $field->get_type() === 'timezone' ) {
			return $field->get_timezone();
		}

		return wp_timezone();
	}

	public function get_local_time(): \DateTime {
		$timezone = $this->get_timezone();
		return new \DateTime( 'now', $timezone );
	}

	public function is_editable_by_current_user(): bool {
		return (
			current_user_can( 'edit_others_posts', $this->get_id() ) ||
			absint( $this->get_author_id() ) === absint( get_current_user_id() )
		);
	}

	public function is_editable_by_user( \Voxel\User $user ): bool {
		return (
			$user->has_cap( 'edit_others_posts', $this->get_id() ) ||
			absint( $this->get_author_id() ) === absint( $user->get_id() )
		);
	}

	public function is_viewable_by_current_user(): bool {
		if ( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
			return true;
		}

		$status = get_post_status_object( $this->get_status() );
		if ( ! $status ) {
			return false;
		}

		if ( $status->public ) {
			return true;
		}

		return is_user_logged_in() && absint( $this->get_author_id() ) === absint( get_current_user_id() );
	}

	public function is_viewable_by_user( \Voxel\User $user ): bool {
		if ( $user->has_cap( 'administrator' ) || $user->has_cap( 'editor' ) ) {
			return true;
		}

		$status = get_post_status_object( $this->get_status() );
		if ( ! $status ) {
			return false;
		}

		if ( $status->public ) {
			return true;
		}

		return is_user_logged_in() && absint( $this->get_author_id() ) === absint( $user->get_id() );
	}

	public function is_deletable_by_current_user(): bool {
		return current_user_can( 'manage_options' ) || (
			$this->is_editable_by_current_user()
			&& $this->post_type
			&& $this->post_type->get_setting( 'submissions.deletable' )
		);
	}

	public function is_deletable_by_user( \Voxel\User $user ): bool {
		return $user->has_cap( 'manage_options' ) || (
			$this->is_editable_by_user( $user )
			&& $this->post_type
			&& $this->post_type->get_setting( 'submissions.deletable' )
		);
	}

	public function should_index(): bool {
		if ( ! $this->post_type ) {
			return false;
		}

		$status = $this->get_status();
		$index_statuses = $this->post_type->repository->get_indexable_statuses();
		return isset( $index_statuses[ $status ] );
	}

	public function index() {
		if ( $this->post_type ) {
			$this->post_type->get_index_table()->index( [ $this->get_id() ] );
		}
	}

	public function unindex() {
		if ( $this->post_type ) {
			$this->post_type->get_index_table()->unindex( [ $this->get_id() ] );
		}
	}

	public static function current_user_can_edit( $post_id ): bool {
		if ( ! ( $post = self::get( $post_id ) ) ) {
			return false;
		}

		return $post->is_editable_by_current_user();
	}

	public function is_verified(): bool {
		return !! get_post_meta( $this->get_id(), 'voxel:verified', true );
	}

	public function set_verified( bool $status ): void {
		if ( $status ) {
			update_post_meta( $this->get_id(), 'voxel:verified', 1 );
		} else {
			delete_post_meta( $this->get_id(), 'voxel:verified' );
		}
	}

	public static function dummy( array $args = [] ) {
		return static::mock( $args );
	}

	public static function mock( array $args = [] ) {
		return new static( new \WP_Post( (object) array_merge( [
			'ID' => 0,
			'post_type' => 'post',
			'__is_mock_post' => true,
		], $args ) ) );
	}

	public function is_mock_post(): bool {
		return !! ( $this->wp_post->__is_mock_post ?? false );
	}

	public function get_wp_post_object() {
		return $this->wp_post;
	}

	public function get_object_type() {
		return 'post';
	}

	public function get_expiry_date() {
		$custom_expiry = (string) get_post_meta( $this->get_id(), 'voxel:expiry_date', true );
		$has_custom_expiry = !! strtotime( $custom_expiry );

		// post has custom expiration date set
		if ( $has_custom_expiry ) {
			// post set to never expire
			if ( $custom_expiry === '9999-01-01 00:00:00' ) {
				return null;
			}

			return date( 'Y-m-d H:i:s', strtotime( $custom_expiry ) );
		}

		// retrieve date from expiration rules
		$rule_expirations = \Voxel\resolve_expiration_rules( $this );

		if ( ! empty( $rule_expirations ) ) {
			return date( 'Y-m-d H:i:s', min( $rule_expirations ) );
		}

		return null;
	}

	public function get_priority(): int {
		$promotion_package = (array) json_decode( get_post_meta( $this->get_id(), 'voxel:promotion', true ), true );
		if ( ( $promotion_package['status'] ?? null ) === 'active' && is_numeric( $promotion_package['priority'] ?? null ) ) {
			return intval( $promotion_package['priority'] );
		}

		$priority = (int) get_post_meta( $this->get_id(), 'voxel:priority', true );
		if ( is_numeric( $priority ) ) {
			return intval( $priority );
		}

		return 0;
	}

	public function get_timeline_publisher_config(): array {
		return [
			'exists' => true,
			'type' => 'post',
			'id' => $this->get_id(),
			'username' => null,
			'display_name' => $this->get_display_name(),
			'avatar_url' => $this->get_avatar_url(),
			'link' => $this->get_link(),
			'is_verified' => $this->is_verified(),
		];
	}
}
