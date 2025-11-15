<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

if ( is_post_type_archive() ) {
	$post_type = Voxel\Post_Type::get( get_queried_object() );
	if ( $post_type && $post_type->has_archive_page() ) {
		do_action( 'voxel/post-type-archive', $post_type );
	} else {
		require_once locate_template( 'templates/defaults/archive.php' );
	}
} elseif ( is_category() || is_tag() ) {
	require_once locate_template( 'taxonomy.php' );
} else {
	require_once locate_template( 'templates/defaults/archive.php' );
}
