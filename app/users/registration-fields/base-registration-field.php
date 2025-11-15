<?php

namespace Voxel\Users\Registration_Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Registration_Field {

	protected $key;

	public function __construct( $props = [] ) {
		$this->props = array_merge( $this->base_props(), $this->props );

		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}

		$this->key = $this->props['key'];
	}

	protected function base_props(): array {
		return [
			'key' => '',
			'label' => '',
			'description' => '',
			'placeholder' => '',
		];
	}

	public function get_frontend_config() {
		return [
			'key' => $this->get_prop('key'),
			'label' => $this->get_prop('label'),
			'description' => $this->get_prop('description'),
			'placeholder' => $this->get_prop('placeholder') ?: $this->get_prop('label'),
			'props' => $this->frontend_props(),
			'value' => null,
			'_is_auth_field' => true,
		];
	}

	protected function frontend_props() {
		return [];
	}

	public function set_prop( $key, $value ) {
		if ( array_key_exists( $key, $this->props ) ) {
			$this->props[ $key ] = $value;
		}
	}

	public function get_prop( $prop ) {
		if ( ! isset( $this->props[ $prop ] ) ) {
			return null;
		}

		return $this->props[ $prop ];
	}

	public function get_props() {
		return $this->props;
	}

	public function get_key() {
		return $this->key;
	}

	public function get_required_scripts(): array {
		return [];
	}

}
