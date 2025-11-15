<?php

namespace Voxel\Controllers\Library;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Import_Controller extends \Voxel\Controllers\Base_Controller {

	protected $imported_post_types = [];
	protected $imported_product_types = [];
	protected $imported_taxonomies = [];
	protected $imported_terms = [];
	protected $import_key;
	protected $package_posts;

	protected function authorize() {
		return current_user_can( 'administrator' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.library.prepare_import', '@prepare_import' );
		$this->on( 'voxel_ajax_backend.library.run_import', '@run_import' );
		$this->on( 'voxel_ajax_backend.library.finish_import', '@finish_import' );
	}

	protected function prepare_import() {
		try {
			set_time_limit(300);

			require_once ABSPATH.'wp-admin/includes/file.php';
			WP_Filesystem();

			$import_key = strtolower( \Voxel\random_string(8) );
			$source = $_REQUEST['source'] ?? null;
			if ( $source === 'upload' ) {
				if ( empty( $_FILES['package'] ) ) {
					throw new \Exception( 'No file selected.' );
				}

				$package = $_FILES['package']['tmp_name'];
				$unzip_to = \Voxel\uploads_dir( sprintf( '/vx-library/%s/', $import_key ) );

				$result = unzip_file( $package, $unzip_to );
				if ( is_wp_error( $result ) ) {
					throw new \Exception( _x( 'Unpacking failed: ', 'vx-library', 'voxel-backend' ).$result->get_error_message() );
				}

				// zip file is no longer needed
				@unlink( $package );
			} else {
				// download from library
				$package_id = absint( $_REQUEST['package_id'] ?? null );
				if ( empty( $package_id ) ) {
					throw new \Exception( 'Invalid request.' );
				}

				$request_base_url = ( \Voxel\is_dev_mode() && defined('VOXEL_DEV_LIBRARY') && VOXEL_DEV_LIBRARY )
					? home_url( '/?vx=1&action=voxel_library.get_package' )
					: 'https://getvoxel.io/?vx=1&action=voxel_library.get_package';

				$request_url = add_query_arg( [
					'environment' => \Voxel\get_license_data('env'),
					'license_key' => \Voxel\get_license_data('key'),
					'site_url' => \Voxel\get_license_url(),
					'package_id' => $package_id,
				], $request_base_url );

				add_filter( 'http_request_args', function( $parsed_args, $url ) use ( $request_base_url ) {
					if ( str_starts_with( $url, $request_base_url ) ) {
						if ( ! is_array( $parsed_args['headers'] ) ) {
							$parsed_args['headers'] = [];
						}

						$parsed_args['headers']['Voxel-License-Key'] = \Voxel\get_license_data('key');
					}

					return $parsed_args;
				}, 10, 2 );

				$request = wp_remote_get( $request_url, [
					'timeout' => 10,
					'sslverify' => false,
				] );

				$response = (array) json_decode( wp_remote_retrieve_body( $request ), true );
				if ( empty( $response['success'] ) || empty( $response['package_url'] ) ) {
					throw new \Exception( $response['message'] ?? _x( 'Could not download package.', 'vx-library', 'voxel-backend' ) );
				}

				$package = download_url( $response['package_url'], $timeout = 600 );
				$unzip_to = \Voxel\uploads_dir( sprintf( '/vx-library/%s/', $import_key ) );
				if ( is_wp_error( $package ) ) {
					throw new \Exception( _x( 'Couldn\'t download package: ', 'vx-library', 'voxel-backend' ).$package->get_error_message() );
				}

				$result = unzip_file( $package, $unzip_to );
				@unlink( $package );
				if ( is_wp_error( $result ) ) {
					throw new \Exception( _x( 'Unpacking failed: ', 'vx-library', 'voxel-backend' ).$result->get_error_message() );
				}
			}

			$post_types = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/post_types.json', $import_key ) ) );
			$product_types = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/product_types.json', $import_key ) ) );
			$taxonomies = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/taxonomies.json', $import_key ) ) );

			$config = [
				'post_types' => [],
				'product_types' => [],
				'taxonomies' => [],
				'elementor_system_colors' => [],
				'elementor_custom_colors' => [],
			];

			foreach ( $post_types as $post_type ) {
				if ( ! empty( $post_type['settings']['key'] ?? null ) ) {
					$existing_post_type = \Voxel\Post_Type::get( $post_type['settings']['key'] );
					$config['post_types'][ $post_type['settings']['key'] ] = [
						'enabled' => true,
						'import_to' => $existing_post_type && $existing_post_type->is_managed_by_voxel() ? 'existing' : 'new',
						'key' => $post_type['settings']['key'],
						'singular' => $post_type['settings']['singular'],
						'plural' => $post_type['settings']['plural'],
						'permalinks' => [
							'custom' => $post_type['settings']['permalinks']['custom'] ?? false,
							'slug' => $post_type['settings']['permalinks']['slug'] ?? $post_type['settings']['key'],
						],

						'original_key' => $post_type['settings']['key'],
						'original_label' => $post_type['settings']['plural'],
					];
				}
			}

			foreach ( $product_types as $product_type ) {
				if ( ! empty( $product_type['settings']['key'] ?? null ) ) {
					$existing_product_type = \Voxel\Product_Type::get( $product_type['settings']['key'] );
					$config['product_types'][ $product_type['settings']['key'] ] = [
						'enabled' => true,
						'import_to' => $existing_product_type ? 'existing' : 'new',
						'key' => $product_type['settings']['key'],
						'label' => $product_type['settings']['label'],
						'original_key' => $product_type['settings']['key'],
						'original_label' => $product_type['settings']['label'],
					];
				}
			}

			foreach ( $taxonomies as $taxonomy ) {
				if ( ! empty( $taxonomy['settings']['key'] ?? null ) ) {
					$existing_taxonomy = \Voxel\Taxonomy::get( $taxonomy['settings']['key'] );
					$config['taxonomies'][ $taxonomy['settings']['key'] ] = [
						'enabled' => true,
						'import_to' => $existing_taxonomy && $existing_taxonomy->is_managed_by_voxel() ? 'existing' : 'new',
						'key' => $taxonomy['settings']['key'],
						'singular' => $taxonomy['settings']['singular'],
						'plural' => $taxonomy['settings']['plural'],
						'original_key' => $taxonomy['settings']['key'],
						'original_label' => $taxonomy['settings']['plural'],
						'_with_terms' => $taxonomy['_with_terms'] ?? false,
						'import_terms' => true,
					];
				}
			}

			$package_config = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/config.json', $import_key ) ) );
			if ( ! empty( $package_config['elementor']['system_colors'] ?? [] ) ) {
				foreach ( (array) $package_config['elementor']['system_colors'] as $color ) {
					$config['elementor_system_colors'][] = [
						'enabled' => false,
						'original_title' => $color['title'],
						'details' => $color,
					];
				}
			}

			if ( ! empty( $package_config['elementor']['custom_colors'] ?? [] ) ) {
				foreach ( (array) $package_config['elementor']['custom_colors'] as $color ) {
					$config['elementor_custom_colors'][] = [
						'enabled' => true,
						'original_title' => $color['title'],
						'details' => $color,
					];
				}
			}

			$all_empty = true;
			foreach ( $config as $data ) {
				if ( ! empty( $data ) ) {
					$all_empty = false;
					break;
				}
			}

			if ( $all_empty ) {
				throw new \Exception( _x( 'Package does not contain any data for import.', 'vx-library', 'voxel-backend' ) );
			}

			return wp_send_json( [
				'success' => true,
				'config' => $config,
				'import_key' => $import_key,
			] );
		} catch ( \Exception $e ) {
			\Voxel\delete_directory( \Voxel\uploads_dir( 'vx-library/' ) );

			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function run_import() {
		try {
			set_time_limit(300);

			$import_key = sanitize_key( $_REQUEST['import_key'] ?? '' );
			$config = json_decode( stripslashes( $_REQUEST['config'] ?? '' ), true );

			if ( ! file_exists( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/post_types.json', $import_key ) ) ) ) {
				throw new \Exception( 'Import data not found.' );
			}

			$this->import_key = $import_key;
			$post_types = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/post_types.json', $import_key ) ) );
			$product_types = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/product_types.json', $import_key ) ) );
			$taxonomies = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/taxonomies.json', $import_key ) ) );
			$this->package_posts = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/posts.json', $import_key ) ) );
			// $package_config = $this->_load_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/config.json', $import_key ) ) );

			foreach ( $config['post_types'] as $post_type ) {
				if ( ! $post_type['enabled'] ) {
					continue;
				}

				$post_type['key'] = sanitize_key( $post_type['key'] );

				if ( $post_type['import_to'] === 'existing' ) {
					$existing_post_type = \Voxel\Post_Type::get( $post_type['key'] );
					if ( ! ( $existing_post_type && $existing_post_type->is_managed_by_voxel() ) ) {
						throw new \Exception( sprintf( 'Post type "%s" not found', $post_type['key'] ) );
					}

					$this->imported_post_types[ $post_type['original_key'] ] = [
						'key' => $post_type['key'],
					];
				}

				if ( $post_type['import_to'] === 'new' ) {
					$existing_post_type = \Voxel\Post_Type::get( $post_type['key'] );
					if ( $existing_post_type ) {
						throw new \Exception( sprintf( 'Post type "%s" already exists.', $post_type['key'] ) );
					}

					$this->imported_post_types[ $post_type['original_key'] ] = [
						'key' => $post_type['key'],
						'singular' => $post_type['singular'],
						'plural' => $post_type['plural'],
						'permalinks' => $post_type['permalinks'],
					];
				}
			}

			foreach ( $config['taxonomies'] as $taxonomy ) {
				if ( ! $taxonomy['enabled'] ) {
					continue;
				}

				if ( $taxonomy['import_to'] === 'existing' ) {
					$existing_taxonomy = \Voxel\Taxonomy::get( $taxonomy['key'] );
					if ( ! ( $existing_taxonomy && $existing_taxonomy->is_managed_by_voxel() ) ) {
						throw new \Exception( sprintf( 'Taxonomy "%s" not found', $taxonomy['key'] ) );
					}

					$this->imported_taxonomies[ $taxonomy['original_key'] ] = [
						'key' => $taxonomy['key'],
					];
				}

				if ( $taxonomy['import_to'] === 'new' ) {
					$existing_taxonomy = \Voxel\Taxonomy::get( $taxonomy['key'] );
					if ( $existing_taxonomy ) {
						throw new \Exception( sprintf( 'Taxonomy "%s" already exists.', $taxonomy['key'] ) );
					}

					$this->imported_taxonomies[ $taxonomy['original_key'] ] = [
						'key' => $taxonomy['key'],
						'singular' => $taxonomy['singular'],
						'plural' => $taxonomy['plural'],
					];
				}

				if ( $taxonomy['_with_terms'] && $taxonomy['import_terms'] ) {
					$this->imported_terms[] = $this->imported_taxonomies[ $taxonomy['original_key'] ]['key'];
				}
			}

			foreach ( $config['product_types'] as $product_type ) {
				if ( ! $product_type['enabled'] ) {
					continue;
				}

				if ( $product_type['import_to'] === 'existing' ) {
					$existing_product_type = \Voxel\Product_Type::get( $product_type['key'] );
					if ( ! $existing_product_type ) {
						throw new \Exception( sprintf( 'Product type "%s" not found', $product_type['key'] ) );
					}

					$this->imported_product_types[ $product_type['original_key'] ] = [
						'key' => $product_type['key'],
					];
				}

				if ( $product_type['import_to'] === 'new' ) {
					$existing_product_type = \Voxel\Product_Type::get( $product_type['key'] );
					if ( $existing_product_type ) {
						throw new \Exception( sprintf( 'Product type "%s" already exists.', $product_type['key'] ) );
					}

					$this->imported_product_types[ $product_type['original_key'] ] = [
						'key' => $product_type['key'],
						'label' => $product_type['label'],
					];
				}
			}

			$active_kit = get_option( 'elementor_active_kit' );
			$kit_settings = get_post_meta( $active_kit, '_elementor_page_settings', true );
			$has_imported_colors = false;
			if ( isset( $kit_settings['system_colors'] ) ) {
				foreach ( $config['elementor_system_colors'] as $color ) {
					if ( ! $color['enabled'] ) {
						continue;
					}

					if ( empty( $color['details']['_id'] ) || empty( $color['details']['title'] ) || empty( $color['details']['color'] ) ) {
						continue;
					}

					$has_imported_colors = true;
					foreach ( $kit_settings['system_colors'] as $i => $system_color ) {
						if ( $system_color['_id'] === $color['details']['_id'] ) {
							$kit_settings['system_colors'][ $i ]['title'] = $color['details']['title'];
							$kit_settings['system_colors'][ $i ]['color'] = $color['details']['color'];
							continue(2);
						}
					}

					$kit_settings['system_colors'][] = [
						'_id' => $color['details']['_id'],
						'title' => $color['details']['title'],
						'color' => $color['details']['color'],
					];
				}
			}

			if ( isset( $kit_settings['custom_colors'] ) ) {
				foreach ( $config['elementor_custom_colors'] as $color ) {
					if ( ! $color['enabled'] ) {
						continue;
					}

					if ( empty( $color['details']['_id'] ) && empty( $color['details']['title'] ) && empty( $color['details']['color'] ) ) {
						continue;
					}

					$has_imported_colors = true;
					foreach ( $kit_settings['custom_colors'] as $i => $custom_color ) {
						if ( $custom_color['_id'] === $color['details']['_id'] ) {
							$kit_settings['custom_colors'][ $i ]['title'] = $color['details']['title'];
							$kit_settings['custom_colors'][ $i ]['color'] = $color['details']['color'];
							continue(2);
						}
					}

					$kit_settings['custom_colors'][] = [
						'_id' => $color['details']['_id'],
						'title' => $color['details']['title'],
						'color' => $color['details']['color'],
					];
				}
			}

			if ( empty( $this->imported_post_types ) && empty( $this->imported_product_types ) && empty( $this->imported_taxonomies ) && ! $has_imported_colors ) {
				throw new \Exception( 'No data selected for import.' );
			}

			$existing_post_types = \Voxel\get( 'post_types', [] );
			foreach ( $this->imported_post_types as $original_key => $details ) {
				$post_type_config = json_decode( $this->_replace_vars( wp_json_encode( $post_types[ $original_key ] ) ), true );
				$post_type_config['settings']['key'] = $details['key'];

				// if importing to an existing post type, preserve the current labels and permalinks
				$existing_post_type = $existing_post_types[ $details['key'] ] ?? null;
				if ( $existing_post_type ) {
					$post_type_config['settings']['singular'] = $existing_post_type['settings']['singular'];
					$post_type_config['settings']['plural'] = $existing_post_type['settings']['plural'];
					$post_type_config['settings']['permalinks'] = $existing_post_type['settings']['permalinks'];
				} else {
					$post_type_config['settings']['singular'] = $details['singular'];
					$post_type_config['settings']['plural'] = $details['plural'];
					$post_type_config['settings']['permalinks'] = $details['permalinks'];
				}

				$existing_post_types[ $details['key'] ] = $post_type_config;
			}
			\Voxel\set( 'post_types', $existing_post_types );

			$existing_taxonomies = \Voxel\get( 'taxonomies', [] );
			foreach ( $this->imported_taxonomies as $original_key => $details ) {
				$taxonomy_config = json_decode( $this->_replace_vars( wp_json_encode( $taxonomies[ $original_key ] ) ), true );
				$taxonomy_config['settings']['key'] = $details['key'];

				$existing_taxonomy = $existing_taxonomies[ $details['key'] ] ?? null;
				if ( $existing_taxonomy ) {
					$taxonomy_config['settings']['singular'] = $existing_taxonomy['settings']['singular'];
					$taxonomy_config['settings']['plural'] = $existing_taxonomy['settings']['plural'];
					$taxonomy_config['settings']['post_type'] = array_unique( array_merge( $existing_taxonomy['settings']['post_type'], $taxonomy_config['settings']['post_type'] ) );
				} else {
					$taxonomy_config['settings']['singular'] = $details['singular'];
					$taxonomy_config['settings']['plural'] = $details['plural'];

					// so wp_insert_term() doesn't fail for newly imported taxonomies
					register_taxonomy( $taxonomy_config['settings']['key'], $taxonomy_config['settings']['post_type'] );
				}

				unset( $taxonomy_config['_with_terms'] );
				$existing_taxonomies[ $details['key'] ] = $taxonomy_config;
			}
			\Voxel\set( 'taxonomies', $existing_taxonomies );

			$existing_product_types = \Voxel\get( 'product_types', [] );
			foreach ( $this->imported_product_types as $original_key => $details ) {
				$product_type_config = json_decode( $this->_replace_vars( wp_json_encode( $product_types[ $original_key ] ) ), true );
				$product_type_config['settings']['key'] = $details['key'];

				$existing_product_type = $existing_product_types[ $details['key'] ] ?? null;
				if ( $existing_product_type ) {
					$product_type_config['settings']['label'] = $existing_product_type['settings']['label'];
				} else {
					$product_type_config['settings']['label'] = $details['label'];
				}

				$existing_product_types[ $details['key'] ] = $product_type_config;
			}
			\Voxel\set( 'product_types', $existing_product_types );

			$terms = $this->_load_optional_json_file( \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/terms.json', $import_key ) ) );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$this->_import_term( $term );
				}
			}

			update_post_meta( $active_kit, '_elementor_page_settings', $kit_settings );
			// \Voxel\log($active_kit, $kit_settings);

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function finish_import() {
		global $wpdb;

		// cleanup db
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '__vx_import_postid'" );

		// index tables
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			if ( ! $post_type->index_table->exists() ) {
				try {
					$post_type->index_table->create();
				} catch ( \Exception $e ) {}
			}
		}

		// regenerate css
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor = \Elementor\Plugin::$instance;
			if ( is_object( $elementor ) && is_object( $elementor->files_manager ) && method_exists( $elementor->files_manager, 'clear_cache' ) ) {
				$elementor->files_manager->clear_cache();
			}
		}

		\Voxel\delete_directory( \Voxel\uploads_dir( 'vx-library/' ) );

		flush_rewrite_rules(true);
		die;
	}

	protected function _replace_vars( $data ) {
		$data = preg_replace_callback( '/<<#filesrc:(?P<file_id>.*?)#>>/', function( $matches ) {
			if ( $attachment_id = $this->_import_file( $matches['file_id'] ) ) {
				return wp_attachment_is_image( $attachment_id )
					? wp_get_attachment_image_url( $attachment_id, 'full' )
					: wp_get_attachment_url( $attachment_id );
			}

			return '';
		}, $data );

		$data = preg_replace_callback( '/<<#fileid:(?P<file_id>.*?)#>>/', function( $matches ) {
			return $this->_import_file( $matches['file_id'] ) ?: '';
		}, $data );

		$data = preg_replace_callback( '/<<#postid:(?P<post_id>.*?)#>>/', function( $matches ) {
			return $this->_import_post( $matches['post_id'] ) ?: '';
		}, $data );

		$data = str_replace( '<<#siteurl#>>', untrailingslashit( site_url() ), $data );

		$data = preg_replace_callback( '/<<#posttype:(?P<key>.*?):key#>>/', function( $matches ) {
			return $this->imported_post_types[ $matches['key'] ]['key'] ?? '';
		}, $data );

		$data = preg_replace_callback( '/<<#producttype:(?P<key>.*?):key#>>/', function( $matches ) {
			return $this->imported_product_types[ $matches['key'] ]['key'] ?? '';
		}, $data );

		$data = preg_replace_callback( '/<<#taxonomy:(?P<key>.*?):key#>>/', function( $matches ) {
			return $this->imported_taxonomies[ $matches['key'] ]['key'] ?? '';
		}, $data );

		return $data;
	}

	protected function _import_post( $original_post_id ) {
		// check if this post has already been imported
		if ( $post_id = $this->_get_imported_post_id( $original_post_id ) ) {
			return $post_id;
		}

		if ( ! isset( $this->package_posts[ $original_post_id ] ) ) {
			return null;
		}

		$item = $this->package_posts[ $original_post_id ];

		$post_id = wp_insert_post( [
			'post_type' => $item['post_type'],
			'post_title' => $item['title'],
			'post_content' => $this->_replace_vars( $item['content'] ),
			'post_status' => 'publish',
			'post_name' => $item['slug'],
			'meta_input' => [
				'__vx_import_postid' => $item['id'],
			],
		], true );

		if ( is_wp_error( $post_id ) ) {
			return null;
		}

		if ( $item['is-elementor'] ) {
			update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $post_id, '_elementor_page_settings', $item['_elementor_page_settings'] );
			update_post_meta( $post_id, '_voxel_page_settings', $item['_voxel_page_settings'] );

			if ( ! empty( $item['_elementor_data'] ) ) {
				update_post_meta( $post_id, '_elementor_data', wp_slash( $this->_replace_vars( wp_json_encode( $item['_elementor_data'] ) ) ) );
			}
		}

		return $post_id;
	}

	protected function _import_file( $file_id ) {
		// check if this file has already been imported
		if ( $attachment_id = $this->_get_imported_post_id( $file_id ) ) {
			return $attachment_id;
		}

		require_once ABSPATH.'wp-admin/includes/file.php';
		require_once ABSPATH.'wp-admin/includes/media.php';
		require_once ABSPATH.'wp-admin/includes/image.php';

		// allow svgs
		add_filter( 'upload_mimes', function( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		} );

		$filepath = \Voxel\uploads_dir( sprintf( '/vx-library/%s/vx-export/files/%s', $this->import_key, $file_id ) );
		if ( ! file_exists( $filepath ) ) {
			return null;
		}

		$upload = wp_upload_bits( $file_id, null, file_get_contents( $filepath ) );
		if ( ! empty( $upload['error'] ) ) {
			// @todo: log error mesage
			return null;
		}

		// create attachment
		$attachment_id = wp_insert_attachment( [
			'post_title' => pathinfo( $upload['file'], PATHINFO_FILENAME ),
			'guid' => $upload['url'],
			'post_mime_type' => $upload['type'],
			'post_status' => 'inherit',
		], $upload['file'] );

		if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
			return null;
		}

		// generate attachment details and sizes
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata(
			$attachment_id,
			get_attached_file( $attachment_id )
		) );

		// set temporary postmeta to identify this file in other import steps
		update_post_meta( $attachment_id, '__vx_import_postid', $file_id );

		return $attachment_id;
	}

