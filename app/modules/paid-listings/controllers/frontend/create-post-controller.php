<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Create_Post_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/create_post/no_permission_screen/content', '@no_permission_screen_content', 100, 3 );
		$this->on( 'template_redirect', '@maybe_redirect_to_pricing_page' );
		$this->on( 'voxel/frontend/before_post_update', '@frontend_before_post_update' );
		$this->on( 'voxel/frontend/post_updated', '@frontend_post_updated' );
		$this->on( 'voxel/user/can_create_post', '@user_can_create_post', 100, 3 );
	}

	protected function no_permission_screen_content( $content, $post_type, $user ) {
		if ( ! Module\has_plans_for_post_type( $post_type ) ) {
			return $content;
		}

		$content['title'] = _x( 'You need a plan to proceed', 'paid listings', 'voxel' );
		$content['actions'] = [
			[
				'text' => _x( 'View plans', 'paid listings', 'voxel' ),
				'link' => add_query_arg( [
					'process' => 'new',
					'item_type' => $post_type->get_key(),
				], get_permalink( \Voxel\get('paid_listings.settings.templates.pricing') ) ),
			],
		];

		return $content;
	}

	protected function maybe_redirect_to_pricing_page() {
		foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
			$page_id = $post_type->get_templates()['form'] ?? null;
			if ( is_numeric( $page_id ) && $page_id > 0 && is_page( $page_id ) && ! is_admin() ) {
				if ( $post_type->get_key() === 'profile' ) {
					return;
				}

				if ( ! empty( $_GET['post_id'] ) ) {
					$post = \Voxel\Post::get( $_GET['post_id'] );
					if ( $post && $post->is_editable_by_current_user() && $post->post_type->get_key() === $post_type->get_key() ) {
						return;
					}
				}

				if ( ! Module\has_plans_for_post_type( $post_type ) ) {
					return;
				}

				$pricing_page_id = \Voxel\get('paid_listings.settings.templates.pricing');
				if ( ! ( is_numeric( $pricing_page_id ) && \Voxel\page_exists( $pricing_page_id ) ) ) {
					return;
				}

				if ( \Voxel\is_preview_mode() ) {
					return;
				}

				$redirect_to = add_query_arg( [
					'process' => 'new',
					'item_type' => $post_type->get_key(),
				], get_permalink( $pricing_page_id ) );

				wp_safe_redirect( $redirect_to );
				exit;
			}
		}
	}

	protected function frontend_before_post_update( array $data ) {
		$post = $data['post'];
		$status = $data['status'];
		$previous_status = $data['previous_status'];

		if ( $previous_status === 'draft' && in_array( $status, [ 'publish', 'pending' ], true ) ) {
			$details = Module\get_assigned_package( $post );
			$plan = $details['plan'];
			$package = $details['package'];
			$use_slot_on_publish = $details['use_slot_on_publish'];

			if ( $use_slot_on_publish && $package && ! ( $package->can_create_post( $post->post_type ) ) ) {
				throw new \Exception( _x( 'You do not have permission to create new posts.', 'create post', 'voxel' ), 190 );
			}
		}
	}

	protected function frontend_post_updated( array $data ) {
		$post = $data['post'];
		$status = $data['status'];
		$previous_status = $data['previous_status'];

		delete_post_meta( $post->get_id(), '_is_blank_draft' );

		if ( $previous_status === 'draft' && in_array( $status, [ 'publish', 'pending' ], true ) ) {
			$details = Module\get_assigned_package( $post );
			$plan = $details['plan'];
			$package = $details['package'];
			$use_slot_on_publish = $details['use_slot_on_publish'];

			if ( $use_slot_on_publish && $package ) {
				$package->assign_to_post( $post );
			}
		}
	}

	protected function user_can_create_post( bool $can_create_post, \Voxel\User $user, \Voxel\Post_Type $post_type ): bool {
		if ( Module\has_plans_for_post_type( $post_type ) ) {
			return false;
		}

		return $can_create_post;
	}

}
