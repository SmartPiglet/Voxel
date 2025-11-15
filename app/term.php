<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term {
	use \Voxel\Taxonomies\Term_Singleton_Trait;

	public
		$taxonomy,
		$post_counts;

	protected $wp_term;

	public function __construct( \WP_Term $term ) {
		$this->wp_term = $term;
		$this->taxonomy = \Voxel\Taxonomy::get( $term->taxonomy );
		$this->post_counts = new \Voxel\Taxonomies\Term_Post_Counts( $this );
	}

	public function get_id() {
		return $this->wp_term->term_id;
	}

	public function get_term_taxonomy_id() {
		return $this->wp_term->term_taxonomy_id;
	}

	public function get_label() {
		return wp_specialchars_decode( $this->wp_term->name );
	}

	public function get_description() {
		return $this->wp_term->description;
	}

	public function get_slug() {
		return urldecode( $this->wp_term->slug );
	}

	public function get_parent_id() {
		return $this->wp_term->parent;
	}

	public function get_parent(): ?\Voxel\Term {
		return \Voxel\Term::get( $this->get_parent_id() );
	}

	public function get_link() {
		return get_term_link( $this->wp_term );
	}

	public function get_icon() {
		return get_term_meta( $this->get_id(), 'voxel_icon', true );
	}

	public function get_color() {
		return get_term_meta( $this->get_id(), 'voxel_color', true );
	}

	public function get_image_id() {
		return get_term_meta( $this->get_id(), 'voxel_image', true );
	}

	public function get_area() {
		$area = (array) json_decode( get_term_meta( $this->get_id(), 'voxel_area', true ), ARRAY_A );

		return [
			'address' => $area['address'] ?? null,
			'swlat' => $area['swlat'] ?? null,
			'swlng' => $area['swlng'] ?? null,
			'nelat' => $area['nelat'] ?? null,
			'nelng' => $area['nelng'] ?? null,
		];
	}

	public function get_ancestor_ids() {
		return get_ancestors( $this->get_id(), $this->taxonomy->get_key(), 'taxonomy' );
	}

	public function is_managed_by_voxel() {
		return $this->taxonomy && $this->taxonomy->is_managed_by_voxel();
	}

	public static function dummy() {
		return static::mock();
	}

	public static function mock() {
		return static::get( new \WP_Term( (object) [
			'term_id' => 0,
		] ) );
	}
}
