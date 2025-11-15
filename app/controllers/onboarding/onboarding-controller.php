<?php

namespace Voxel\Controllers\Onboarding;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Onboarding_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'administrator' );
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 39 );
		$this->on( 'after_switch_theme', '@start_onboarding' );
		$this->on( 'voxel_ajax_onboarding.verify_license', '@verify_license' );
		$this->on( 'voxel_ajax_onboarding.prepare_install', '@prepare_install' );
		$this->on( 'voxel_ajax_onboarding.start_blank', '@start_blank' );
		$this->on( 'update_themes_getvoxel.io', '@check_for_updates', 10, 4 );
		$this->on( 'http_request_args', '@update_request_headers', 10, 2 );
		$this->on( 'upgrader_pre_download', '@check_update_permissions', 10, 4 );
	}

	protected function add_menu_page() {
		add_submenu_page(
			'voxel-settings',
			__( 'Onboarding', 'voxel' ),
			__( 'Onboarding', 'voxel' ),
			'manage_options',
			'voxel-onboarding',
			function() {
				wp_enqueue_script( 'vx:onboarding.js' );
				require locate_template( 'templates/backend/onboarding/onboarding.php' );
			}
		);
	}

	protected function prepare_install() {
		try {
			if ( class_exists( '\Elementor\Plugin' ) ) {
				return wp_send_json( [
					'success' => true,
				] );
			}

			// $page_builder = ( $_GET['page_builder'] ?? null ) === 'elementor' ? 'elementor' : 'voxel-elements';
			$page_builder = 'elementor';

			require_once ABSPATH.'wp-admin/includes/plugin.php';
			require_once ABSPATH.'wp-admin/includes/file.php';
			WP_Filesystem();

			if ( ! empty( $_GET['activate_page_builder'] ) ) {
				if ( $page_builder === 'elementor' ) {
					activate_plugin( 'elementor/elementor.php' );
				} else {
					activate_plugin( 'voxel-elements/elementor.php' );
				}

				delete_transient( 'elementor_activation_redirect' );
			} else {
				if ( $page_builder === 'elementor' ) {
					if ( ! file_exists( WP_PLUGIN_DIR.'/elementor/elementor.php' ) ) {
						$package_url = 'https://downloads.wordpress.org/plugin/elementor.3.32.3.zip';
						$download_to = \Voxel\uploads_dir('elementor.zip');
						@unlink( $download_to );

						$download_file = download_url( $package_url, $timeout = 600 );
						if ( is_wp_error( $download_file ) ) {
							throw new \Exception( _x( 'Couldn\'t download Elementor: ', 'onboarding', 'voxel-backend' ).$download_file->get_error_message() );
						}

						@copy( $download_file, $download_to );
						unlink( $download_file );

						// unzip
						$unzip_to = WP_PLUGIN_DIR.'/';
						$result = unzip_file( $download_to, $unzip_to );
						if ( is_wp_error( $result ) ) {
							throw new \Exception( _x( 'Unpacking failed: ', 'onboarding', 'voxel-backend' ).$result->get_error_message() );
						}

						// zip file is no longer needed
						@unlink( $download_to );
					}

					deactivate_plugins( 'voxel-elements/elementor.php', true );
				} else {
					if ( ! file_exists( WP_PLUGIN_DIR.'/voxel-elements/elementor.php' ) ) {
						$package_url = 'https://mylisting-cdn.sfo2.cdn.digitaloceanspaces.com/voxel-elements/voxel-elements.zip';
						$download_to = \Voxel\uploads_dir('voxel-elements.zip');
						@unlink( $download_to );

						$download_file = download_url( $package_url, $timeout = 600 );
						if ( is_wp_error( $download_file ) ) {
							throw new \Exception( _x( 'Couldn\'t download Voxel Elements: ', 'onboarding', 'voxel-backend' ).$download_file->get_error_message() );
						}

						@copy( $download_file, $download_to );
						unlink( $download_file );

						// unzip
						$unzip_to = WP_PLUGIN_DIR.'/';
						$result = unzip_file( $download_to, $unzip_to );
						if ( is_wp_error( $result ) ) {
							throw new \Exception( _x( 'Unpacking failed: ', 'onboarding', 'voxel-backend' ).$result->get_error_message() );
						}

						// zip file is no longer needed
						@unlink( $download_to );
					}

					deactivate_plugins( 'elementor/elementor.php', true );
				}
			}

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

	protected function verify_license() {
		try {
			// \Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_onboarding' ); // @todo
			if ( ! current_user_can('administrator') ) {
				throw new Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$license_key = strtoupper( sanitize_text_field( $_GET['license_key'] ?? null ) );
			if ( empty( $license_key ) || ! str_starts_with( $license_key, 'VX' ) ) {
				throw new \Exception( _x( 'Invalid license key.', 'onboarding', 'voxel-backend' ) );
			}

			if ( strlen( $license_key ) !== 29 || substr_count( $license_key, '-' ) !== 4 ) {
				throw new \Exception( _x( 'Invalid license key.', 'onboarding', 'voxel-backend' ) );
			}

			$environment = sanitize_text_field( $_GET['environment'] ?? null );
			if ( ! in_array( $environment, [ 'staging', 'production' ], true ) ) {
				throw new \Exception( _x( 'Site environment not provided.', 'onboarding', 'voxel-backend' ) );
			}

			$request_url = add_query_arg( [
				'action' => 'voxel_licenses.verify',
				'mode' => 'update',
				'environment' => $environment,
				'license_key' => $license_key,
				'site_url' => \Voxel\get_license_url(),
			], 'https://getvoxel.io/?vx=1' );

			$request = wp_remote_get( $request_url, [
				'timeout' => 10,
			] );

			$response = (array) json_decode( wp_remote_retrieve_body( $request ) );
			if ( ! isset( $response['success'] ) ) {
				throw new \Exception( _x( 'Verification request failed, please try again.', 'onboarding', 'voxel-backend' ) );
			}

			if ( ! $response['success'] ) {
				throw new \Exception( $response['message'] ?? _x( 'Could not verify license.', 'onboarding', 'voxel-backend' ) );
			}

			\Voxel\update_license_data( [
				'key' => $license_key,
				'env' => $environment,
				'active' => true,
				'last_checked' => date( 'Y-m-d H:i:s', time() ),
			] );

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

	protected function start_blank() {
		\Voxel\set( 'onboarding', [ 'done' => true ] );
		return wp_send_json( [
			'success' => true,
		] );
	}

	protected function start_onboarding() {
		if ( ! \Voxel\get( 'onboarding.done' ) && ! \Voxel\get( 'onboarding.redirected' ) ) {
			\Voxel\set( 'onboarding.redirected', true );
			wp_redirect( admin_url( 'admin.php?page=voxel-onboarding&tab=welcome' ) );
			exit;
		}
	}

	protected function check_for_updates( $update, $theme_data, $theme_stylesheet, $locales ) {
		if ( $theme_stylesheet === 'voxel' && ! ( \Voxel\is_dev_mode() || locate_template('.git/index') ) ) {
			$request = wp_remote_get( 'https://getvoxel.io/?vx=1&action=voxel_releases.get_latest_version', [
				'timeout' => 5,
			] );

			$response = (array) json_decode( wp_remote_retrieve_body( $request ) );
			if ( isset( $response['version'] ) && isset( $response['url'] ) ) {
				$package_url = add_query_arg( [
					'environment' => \Voxel\get_license_data('env'),
					'license_key' => \Voxel\get_license_data('key'),
					'site_url' => \Voxel\get_license_url(),
				], 'https://getvoxel.io/?vx=1&action=voxel_releases.download_external' );

				$response['package'] = $package_url;
				return $response;
			}
		}

		return $update;
	}

	protected function update_request_headers( $parsed_args, $url ) {
		if ( str_starts_with( $url, 'https://getvoxel.io/?vx=1&action=voxel_releases.download_external' ) ) {
			if ( ! is_array( $parsed_args['headers'] ) ) {
				$parsed_args['headers'] = [];
			}

			$parsed_args['headers']['Voxel-License-Key'] = \Voxel\get_license_data('key');
			return $parsed_args;
		}

		return $parsed_args;
	}

	protected function check_update_permissions( $reply, $package, $upgrader, $hook_extra ) {
		if ( str_starts_with( $package, 'https://getvoxel.io/?vx=1&action=voxel_releases.download_external' ) ) {
			$request_url = add_query_arg( [
				'environment' => \Voxel\get_license_data('env'),
				'license_key' => \Voxel\get_license_data('key'),
				'site_url' => \Voxel\get_license_url(),
				'mode' => 'check_permissions',
			], 'https://getvoxel.io/?vx=1&action=voxel_releases.download_external' );

			$request = wp_remote_get( $request_url, [ 'timeout' => 5 ] );
			$response = (array) json_decode( wp_remote_retrieve_body( $request ) );
			if ( empty( $response['success'] ) ) {
				return new \WP_Error( 'download_failed', $response['message'] ?? 'Could not retrieve package.' );
			}
		}

		return $reply;
	}

	protected function activate_plugin( $plugin ) {
		$plugin = plugin_basename( trim( $plugin ) );
		$current = get_option( 'active_plugins', array() );
		$valid = validate_plugin( $plugin );
		if ( is_wp_error( $valid ) ) {
			throw new \Exception( $valid->get_error_message() );
		}

		$requirements = validate_plugin_requirements( $plugin );
		if ( is_wp_error( $requirements ) ) {
			throw new \Exception( $requirements->get_error_message() );
		}

		if ( ! in_array( $plugin, $current, true ) ) {
			$current = get_option( 'active_plugins', [] );
			$current[] = $plugin;
			sort( $current );
			update_option( 'active_plugins', $current );
		}
	}

	protected function deactivate_plugin( $plugin ) {
		deactivate_plugins( $plugin, true );
	}
}
