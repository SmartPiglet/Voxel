<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Utils\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class File_Field extends Base_Post_Field {
	use \Voxel\Utils\Object_Fields\File_Field_Trait;

	protected $supported_conditions = ['file'];

	protected $sortable = true;

	protected $props = [
		'type' => 'file',
		'label' => 'File',
		'max-count' => 1,
		'max-size' => 2000,
		'allowed-types' => [],
	];

	public function get_upload_key(): string {
		$indexes = [];
		$target = $this;
		while ( $target->get_repeater() ) {
			$indexes[] = $target->get_repeater_index();
			$target = $target->get_repeater();
		}

		$key = $this->get_id();
		if ( ! empty( $indexes ) ) {
			$indexes = array_reverse( $indexes );
			$key .= sprintf( '::row-%s', join( '.', $indexes ) );
		}

		return $key;
	}

	public function sanitize( $value ) {
		$upload_key = $this->get_upload_key();
		$files = [];
		$uploads = \Voxel\Utils\File_Uploader::prepare( $upload_key, $_FILES['files'] ?? [] );
		$upload_index = 0;

		foreach ( (array) $value as $file ) {
			if ( $file === 'uploaded_file' ) {
				$files[] = [
					'source' => 'new_upload',
					'data' => $uploads[ $upload_index ],
				];

				$upload_index++;
			} elseif ( is_numeric( $file ) ) {
				$files[] = [
					'source' => 'existing',
					'file_id' => absint( $file ),
				];
			}
		}

		return $files;
	}

	public function update( $value ): void {
		$file_ids = $this->_prepare_ids_from_sanitized_input( $value, [
			'post_parent' => $this->post->get_id(),
		] );

		if ( empty( $file_ids ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), join( ',', $file_ids ) );
		}
	}

	public function update_value_in_repeater( $value ) {
		$file_ids = $this->_prepare_ids_from_sanitized_input( $value, [
			'post_parent' => $this->post->get_id(),
		] );

		return ! empty( $file_ids ) ? $file_ids : null;
	}

	public function get_value_from_post() {
		$meta_value = get_post_meta( $this->post->get_id(), $this->get_key(), true );
		$ids = explode( ',', (string) $meta_value );
		$ids = array_filter( array_map( 'absint', $ids ) );
		return $ids;
	}

	protected function editing_value() {
		if ( ! $this->post ) {
			return [];
		}

		$ids = $this->get_value();
		if ( $ids === null ) {
			return [];
		}

		$config = [];

		foreach ( $ids as $attachment_id ) {
			if ( $attachment = get_post( $attachment_id ) ) {
				$config[] = [
					'source' => 'existing',
					'id' => $attachment->ID,
					'name' => wp_basename( get_attached_file( $attachment->ID ) ),
					'type' => $attachment->post_mime_type,
					'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
				];
			}
		}

		return $config;
	}

	public function get_required_scripts(): array {
		if ( $this->sortable ) {
			return [ 'sortable', 'vue-draggable' ];
		}

		return [];
	}

	public function get_default() {
		if ( ! isset( $this->props['default'] ) || ! is_numeric( $this->props['default'] ) ) {
			return null;
		}

		return absint( $this->props['default'] );
	}

	public function dynamic_data() {
		$is_loopable = absint( $this->get_model_value('max-count') ) >= 2;
		if ( $is_loopable ) {
			return Tag::Object_List( $this->get_label() )->items( function() {
				return (array) $this->get_value();
			} )->properties( function( $index, $item ) {
				return [
					'id' => Tag::Number('File ID')->render( function() use ( $item ) {
						return $item ?? $this->get_default();
					} ),
					'url' => Tag::URL('File URL')->render( function() use ( $item ) {
						return wp_get_attachment_url( $item ?? $this->get_default() ) ?: null;
					} ),
					'name' => Tag::String('File name')->render( function() use ( $item ) {
						$attachment = get_post( $item ?? $this->get_default() );
						return $attachment ? wp_basename( get_attached_file( $attachment->ID ) ) : null;
					} ),
					'ids' => Tag::String('All ids')->render( function() {
						$value = (array) $this->get_value();
						if ( empty( $value ) ) {
							return $this->get_default();
						}

						return join( ',', $value );
					} ),
				];
			} );
		} else {
			return Tag::Object( $this->get_label() )->properties( function() {
				return [
					'id' => Tag::Number('File ID')->render( function() {
						$value = (array) $this->get_value();
						return $value[0] ?? $this->get_default();
					} ),
					'url' => Tag::URL('File URL')->render( function() {
						$value = (array) $this->get_value();
						return wp_get_attachment_url( $value[0] ?? $this->get_default() ) ?: null;
					} ),
					'name' => Tag::String('File name')->render( function() {
						$value = (array) $this->get_value();
						$attachment = get_post( $value[0] ?? $this->get_default() );
						return $attachment ? wp_basename( get_attached_file( $attachment->ID ) ) : null;
					} ),
				];
			} );
		}
	}

	protected function overridable_models(): array {
		return [
			'max-count' => [
				'type' => 'number',
				'label' => 'Maximum file count',
			],
			'max-size' => [
				'type' => 'number',
				'label' => 'Max file size (kB)',
			],
		];
	}
}
