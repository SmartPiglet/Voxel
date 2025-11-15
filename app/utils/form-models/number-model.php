<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Number_Model extends Base_Form_Model {

	protected $args = [
		'min' => null,
		'max' => null,
		'step' => null,
		'placeholder' => null,
	];

	protected function template() { ?>
		<input type="number" <?= $this->attributes('v-model', 'required', 'min', 'max', 'step', 'placeholder') ?>>
	<?php }

}
