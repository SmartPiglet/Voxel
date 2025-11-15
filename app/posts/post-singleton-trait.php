<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Post_Singleton_Trait {

	/**
	 * Store post instances.
	 *
	 * @since 1.0
	 */
	private static $instances = [];

	/**
	 * Get a post based on its key.
	 *
	 * @since 1.0
	 */
	public static function get( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		if ( ! array_key_exists( $post->ID, static::$instances ) ) {
			static::$instances[ $post->ID ] = new static( $post );
		}

		return static::$instances[ $post->ID ];
	}

	/**
	 * Ignore cache and retrieve post information from db.
	 *
	 * @since 1.0
	 */
	public static function force_get( int $post_id ) {
		clean_post_cache( $post_id );
		if ( isset( static::$instances[ $post_id ] ) ) {
			unset( static::$instances[ $post_id ] );
		}

		\Voxel\Dynamic_Data\Data_Groups\Post\Post_Data_Group::unset( $post_id );
		\Voxel\Dynamic_Data\Data_Groups\Post\Simple_Post_Data_Group::unset( $post_id );

		return static::get( $post_id );
	}

	public static function query( array $args ): array {
		$posts = get_posts( $args );
		return array_map( '\Voxel\Post::get', $posts );
	}

	public static function find( array $args ) {
		$args['posts_per_page'] = 1;
		$args['offset'] = null;
		$results = static::query( $args );
		return array_shift( $results );
	}
}
