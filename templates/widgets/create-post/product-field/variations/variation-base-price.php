<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-variation-base-price">
	<div class="ts-form-group" :class="{'vx-1-2': field.props.discount_price.enabled}">
		<label><?= _x( 'Price', 'product field variations', 'voxel' ) ?></label>
		<div class="input-container">
			<input
				type="number" class="ts-filter" v-model="value.amount" min="0"
				placeholder="<?= esc_attr( _x( 'Add price', 'product field', 'voxel' ) ) ?>"
			>
			<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
		</div>
	</div>

	<div v-if="field.props.discount_price.enabled" class="ts-form-group vx-1-2">
		<label><?= _x( 'Discount price', 'product field variations', 'voxel' ) ?></label>
		<div class="input-container">
			<input
				type="number" class="ts-filter" v-model="value.discount_amount" min="0"
				placeholder="<?= esc_attr( _x( 'Add price', 'product field', 'voxel' ) ) ?>"
			>
			<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
		</div>
	</div>
</script>
