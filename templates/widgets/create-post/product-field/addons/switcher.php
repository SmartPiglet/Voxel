<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-addon-switcher">
	<div class="ts-form-group switcher-label">
		<template v-if="!addon.required">
			<label>
				<div class="switch-slider">
					<div class="onoffswitch">
						<input type="checkbox" class="onoffswitch-checkbox" v-model="value.enabled">
						<label class="onoffswitch-label" @click.prevent="value.enabled = !value.enabled"></label>
					</div>
				</div>
				{{ addon.label }}
				<small>{{ addon.description }}</small>
			</label>
		</template>
		<template v-else>
			<label>
				{{ addon.label }}
				<small>{{ addon.description }}</small>
			</label>
		</template>
		<div class="ts-field-repeater" v-if="addon.required || value.enabled">
			<div class="medium form-field-grid">
				<div class="ts-form-group">
					<label><?= _x( 'Enter price for this add-on', 'product field', 'voxel' ) ?></label>
					<div class="input-container">
						<input type="number" v-model="value.price" class="ts-filter" placeholder="<?= esc_attr( _x( 'Add price', 'product field', 'voxel' ) ) ?>" min="0">
						<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
