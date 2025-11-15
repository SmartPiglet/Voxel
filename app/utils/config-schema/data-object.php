<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Object extends Base_Data_Type {

	protected
		$custom_transformer;

	public function __construct( array $value ) {
		$this->value = $value;
	}

	public function set_value( $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		if ( $this->custom_transformer !== null ) {
			$value = ($this->custom_transformer)( $value );
		}

		foreach ( $this->value as $key => $prop ) {
			$prop->set_value( $value[ $key ] ?? null );
		}
	}

	public function get_prop( $key ) {
		return $this->value[ $key ] ?? null;
	}

	public function get_props() {
		return $this->value;
	}

	public function set_prop( $key, Base_Data_Type $prop ) {
		return $this->value[ $key ] = $prop;
	}

	public function transformer( $cb ): self {
		$this->custom_transformer = $cb;
		return $this;
	}

	public function export() {
		$export = [];
		foreach ( $this->value as $key => $prop ) {
			$export[ $key ] = $prop->export();
		}

		if ( empty( $export ) ) {
			return $this->get_default_value();
		}

		return $export;
	}

	public function __clone() {
		foreach ( $this->value as $key => $prop ) {
			$this->value[ $key ] = clone $prop;
		}
	}
}
