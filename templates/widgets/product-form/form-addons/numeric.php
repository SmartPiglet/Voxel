<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-numeric">
	<div v-if="addon.props.display_mode === 'input'" class="ts-form-group">
		<label>{{ addon.label }}</label>
		<div class="input-container">
			<input v-model="value.quantity" type="number" class="ts-filter" :min="addon.required ? addon.props.min_units : 0" :max="addon.props.max_units" @input="validateValueInBounds">
		</div>
	</div>
	<div v-else class="ts-form-group">
		<label>{{ addon.label }}</label>
		<div class="ts-stepper-input flexify">
			<button class="ts-stepper-left ts-icon-btn" @click.prevent="decrement"
				:class="{'vx-disabled': (addon.required ? addon.props.min_units : 0) >= value.quantity}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			</button>
			<input v-model="value.quantity" type="number" class="ts-input-box" @input="validateValueInBounds">
			<button class="ts-stepper-right ts-icon-btn" @click.prevent="increment"
				:class="{'vx-disabled': addon.props.max_units <= value.quantity}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			</button>
		</div>
	</div>
</script>
