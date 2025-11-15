<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

$post_type = \Voxel\Post_Type::get('post');
if ( $post_type && $post_type->has_archive_page() ) {
	do_action( 'voxel/post-type-archive', $post_type );
} else {
	get_header();
	\Voxel\print_header();

	\Voxel\print_footer();
	get_footer();
}
