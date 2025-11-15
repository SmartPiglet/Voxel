<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Ajax_Controller extends Base_Controller {

	/**
	 * Custom AJAX handler for better performance compared to admin-ajax.php
	 *
	 * @link  https://woocommerce.wordpress.com/2015/07/30/custom-ajax-endpoints-in-2-4/
	 * @since 1.0
	 */
	protected function hooks() {
		$this->on( 'init', '@define_ajax', 0 );
		$this->on( 'template_redirect', '@do_ajax', 0 );
	}

	protected function define_ajax() {
		if ( empty( $_GET['vx'] ) ) {
			return;
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		if ( ! defined( 'VOXEL_AJAX_HIDE_ERRORS' ) ) {
			define( 'VOXEL_AJAX_HIDE_ERRORS', true );
		}

		// prevent malformed JSON
		if ( VOXEL_AJAX_HIDE_ERRORS ) {
			@ini_set( 'display_errors', 0 );
			$GLOBALS['wpdb']->hide_errors();
		}

       	// close session to allow concurrent requests
        session_write_close();
	}

	protected function do_ajax() {
		if ( empty( $_GET['vx'] ) ) {
			return;
		}

		// send headers
		if ( ! headers_sent() ) {
			send_origin_headers();
			@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			@header( 'X-Robots-Tag: noindex' );
			send_nosniff_header();
			nocache_headers();
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "Cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
		}

		// 'action' parameter is required
		if ( empty( $_REQUEST['action'] ) ) {
			wp_die();
		}

		global $wp_query;
		$wp_query->set( 'vx-action', sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) );
		$action = $wp_query->get( 'vx-action' );

		if ( is_user_logged_in() ) {
			// an action must be registered
			if ( ! has_action( "voxel_ajax_{$action}" ) ) {
				wp_die();
			}

			status_header(200);
			do_action( "voxel_ajax_{$action}" );
		} else {
			// an action must be registered
			if ( ! has_action( "voxel_ajax_nopriv_{$action}" ) ) {
				wp_die();
			}

			status_header(200);
			do_action( "voxel_ajax_nopriv_{$action}" );
		}

		wp_die();
	}
}
