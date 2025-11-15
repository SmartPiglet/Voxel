<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-base-price-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Price settings</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.discount_price.enabled',
				'label' => 'Enable discount price',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>
</script>
