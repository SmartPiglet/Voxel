<?php

namespace Voxel\Dynamic_Data\Modifiers\Group_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Group_Method extends \Voxel\Dynamic_Data\Modifiers\Base_Modifier {

	public function get_type(): string {
		return 'method';
	}

	public function apply( string $value ) {
		return $this->run( $this->tag->get_group() );
	}

	abstract public function run( $group );

}
