<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Type {
	use \Voxel\Product_Types\Product_Type_Query_Trait;

	public
		$config,
		$repository;

	protected function __construct( array $config ) {
		$this->config = $config;
		$this->repository = new \Voxel\Product_Types\Product_Type_Repository( $this );
	}

	public function config( $option, $default = null ) {
		return $this->repository->config( $option, $default );
	}

	public function get_label() {
		return $this->config( 'settings.label' );
	}

	public function get_key() {
		return $this->config( 'settings.key' );
	}

	public function get_edit_link() {
		return admin_url( 'admin.php?page=voxel-product-types&action=edit-type&product_type='.$this->get_key() );
	}

	public function get_product_mode() {
		return $this->config( 'settings.product_mode' );
	}

	public function get_config(): array {
		return $this->config;
	}

	public function supports_marketplace(): bool {
		if ( \Voxel\get('payments.provider') !== 'stripe' ) {
			return false;
		}

		if ( ! \Voxel\get( 'payments.stripe.stripe_connect.enabled' ) ) {
			return false;
		}

		if ( $this->config( 'settings.payments.mode' ) === 'offline' ) {
			return false;
		}

		return !! $this->config( 'settings.supports_marketplace' );
	}
}
