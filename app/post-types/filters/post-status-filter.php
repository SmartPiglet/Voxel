<?php

namespace Voxel\Post_Types\Filters;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Status_Filter extends Base_Filter {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'post-status',
		'label' => 'Post status',
		'placeholder' => '',
	];

	public function get_models(): array {
		return [
			'label' => $this->get_model( 'label', [ 'classes' => 'x-col-12' ]),
			'placeholder' => $this->get_placeholder_model(),
			'key' => $this->get_model( 'key', [ 'classes' => 'x-col-6' ]),
			'icon' => $this->get_icon_model(),
		];
	}

	public function query( \Voxel\Post_Types\Index_Query $query, array $args ): void {
		$value = $this->parse_value( $args[ $this->get_key() ] ?? null );
		if ( $value === null ) {
			return;
		}

		// index query will by default only show published posts, no further validation needed
		if ( $value === 'publish' ) {
			return;
		}

		// invalid post status, fallback to published posts
		$indexable_statuses = $this->post_type->repository->get_indexable_statuses();
		if ( ! isset( $indexable_statuses[ $value ] ) && $value !== 'all' ) {
			return;
		}

		// only allow statuses other than publish when users are querying their own posts
		$querying_current_user_posts = false;
		if ( is_user_logged_in() ) {
			foreach ( $this->post_type->get_filters() as $filter ) {
				if ( $filter->get_type() === 'user' ) {
					$author_id = $filter->parse_value( $args[ $filter->get_key() ] ?? null );
					if ( is_numeric( $author_id ) && ( absint( $author_id ) === absint( get_current_user_id() ) ) ) {
						$querying_current_user_posts = true;
					}

					break;
				}
			}
		}

		if ( ! ( current_user_can('administrator') || current_user_can('editor') || $querying_current_user_posts ) ) {
			if ( $value !== 'all' ) {
				$query->set_post_statuses( [ '__none__' ] );
			}
			return;
		}

		if ( $value === 'all' ) {
			$query->set_post_statuses( [] );
		} else {
			$query->set_post_statuses( [ $value ] );
		}
	}

	public function parse_value( $value ) {
		$value = sanitize_text_field( $value );
		if ( empty( $value ) ) {
			return null;
		}

		return $value;
	}

	public function frontend_props() {
		return [
			'choices' => $this->_get_selected_choices(),
			'display_as' => ( $this->elementor_config['display_as'] ?? null ) === 'buttons' ? 'buttons' : 'popup',
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
		];
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

	protected function _get_choices() {
		global $wp_post_statuses;
		$choices = [];
		$indexable_statuses = array_keys( $this->post_type->repository->get_indexable_statuses() );

		foreach ( $indexable_statuses as $status_key ) {
			$choices[ $status_key ] = [
				'key' => $status_key,
				'label' => $wp_post_statuses[ $status_key ]->label ?? $status_key,
			];
		}

		$choices['all'] = [
			'key' => 'all',
			'label' => _x( 'All', 'post status filter', 'voxel' ),
		];

		return $choices;
	}

	public function get_elementor_controls(): array {
		$choices = [];
		foreach ( $this->_get_choices() as $choice ) {
			$choices[ $choice['key'] ] = $choice['label'];
		}

		return [
			'value' => [
				'label' => _x( 'Default value', 'post status filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $choices,
			],
			'display_as' => [
				'label' => _x( 'Display as', 'post status filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup' => _x( 'Popup', 'post status filter', 'voxel-backend' ),
					'buttons' => _x( 'Buttons', 'post status filter', 'voxel-backend' ),
				],
				'conditional' => false,
			],
			'choices' => [
				'label' => _x( 'Choices', 'post status filter', 'voxel-backend' ),
				'description' => _x( 'Leave blank to list all options available', 'post status filter', 'voxel-backend' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $choices,
				'conditional' => false,
			],
		];
	}
}
