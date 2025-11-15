<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Keyed_List extends Base_Data_Type {

	protected
		$custom_validator,
		$custom_transformer;

	public function set_value( $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		$valid_values = [];
		foreach ( $value as $key => $item ) {
			if ( $this->custom_validator !== null && ! ($this->custom_validator)( $item, $key ) ) {
				continue;
			}

			if ( $this->custom_transformer !== null ) {
				list( $item, $key ) = ($this->custom_transformer)( $item, $key );
			}

			$valid_values[ $key ] = $item;
		}

		$this->value = $valid_values;
	}

	public function validator( $cb ): self {
		$this->custom_validator = $cb;
		return $this;
	}

	public function transformer( $cb ): self {
		$this->custom_transformer = $cb;
		return $this;
	}
}
