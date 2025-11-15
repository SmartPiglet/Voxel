<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_List extends Base_Data_Type {

	protected
		$allowed_values,
		$custom_validator,
		$custom_transformer,
		$unique_values;

	public function allowed_values( array $values ): self {
		$this->allowed_values = $values;
		return $this;
	}

	public function set_value( $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		$valid_values = [];
		foreach ( $value as $item ) {
			if ( $this->allowed_values !== null && ! in_array( $item, $this->allowed_values, true ) ) {
				continue;
			}

			if ( $this->custom_validator !== null && ! ($this->custom_validator)( $item ) ) {
				continue;
			}

			if ( $this->custom_transformer !== null ) {
				$item = ($this->custom_transformer)( $item );
			}

			$valid_values[] = $item;
		}

		if ( $this->unique_values === true ) {
			$valid_values = array_unique( $valid_values );
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

	public function unique(): self {
		$this->unique_values = true;
		return $this;
	}
}
