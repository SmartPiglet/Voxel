<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_Author extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_author';
	}

	public function get_label(): string {
		return _x( 'Is author profile', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'author_id', [
			'type' => 'text',
			'label' => _x( 'Enter author ID', 'visibility rules', 'voxel-backend' ),
		] );
	}

	public function evaluate(): bool {
		return is_author( $this->get_arg('author_id') );
	}
}
