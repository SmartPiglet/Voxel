<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type {
	use \Voxel\Post_Types\Post_Type_Singleton_Trait;

	public $wp_post_type;

	public
		$repository,
		$schema,
		$reviews,
		$timeline,
		$revisions,
		$templates,
		$index_table,
		$index_query;

	private function __construct( \WP_Post_Type $wp_post_type ) {
		$this->wp_post_type = $wp_post_type;
		$this->repository = new \Voxel\Post_Types\Post_Type_Repository( $this );
		$this->schema = new \Voxel\Post_Types\Post_Type_Schema( $this );
		$this->reviews = new \Voxel\Post_Types\Post_Type_Reviews( $this );
		$this->revisions = new \Voxel\Post_Types\Post_Type_Revisions( $this );
		$this->templates = new \Voxel\Post_Types\Post_Type_Templates( $this );
		$this->index_table = new \Voxel\Post_Types\Index_Table( $this );
		$this->index_query = new \Voxel\Post_Types\Index_Query( $this );
		$this->timeline = new \Voxel\Post_Types\Post_Type_Timeline( $this );
	}

	public function get_key() {
		return $this->wp_post_type->name;
	}

	public function get_label(): string {
		return (string) $this->wp_post_type->label;
	}

	public function get_singular_name() {
		return $this->wp_post_type->labels->singular_name;
	}

	public function get_plural_name() {
		return $this->get_label();
	}

	public function get_icon() {
		return $this->get_setting('icon');
	}

	public function get_description() {
		return $this->wp_post_type->description;
	}

	public function get_taxonomies() {
		return array_map( '\Voxel\Taxonomy::get', get_object_taxonomies( $this->get_key(), 'objects' ) );
	}

	/**
	 * Whether this post type is a WordPress native or "built-in" post_type.
	 *
	 * @since 1.0
	 */
	public function is_built_in() {
		return $this->wp_post_type->_builtin;
	}

	public function is_managed_by_voxel() {
		return $this->repository->is_managed_by_voxel();
	}

	public function is_created_by_voxel() {
		return $this->wp_post_type->_is_created_by_voxel ?? false;
	}

	public function get_edit_link() {
		if ( $this->get_key() === 'post' ) {
			return admin_url( sprintf( 'edit.php?page=edit-post-type-%s', $this->get_key() ) );
		}

		return admin_url( sprintf( 'edit.php?post_type=%s&page=edit-post-type-%s', $this->get_key(), $this->get_key() ) );
	}

	public function get_archive_link() {
		return get_post_type_archive_link( $this->get_key() );
	}

	public function config( $option, $default = null ) {
		return $this->schema->config( $option, $default );
	}

	public function get_create_post_link() {
		return get_permalink( $this->get_templates()['form'] );
	}

	public function get_fields() {
		return $this->repository->get_fields();
	}

	public function get_field( $field_key ) {
		return $this->repository->get_field( $field_key );
	}

	public function get_filters() {
		return $this->repository->get_filters();
	}

	public function get_filter( $filter_key ) {
		return $this->repository->get_filter( $filter_key );
	}

	public function get_search_orders() {
		return $this->repository->get_search_orders();
	}

	public function get_search_order( $search_order_key ) {
		return $this->repository->get_search_order( $search_order_key );
	}

	public function get_templates( $create_if_not_exists = false ) {
		return $this->templates->get_templates( $create_if_not_exists );
	}

	public function get_settings() {
		return $this->repository->get_settings();
	}

	public function get_setting( $setting_key, $default = null ) {
		return $this->repository->get_setting( $setting_key, $default );
	}

	public function get_index_table() {
		return $this->index_table;
	}

	public function get_index_query() {
		return $this->index_query;
	}

	public function query( array $args = [], $cb = null ) {
		return $this->index_query->get_posts( $args, $cb );
	}

	public function is_tracking_enabled(): bool {
		return in_array( $this->get_key(), (array) \Voxel\get( 'settings.stats.enabled_post_types', [] ), true );
	}

	public function has_archive_page(): bool {
		if ( ! $this->is_managed_by_voxel() ) {
			return false;
		}

		$template_id = $this->get_templates()['archive'] ?? null;
		if ( post_password_required( $template_id ) ) {
			return false;
		}

		if ( ! \Voxel\is_elementor_active() ) {
			return false;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $template_id );
		if ( ! ( $document && $document->is_built_with_elementor() ) ) {
			return false;
		}

		return true;
	}
}
