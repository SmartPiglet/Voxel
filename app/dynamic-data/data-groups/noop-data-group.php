<?php

namespace Voxel\Dynamic_Data\Data_Groups;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Noop_Data_Group extends Base_Data_Group {

	public function get_type(): string {
		return 'noop';
	}

	protected function properties(): array {
		return [];
	}
}
