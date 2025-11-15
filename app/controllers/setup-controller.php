<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Setup_Controller extends Base_Controller {

	protected function hooks() {
		foreach ( \Voxel\config('modules') as $module_root_path ) {
			if ( $module_root_path ) {
				require_once $module_root_path;
			}
		}

		$this->on( 'admin_menu', '@set_menu_icons', 1000 );
		$this->on( 'admin_menu', '@reorder_menu_items', 1000 );
		$this->on( 'admin_head', '@enqueue_custom_font' );
		$this->load_theme_textdomain();
	}

	protected function set_menu_icons() {
		global $menu;

		foreach ( $menu as $index => $item ) {
			if ( str_starts_with( $item[2], 'edit.php' ) ) {
				if ( $item[2] === 'edit.php' ) {
					$post_type = \Voxel\Post_Type::get('post');
				} else {
					$post_type = \Voxel\Post_Type::get( substr( $item[2], 19 ) );
				}

				if ( ! $post_type && $post_type->is_managed_by_voxel() ) {
					continue;
				}

				$icon = \Voxel\parse_icon_string( $post_type->get_icon() );
				if ( $icon['library'] !== 'svg' ) {
					continue;
				}

				$icon_path = get_attached_file( $icon['value']['id'] ?? null );
				if ( ! empty( $icon_path ) ) {
					$menu[ $index ][6] = sprintf(
						'data:image/svg+xml;base64,%s',
						base64_encode( \Voxel\paint_svg( file_get_contents( $icon_path ), '#a7aaad' ) )
					);

					$menu[ $index ][4] = str_replace( ' menu-icon-', ' _menu-icon-', $menu[ $index ][4] );
				}
			}
		}
	}

	protected function reorder_menu_items() {
		global $submenu;

		if ( isset( $submenu['voxel-settings'] ) ) {
			$submenu['voxel-settings'][0][0] = 'Settings';
		}

		if ( isset( $submenu['voxel-post-types'] ) ) {
			$submenu['voxel-post-types'][0][0] = 'Post Types';
		}

		if ( isset( $submenu['voxel-orders'] ) ) {
			$submenu['voxel-orders'][0][0] = 'Orders';
		}

		if ( isset( $submenu['voxel-paid-members'] ) ) {
			$submenu['voxel-paid-members'][0][0] = 'Members';
		}

		if ( isset( $submenu['voxel-paid-listings'] ) ) {
			$submenu['voxel-paid-listings'][0][0] = 'Assigned Plans';
		}

		// if ( isset( $submenu['voxel-timeline'] ) ) {
		// 	$submenu['voxel-timeline'][0][0] = 'Activity';
		// }

		if ( isset( $submenu['voxel-templates'] ) ) {
			$submenu['voxel-templates'][0][0] = 'General';

			foreach ( $submenu['voxel-templates'] as $i => $item ) {
				if ( str_starts_with( $item[2], 'vx-templates-post-type-' ) ) {
					$post_type_key = substr( $item[2], 23 );
					$submenu['voxel-templates'][$i][2] = ( $post_type_key === 'post' )
						? 'edit.php?page=edit-post-type-post&tab=templates.base-templates'
						: sprintf(
						'edit.php?post_type=%s&page=edit-post-type-%s&tab=templates.base-templates',
						$post_type_key,
						$post_type_key
					);
				}
			}
		}

		if ( apply_filters( 'voxel/admin-menu/show-comments', false ) === false ) {
			remove_menu_page( 'edit-comments.php' );
		}
	}

	protected function enqueue_custom_font() {
		// echo '<link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">';
	}

	protected function load_theme_textdomain(): void {
		load_theme_textdomain( 'voxel', trailingslashit( get_template_directory() ).'languages' );
		if ( is_admin() ) {
			load_theme_textdomain( 'voxel-backend', trailingslashit( get_template_directory() ).'languages' );
			load_theme_textdomain( 'voxel-elementor', trailingslashit( get_template_directory() ).'languages' );
		}
	}
}
