<?php

namespace Voxel\Taxonomies;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Term_Singleton_Trait {

	private static $instances = [];

	public static function get( $term ) {
		if ( is_numeric( $term ) ) {
			$term = get_term( $term );
		}

		if ( ! $term instanceof \WP_Term ) {
			return null;
		}

		if ( ! array_key_exists( $term->term_id, self::$instances ) ) {
			self::$instances[ $term->term_id ] = new self( $term );
		}

		self::$instances[ $term->term_id ]->wp_term = $term;
		return self::$instances[ $term->term_id ];
	}

	public static function query( $args ) {
		$terms = get_terms( $args );
		return array_map( '\Voxel\Term::get', $terms );
	}

}
