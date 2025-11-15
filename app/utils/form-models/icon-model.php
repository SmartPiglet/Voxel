<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Icon_Model extends Base_Form_Model {

	protected $args = [
		':allow-fonticons' => null,
	];

	protected function template() { ?>
		<icon-picker <?= $this->attributes('v-model', ':allow-fonticons') ?>></icon-picker>
	<?php }

}
