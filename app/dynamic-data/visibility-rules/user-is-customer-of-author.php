<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Is_Customer_Of_Author extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:is_customer_of_author';
	}

	public function get_label(): string {
		return _x( 'User is customer of author', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'author_id', [
			'type' => 'text',
			'label' => _x( 'Author ID', 'visibility rules', 'voxel-backend' ),
			'description' => 'Leave empty for current post author',
			'placeholder' => 'Current post author',
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		if ( is_numeric( $this->get_arg('author_id') ) ) {
			$vendor = \Voxel\User::get( $this->get_arg('author_id') );
			if ( ! $vendor ) {
				return false;
			}

			if ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
				return $current_user->has_bought_product_from_platform();
			}

			return $current_user->has_bought_product_from_vendor( $vendor->get_id() );
		} else {
			$post = \Voxel\get_current_post();
			if ( ! $post ) {
				return false;
			}

			$vendor = $post->get_author();
			if ( ! $vendor ) {
				return false;
			}

			if ( $vendor->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) {
				return $current_user->has_bought_product_from_platform();
			}

			return $current_user->has_bought_product_from_vendor( $vendor->get_id() );
		}
	}
}
