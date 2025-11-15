<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-textarea">
	<div class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="input-container">
			<textarea
				v-model="dataInputs.values[ dataInput.key ]"
				class="ts-filter min-scroll"
				:placeholder="dataInput.props.placeholder"
				:minlength="dataInput.props.minlength"
				:maxlength="dataInput.props.maxlength"
			></textarea>
		</div>
	</div>
</script>