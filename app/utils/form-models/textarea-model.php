<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Textarea_Model extends Base_Form_Model {

	protected $args = [
		'style' => null,
	];

	protected function template() { ?>
		<textarea <?= $this->attributes('v-model', 'required', 'style') ?>></textarea>
	<?php }

}
