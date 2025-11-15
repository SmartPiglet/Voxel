<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-quantity">
	<div v-if="field.props.enabled && !field.props.sold_individually" class="ts-form-group">
		<label><?= _x( 'Select quantity', 'product form quantity', 'voxel' ) ?></label>
		<div class="ts-stepper-input flexify">
			<button class="ts-stepper-left ts-icon-btn" @click.prevent="decrement"
				:class="{'vx-disabled': value.quantity <= 1}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			</button>
			<input v-model="value.quantity" type="number" class="ts-input-box" @change="validateValueInBounds">
			<button class="ts-stepper-right ts-icon-btn" @click.prevent="increment"
				:class="{'vx-disabled': field.props.quantity <= value.quantity}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			</button>
		</div>
	</div>
</script>
