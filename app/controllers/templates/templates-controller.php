<?php

namespace Voxel\Controllers\Templates;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Templates_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page' );
		$this->filter( 'display_post_states', '@display_template_labels', 100, 2 );
	}

	protected function add_menu_page() {
		add_menu_page(
			__( 'Design', 'voxel-backend' ),
			__( 'Design', 'voxel-backend' ),
			'manage_options',
			'voxel-templates',
			function() {
				$config = [
					'tab' => $_GET['tab'] ?? 'membership',
					'templates' => \Voxel\get_base_templates(),
					'editLink' => admin_url( 'post.php?post={id}&action=elementor' ),
					'previewLink' => home_url( '/?p={id}' ),
					'nonce' => wp_create_nonce( 'vx_admin_edit_templates' ),
				];

				wp_enqueue_script('vx:template-manager.js');
				require locate_template( 'templates/backend/templates/general.php' );
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/brush-alt.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.278'
		);

		add_submenu_page(
			'voxel-templates',
			__( 'Header & Footer', 'voxel-backend' ),
			__( 'Header & Footer', 'voxel-backend' ),
			'manage_options',
			'vx-templates-header-footer',
			function() {
				$this->create_required_templates();

				$config = [
					'tab' => $_GET['tab'] ?? 'header',
					'custom_templates' => \Voxel\get_custom_templates(),
					'templates' => \Voxel\get_base_templates(),
					'editLink' => admin_url( 'post.php?post={id}&action=elementor' ),
					'previewLink' => home_url( '/?p={id}' ),
					'nonce' => wp_create_nonce( 'vx_admin_edit_templates' ),
				];

				wp_enqueue_script('vx:template-manager.js');
				require locate_template( 'templates/backend/templates/header-footer.php' );
			},
			1
		);

		add_submenu_page(
			'voxel-templates',
			__( 'Taxonomies', 'voxel-backend' ),
			__( 'Taxonomies', 'voxel-backend' ),
			'manage_options',
			'vx-templates-taxonomies',
			function() {
				$this->create_required_templates();

				$config = [
					'tab' => $_GET['tab'] ?? 'term_single',
					'custom_templates' => \Voxel\get_custom_templates(),
					'templates' => \Voxel\get_base_templates(),
					'editLink' => admin_url( 'post.php?post={id}&action=elementor' ),
					'previewLink' => home_url( '/?p={id}' ),
					'nonce' => wp_create_nonce( 'vx_admin_edit_templates' ),
				];

				wp_enqueue_script('vx:template-manager.js');
				require locate_template( 'templates/backend/templates/taxonomies.php' );
			},
			2
		);

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			add_submenu_page(
				'voxel-templates',
				'&mdash; '.$post_type->get_label(),
				'&mdash; '.$post_type->get_label(),
				'manage_options',
				'vx-templates-post-type-'.$post_type->get_key(),
				function() {},
				100
			);
		}
	}

	protected function create_required_templates() {
		$templates = \Voxel\get( 'templates' );

		if ( ! \Voxel\template_exists( $templates['header'] ?? '' ) ) {
			$template_id = \Voxel\create_template( 'site template: header' );
			if ( ! is_wp_error( $template_id ) ) {
				$templates['header'] = $template_id;
			}
		}

		if ( ! \Voxel\template_exists( $templates['footer'] ?? '' ) ) {
			$template_id = \Voxel\create_template( 'site template: footer' );
			if ( ! is_wp_error( $template_id ) ) {
				$templates['footer'] = $template_id;
			}
		}

		\Voxel\set( 'templates', $templates );
	}

	protected function display_template_labels( $states, $post ) {
		if ( $post->post_type !== 'page' ) {
			return $states;
		}

		$labels = [
			'auth' => _x( 'Auth Page', 'templates', 'voxel-backend' ),
			'pricing' => _x( 'Pricing Plans Page', 'templates', 'voxel-backend' ),
			'current_plan' => _x( 'Current Plan Page', 'templates', 'voxel-backend' ),
			'orders' => _x( 'Orders Page', 'templates', 'voxel-backend' ),
			// 'reservations' => _x( 'Reservations Page', 'templates', 'voxel-backend' ),
			// 'qr_tags' => _x( 'Order tags: QR code handler', 'templates', 'voxel-backend' ),
			'terms' => _x( 'Terms & Conditions', 'templates', 'voxel-backend' ),
			'stripe_account' => _x( 'Seller Dashboard', 'templates', 'voxel-backend' ),
		];

		$templates = \Voxel\get( 'templates', [] );
		$template = array_search( absint( $post->ID ), $templates, true );
		if ( $template && isset( $labels[ $template ] ) ) {
			$states[ 'vx:'.$template ] = $labels[ $template ];
		}

		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			if ( $post_type->get_templates()['form'] === $post->ID ) {
				$states[ 'vx:create_post' ] = sprintf( '%s: Submit page', $post_type->get_label() );
			}
		}

		return $states;
	}
}
