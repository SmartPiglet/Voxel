<?php

namespace Voxel\Post_Types\Field_Conditions;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Date_Lt extends Base_Condition {
	use Traits\Single_Value_Model;

	public function get_type(): string {
		return 'date:lt';
	}

	public function get_label(): string {
		return _x( 'Less than', 'field conditions', 'voxel-backend' );
	}

	public function evaluate( $value ): bool {
		$date = strtotime( trim( sprintf( '%s %s', $value['date'] ?? '', $value['time'] ?? '' ) ) );
		$compare = strtotime( $this->props['value'] );
		return $date && $compare && $date < $compare;
	}
}
