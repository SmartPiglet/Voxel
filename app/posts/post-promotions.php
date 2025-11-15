<?php

namespace Voxel\Posts;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Promotions {

	protected $post;

	public function __construct( \Voxel\Post $post ) {
		$this->post = $post;
	}

	public function is_promotable_by_user( \Voxel\User $user ): bool {
		if ( ! \Voxel\get( 'paid_listings.settings.promotions.enabled' ) ) {
			return false;
		}

		if ( ! ( $this->post->post_type && $this->post->get_status() === 'publish' ) ) {
			return false;
		}

		if ( ! $this->post->is_editable_by_user( $user ) ) {
			return false;
		}

		foreach ( \Voxel\Product_Types\Promotions\Promotion_Package::get_all() as $package ) {
			if ( $package->supports_post( $this->post ) ) {
				return true;
			}
		}

		return false;
	}

	public function get_available_packages(): array {
		$packages = [];

		if ( ! \Voxel\get( 'paid_listings.settings.promotions.enabled' ) ) {
			return $packages;
		}

		foreach ( \Voxel\Product_Types\Promotions\Promotion_Package::get_all() as $package ) {
			if ( ! $package->supports_post( $this->post ) ) {
				continue;
			}

			$packages[ $package->get_key() ] = $package;
		}

		return $packages;
	}

	public function get_active_package(): ?array {
		$package = (array) json_decode( get_post_meta( $this->post->get_id(), 'voxel:promotion', true ), true );
		if ( ! ( ( $package['status'] ?? null ) === 'active' && is_numeric( $package['priority'] ?? null ) ) ) {
			return null;
		}

		return $package;
	}
}
