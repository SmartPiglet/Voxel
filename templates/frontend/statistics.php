<?php

if ( \Voxel\is_edit_mode() || \Voxel\is_preview_mode() ) {
	get_header();
	if ( \Voxel\get_page_setting( 'voxel_hide_header' ) !== 'yes' ) {
		\Voxel\print_header();
	}

	the_content();

	if ( \Voxel\get_page_setting( 'voxel_hide_footer' ) !== 'yes' ) {
		\Voxel\print_footer();
	}
	get_footer();
	return;
}

$template_id = \Voxel\get( 'templates.post_stats' );

$requested_post = \Voxel\Post::get( $_GET['post_id'] ?? null );

if ( ! ( $requested_post && $requested_post->post_type && $requested_post->is_managed_by_voxel() ) ) {
	return require locate_template( '404.php' );
}

if ( ! ( $requested_post->post_type->is_tracking_enabled() && $requested_post->is_editable_by_current_user() ) ) {
	return require locate_template( '404.php' );
}

if ( post_password_required( $template_id ) ) {
	return require locate_template( '404.php' );
}

$document = \Elementor\Plugin::$instance->documents->get( $template_id );
if ( ! ( $document && $document->is_built_with_elementor() ) ) {
	return require locate_template( '404.php' );
}

add_filter( '_voxel/disable-visit-tracking', '__return_true' );

$frontend = \Elementor\Plugin::$instance->frontend;
add_action( 'wp_enqueue_scripts', function() use ( $frontend, $template_id ) {
	$frontend->enqueue_styles();
	\Voxel\enqueue_template_css( $template_id );
} );

get_header();

if ( \Voxel\get_page_setting( 'voxel_hide_header', $template_id ) !== 'yes' ) {
	\Voxel\print_header();
}

\Voxel\set_current_post( $requested_post );
echo $frontend->get_builder_content_for_display( $template_id );

if ( \Voxel\get_page_setting( 'voxel_hide_footer', $template_id ) !== 'yes' ) {
	\Voxel\print_footer();
}

get_footer();
