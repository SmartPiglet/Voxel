<?php

namespace Voxel\Post_Types\Filter_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Condition {

	/**
	 * Unique string identifier for condition types.
	 *
	 * @since 1.6
	 */
	protected $type;

	/**
	 * Post filter object this condition belongs to.
	 *
	 * @since 1.6
	 */
	protected $filter;

	/**
	 * Post type object this condition's filter belongs to.
	 *
	 * @since 1.6
	 */
	protected $post_type;

	/**
	 * List of condition properties for individual condition classes
	 * to store their custom data.
	 *
	 * @since 1.6
	 */
	protected $props;

	public function __construct( $props = [] ) {
		$this->type = $this->get_type();
		$this->props = array_merge( [
			'source' => '',
		], $this->props() );

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}
	}

	public function evaluate( $value ): bool {
		return true;
	}

	abstract public function get_type(): string;

	abstract public function get_label(): string;

	protected function props(): array {
		return [];
	}

	/* Getters */
	public function get_models(): array {
		return [];
	}

	public function get_source(): string {
		return $this->props['source'];
	}

	public function get_props(): array {
		return $this->props;
	}

	public function get_prop( $prop ) {
		return $this->props[ $prop ] ?? null;
	}

	public function get_group(): string {
		return explode( ':', $this->get_type() )[0];
	}

	public function set_filter( \Voxel\Post_Types\Filters\Base_filter $filter ): void {
		$this->filter = $filter;
	}

	public function set_post_type( \Voxel\Post_Type $post_type ): void {
		$this->post_type = $post_type;
	}
}
