<?php

namespace Voxel\Post_Types\Fields\Traits;

use \Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Model_Helpers {

	protected function get_key_model() {
		return [
			'type' => Form_Models\Key_Model::class,
			'label' => 'Field Key',
			'description' => 'Enter a unique field key',
			'classes' => 'x-col-4',
			'editable' => '!$root.isSingular(field.type)',
			'ref' => 'keyInput',
		];
	}

}
