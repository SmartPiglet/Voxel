<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-number">
	<div v-if="dataInput.props.display_mode === 'stepper'" class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="ts-stepper-input flexify">
			<button class="ts-stepper-left ts-icon-btn" @click.prevent="decrement"
				:class="{'vx-disabled': dataInputs.values[ dataInput.key ] <= dataInput.props.min}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			</button>
			<input v-model="dataInputs.values[ dataInput.key ]" type="number" class="ts-input-box" :placeholder="dataInput.props.placeholder" @input="validateValueInBounds">
			<button class="ts-stepper-right ts-icon-btn" @click.prevent="increment"
				:class="{'vx-disabled': dataInputs.values[ dataInput.key ] >= dataInput.props.max}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			</button>
		</div>
	</div>
	<div v-else class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="input-container">
			<input
				v-model="dataInputs.values[ dataInput.key ]"
				type="number"
				class="ts-filter"
				:placeholder="dataInput.props.placeholder"
				:min="dataInput.props.min"
				:max="dataInput.props.max"
				@input="validateValueInBounds"
			>
		</div>
	</div>
</script>