<?php

namespace Voxel\Post_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Post_Type_Singleton_Trait {

	/**
	 * Store post type instances.
	 *
	 * @since 1.0
	 */
	private static $instances = [];

	/**
	 * Get a post type based on its key.
	 *
	 * @since 1.0
	 */
	public static function get( $key ) {
		if ( is_string( $key ) ) {
			$post_type = get_post_type_object( $key );
			if ( ! $post_type instanceof \WP_Post_Type ) {
				return null;
			}
		} elseif ( $key instanceof \WP_Post_Type ) {
			$post_type = $key;
		} else {
			return null;
		}

		if ( ! array_key_exists( $post_type->name, static::$instances ) ) {
			static::$instances[ $post_type->name ] = new static( $post_type );
		}

		// this is needed since some default post types reference the initial
		// post type configuration created by WordPress, instead of the one
		// modified by the theme.
		static::$instances[ $post_type->name ]->wp_post_type = $post_type;

		return static::$instances[ $post_type->name ];
	}

	public static function force_get( $post_id ) {
		if ( isset( static::$instances[ $post_id ] ) ) {
			unset( static::$instances[ $post_id ] );
		}

		return static::get( $post_id );
	}

	public static function get_all() {
		return \Voxel\Post_Types\Post_Type_Repository::get_all();
	}

	public static function get_voxel_types() {
		return \Voxel\Post_Types\Post_Type_Repository::get_voxel_types();
	}

	public static function get_other_types() {
		return \Voxel\Post_Types\Post_Type_Repository::get_other_types();
	}
}
