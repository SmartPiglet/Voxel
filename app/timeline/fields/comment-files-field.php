<?php

namespace Voxel\Timeline\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Comment_Files_Field extends \Voxel\Utils\Object_Fields\Base_Field {
	use \Voxel\Utils\Object_Fields\File_Field_Trait;

	protected function base_props(): array {
		return [
			'key' => 'timeline',
			'label' => _x( 'Attach files', 'timeline', 'voxel' ),
			'max-count' => \Voxel\get( 'settings.timeline.replies.images.max_count', 1 ),
			'max-size' => \Voxel\get( 'settings.timeline.replies.images.max_size', 2000 ),
			'private_upload' => true,
			'allowed-types' => \Voxel\get( 'settings.timeline.replies.images.allowed_formats', [
				'image/jpeg',
				// 'image/gif',
				'image/png',
				'image/webp',
			] ),
			'generate_sizes' => [ 'large' ],
		];
	}

	public function prepare_for_storage( $value ) {
		$file_ids = $this->_prepare_ids_from_sanitized_input( $value );
		return ! empty( $file_ids ) ? join( ',', $file_ids ) : null;
	}

	public function prepare_for_display( $value ) {
		$ids = explode( ',', (string) $value );
		$ids = array_filter( array_map( 'absint', $ids ) );

		$items = [];
		foreach ( $ids as $id ) {
			if ( $url = wp_get_attachment_url( $id ) ) {
				$items[] = [
					'source' => 'existing',
					'id' => $id,
					'name' => wp_basename( get_attached_file( $id ) ),
					'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
					'url' => $url,
					'preview' => wp_get_attachment_image_url( $id, 'large' ),
					'type' => get_post_mime_type( $id ),
				];
			}
		}

		if ( empty( $items ) ) {
			return null;
		}

		return $items;
	}
}
