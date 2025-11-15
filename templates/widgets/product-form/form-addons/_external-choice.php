<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-external-choice">
	<div class="ts-form-group">
		<label>{{ data.choice.label }}</label>

		<template v-if="data.ref.addon.type === 'custom-select'">
			<div class="ts-stepper-input flexify">
				<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="data.ref.decrementQuantity(data.choice)"
					:class="{'vx-disabled': data.choice.quantity.min >= data.ref.value.selected.quantity}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
				</button>
				<input v-model="data.ref.value.selected.quantity" type="number" class="ts-input-box" @change="data.ref.validateQuantity(data.choice)">
				<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="data.ref.incrementQuantity(data.choice)"
					:class="{'vx-disabled': data.choice.quantity.max <= data.ref.value.selected.quantity}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
				</button>
			</div>
		</template>
		<template v-else>
			<div class="ts-stepper-input flexify">
				<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="data.ref.decrementQuantity(data.choice)"
					:class="{'vx-disabled': data.choice.quantity.min >= data.ref.value.selected[ data.ref.getSelectionIndex(data.choice) ].quantity}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
				</button>
				<input v-model="data.ref.value.selected[ data.ref.getSelectionIndex(data.choice) ].quantity" type="number" class="ts-input-box" @change="data.ref.validateQuantity(data.choice)">
				<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="data.ref.incrementQuantity(data.choice)"
					:class="{'vx-disabled': data.choice.quantity.max <= data.ref.value.selected[ data.ref.getSelectionIndex(data.choice) ].quantity}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
				</button>
			</div>
		</template>
	</div>
</script>
