<?php

namespace Voxel\Modules\Direct_Messages\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Attachments_Field extends \Voxel\Utils\Object_Fields\Base_Field {
	use \Voxel\Utils\Object_Fields\File_Field_Trait;

	protected function base_props(): array {
		return [
			'key' => 'files',
			'label' => 'Attachments',
			'max-count' => \Voxel\get( 'settings.messages.files.max_count', 1 ),
			'max-size' => \Voxel\get( 'settings.messages.files.max_size', 1000 ),
			'allowed-types' => (array) \Voxel\get( 'settings.messages.files.allowed_file_types', [
				'image/jpeg',
				'image/png',
				'image/webp',
			] ),
			'upload_dir' => null,
			'skip_subdir' => null,
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
				$is_image = wp_attachment_is_image( $id );

				if ( $is_image ) {
					$preview = wp_get_attachment_image_src( $id, 'medium_large', false );
					$items[] = [
						'is_image' => true,
						'name' => wp_basename( get_attached_file( $id ) ),
						'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
						'url' => $url,
						'preview' => $preview[0],
						'width' => $preview[1],
						'height' => $preview[2],
						'type' => get_post_mime_type( $id ),
					];
				} else {
					$items[] = [
						'is_image' => false,
						'name' => wp_basename( get_attached_file( $id ) ),
						'url' => $url,
						'type' => get_post_mime_type( $id ),
					];
				}
			}
		}

		if ( empty( $items ) ) {
			return null;
		}

		return $items;
	}
}
