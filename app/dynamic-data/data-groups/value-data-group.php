<?php

namespace Voxel\Dynamic_Data\Data_Groups;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Types\Base_Data_Type as Base_Data_Type;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Value_Data_Group extends Base_Data_Group {

	protected $token;

	public function get_type(): string {
		return 'value';
	}

	protected function properties(): array {
		return [
			'' => Tag::String('Current value')->render( function() {
				if ( $this->token === null ) {
					return '';
				}

				$parent = $this->token->get_parent();
				if ( ! $parent instanceof \Voxel\Dynamic_Data\Modifiers\Base_Modifier ) {
					return '';
				}

				return $parent->get_current_value();
			} ),
		];
	}

	public function set_token( \Voxel\Dynamic_Data\VoxelScript\Tokens\Dynamic_Tag $token ) {
		$this->token = $token;
	}
}
