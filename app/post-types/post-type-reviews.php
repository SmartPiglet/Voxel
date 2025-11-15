<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Type_Reviews {

	private $post_type, $repository;

	public function __construct( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
		$this->repository = $post_type->repository;
	}

	/**
	 * Get review categories as configured in the post type editor. Duplicates and
	 * items missing required details are removed.
	 *
	 * @since 1.2.9
	 */
	public function get_categories(): array {
		$categories = [];

		foreach ( (array) ($this->repository->config['settings']['reviews']['categories'] ?? [] ) as $category ) {
			if ( empty( $category['key'] ) || empty( $category['label'] ) || isset( $categories[ $category['key'] ] ) ) {
				continue;
			}

			$categories[ $category['key'] ] = [
				'label' => $category['label'],
				'key' => $category['key'],
				'icon' => $category['icon'] ?? '',
				'required' => (bool) ( $category['required'] ?? false ),
			];
		}

		if ( ! isset( $categories['score'] ) ) {
			$categories['score'] = [
				'label' => _x( 'Rating', 'review categories', 'voxel' ),
				'key' => 'score',
				'icon' => '',
				'required' => true,
			];
		}

		return $categories;
	}

	public function get_input_mode(): string {
		$input_mode = $this->repository->config['settings']['reviews']['input_mode'] ?? '';
		return \Voxel\from_list( $input_mode, [ 'numeric', 'stars' ], 'numeric' );
	}

	public function get_active_icon(): string {
		return $this->repository->config['settings']['reviews']['icons']['active'] ?? '';
	}

	public function get_inactive_icon(): string {
		return $this->repository->config['settings']['reviews']['icons']['inactive'] ?? '';
	}

	public function get_rating_levels(): array {
		$config = $this->repository->config['settings']['reviews']['rating_levels'] ?? [];
		$levels = $this->get_default_rating_levels();
		foreach ( $levels as $key => $level ) {
			if ( ! empty( $config[ $key ]['label'] ) ) {
				$levels[ $key ]['label'] = $config[ $key ]['label'];
			}

			if ( ! empty( $config[ $key ]['color'] ) ) {
				$levels[ $key ]['color'] = $config[ $key ]['color'];
			}
		}

		return $levels;
	}

	public function get_default_rating_levels(): array {
		return [
			'poor' => [
				'key' => 'poor',
				'label' => _x( 'Poor', 'reviews', 'voxel' ),
				'color' => null,
				'score' => -2,
			],
			'fair' => [
				'key' => 'fair',
				'label' => _x( 'Fair', 'reviews', 'voxel' ),
				'color' => null,
				'score' => -1,
			],
			'good' => [
				'key' => 'good',
				'label' => _x( 'Good', 'reviews', 'voxel' ),
				'color' => null,
				'score' => 0,
			],
			'very_good' => [
				'key' => 'very_good',
				'label' => _x( 'Very good', 'reviews', 'voxel' ),
				'color' => null,
				'score' => 1,
			],
			'excellent' => [
				'key' => 'excellent',
				'label' => _x( 'Excellent', 'reviews', 'voxel' ),
				'color' => null,
				'score' => 2,
			],
		];
	}

	public function get_timeline_config(): array {
		return [
			'post_type' => $this->post_type->get_key(),
			'input_mode' => $this->get_input_mode(),
			'active_icon' => \Voxel\get_icon_markup( $this->get_active_icon() ),
			'inactive_icon' => \Voxel\get_icon_markup( $this->get_inactive_icon() ),
			'default_icon' => \Voxel\get_svg('star.svg'),
			'categories' => array_values( array_map( function( $category ) {
				return [
					'label' => $category['label'],
					'key' => $category['key'],
					'required' => $category['required'],
				];
			}, $this->get_categories() ) ),
			'rating_levels' => array_values( array_map( function( $level ) {
				return [
					'score' => $level['score'],
					'label' => $level['label'],
					'key' => $level['key'],
					'color' => $level['color'],
				];
			}, $this->get_rating_levels() ) ),
		];
	}
}