	protected function _import_term( $term_data, $parent_id = null ) {
		$term_data = json_decode( $this->_replace_vars( wp_json_encode( $term_data ) ), true );
		if ( ! in_array( $term_data['taxonomy'], $this->imported_terms, true ) ) {
			return;
		}

		if ( term_exists( $term_data['slug'], $term_data['taxonomy'] ) ) {
			return;
		}

		$term_ids = wp_insert_term( $term_data['name'], $term_data['taxonomy'], [
			'description' => $term_data['description'],
			'slug' => $term_data['slug'],
			'parent' => $parent_id ?? 0,
		] );

		if ( is_wp_error( $term_ids ) ) {
			return;
		}

		$term_id = $term_ids['term_id'];

		if ( ! empty( $term_data['icon'] ) ) {
			update_term_meta( $term_id, 'voxel_icon', $term_data['icon'] );
		}

		if ( ! empty( $term_data['image'] ) ) {
			update_term_meta( $term_id, 'voxel_image', $term_data['image'] );
		}

		if ( ! empty( $term_data['area'] ) ) {
			update_term_meta( $term_id, 'voxel_area', wp_slash( wp_json_encode( $term_data['area'] ) ) );
		}

		foreach ( (array) ( $term_data['children'] ?? [] ) as $child_data ) {
			$this->_import_term( $child_data, $term_id );
		}
	}

	protected function _get_imported_post_id( $original_post_id ) {
		global $wpdb;

		$result = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '__vx_import_postid' AND meta_value = %s LIMIT 1",
			$original_post_id
		) );

		return (int) array_shift( $result );
	}

	protected function _load_json_file( $file ) {
		$raw_contents = file_get_contents( $file );
		$file_contents = json_decode( $raw_contents, ARRAY_A );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( 'Could not parse "'.basename($file).'", invalid file format.' );
		}

		return $file_contents;
	}

	protected function _load_optional_json_file( $file ) {
		$raw_contents = file_get_contents( $file );
		$file_contents = json_decode( $raw_contents, ARRAY_A );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		return $file_contents;
	}
}
