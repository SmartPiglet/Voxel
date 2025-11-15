<?php

namespace Voxel\Modules\Collections\Controllers;

use \Voxel\Modules\Collections as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Collections_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! \Voxel\get( 'settings.addons.collections.enabled', true );
	}

	protected function dependencies() {
		new Frontend\Frontend_Controller;
	}

	protected function hooks() {
		$this->on( 'init', '@register_post_type', -1 );
		$this->on( 'admin_menu', '@show_in_admin_menu', 60 );
		$this->filter( 'voxel/advanced-list/actions', '@register_save_to_collection_action' );
		$this->on( 'voxel/advanced-list/action:action_save', '@render_save_to_collection_action', 10, 2 );
		$this->filter( 'voxel/user/can_create_post', '@user_can_create_collection', 10, 3 );
	}

	protected function register_post_type() {
		register_post_type( 'collection', [
			'labels' => [
				'name' => 'Collections',
				'singular_name' => 'Collection',
			],
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'hierarchical'        => false,
			'query_var'           => true,
			'supports'            => [ 'title', 'publicize', 'thumbnail', 'comments' ],
			'menu_position'       => 72,
			'delete_with_user'    => true,
			'_is_created_by_voxel' => false,
			'has_archive' => 'collections',
		] );
	}

	protected function show_in_admin_menu() {
		add_users_page(
			__('Collections (Voxel)', 'voxel-backend'),
			__('Collections (Voxel)', 'voxel-backend'),
			'manage_options',
			'edit.php?post_type=collection'
		);
	}

	protected function register_save_to_collection_action( $actions ) {
		$actions['action_save'] = __( 'Save post to collection', 'voxel-elementor' );
		return $actions;
	}

	protected function render_save_to_collection_action( $widget, $action ) {
		require locate_template( 'app/modules/collections/templates/frontend/save-to-collection-action.php' );
	}

	protected function user_can_create_collection( bool $can_create_post, \Voxel\User $user, \Voxel\Post_Type $post_type ): bool {
		// Only apply this filter to collection post type
		if ( $post_type->get_key() !== 'collection' ) {
			return $can_create_post;
		}

		// Use global limit check instead of listing plan limits
		return Module\user_can_create_collection( $user->get_id() );
	}
}
