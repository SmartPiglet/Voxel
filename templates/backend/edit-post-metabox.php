<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$taxonomy_fields_as_custom_fields = array_filter( $post->get_fields(), function( $field ) {
	return $field->get_type() === 'taxonomy' && $field->get_prop('taxonomy') && $field->get_prop( 'backend_edit_mode' ) !== 'native_metabox';
} );

?>

<div id="vx-fields-wrapper" data-config="<?= esc_attr( wp_json_encode( [
	'hide_taxonomy_metaboxes' => array_values( array_map( function( $field ) {
		return $field->get_prop('taxonomy');
	}, $taxonomy_fields_as_custom_fields ) ),
] ) ) ?>">
	<iframe
		data-src="<?= add_query_arg( [
			'action' => 'admin.get_fields_form',
			'post_type' => $post->post_type->get_key(),
			'post_id' => $post->get_id(),
			'_wpnonce' => wp_create_nonce( 'vx_admin_edit_post' ),
		], home_url('/?vx=1') ) ?>"
		style="width: 100%; display: block; min-width: 100vw;"
		frameborder="0"
	></iframe>
</div>

<style type="text/css">
	.edit-post-meta-boxes-area.is-loading::before {
		display: none;
	}

	#vx-fields-wrapper {
		display: flex;
		justify-content: center;
		overflow: hidden;
	}


</style>
