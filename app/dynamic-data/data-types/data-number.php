<?php

namespace Voxel\Dynamic_Data\Data_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Number extends Base_Data_Type {

	public function get_type(): string {
		return 'number';
	}

}
