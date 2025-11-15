<?php

namespace Voxel\Taxonomies;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Taxonomy_Singleton_Trait {

	private static $instances = [];

	public static function get( $key ) {
		if ( is_string( $key ) ) {
			$taxonomy = get_taxonomy( $key );
			if ( ! $taxonomy instanceof \WP_Taxonomy ) {
				return null;
			}
		} elseif ( $key instanceof \WP_Taxonomy ) {
			$taxonomy = $key;
		} else {
			return null;
		}

		if ( ! array_key_exists( $taxonomy->name, self::$instances ) ) {
			self::$instances[ $taxonomy->name ] = new static( $taxonomy );
		}

		return self::$instances[ $taxonomy->name ];
	}

}
