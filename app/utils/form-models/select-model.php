<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Select_Model extends Base_Form_Model {

	protected $args = [
		'choices' => [],
	];

	protected function template() { ?>
		<select <?= $this->attributes('v-model', 'required') ?>>
			<?php foreach ( (array) $this->args['choices'] as $value => $label ): ?>
				<option value="<?= esc_attr( $value ) ?>"><?= esc_html( $label ) ?></option>
			<?php endforeach ?>
		</select>
	<?php }

}
