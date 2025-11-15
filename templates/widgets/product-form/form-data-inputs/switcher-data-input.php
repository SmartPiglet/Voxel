<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-switcher">
	<div class="ts-form-group switcher-label">
		<label>
			<div class="switch-slider">
				<div class="onoffswitch">
					<input v-model="dataInputs.values[ dataInput.key ]" type="checkbox" class="onoffswitch-checkbox">
					<label class="onoffswitch-label" @click.prevent="dataInputs.values[ dataInput.key ] = !dataInputs.values[ dataInput.key ]"></label>
				</div>
			</div>{{ dataInput.label }}
		</label>
	</div>
</script>