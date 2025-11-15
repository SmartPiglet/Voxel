<?php

namespace Voxel\Controllers;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Nav_Menus_Controller extends Base_Controller {

	protected function hooks() {
		$this->on( 'init', '@register_menus' );
		$this->on( 'wp_nav_menu_item_custom_fields', '@add_custom_fields', 100, 5 );
		$this->on( 'wp_update_nav_menu_item', '@save_custom_fields', 100, 3 );
		$this->filter( 'wp_setup_nav_menu_item', '@render_nav_menu_tags' );
	}

	protected function register_menus() {
		register_nav_menus( [
			'voxel-desktop-menu' => __( 'Desktop Menu', 'voxel-backend' ),
			'voxel-mobile-menu' => __( 'Mobile menu', 'voxel-backend' ),
			'voxel-user-menu' => __( 'User Dashboard Menu', 'voxel-backend' ),
			'voxel-create-menu' => __( 'Create post menu', 'voxel-backend' ),
		] );

		$custom_locations = (array) \Voxel\get('settings.nav_menus.custom_locations', [] );
		register_nav_menus( array_column( $custom_locations, 'label', 'key' ) );
	}

	protected function add_custom_fields( $item_id, $item, $depth, $args, $id ) {
		$icon_string = get_post_meta( $item_id, '_voxel_item_icon', true );
		$label = get_post_meta( $item_id, '_voxel_item_label', true );
		$url = get_post_meta( $item_id, '_voxel_item_url', true );
		$visibility_behavior = get_post_meta( $item_id, '_voxel_visibility_behavior', true );
		$visibility_rules = get_post_meta( $item_id, '_voxel_visibility_rules', true );
		require locate_template( 'templates/backend/nav-menus/menu-item-fields.php' );
	}

	protected function save_custom_fields( $menu_id, $menu_item_db_id, $args ) {
		$icons = $_POST['voxel_item_icon'] ?? [];
		$icon_string = sanitize_text_field( $icons[ $menu_item_db_id ] ?? '' );
		if ( empty( $icon_string ) ) {
			delete_post_meta( $menu_item_db_id, '_voxel_item_icon' );
		} else {
			update_post_meta( $menu_item_db_id, '_voxel_item_icon', $icon_string );
		}

		$labels = $_POST['voxel_item_label'] ?? [];
		$label = trim( $labels[ $menu_item_db_id ] ?? '' );
		if ( empty( $label ) ) {
			delete_post_meta( $menu_item_db_id, '_voxel_item_label' );
		} else {
			update_post_meta( $menu_item_db_id, '_voxel_item_label', $label );
		}

		$urls = $_POST['voxel_item_url'] ?? [];
		$url = trim( $urls[ $menu_item_db_id ] ?? '' );
		if ( empty( $url ) ) {
			delete_post_meta( $menu_item_db_id, '_voxel_item_url' );
		} else {
			update_post_meta( $menu_item_db_id, '_voxel_item_url', $url );
		}

		$behavior_all = $_POST['voxel_visibility_behavior'] ?? [];
		$behavior = trim( $behavior_all[ $menu_item_db_id ] ?? '' );
		if ( empty( $behavior ) ) {
			delete_post_meta( $menu_item_db_id, '_voxel_visibility_behavior' );
		} else {
			update_post_meta( $menu_item_db_id, '_voxel_visibility_behavior', $behavior );
		}

		$rules_all = $_POST['voxel_visibility_rules'] ?? [];
		$_rules = $rules_all[ $menu_item_db_id ] ?? null;
		$rules = $_rules ? json_decode( wp_unslash( $_rules ), ARRAY_A ) : null;
		if ( empty( $rules ) || ! is_array( $rules ) || json_last_error() !== JSON_ERROR_NONE ) {
			delete_post_meta( $menu_item_db_id, '_voxel_visibility_rules' );
		} else {
			update_post_meta( $menu_item_db_id, '_voxel_visibility_rules', wp_slash( wp_json_encode( $rules ) ) );
		}
	}

	protected function render_nav_menu_tags( $item ) {
		if ( is_admin() && ! \Voxel\is_edit_mode() && ! \Voxel\is_elementor_ajax() ) {
			return $item;
		}

		if ( ! $this->passes_visibility_settings( $item ) ) {
			$item->_invalid = true;
			return $item;
		}

		if ( ! empty( $item->_voxel_item_label ) ) {
			$item->title = \Voxel\render( $item->_voxel_item_label );
		}

		if ( ! empty( $item->_voxel_item_url ) ) {
			$item->url = \Voxel\render( $item->_voxel_item_url );
		}

		return $item;
	}

	protected function passes_visibility_settings( $item ) {
		$behavior = $item->_voxel_visibility_behavior ?? null;
		$_rules = $item->_voxel_visibility_rules ?? null;
		$rules = $_rules ? json_decode( wp_unslash( $_rules ), ARRAY_A ) : null;

		if ( empty( $rules ) || ! is_array( $rules ) || json_last_error() !== JSON_ERROR_NONE ) {
			return true;
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );
		if ( $behavior === 'hide' ) {
			return $rules_passed ? false : true;
		} else {
			return $rules_passed ? true : false;
		}
	}
}
