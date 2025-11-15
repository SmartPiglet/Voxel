<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_By_Filter extends Base_Filter {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'order-by',
		'label' => 'Order By',
		'placeholder' => '',
		'key' => 'sort', // 'order' clashes with WP permalinks
		'singular' => true,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-12' ]),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'icon' => $this->get_icon_model(),
		];
	}

	public function setup( \Voxel\Post_Types\Index_Table $table ): void {
		foreach ( $this->post_type->get_search_orders() as $search_order ) {
			foreach ( $search_order->get_clauses() as $clause ) {
				$clause->setup( $table );
			}
		}
	}

	public function index( \Voxel\Post $post ): array {
		$columns = [];
		foreach ( $this->post_type->get_search_orders() as $search_order ) {
			foreach ( $search_order->get_clauses() as $clause ) {
				$columns = array_merge( $columns, $clause->index( $post ) );
			}
		}

		return $columns;
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		$search_order = $this->post_type->get_search_order( $value['key'] );
		if ( $search_order ) {
			foreach ( $search_order->get_clauses() as $clause ) {
				$clause->query( $query, $args, $value['args'] );
			}
		}
	}

	public function frontend_props() {
		$choices = $this->_get_selected_choices();
		$value = $this->parse_value( $this->get_value() );

		if ( $value !== null && $value['key'] && ( $choices[ $value['key'] ]['requires_location'] ?? false ) && count( $value['args'] ) === 2 ) {
			$choices[ $value['key'] ]['has_location'] = true;
		}

		return [
			'choices' => $choices,
			'display_as' => $this->elementor_config['display_as'] ?? null,
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
		];
	}

	protected function _get_choices() {
		if ( array_key_exists( 'choices', $this->cache ) ) {
			return $this->cache['choices'];
		}

		$choices = [];
		foreach ( $this->post_type->get_search_orders() as $search_order ) {
			$choices[ $search_order->get_key() ] = [
				'key' => $search_order->get_key(),
				'label' => $search_order->get_label(),
				'placeholder' => $search_order->get_placeholder() ?: $search_order->get_label(),
				'icon' => \Voxel\get_icon_markup( $search_order->get_icon() ),
				'requires_location' => $search_order->requires_user_location(),
			];
		}

		$this->cache['choices'] = $choices;
		return $this->cache['choices'];
	}

	protected function _get_selected_choices() {
		$all_choices = $this->_get_choices();
		$choices = [];
		$selected = ( $this->elementor_config['choices'] ?? null );
		if ( is_array( $selected ) && ! empty( $selected ) ) {
			foreach ( $selected as $choice_key ) {
				if ( isset( $all_choices[ $choice_key ] ) ) {
					$choices[ $choice_key ] = $all_choices[ $choice_key ];
				}
			}
		}

		return ! empty( $choices ) ? $choices : $all_choices;
	}

	public function parse_value( $value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		preg_match( '/(?P<key>[^\(]*)(\((?P<args>.*)\))?/i', (string) $value, $matches );
		if ( ! isset( $matches['key'] ) ) {
			return null;
		}

		$args = [];
		if ( isset( $matches['args'] ) ) {
			$args = explode( ',', $matches['args'] );
			$args = array_map( 'sanitize_text_field', $args );
		}

		return [
			'key' => sanitize_text_field( $matches['key'] ),
			'args' => $args,
		];
	}

	public function get_elementor_controls(): array {
		$choices = [];
		foreach ( $this->_get_choices() as $choice ) {
			$choices[ $choice['key'] ] = $choice['label'];
		}

		return [
			'value' => [
				'label' => _x( 'Default value', 'orderby filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $choices,
			],
			'display_as' => [
				'label' => _x( 'Display as', 'orderby filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup' => _x( 'Popup', 'orderby filter', 'voxel-backend' ),
					'buttons' => _x( 'Buttons', 'orderby filter', 'voxel-backend' ),
					'alt-btn' => _x( 'Buttons Alt', 'orderby filter', 'voxel-backend' ),
					'post-feed' => _x( 'Post feed', 'orderby filter', 'voxel-backend' ),
				],
				'conditional' => false,
			],
			'choices' => [
				'label' => _x( 'Ordering options', 'orderby filter', 'voxel-backend' ),
				'description' => _x( 'Leave blank to list all options available', 'orderby filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $choices,
				'conditional' => false,
			],
		];
	}
}
