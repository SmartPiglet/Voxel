<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_404 extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_404';
	}

	public function get_label(): string {
		return _x( 'Is 404 page', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		return is_404();
	}
}
