<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Helper; The original item ID is stored in `__demo_import_postid` meta on
 * import. Use this to identify imported items and retrieve their new ID
 * assigned to them. This meta key is removed once the import is finished.
 *
 * @since 1.0
 */
function get_imported_post_id( $import_id ) {
	global $wpdb;

	$result = $wpdb->get_col( $wpdb->prepare( "
		SELECT post_id FROM {$wpdb->postmeta}
		WHERE meta_key = '__demo_import_postid'
		AND meta_value = %s
		LIMIT 1
	", $import_id ) );

	return (int) array_shift( $result );
}

function import_post_content( $content ) {
	// replace <<#filesrc:(file_id)#>> with link to file (or large size if it's an image)
	// file_id is the post id in the website the demo was exported from, or the filename
	// for attachments
	$content = preg_replace_callback( '/<<#filesrc:(?P<file_id>.*?)#>>/', function( $matches ) {
		if ( $attachment_id = get_imported_post_id( $matches['file_id'] ) ) {
			return wp_attachment_is_image( $attachment_id )
				? wp_get_attachment_image_url( $attachment_id, 'large' )
				: wp_get_attachment_url( $attachment_id );
		}

		// in case the file was not imported (unlikely), use "#" as the link href
		return '#';
	}, $content );

	// replace <<#siteurl#>> with untrailingslashit( site_url() )
	$content = str_replace( '<<#siteurl#>>', untrailingslashit( site_url() ), $content );

	return $content;
}

function import_elementor_data( $data ) {
	// replace <<#filesrc:(file_id)#>> with link to file (or full size if it's an image)
	$data = preg_replace_callback( '/<<#filesrc:(?P<file_id>.*?)#>>/', function( $matches ) {
		if ( $attachment_id = get_imported_post_id( $matches['file_id'] ) ) {
			return wp_attachment_is_image( $attachment_id )
				? wp_get_attachment_image_url( $attachment_id, 'full' )
				: wp_get_attachment_url( $attachment_id );
		}

		return '';
	}, $data );

	// replace <<#fileid:(file_id)#>> with imported attachment id
	$data = preg_replace_callback( '/<<#fileid:(?P<file_id>.*?)#>>/', function( $matches ) {
		return ( $attachment_id = get_imported_post_id( $matches['file_id'] ) )
			? $attachment_id
			: '';
	}, $data );

	// replace <<#postid:(post_id)#>> with imported attachment id
	$data = preg_replace_callback( '/<<#postid:(?P<post_id>.*?)#>>/', function( $matches ) {
		return ( $post_id = get_imported_post_id( $matches['post_id'] ) )
			? $post_id
			: '';
	}, $data );

	// replace <<#siteurl#>> with untrailingslashit( site_url() )
	$data = str_replace( '<<#siteurl#>>', untrailingslashit( site_url() ), $data );

	return $data;
}

function import_dynamic_values( $data ) {
	return \Voxel\import_elementor_data( $data );
}
