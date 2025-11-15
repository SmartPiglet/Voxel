<?php

namespace Voxel\Modules\Elementor\Custom_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loop_Control extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'voxel-loop';
	}

	protected function get_default_settings() {
		return [
			'label_block' => true,
		];
	}

	public function content_template() {
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="vx-current-loop elementor-control-input-wrapper"></div>
			<div class="vx-modify-loop elementor-control-input-wrapper">
				<a href="#" class="elementor-button vx-loop-edit elementor-button-default "><?= __( 'Edit loop', 'voxel-backend' ) ?></a>
				<a href="#" class="elementor-button vx-loop-remove elementor-button-default "><?= __( 'Remove', 'voxel-backend' ) ?></a>
			</div>
		</div>
		<?php
	}
}
