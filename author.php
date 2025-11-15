<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

$post_type = \Voxel\Post_Type::get( 'profile' );
if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
	require_once locate_template( 'templates/defaults/author.php' );
	return;
}

$user = \Voxel\User::get( get_the_author_meta('ID') );
if ( ! $user ) {
	require_once locate_template( 'templates/defaults/author.php' );
	return;
}

\Voxel\set_current_post( $user->get_or_create_profile() );

$template_id = \Voxel\get_single_post_template_id( $post_type );

if ( post_password_required( $template_id ) ) {
	require_once locate_template( 'templates/defaults/author.php' );
	return;
}

if ( ! \Elementor\Plugin::$instance->documents->get( $template_id )->is_built_with_elementor() ) {
	require_once locate_template( 'templates/defaults/author.php' );
	return;
}

$frontend = \Elementor\Plugin::$instance->frontend;
add_action( 'wp_enqueue_scripts', function() use ( $frontend, $template_id ) {
	$frontend->enqueue_styles();
	\Voxel\enqueue_template_css( $template_id );
} );

get_header();
\Voxel\set_current_post( $user->get_or_create_profile() );
if ( \Voxel\get_page_setting( 'voxel_hide_header', $template_id ) !== 'yes' ) {
	\Voxel\print_header();
}

echo $frontend->get_builder_content_for_display( $template_id );

if ( \Voxel\get_page_setting( 'voxel_hide_footer', $template_id ) !== 'yes' ) {
	\Voxel\print_footer();
}
get_footer();