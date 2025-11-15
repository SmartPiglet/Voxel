<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Int extends Base_Data_Type {

	protected
		$min,
		$max;

	public function min( int $min ): self {
		$this->min = $min;
		return $this;
	}

	public function max( int $max ): self {
		$this->max = $max;
		return $this;
	}

	public function set_value( $value ) {
		if ( ! is_numeric( $value ) ) {
			$this->value = null;
			return;
		}

		if ( $this->min !== null && $value < $this->min ) {
			$this->value = null;
			return;
		}

		if ( $this->max !== null && $value > $this->max ) {
			$this->value = null;
			return;
		}

		$this->value = (int) $value;
	}
}
