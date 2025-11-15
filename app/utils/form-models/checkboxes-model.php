<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Checkboxes_Model extends Base_Form_Model {

	protected $args = [
		'choices' => [],
		'columns' => 'one',
	];

	protected function init() {
		$this->args['classes'][] = 'ts-checkbox';
	}

	protected function template() { ?>
		<div class="ts-checkbox-container <?= esc_attr( $this->args['columns'] ) ?>-column min-scroll">
			<?php foreach ( (array) $this->args['choices'] as $value => $label ): ?>
				<label class="container-checkbox">
					<?= $label ?>
					<input type="checkbox" value="<?= esc_attr( $value ) ?>" <?= $this->attributes('v-model') ?>>
					<span class="checkmark"></span>
				</label>
			<?php endforeach ?>
		</div>
	<?php }

}
