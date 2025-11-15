<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class DTag_Model extends Base_Form_Model {

	protected $args = [
		':tag-groups' => null,
		'placeholder' => null,
	];

	protected function template() { ?>
		<dtag-input <?= $this->attributes('v-model', ':tag-groups', 'placeholder') ?>></dtag-input>
	<?php }

}
