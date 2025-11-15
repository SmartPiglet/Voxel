<?php

namespace Voxel\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_user.follow_user', '@follow_user' );
		$this->on( 'voxel_ajax_user.follow_post', '@follow_post' );

		$this->on( 'voxel_ajax_user.posts.delete_post', '@delete_post' );
		$this->on( 'voxel_ajax_user.posts.unpublish_post', '@unpublish_post' );
		$this->on( 'voxel_ajax_user.posts.republish_post', '@republish_post' );

		$this->on( 'voxel_ajax_user.profile', '@open_user_profile' );
		$this->on( 'voxel_ajax_nopriv_user.profile', '@open_user_profile' );
	}

	protected function follow_user() {
		try {
			$current_user = \Voxel\current_user();
			$user_id = ! empty( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : null;
			$user = \Voxel\User::get( $user_id );
			if ( ! $user ) {
				throw new \Exception( _x( 'User not found.', 'timeline', 'voxel' ) );
			}

			$profile = $user->get_or_create_profile();

			if ( $current_user->get_follow_status( 'user', $user->get_id() ) === \Voxel\FOLLOW_ACCEPTED ) {
				$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_NONE );
				$current_user->set_follow_status( 'post', $profile->get_id(), \Voxel\FOLLOW_NONE );
			} else {
				$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_ACCEPTED );
				$current_user->set_follow_status( 'post', $profile->get_id(), \Voxel\FOLLOW_ACCEPTED );

				( new \Voxel\Events\Timeline\Followers\User_Followed_Event )->dispatch( $user->get_id(), $current_user->get_id() );
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

	protected function follow_post() {
		try {
			$current_user = \Voxel\current_user();
			$post_id = ! empty( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null;
			$post = \Voxel\Post::get( $post_id );
			if ( ! ( $post && $post->post_type ) ) {
				throw new \Exception( _x( 'Post not found.', 'timeline', 'voxel' ) );
			}

			if ( $post->post_type->get_key() === 'profile' ) {
				$user = \Voxel\User::get_by_profile_id( $post->get_id() );
				if ( ! $user ) {
					throw new \Exception( _x( 'User not found.', 'timeline', 'voxel' ) );
				}

				if ( $current_user->get_follow_status( 'user', $user->get_id() ) === \Voxel\FOLLOW_ACCEPTED ) {
					$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_NONE );
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_NONE );
				} else {
					$current_user->set_follow_status( 'user', $user->get_id(), \Voxel\FOLLOW_ACCEPTED );
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_ACCEPTED );

					( new \Voxel\Events\Timeline\Followers\User_Followed_Event )->dispatch( $user->get_id(), $current_user->get_id() );
				}
			} else {
				$current_status = $current_user->get_follow_status( 'post', $post->get_id() );
				if ( $current_status === \Voxel\FOLLOW_ACCEPTED ) {
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_NONE );
				} else {
					$current_user->set_follow_status( 'post', $post->get_id(), \Voxel\FOLLOW_ACCEPTED );

					( new \Voxel\Events\Timeline\Followers\Post_Followed_Event )->dispatch( $post->get_id(), $current_user->get_id() );
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

	protected function delete_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_delete_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			if ( ! ( $post && $post->is_deletable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			wp_trash_post( $post->get_id() );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function unpublish_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_modify_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\current_user();
			if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( ! ( $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			// excluded post types
			if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( $post->get_status() !== 'publish' ) {
				throw new \Exception( __( 'Only published posts can be unpublished.', 'voxel' ) );
			}

			wp_update_post( [
				'ID' => $post->get_id(),
				'post_status' => 'unpublished',
			] );

			delete_post_meta( $post->get_id(), '_pending_review_on_republish' );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function republish_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_modify_post' );
			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\current_user();
			if ( ! ( $post && $post->is_editable_by_current_user() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( ! ( $post->post_type && $post->post_type->is_managed_by_voxel() ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			// excluded post types
			if ( in_array( $post->post_type->get_key(), [ 'profile' ], true ) ) {
				throw new \Exception( __( 'Permission denied.', 'voxel' ) );
			}

			if ( $post->get_status() !== 'unpublished' ) {
				throw new \Exception( __( 'Only unpublished posts can be republished using this action.', 'voxel' ) );
			}

			$new_post_status = 'publish';

			// if the user edited this post after unpublishing, the post must transition to pending first
			if (
				$post->post_type->get_setting( 'submissions.update_status' ) === 'pending'
				&& !! get_post_meta( $post->get_id(), '_pending_review_on_republish', true )
			) {
				$new_post_status = 'pending';
			}

			// @todo check submission limits

			wp_update_post( [
				'ID' => $post->get_id(),
				'post_status' => $new_post_status,
			] );

			delete_post_meta( $post->get_id(), '_pending_review_on_republish' );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => '(reload)',
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function open_user_profile() {
		$user_id = -1;

		$username = $_REQUEST['username'] ?? null;
		if ( is_string( $username ) && ! empty( $username ) ) {
			$user = get_user_by( 'login', sanitize_user( str_replace( '·', ' ', $username ) ) );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		wp_safe_redirect( add_query_arg( 'author', $user_id, home_url() ) );
		exit;
	}
}
