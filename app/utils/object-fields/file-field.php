<?php

namespace Voxel\Utils\Object_Fields;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class File_Field extends \Voxel\Utils\Object_Fields\Base_Field {
	use \Voxel\Utils\Object_Fields\File_Field_Trait;

	protected function base_props(): array {
		return [
			'key' => 'file-field',
			'label' => '',
			'max-count' => 1,
			'max-size' => 2000,
			'allowed-types' => [
				'image/jpeg',
				'image/png',
				'image/webp',
			],
			'private_upload' => false,
		];
	}

	public function prepare_for_storage( $value ) {
		$file_ids = $this->_prepare_ids_from_sanitized_input( $value );
		return ! empty( $file_ids ) ? join( ',', $file_ids ) : null;
	}
}
