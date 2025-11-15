<?php

namespace Voxel\Dynamic_Data\Modifiers\Group_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term_Meta_Method extends Base_Group_Method {

	public function get_key(): string {
		return 'meta';
	}

	public function get_label(): string {
		return _x( 'Term meta', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Meta key', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function run( $group ) {
		$meta_key = $this->get_arg(0);
		if ( $meta_key === '' ) {
			return null;
		}

		return get_term_meta( $group->get_term()->get_id(), $meta_key, true );
	}
}
