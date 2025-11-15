<?php

namespace Voxel\Controllers\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Users_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'wp_insert_post', '@cache_user_post_stats', 10 );
		$this->on( 'after_delete_post', '@cache_user_post_stats', 10 );
		$this->on( 'pre_get_avatar_data', '@show_custom_avatar', 35, 2 );
		$this->filter( 'show_admin_bar', '@should_show_admin_bar' );
		$this->filter( 'user_row_actions', '@user_row_actions', 10, 2 );
	}

	protected function cache_user_post_stats( $post_id ) {
		$post = \Voxel\Post::get( $post_id );
		if ( $post && $post->post_type && $post->post_type->is_managed_by_voxel() ) {
			\Voxel\queue_user_post_stats_for_caching( $post->get_author_id() );
		}
	}

	protected function show_custom_avatar( $args, $id_or_email ) {
		if ( (bool) $args['force_default'] === true ) {
			return $args;
		}

		if ( ! ( $user = \Voxel\get_user_by_id_or_email( $id_or_email ) ) ) {
			return $args;
		}

		$avatar_id = $user->get_avatar_id();
		$avatar_url = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );
		if ( $avatar_id && $avatar_url ) {
			$args['url'] = $avatar_url;
		}

		return $args;
	}

	protected function should_show_admin_bar( $should_show ) {
		$user = \Voxel\current_user();
		if ( ! ( $user && ( $user->has_role( 'administrator' ) || $user->has_role( 'editor' ) ) ) ) {
			return false;
		}

		return $should_show;
	}

	protected function user_row_actions( $actions, $user ) {
		$user = \Voxel\User::get( $user );
		$profile = $user->get_or_create_profile();
		$actions['edit_profile'] = sprintf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $profile->get_id() ) ), __( 'Edit profile', 'voxel-backend' ) );
		return $actions;
	}
}
