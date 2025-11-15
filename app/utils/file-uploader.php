<?php

namespace Voxel\Utils;

if ( ! defined('ABSPATH') ) {
	exit;
}

class File_Uploader {

	private static $upload_dir;

	private static $skip_subdir;

	public static $session_uploads_by_uid = [];

	protected static $session_unique_id;
	protected static function get_session_unique_id(): string {
		if ( static::$session_unique_id === null ) {
			static::$session_unique_id = strtolower( \Voxel\random_string(10) );
		}

		return static::$session_unique_id;
	}

	public static function upload( $file, $args = [] ) {
		require_once ABSPATH.'wp-admin/includes/file.php';
		require_once ABSPATH.'wp-admin/includes/media.php';
		require_once ABSPATH.'wp-admin/includes/image.php';

		$args = wp_parse_args( $args, [
			'upload_dir' => null,
			'skip_subdir' => false,
		] );

		if ( is_string( $args['upload_dir'] ) && ! empty( trim( $args['upload_dir'] ) ) ) {
			static::$upload_dir = $args['upload_dir'];
			static::$skip_subdir = $args['skip_subdir'] ?? false;
			add_filter( 'upload_dir', '\Voxel\Utils\File_Uploader::set_upload_dir', 35 );
		}

		$upload = wp_handle_upload( $file, [ 'test_form' => false ] );
		if ( ! empty( $upload['error'] ) ) {
			throw new \Exception( $upload['error'] );
		}

		remove_filter( 'upload_dir', '\Voxel\Utils\File_Uploader::set_upload_dir', 35 );

		return [
			'url' => $upload['url'],
			'path' => $upload['file'],
			'type' => $upload['type'],
			'name' => wp_basename( $upload['file'] ),
			'size' => $file['size'],
			'extension' => $file['ext'],
		];
	}

	public static function set_upload_dir( $pathdata ) {
		$dir = untrailingslashit( static::$upload_dir );

		if ( static::$skip_subdir ) {
			$pathdata['path'] = trailingslashit( $pathdata['basedir'] ) . $dir;
			$pathdata['url'] = trailingslashit( $pathdata['baseurl'] ) . $dir;
			$pathdata['subdir'] = '/'.$dir;
		} elseif ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path'] = trailingslashit( $pathdata['path'] ) . $dir;
			$pathdata['url'] = trailingslashit( $pathdata['url'] ) . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir = '/' . $dir . $pathdata['subdir'];
			$pathdata['path'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = $new_subdir;
		}

		return $pathdata;
	}

	public static function prepare( $key, $files ) {
		$prepared = [];

		if ( ! empty( $files['name'][ $key ] ) ) {
			foreach ( (array) $files['name'][ $key ] as $index => $filename ) {
				$filetype = wp_check_filetype( $filename );
				$prepared[] = [
					'name' => $filename,
					'type' => $filetype['type'],
					'ext' => $filetype['ext'],
					'tmp_name' => ( (array) $files['tmp_name'][ $key ] )[ $index ],
					'error' => ( (array) $files['error'][ $key ] )[ $index ],
					'size' => ( (array) $files['size'][ $key ] )[ $index ],
					'is_alias' => false,
					'unique_id' => substr( md5( wp_json_encode( [
						'name' => $filename,
						'type' => $filetype['type'],
						'size' => ( (array) $files['size'][ $key ] )[ $index ],
						'tmp_name' => ( (array) $files['tmp_name'][ $key ] )[ $index ],
					] ) ), 0, 8 ).'.'.static::get_session_unique_id(),
				];
			}
		}

		if ( ! empty( $_REQUEST['_vx_file_aliases'][ $key ] ) ) {
			foreach ( (array) ( $_REQUEST['_vx_file_aliases'][ $key ] ) as $index => $alias ) {
				if ( empty( $alias['path'] ) || ! isset( $alias['index'] ) ) {
					continue;
				}

				if ( ! isset( $files['name'][ $alias['path'] ][ $alias['index'] ] ) ) {
					continue;
				}

				$filename = $files['name'][ $alias['path'] ][ $alias['index'] ];
				$filetype = wp_check_filetype( $filename );
				$file = [
					'name' => $filename,
					'type' => $filetype['type'],
					'ext' => $filetype['ext'],
					'tmp_name' => ( (array) $files['tmp_name'][ $alias['path'] ] )[ $alias['index'] ],
					'error' => ( (array) $files['error'][ $alias['path'] ] )[ $alias['index'] ],
					'size' => ( (array) $files['size'][ $alias['path'] ] )[ $alias['index'] ],
					'is_alias' => true,
					'unique_id' => substr( md5( wp_json_encode( [
						'name' => $filename,
						'type' => $filetype['type'],
						'size' => ( (array) $files['size'][ $alias['path'] ] )[ $alias['index'] ],
						'tmp_name' => ( (array) $files['tmp_name'][ $alias['path'] ] )[ $alias['index'] ],
					] ) ), 0, 8 ).'.'.static::get_session_unique_id(),
				];

				array_splice( $prepared, $index, 0, [ $file ] );
			}
		}

		return $prepared;
	}

	public static function create_attachment( $uploaded_file, $args = [] ) {
		$attachment_id = wp_insert_attachment( array_merge( [
			'post_title' => $uploaded_file['name'],
			'post_content' => '',
			'post_mime_type' => $uploaded_file['type'],
			'post_status' => 'inherit',
		], $args ), $uploaded_file['path'] );

		if ( is_wp_error( $attachment_id ) ) {
			throw new \Exception( $attachment_id->get_error_message() );
		}

		if ( empty( $args['_skip_metadata'] ) ) {
			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $uploaded_file['path'] )
			);
		}

		if ( ! empty( $args['_display_filename'] ) ) {
			update_post_meta( $attachment_id, '_display_filename', sanitize_file_name( $args['_display_filename'] ) );
		}

		return $attachment_id;
	}
}
