<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-text">
	<div class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="input-container">
			<input
				v-model="dataInputs.values[ dataInput.key ]"
				type="text"
				class="ts-filter"
				:placeholder="dataInput.props.placeholder"
				:minlength="dataInput.props.minlength"
				:maxlength="dataInput.props.maxlength"
			>
		</div>
	</div>
</script>