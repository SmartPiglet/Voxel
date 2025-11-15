<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

$_post = \Voxel\Post::get( $post );
$post_type = $_post->post_type;

if ( $post_type->get_key() === 'elementor_library' && ! ( \Voxel\is_edit_mode() || \Voxel\is_preview_mode() ) ) {
	require locate_template( 'preview.php' );
	return;
}

if ( $_post->is_built_with_elementor() ) {
	$template_id = $post->ID;
	get_header();
	if ( \Voxel\get_page_setting( 'voxel_hide_header', $template_id ) !== 'yes' ) {
		\Voxel\print_header();
	}

	while ( have_posts() ): the_post();
		the_content();
	endwhile;

	if ( \Voxel\get_page_setting( 'voxel_hide_footer', $template_id ) !== 'yes' ) {
		\Voxel\print_footer();
	}
	get_footer();
	return;
}

if ( ! $post_type->is_managed_by_voxel() ) {
	require_once locate_template( 'templates/defaults/single.php' );
}

$template_id = \Voxel\get_single_post_template_id( $post_type );
if ( post_password_required( $template_id ) ) {
	require_once locate_template( 'templates/defaults/single.php' );
	return;
}

if ( ! \Voxel\is_elementor_active() ) {
	require_once locate_template( 'templates/defaults/single.php' );
	return;
}

$document = \Elementor\Plugin::$instance->documents->get( $template_id );
if ( ! ( $document && $document->is_built_with_elementor() ) ) {
	require_once locate_template( 'templates/defaults/single.php' );
	return;
}

$frontend = \Elementor\Plugin::$instance->frontend;
add_action( 'wp_enqueue_scripts', function() use ( $frontend, $template_id ) {
	$frontend->enqueue_styles();
	\Voxel\enqueue_template_css( $template_id );
} );

get_header();

if ( \Voxel\get_page_setting( 'voxel_hide_header', $template_id ) !== 'yes' ) {
	\Voxel\print_header();
}

echo $frontend->get_builder_content_for_display( $template_id );

if ( \Voxel\get_page_setting( 'voxel_hide_footer', $template_id ) !== 'yes' ) {
	\Voxel\print_footer();
}

get_footer();
