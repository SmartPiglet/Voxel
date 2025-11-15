<?php

namespace Voxel\Controllers\Library;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Library_Controller extends \Voxel\Controllers\Base_Controller {

	protected $template_labels = [
		'header' => 'Header',
		'footer' => 'Footer',
		'timeline' => 'Timeline',
		'inbox' => 'Inbox',
		'privacy_policy' => 'Privacy Policy',
		'terms' => 'Terms & Conditions',
		'404' => '404 Not Found',
		'restricted' => 'Restricted Content',
		'auth' => 'Login & Registration',
		'pricing' => 'Pricing Plans',
		'current_plan' => 'Current Plan',
		'orders' => 'Orders page',
		// 'reservations' => 'Reservations page',
		'stripe_account' => 'Stripe Connect account',
		'checkout' => 'Checkout',
		// 'qr_tags' => 'Order tags: QR code handler',
		'kit_popups' => 'Popup styles',
		'kit_timeline' => 'Timeline styles',
	];

	protected function authorize() {
		return current_user_can( 'administrator' );
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 25 );
		$this->on( 'voxel_ajax_backend.library.get_package_list', '@get_package_list' );
	}

	protected function add_menu_page() {
		add_submenu_page(
			'voxel-settings',
			__( 'Library', 'voxel-backend' ),
			__( 'Library', 'voxel-backend' ),
			'manage_options',
			'voxel-library',
			function() {
				$cached_packages = $this->_get_cached_packages();
				$config = [
					'post_types' => [],
					'product_types' => [],
					'templates' => [],
					'taxonomies' => [],
					'packages' => [
						'list' => $cached_packages,
						'loading' => $cached_packages === null,
						'fallback_image' => \Voxel\get_image('post-types/lib.jpg'),
					],
				];

				$post_types = \Voxel\Post_Type::get_voxel_types();
				foreach ( $post_types as $post_type ) {
					$config['post_types'][ $post_type->get_key() ] = [
						'key' => $post_type->get_key(),
						'label' => $post_type->get_label(),
						'is_created_by_voxel' => $post_type->is_created_by_voxel(),
						'has_taxonomies' => false,
						'taxonomies' => [],
						'has_product_types' => false,
						'product_types' => [],
						'has_related_post_types' => false,
						'related_post_types' => [],
					];

					foreach ( $post_type->get_fields() as $field ) {
						if ( $field->get_type() === 'product' ) {
							foreach ( (array) ( $field->get_prop('product-types') ?? [] ) as $product_type_key ) {
								if ( $product_type = \Voxel\Product_Type::get( $product_type_key ) ) {
									$config['post_types'][ $post_type->get_key() ]['has_product_types'] = true;
									$config['post_types'][ $post_type->get_key() ]['product_types'][ $product_type->get_key() ] = true;
								}
							}
						}

						if ( $field->get_type() === 'post-relation' ) {
							foreach ( (array) $field->get_prop('post_types') as $v ) {
								if ( $related_post_type = \Voxel\Post_Type::get( $v ) ) {
									$config['post_types'][ $post_type->get_key() ]['has_related_post_types'] = true;
									$config['post_types'][ $post_type->get_key() ]['related_post_types'][ $related_post_type->get_key() ] = true;
								}
							}
						}
					}
				}

				$product_types = \Voxel\Product_Type::all();
				foreach ( $product_types as $product_type ) {
					$config['product_types'][ $product_type->get_key() ] = [
						'key' => $product_type->get_key(),
						'label' => $product_type->get_label(),
					];
				}

				$templates = \Voxel\get('templates');
				foreach ( (array) $templates as $template_key => $template_id ) {
					if ( $template_id ) {
						$config['templates'][ $template_key ] = [
							'label' => $this->template_labels[ $template_key ] ?? $template_key,
							'key' => $template_key,
						];
					}
				}

				$taxonomies = \Voxel\Taxonomy::get_voxel_taxonomies();
				foreach ( $taxonomies as $taxonomy ) {
					$config['taxonomies'][ $taxonomy->get_key() ] = [
						'key' => $taxonomy->get_key(),
						'label' => $taxonomy->get_label(),
					];

					foreach ( $taxonomy->get_post_types() as $post_type_key ) {
						if ( isset( $config['post_types'][ $post_type_key ] ) ) {
							$config['post_types'][ $post_type_key ]['has_taxonomies'] = true;
							$config['post_types'][ $post_type_key ]['taxonomies'][ $taxonomy->get_key() ] = true;
						}
					}
				}

				wp_enqueue_script( 'vx:library.js' );
				require locate_template( 'templates/backend/library/library.php' );
			}
		);
	}

	protected function get_package_list() {
		try {
			$force_get = ! empty( $_REQUEST['force_get'] ?? null );
			$packages = json_decode( get_transient( 'vx-library-packages' ), true );
			if ( ! is_array( $packages ) || empty( $packages ) || $force_get ) {
				// \Voxel\log('remote request');
				$request_url = ( \Voxel\is_dev_mode() && defined('VOXEL_DEV_LIBRARY') && VOXEL_DEV_LIBRARY )
					? home_url( '/wp-content/uploads/voxel-cache/platform/library-packages.json' )
					: 'https://getvoxel.io/wp-content/uploads/voxel-cache/platform/library-packages.json';

				$request = wp_remote_get( $request_url, [
					'timeout' => 10,
					'sslverify' => false,
				] );

				$response = (array) json_decode( wp_remote_retrieve_body( $request ), true );
				if ( ! is_array( $response ) ) {
					throw new \Exception( _x( 'Could not retrieve library, please try later.', 'vx-library', 'voxel-backend' ) );
				}

				delete_transient( 'vx-library-packages' );

				$packages = $this->_validate_packages( $response );
				set_transient( 'vx-library-packages', wp_json_encode( $packages ), 6 * HOUR_IN_SECONDS );
			}

			return wp_send_json( [
				'success' => true,
				'data' => $packages,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function _get_cached_packages() {
		$cached_packages = json_decode( get_transient( 'vx-library-packages' ), true );
		if ( ! is_array( $cached_packages ) ) {
			return null;
		}

		return $this->_validate_packages( $cached_packages );
	}

	protected function _validate_packages( $packages ) {
		$validated = [];

		foreach ( $packages as $package ) {
			if ( ! is_numeric( $package['id'] ?? null ) ) {
				continue;
			}

			if ( empty( $package['title'] ) ) {
				continue;
			}

			$validated[] = $package;
		}

		return $validated;
	}

}
