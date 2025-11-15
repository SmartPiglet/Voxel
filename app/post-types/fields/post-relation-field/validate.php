<?php

namespace Voxel\Post_Types\Fields\Post_Relation_Field;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Validate {

	public function validate( $value ): void {
		if ( in_array( $this->props['relation_type'], [ 'has_many', 'belongs_to_many' ] ) ) {
			if ( ! empty( $this->props['max_count'] ) && count( $value ) > absint( $this->props['max_count'] ) ) {
				throw new \Exception( sprintf(
					_x( '%s cannot have more than %d items.', 'field validation', 'voxel' ),
					$this->get_label(),
					absint( $this->props['max_count'] )
				) );
			}
		}
	}
}
