<?php

namespace Voxel\Dynamic_Data\Data_Types;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Data_Bool extends Base_Data_Type {

	public function get_type(): string {
		return 'bool';
	}

	public function get_value() {
		if ( $this->render_cb === null ) {
			return null;
		}

		$value = ($this->render_cb)();
		return $value ? '1' : '';
	}
}
