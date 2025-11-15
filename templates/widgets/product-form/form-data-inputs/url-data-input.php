<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-url">
	<div class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="input-container">
			<input
				v-model="dataInputs.values[ dataInput.key ]"
				type="url"
				class="ts-filter"
				:placeholder="dataInput.props.placeholder"
			>
		</div>
	</div>
</script>