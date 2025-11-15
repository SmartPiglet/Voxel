<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Text_Model extends Base_Form_Model {

	protected $args = [
		'placeholder' => null,
		'autocomplete' => null,
	];

	protected function template() { ?>
		<input type="text" <?= $this->attributes('v-model', 'required', 'placeholder', 'autocomplete') ?>>
	<?php }

}
