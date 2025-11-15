<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

$term = \Voxel\Term::get( get_queried_object() );
$taxonomy = $term->taxonomy;

if ( ! ( $taxonomy && $taxonomy->is_managed_by_voxel() ) ) {
	get_template_part('archive');
	return require locate_template( '404.php' );
}

$template_id = \Voxel\get_single_term_template_id();
if ( $template_id === null ) {
	return require locate_template( '404.php' );
}

if ( post_password_required( $template_id ) ) {
	return require locate_template( '404.php' );
}

if ( ! ( $template_id && \Elementor\Plugin::$instance->documents->get( $template_id )->is_built_with_elementor() ) ) {
	return require locate_template( '404.php' );
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
