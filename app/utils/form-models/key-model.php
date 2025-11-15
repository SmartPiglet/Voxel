<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Key_Model extends Base_Form_Model {

	protected $args = [
		'editable' => true,
		'ref' => null,
		'unlocked' => false,
		'format' => null,
		'placeholder' => null,
	];

	protected function template() { ?>
		<field-key
			<?= $this->attributes('v-model', 'ref', 'format', 'placeholder') ?>
			:editable="<?= is_string( $this->args['editable'] )
				? $this->args['editable']
				: ( !! $this->args['editable'] ? 'true' : 'false' ) ?>"
			:unlocked="<?= $this->args['unlocked'] ? 'true' : 'false' ?>"
		></field-key>
	<?php }

}
