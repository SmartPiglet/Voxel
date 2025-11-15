<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Radio_Buttons_Model extends Base_Form_Model {

	protected $args = [
		'choices' => [],
		'columns' => 'two',
	];

	protected function init() {
		$this->args['classes'][] = 'ts-radio';
	}

	protected function template() { ?>
		<div class="ts-radio-container <?= esc_attr( $this->args['columns'] ) ?>-column">
			<?php foreach ( (array) $this->args['choices'] as $value => $label ): ?>
				<label class="container-radio">
					<?= $label ?>
					<input type="radio" value="<?= esc_attr( $value ) ?>" <?= $this->attributes('v-model') ?>>
					<span class="checkmark"></span>
				</label>
			<?php endforeach ?>
		</div>
	<?php }

}
