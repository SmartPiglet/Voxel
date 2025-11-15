<?php

namespace Voxel\Post_Types\Fields\Taxonomy_Field;

use \Voxel\Dynamic_Data\Tag as Tag;
use \Voxel\Dynamic_Data\Data_Groups\Term\Term_Data_Group as Term_Data_Group;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Exports {

	public function dynamic_data() {
		return Tag::Object_List( $this->get_label() )->items( function() {
			return (array) $this->get_value();
		} )->properties( function( $index, $item ) {
			if ( ! $item instanceof \Voxel\Term ) {
				return Term_Data_Group::mock();
			}

			return \Voxel\Dynamic_Data\Group::Term( $item );
		} );
	}
}
