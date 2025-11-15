<?php

namespace Voxel\Post_Types\Order_By;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_By_Group {

	/**
	 * Post type object which this order by group belongs to.
	 *
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * List of order by group properties/configuration.
	 *
	 * @since 1.0
	 */
	protected $props = [
		'key' => '',
		'label' => '',
		'placeholder' => '',
		'icon' => '',
		'clauses' => [],
	];

	protected $clauses;

	public function __construct( $props, \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}

		$clauses = $this->get_clauses();
		$this->props['clauses'] = [];
		foreach ( $clauses as $clause ) {
			$this->props['clauses'][] = $clause->get_props();
		}
	}

	public static function preset( $props, \Voxel\Post_Type $post_type ) {
		return ( new static( $props, $post_type ) )->get_props();
	}

	public function get_key() {
		return $this->props['key'];
	}

	public function get_label() {
		return $this->props['label'];
	}

	public function get_placeholder() {
		return $this->props['placeholder'];
	}

	public function get_icon() {
		return $this->props['icon'];
	}

	public function get_prop( $prop ) {
		if ( ! isset( $this->props[ $prop ] ) ) {
			return null;
		}

		return $this->props[ $prop ];
	}

	public function get_props() {
		return $this->props;
	}

	public function get_clauses() {
		if ( ! is_null( $this->clauses ) ) {
			return $this->clauses;
		}

		$orderby_types = \Voxel\config('post_types.orderby_types');
		$this->clauses = [];

		foreach ( (array) $this->props['clauses'] as $clause_data ) {
			if ( ! empty( $clause_data['type'] ) && isset( $orderby_types[ $clause_data['type'] ] ) ) {
				$clause = new $orderby_types[ $clause_data['type'] ]( $clause_data );
				$clause->set_post_type( $this->post_type );
				$this->clauses[] = $clause;
			}
		}

		return $this->clauses;
	}

	public function requires_user_location(): bool {
		return $this->has_clause( 'nearby' );
	}


	public function has_clause( $clause_type ): bool {
		foreach ( (array) $this->props['clauses'] as $clause ) {
			if ( $clause['type'] === $clause_type ) {
				return true;
			}
		}

		return false;
	}
}
