<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-stock-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Stock settings</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.sku.enabled',
				'label' => 'Enable <abbr title="Stock Keeping Unit">SKU</abbr> input',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>
</script>
