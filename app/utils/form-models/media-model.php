<?php

namespace Voxel\Utils\Form_Models;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Media_Model extends Base_Form_Model {

	protected $args = [
		'file-type' => null,
		'multiple' => false,
	];

	protected function init() {
		wp_enqueue_media();
	}

	protected function template() { ?>
		<media-select
			<?= $this->attributes('v-model') ?>
			:file-type="<?= esc_attr( wp_json_encode( $this->args['file-type'] ) ) ?>"
			:multiple="<?= !! $this->args['multiple'] ? 'true' : 'false' ?>"
		></media-select>
	<?php }

}
