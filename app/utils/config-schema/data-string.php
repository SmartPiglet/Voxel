<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_String extends Base_Data_Type {

	protected
		$minlength,
		$maxlength;

	public function minlength( int $minlength ): self {
		$this->minlength = $minlength;
		return $this;
	}

	public function maxlength( int $maxlength ): self {
		$this->maxlength = $maxlength;
		return $this;
	}

	public function set_value( $value ) {
		if ( is_numeric( $value ) ) {
			$value = (string) $value;
		}

		if ( ! is_string( $value ) ) {
			$this->value = null;
			return;
		}

		if ( $this->minlength !== null && mb_strlen( $value ) < $this->minlength ) {
			$this->value = null;
			return;
		}

		if ( $this->maxlength !== null && mb_strlen( $value ) > $this->maxlength ) {
			$this->value = null;
			return;
		}

		$this->value = $value;
	}

}
