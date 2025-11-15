<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

function get_terms( $taxonomy, $args = [] ) {
	global $wpdb;

	$args = wp_parse_args( $args, [
		'fields' => [ 'order', 'icon' ],
		'orderby' => 'default',
		'slug__in' => null,
		'hide_empty' => null,
	] );

	$selects = [ 't.term_id AS id, tt.parent AS parent, t.slug AS slug, t.name AS label, tt.term_taxonomy_id AS term_taxonomy_id' ];
	$joins = [];
	$where = [];
	$orderby = [];

	foreach ( $args['fields'] as $field ) {
		if ( $field === 'order' ) {
			$selects[] = 't.voxel_order AS `order`';
		}

		if ( $field === 'icon' ) {
			$joins[] = "LEFT JOIN {$wpdb->termmeta} AS meta_icon ON (meta_icon.term_id = t.term_id AND meta_icon.meta_key = 'voxel_icon')";
			$selects[] = 'meta_icon.meta_value as icon';
		}

		if ( $field === 'icon_raw' ) {
			$joins[] = "LEFT JOIN {$wpdb->termmeta} AS meta_icon_raw ON (meta_icon_raw.term_id = t.term_id AND meta_icon_raw.meta_key = 'voxel_icon')";
			$selects[] = 'meta_icon_raw.meta_value as icon_raw';
		}
	}

	// taxonomy where clause
	$where[] = sprintf( 'tt.taxonomy IN (\'%s\')', esc_sql( $taxonomy ) );

	if ( is_array( $args['slug__in'] ) && ! empty( $args['slug__in'] ) ) {
		$_term_slugs = array_map( function( $term_slug ) {
			return '\''.esc_sql( sanitize_title( $term_slug ) ).'\'';
		}, $args['slug__in'] );

		$_joined_terms = join( ',', $_term_slugs );
		$where[] = sprintf( 'slug IN (%s)', $_joined_terms );
	}

	if ( $args['orderby'] === 'name' ) {
		$orderby[] = 'label ASC';
	} else {
		$orderby[] = 't.voxel_order ASC, label ASC';
	}

	if ( is_array( $args['hide_empty'] ) && ! empty( $args['hide_empty'] ) ) {
		$joins[] = "LEFT JOIN {$wpdb->termmeta} AS meta_counts ON (meta_counts.term_id = t.term_id AND meta_counts.meta_key = 'voxel:post_counts')";

		$where_counts = [];
		foreach ( $args['hide_empty'] as $post_type_key ) {
			$where_counts[] = sprintf( "JSON_EXTRACT( meta_counts.meta_value, '$.\"%s\"' ) > 0", esc_sql( $post_type_key ) );
		}

		$_where_counts = join( ' AND ', $where_counts );
		$where[] = "( JSON_VALID( meta_counts.meta_value ) AND {$_where_counts} )";
	}

	$_select_clauses = join( ', ', $selects );
	$_join_clauses = join( " \n ", $joins );
	$_where_clauses = join( ' AND ', $where );
	$_orderby_clauses = join( ', ', $orderby );
	$sql = "
		SELECT {$_select_clauses}
		FROM {$wpdb->terms} AS t
		INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
		{$_join_clauses}
		WHERE {$_where_clauses}
		ORDER BY {$_orderby_clauses}
	";
	// dd_sql($sql);
	$results = $wpdb->get_results( $sql, ARRAY_A );

	foreach ( $results as $key => $term ) {
		$results[ $key ]['slug'] = urldecode( $results[ $key ]['slug'] );
		if ( in_array( 'icon', $args['fields'], true ) ) {
			$results[ $key ]['icon'] = \Voxel\get_icon_markup( $term['icon'] );
		}
	}

	if ( $args['orderby'] === 'name' ) {
		return $results;
	} else {
		return \Voxel\_get_term_tree( $results );
	}
}

function _get_term_tree( $flat_array ) {
	$indexed_array = [];
	foreach ( $flat_array as $element ) {
		$indexed_array[ $element['id'] ] = $element;
		$indexed_array[ $element['id'] ]['children'] = [];
	}

	$tree = [];
	foreach ( $flat_array as $element ) {
		if ( $element['parent'] == 0 ) {
			$tree[] = &$indexed_array[ $element['id'] ];
		} else {
			$indexed_array[ $element['parent'] ]['children'][] = &$indexed_array[ $element['id'] ];
		}
	}

	return $tree;
}
