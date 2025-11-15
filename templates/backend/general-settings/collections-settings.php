<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Collection limits</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.addons.collections.max_count',
			'label' => 'Maximum collections per user',
			'classes' => 'x-col-12',
			'infobox' => 'Set the maximum number of collections a user can create. Set to 0 for unlimited. This limit does not apply to administrators.',
		] ) ?>
	</div>
</div>

