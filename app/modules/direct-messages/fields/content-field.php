<?php

namespace Voxel\Modules\Direct_Messages\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Content_Field extends \Voxel\Utils\Object_Fields\Base_Field {

	protected function base_props(): array {
		return [
			'label' => 'Content',
			'key' => 'content',
			'maxlength' => \Voxel\get( 'settings.messages.maxlength', 2000 ),
		];
	}

	public function sanitize( $value ) {
		return sanitize_textarea_field( $value );
	}

	public function validate( $value ): void {
		$this->validate_maxlength( $value );
	}
}
