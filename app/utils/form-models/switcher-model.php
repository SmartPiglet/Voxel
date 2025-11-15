<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Switcher_Model extends Base_Form_Model {

	private $unique_id;

	protected function init() {
		$this->unique_id = wp_unique_id('switcher-');
		$this->args['classes'][] = 'switch-slider';
	}

	protected function template() { ?>
		<div class="onoffswitch">
			<input
				type="checkbox"
				class="onoffswitch-checkbox"
				id="<?php echo esc_attr( $this->unique_id ) ?>"
				tabindex="0"
				<?= $this->attributes('v-model', 'required') ?>
			>
			<label class="onoffswitch-label" for="<?php echo esc_attr( $this->unique_id ) ?>"></label>
		</div>
	<?php }

}
