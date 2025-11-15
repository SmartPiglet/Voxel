<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Color_Model extends Base_Form_Model {

	protected function template() { ?>
		<color-picker <?= $this->attributes('v-model') ?>></color-picker>
	<?php }

}
