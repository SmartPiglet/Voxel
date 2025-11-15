<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Date extends Base_Data_Type {

	protected
		$min,
		$format = 'Y-m-d H:i:s';

	public function min( int $min ): self {
		$this->min = $min;
		return $this;
	}

	public function format( string $format ): self {
		$this->format = $format;
		return $this;
	}

	public function set_value( $value ) {
		$timestamp = strtotime( (string) $value );
		if ( $timestamp === false ) {
			return;
		}

		if ( $this->min !== null && $timestamp < $this->min ) {
			return;
		}

		$this->value = date( $this->format, $timestamp );
	}
}
