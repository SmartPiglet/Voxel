<?php

namespace Voxel\Dynamic_Data\Modifiers\Group_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Site_Query_Var_Method extends Base_Group_Method {

	public function get_key(): string {
		return 'query_var';
	}

	public function get_label(): string {
		return _x( 'Query variable', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'text',
			'label' => _x( 'Variable name', 'modifiers', 'voxel-backend' ),
		] );
	}

	public function run( $group ) {
		$key = $this->get_arg(0);
		if ( empty( $key ) ) {
			return null;
		}

		$value = $_GET[ $key ] ?? null;
		if ( ! is_scalar( $value ) ) {
			return null;
		}

		$value = wp_unslash( $value );

		if ( $this->get_arg(1) !== 'raw' ) {
			return esc_html( $value );
		}

		return $value;
	}
}
