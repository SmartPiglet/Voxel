<?php

namespace Voxel\Controllers\Library;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Export_Controller extends \Voxel\Controllers\Base_Controller {

	protected $requested_post_types = [];
	protected $requested_product_types = [];
	protected $requested_taxonomies = [];
	protected $requested_terms = [];
	protected $requested_templates = [];

	protected
		$export_post_ids = [],
		$export_files = [];

	protected function authorize() {
		return current_user_can( 'administrator' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_backend.library.prepare_export', '@prepare_export' );
		$this->on( 'voxel_ajax_backend.library.download_export', '@download_export' );
	}

	protected function prepare_export() {
		try {
			$export_config = json_decode( stripslashes( $_REQUEST['export_config'] ?? '' ), true );
			$this->requested_templates = array_keys( $export_config['templates'] ?? [] );
			$this->requested_post_types = array_keys( $export_config['post_types'] ?? [] );

			foreach ( (array) $export_config['post_types'] ?? [] as $post_type_config ) {
				foreach ( (array) $post_type_config['product_types'] ?? [] as $k => $v ) {
					if ( ! in_array( $k, $this->requested_product_types, true ) ) {
						$this->requested_product_types[] = $k;
					}
				}

				foreach ( (array) $post_type_config['taxonomies'] ?? [] as $k => $v ) {
					if ( ! in_array( $k, $this->requested_taxonomies, true ) ) {
						$this->requested_taxonomies[] = $k;
					}

					if ( ! in_array( $k, $this->requested_terms, true ) ) {
						if ( isset( $post_type_config['terms'][ $k ] ) ) {
							$this->requested_terms[] = $k;
						}
					}
				}
			}

			$export = [
				'post_types' => $this->_export_svgs( $this->export_post_types() ),
				'taxonomies' => $this->export_taxonomies(),
				'terms' => $this->_export_svgs( $this->export_terms() ),
				'posts' => $this->export_posts(),
				'product_types' => $this->_export_svgs( $this->export_product_types() ),
				'config' => $this->_export_svgs( $this->export_config() ),
				// 'templates' => $this->export_templates(), // @todo
			];

			if ( empty( $export['post_types'] ) && empty( $export['taxonomies'] ) && empty( $export['posts'] ) && empty( $export['product_types'] ) ) {
				throw new \Exception( 'No data selected for export.' );
			}

			$export_key = strtolower( \Voxel\random_string(8) );
			wp_mkdir_p( \Voxel\uploads_dir( sprintf( '/vx-library-export/%s/', $export_key ) ) );
			$target_path = \Voxel\uploads_dir( sprintf( '/vx-library-export/%s/vx-export.zip', $export_key ) );
			$zip = new \ZipArchive;
			if ( $zip->open( $target_path, \ZipArchive::CREATE ) === true ) {
				foreach ( $export as $key => $data ) {
					$zip->addFromString(
						sprintf( 'vx-export/%s.json', $key ),
						wp_json_encode( $data, JSON_PRETTY_PRINT )
					);
				}

				// allow svgs
				add_filter( 'upload_mimes', function( $mimes ) {
					$mimes['svg'] = 'image/svg+xml';
					return $mimes;
				} );

				foreach ( $this->export_files as $file_id => $file_src ) {
					$file_type = wp_check_filetype( $file_src )['type'];
					$file_ext = wp_check_filetype( $file_src )['ext'];
					if ( $file_type && in_array( $file_type, get_allowed_mime_types(), true ) ) {
						if ( str_starts_with( $file_src, 'https://' ) || str_starts_with( $file_src, 'http://' ) ) {
							$file_contents = wp_remote_get( $file_src, [
								'httpversion' => '1.1',
								'sslverify' => false,
							] );

							if ( is_wp_error( $file_contents ) ) {
								continue;
							}

							$file_contents = wp_remote_retrieve_body( $file_contents );
							if ( empty( $file_contents ) || preg_match( '/<html|<head|<body/ims', $file_contents ) ) {
								continue;
							}

							$zip->addFromString( 'vx-export/files/'.$file_id, $file_contents );
						} else {
							$file_contents = file_get_contents( $file_src );
							$zip->addFromString( 'vx-export/files/'.$file_id, $file_contents );
						}
					}
				}

				$zip->close();
			}

			return wp_send_json( [
				'success' => true,
				'export_key' => $export_key,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function download_export() {
		$export_key = sanitize_key( $_REQUEST['export_key'] ?? '' );
		$target_path = \Voxel\uploads_dir( sprintf( '/vx-library-export/%s/vx-export.zip', $export_key ) );
		if ( file_exists( $target_path ) ) {
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment;');
			readfile( $target_path );

			\Voxel\delete_directory( \Voxel\uploads_dir( 'vx-library-export/' ) );
			die;
		}
	}

	protected function export_post_types() {
		$post_types = \Voxel\get('post_types');
		$export = [];

		foreach ( $this->requested_post_types as $post_type_key ) {
			if ( empty( $post_types[ $post_type_key ] ) ) {
				continue;
			}

			$post_type = $post_types[ $post_type_key ];

			foreach ( $post_type['templates'] as $k => $template_id ) {
				$this->_prepare_post_for_export( $template_id );
				$post_type['templates'][ $k ] = '<<#postid:'.$template_id.'#>>';
			}

			foreach ( ( $post_type['custom_templates'] ?? [] ) as $k1 => $template_group ) {
				foreach ( $template_group as $k2 => $template ) {
					$this->_prepare_post_for_export( $template['id'] );
					$post_type['custom_templates'][ $k1 ][ $k2 ]['id'] =  '<<#postid:'.$template['id'].'#>>';
				}
			}

			foreach ( $post_type['fields'] as $field_key => $field ) {
				if ( in_array( $field['type'], [ 'image', 'profile-avatar' ], true ) ) {
					if ( is_numeric( $field['default'] ?? null ) ) {
						$file_id = $this->_prepare_file_for_export_by_id( $field['default'] );
						$post_type['fields'][ $field_key ]['default'] = $file_id ? '<<#fileid:'.$file_id.'#>>' : '';
					}
				}

				if ( $field['type'] === 'ui-image' ) {
					if ( is_numeric( $field['image'] ?? null ) ) {
						$file_id = $this->_prepare_file_for_export_by_id( $field['image'] );
						$post_type['fields'][ $field_key ]['image'] = $file_id ? '<<#fileid:'.$file_id.'#>>' : '';
					}
				}

				if ( $field['type'] === 'post-relation' ) {
					if ( is_array( $field['post_types'] ?? null ) ) {
						foreach ( $field['post_types'] as $i => $v ) {
							if ( in_array( $v, $this->requested_post_types, true ) ) {
								$post_type['fields'][ $field_key ]['post_types'][ $i ] = sprintf( '<<#posttype:%s:key#>>', $v );
							}
						}
					}
				}

				if ( $field['type'] === 'product' ) {
					if ( in_array( $field['product-type'] ?? null, $this->requested_product_types, true ) ) {
						$post_type['fields'][ $field_key ]['product-type'] = sprintf( '<<#producttype:%s:key#>>', $field['product-type'] );
					}
				}

				if ( $field['type'] === 'taxonomy' ) {
					if ( in_array( $field['taxonomy'] ?? null, $this->requested_taxonomies, true ) ) {
						$post_type['fields'][ $field_key ]['taxonomy'] = sprintf( '<<#taxonomy:%s:key#>>', $field['taxonomy'] );
					}
				}
			}

			$export[ $post_type_key ] = $post_type;
		}

		return $export;
	}

	protected function export_taxonomies() {
		if ( empty( $this->requested_taxonomies ) ) {
			return [];
		}

		$taxonomies = \Voxel\get('taxonomies');
		$export = [];
		foreach ( $taxonomies as $taxonomy_key => $taxonomy ) {
			if ( ! in_array( $taxonomy_key, $this->requested_taxonomies, true ) ) {
				continue;
			}

			foreach ( ( $taxonomy['templates'] ?? [] ) as $k => $template_id ) {
				$this->_prepare_post_for_export( $template_id );
				$taxonomy['templates'][ $k ] = '<<#postid:'.$template_id.'#>>';
			}

			foreach ( $taxonomy['settings']['post_type'] as $i => $v ) {
				if ( in_array( $v, $this->requested_post_types, true ) ) {
					$taxonomy['settings']['post_type'][ $i ] = sprintf( '<<#posttype:%s:key#>>', $v );
				}
			}

			$taxonomy['_with_terms'] = in_array( $taxonomy_key, $this->requested_terms, true );

			$export[ $taxonomy['settings']['key'] ] = $taxonomy;
		}

		return $export;
	}

	protected function export_terms() {
		if ( empty( $this->requested_terms ) ) {
			return [];
		}

		$export = [];
		foreach ( $this->requested_terms as $taxonomy_key ) {
			$terms = \Voxel\get_terms( $taxonomy_key, [ 'fields' => [] ] );
			foreach ( $terms as $term_data ) {
				if ( $term = \Voxel\Term::get( $term_data['id'] ) ) {
					$export[] = $this->_export_term( $term, $term_data );
				}
			}
		}

		return $export;
	}

	protected function _export_term( \Voxel\Term $term, $term_data ) {
		$this->_prepare_file_for_export_by_id( $term->get_image_id() );

		$children = [];
		foreach ( ( $term_data['children'] ?? [] ) as $child_data ) {
			if ( $child = \Voxel\Term::get( $child_data['id'] ) ) {
				$children[] = $this->_export_term( $child, $child_data );
			}
		}

		return [
			'id' => $term->get_id(),
			'slug' => $term->get_slug(),
			'name' => $term->get_label(),
			'description' => $term->get_description(),
			'taxonomy' => sprintf( '<<#taxonomy:%s:key#>>', $term->taxonomy->get_key() ),
			'icon' => $term->get_icon(),
			'image' => $this->_prepare_file_for_export_by_id( $term->get_image_id() ),
			'area' => $term->get_area()['swlat'] ? $term->get_area() : null,
			'children' => $children,
		];
	}

	protected function export_product_types() {
		if ( empty( $this->requested_product_types ) ) {
			return [];
		}

		$product_types = \Voxel\Product_Type::all();
		$export = [];

		foreach ( $product_types as $product_type ) {
			if ( in_array( $product_type->get_key(), $this->requested_product_types, true ) ) {
				$export[ $product_type->get_key() ] = $product_type->get_config();
			}
		}

		return $export;
	}

	protected function export_templates() {
		$export = [
			'vx' => [],
		];

		if ( ! empty( $this->requested_templates ) ) {
			$templates = \Voxel\get('templates');
			foreach ( $this->requested_templates as $requested_template ) {
				$template_id = $templates[ $requested_template ] ?? null;
				if ( ! empty( $template_id ) && is_numeric( $template_id ) ) {
					$this->_prepare_post_for_export( $template_id );
					$export['vx'][ $requested_template ] =  '<<#postid:'.$template_id.'#>>';
				}
			}
		}

		return $export;
	}

	protected function export_config() {
		$active_kit = get_option( 'elementor_active_kit' );
		$kit_settings = get_post_meta( $active_kit, '_elementor_page_settings', true );

		$export = [
			'elementor' => [
				'system_colors' => $kit_settings['system_colors'] ?? [],
				'custom_colors' => $kit_settings['custom_colors'] ?? [],
			],
		];

		return $export;
	}

	protected function export_posts() {
		if ( empty( $this->export_post_ids ) ) {
			return [];
		}

		$post_ids = array_filter( array_map( 'absint', array_keys( $this->export_post_ids ) ) );
		$export = [];
		foreach ( $post_ids as $post_id ) {
			if ( $post = \Voxel\Post::get( $post_id ) ) {
				$export[ $post->get_id() ] = $this->_export_post( $post );
			}
		}

		return $export;
	}

	protected function _export_post( \Voxel\Post $post ) {
		$data = [
			'id' => $post->get_id(),
			'post_type' => $post->post_type->get_key(),
			'title' => $post->get_title(),
			'content' => $this->_export_post_content( $post->get_content() ),
			'slug' => $post->get_slug(),
			'is-elementor' => metadata_exists( 'post', $post->get_id(), '_elementor_edit_mode' ),
			'_elementor_page_settings' => get_post_meta( $post->get_id(), '_elementor_page_settings', true ),
			'_voxel_page_settings' => get_post_meta( $post->get_id(), '_voxel_page_settings', true ),
			'_elementor_data' => $this->_export_elementor_data( get_post_meta( $post->get_id(), '_elementor_data', true ) ),
		];

		return $data;
	}

	protected function _prepare_post_for_export( $post_id ) {
		$this->export_post_ids[ $post_id ] = true;
	}

	protected function _prepare_file_for_export( $src ) {
		if ( empty( $src ) ) {
			return null;
		}

		$extension = pathinfo( $src, PATHINFO_EXTENSION ) ?: 'jpg';
		$file_id = substr( md5( $src ), 0, 7 ).'.'.$extension;
		$this->export_files[ $file_id ] = $src;
		return $file_id;
	}

	protected function _prepare_file_for_export_by_id( $attachment_id ) {
		return $this->_prepare_file_for_export( get_attached_file( $attachment_id ) );
	}

	protected function _export_svgs( $data ) {
		$data = wp_json_encode( $data );
		$data = preg_replace_callback( '/"svg:(?P<id>\d+)"/ims', function( $match ) {
			$file_id = $this->_prepare_file_for_export_by_id( $match['id'] );
			if ( $file_id ) {
				return sprintf( '"svg:<<#fileid:%s#>>"', $file_id );
			} else {
				return '""';
			}
		}, $data );

		return json_decode( $data );
	}

	protected function _export_post_content( $content ) {
		$site_url = trailingslashit( get_site_url() );

		// remove srcset attributes
		$content = preg_replace( '/srcset="(.*?)"/', '', $content );

		// replace link hrefs and image srcs
		$content = preg_replace_callback( '/(?P<tag>href|src)="(?P<link>.*?)"/', function( $matches ) use ( $site_url ) {
			$link = $matches['link'];
			$file_type = wp_check_filetype( $link )['type'];
			$is_internal = substr( $link, 0, strlen( $site_url ) ) === $site_url;

			// a link within the site, points to an asset; try to export it
			if ( $is_internal && $file_type && in_array( $file_type, get_allowed_mime_types(), true ) ) {
				return sprintf( '%s="%s"', $matches['tag'], '<<#filesrc:'.$this->_prepare_file_for_export( $link ).'#>>' );
			}

			return $matches[0];
		}, $content );

		// for non-asset links, simply update the domain part
		$content = str_replace( $site_url, '<<#siteurl#>>', $content );

		return $content;
	}

	protected function _export_elementor_data( $encoded_data ) {
		$site_url = untrailingslashit( get_site_url() );
		$data = json_decode( $encoded_data );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
			return '';
		}

		foreach ( $data as $section_id => $section ) {
			$data[ $section_id ] = $this->_export_elementor_section( $section, $section_id );
		}

		$data = wp_json_encode( $data );
		$data = str_replace( str_replace( '/', '\\/', $site_url ), '<<#siteurl#>>', $data );

		return json_decode( $data );
	}

	protected function _maybe_export_setting_as_image( $setting ) {
		if (
			is_object( $setting )
			&& isset( $setting->id )
			&& isset( $setting->url )
			&& ! empty( $setting->id )
			&& ! str_starts_with( (string) $setting->id, '@tags()' )
			&& ( $file_id = $this->_prepare_file_for_export( $setting->url ) )
		) {
			$setting->url = '<<#filesrc:'.$file_id.'#>>';
			$setting->id = '<<#fileid:'.$file_id.'#>>';
			return $setting;
		}

		// icon controls
		if (
			is_object( $setting )
			&& isset( $setting->value )
			&& is_object( $setting->value )
			&& isset( $setting->value->id )
			&& isset( $setting->value->url )
			&& ! empty( $setting->value->id )
			&& ! str_starts_with( (string) $setting->value->id, '@tags()' )
			&& ( $file_id = $this->_prepare_file_for_export( $setting->value->url ) )
		) {
			$setting->value->url = '<<#filesrc:'.$file_id.'#>>';
			$setting->value->id = '<<#fileid:'.$file_id.'#>>';
			return $setting;
		}
	}

	protected function _export_elementor_section( $section, $section_id ) {
		foreach ( $section->settings as $key => $value ) {
			if ( $exported_value = $this->_maybe_export_setting_as_image( $value ) ) {
				$section->settings->{$key} = $exported_value;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $repeater_item_key => $repeater_item ) {
					if ( ! is_object( $repeater_item ) ) {
						continue;
					}

					foreach ( $repeater_item as $repeater_setting_key => $repeater_setting_value ) {
						if ( $exported_value = $this->_maybe_export_setting_as_image( $repeater_setting_value ) ) {
							$section->settings->{$key}[ $repeater_item_key ]->{$repeater_setting_key} = $exported_value;
						}
					}
				}
			}
		}

		if ( ! empty( $section->elements ) ) {
			foreach ( $section->elements as $element_id => $element ) {
				$section->elements[ $element_id ] = $this->_export_elementor_section( $element, $element_id );
			}
		}

		if ( $section->elType === 'widget' ) {
			if ( $section->widgetType === 'ts-print-template' && ( $section->settings->ts_template_id ?? null ) ) {
				$this->_prepare_post_for_export( $section->settings->ts_template_id );
				$section->settings->ts_template_id = '<<#postid:'.$section->settings->ts_template_id.'#>>';
			}

			if ( $section->widgetType === 'ts-template-tabs' ) {
				foreach ( (array) $section->settings->ts_tabs as $index => $item ) {
					if ( ( $item->template_id ?? null ) && ! str_starts_with( $item->template_id, '@tags()' ) ) {
						$this->_prepare_post_for_export( $item->template_id );
						$section->settings->ts_tabs[ $index ]->template_id = '<<#postid:'.$item->template_id.'#>>';
					}
				}
			}

			if ( $section->widgetType === 'ts-post-feed' ) {
				foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
					$card_key = sprintf( 'ts_card_template__%s', $post_type->get_key() );
					if ( is_numeric( $section->settings->{$card_key} ?? null ) ) {
						$this->_prepare_post_for_export( $section->settings->{$card_key} );
						$section->settings->{$card_key} = '<<#postid:'.$section->settings->{$card_key}.'#>>';
					}

					$manual_card_key = sprintf( 'ts_manual_card_template__%s', $post_type->get_key() );
					if ( is_numeric( $section->settings->{$manual_card_key} ?? null ) ) {
						$this->_prepare_post_for_export( $section->settings->{$manual_card_key} );
						$section->settings->{$manual_card_key} = '<<#postid:'.$section->settings->{$manual_card_key}.'#>>';
					}
				}
			}

			if ( $section->widgetType === 'ts-term-feed' ) {
				if ( is_numeric( $section->settings->ts_card_template ?? null ) ) {
					$this->_prepare_post_for_export( $section->settings->ts_card_template );
					$section->settings->ts_card_template = '<<#postid:'.$section->settings->ts_card_template.'#>>';
				}
			}

			if ( $section->widgetType === 'ts-search-form' ) {
				foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
					$card_key = sprintf( 'ts_card_template__%s', $post_type->get_key() );
					if ( is_numeric( $section->settings->{$card_key} ?? null ) ) {
						$this->_prepare_post_for_export( $section->settings->{$card_key} );
						$section->settings->{$card_key} = '<<#postid:'.$section->settings->{$card_key}.'#>>';
					}

					$map_card_key = sprintf( 'ts_card_template_map__%s', $post_type->get_key() );
					if ( is_numeric( $section->settings->{$map_card_key} ?? null ) ) {
						$this->_prepare_post_for_export( $section->settings->{$map_card_key} );
						$section->settings->{$map_card_key} = '<<#postid:'.$section->settings->{$map_card_key}.'#>>';
					}
				}

				foreach ( $this->requested_post_types as $post_type_key ) {
					$filter_list_key = sprintf( 'ts_filter_list__%s', $post_type_key );
					$filter_list_new_key = sprintf( 'ts_filter_list__<<#posttype:%s:key#>>', $post_type_key );
					if ( isset( $section->settings->{$filter_list_key} ) ) {
						$section->settings->{$filter_list_new_key} = $section->settings->{$filter_list_key};
						unset( $section->settings->{$filter_list_key} );
					}
				}
			}

			if ( $section->widgetType === 'ts-create-post' ) {
				if ( in_array( $section->settings->ts_post_type ?? null, $this->requested_post_types, true ) ) {
					$section->settings->ts_post_type = sprintf( '<<#posttype:%s:key#>>', $section->settings->ts_post_type );
				}
			}

			if ( $section->widgetType === 'ts-post-feed' ) {
				if ( in_array( $section->settings->ts_choose_post_type ?? null, $this->requested_post_types, true ) ) {
					$section->settings->ts_choose_post_type = sprintf( '<<#posttype:%s:key#>>', $section->settings->ts_choose_post_type );
				}

				foreach ( $this->requested_post_types as $post_type_key ) {
					$filter_list_key = sprintf( 'ts_filter_list__%s', $post_type_key );
					$filter_list_new_key = sprintf( 'ts_filter_list__<<#posttype:%s:key#>>', $post_type_key );
					if ( isset( $section->settings->{$filter_list_key} ) ) {
						$section->settings->{$filter_list_new_key} = $section->settings->{$filter_list_key};
						unset( $section->settings->{$filter_list_key} );
					}
				}
			}

			if ( $section->widgetType === 'ts-term-feed' ) {
				if ( in_array( $section->settings->ts_choose_taxonomy ?? null, $this->requested_taxonomies, true ) ) {
					$section->settings->ts_choose_taxonomy = sprintf( '<<#taxonomy:%s:key#>>', $section->settings->ts_choose_taxonomy );
				}
			}

			if ( $section->widgetType === 'ts-search-form' || $section->widgetType === 'quick-search' ) {
				if ( isset( $section->settings->ts_choose_post_types ) && is_array( $section->settings->ts_choose_post_types ) ) {
					foreach ( $section->settings->ts_choose_post_types as $k => $v ) {
						if ( in_array( $v, $this->requested_post_types, true ) ) {
							$section->settings->ts_choose_post_types[ $k ] = sprintf( '<<#posttype:%s:key#>>', $v );
						}
					}
				}
			}
		}

		return $section;
	}

}
