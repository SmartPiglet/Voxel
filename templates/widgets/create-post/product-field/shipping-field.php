<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-shipping">
	<div class="ts-form-group switcher-label">
		<label v-if="!field.props.required">
			<div class="switch-slider">
				<div class="onoffswitch">
					<input type="checkbox" class="onoffswitch-checkbox" v-model="value.enabled">
					<label class="onoffswitch-label" @click.prevent="value.enabled = !value.enabled"></label>
				</div>
			</div>
			<?= _x( 'Enable shipping', 'product field shipping', 'voxel' ) ?>
		</label>

		<template v-if="value.enabled || field.props.required">
			<div class="ts-form-group">
				<label><?= _x( 'Shipping class', 'product field shipping', 'voxel' ) ?></label>
				<div class="ts-filter">
					<select v-model="value.shipping_class">
						<option value="">
							<template v-if="field.props.default_shipping_class && field.props.shipping_classes[field.props.default_shipping_class]">
								<?= _x( 'Default', 'product field shipping', 'voxel' ) ?>
							</template>
							<template v-else>
								<?= _x( 'None', 'product field shipping', 'voxel' ) ?>
							</template>
						</option>
						<option v-for="shipping_class in field.props.shipping_classes" :value="shipping_class.key">
							{{ shipping_class.label }}
						</option>
					</select>
					<div class="ts-down-icon"></div>
				</div>
			</div>
		</template>

		<!-- <pre debug>{{ value }}</pre>
		<pre debug>{{ field }}</pre> -->
	</div>
</script>
