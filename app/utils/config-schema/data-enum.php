<?php

namespace Voxel\Utils\Config_Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Enum extends Base_Data_Type {

	protected $possible_values;

	public function __construct( $possible_values = [] ) {
		$this->possible_values = $possible_values;
	}

	public function values( array $possible_values ): self {
		$this->possible_values = $possible_values;
		return $this;
	}

	public function set_value( $value ) {
		if ( ! in_array( $value, $this->possible_values, true ) ) {
			return;
		}

		$this->value = $value;
	}
}
