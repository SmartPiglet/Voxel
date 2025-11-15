<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Data_Type {

	protected
		$value = null,
		$default_value = null,
		$default_cb = null;

	protected $meta = [];

	public static function new() {
		return new static( ...func_get_args() );
	}

	public function default( $value ) {
		$this->default_value = $value;
		return $this;
	}

	public function default_cb( \Closure $callback ) {
		$this->default_cb = $callback;
		return $this;
	}

	public function get_value() {
		return $this->value ?? $this->get_default_value();
	}

	public function get_default_value() {
		if ( $this->default_cb !== null ) {
			return ($this->default_cb)();
		}

		return $this->default_value;
	}

	public function export() {
		return $this->value ?? $this->get_default_value();
	}

	public function set_meta( string $key, $value ) {
		$this->meta[ $key ] = $value;
	}

	public function get_meta( string $key ) {
		return $this->meta[ $key ] ?? null;
	}
}
