<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-custom-prices">
	<div class="ts-form-group">
		<div class="ts-form-group switcher-label">
			<label>
				<div class="switch-slider">
					<div class="onoffswitch">
						<input type="checkbox" class="onoffswitch-checkbox" v-model="value.enabled">
						<label class="onoffswitch-label" @click.prevent="value.enabled = ! value.enabled"></label>
					</div>
				</div>
				<?= _x( 'Enable custom prices', 'product field custom prices', 'voxel' ) ?>
			</label>

			<template v-if="value.enabled">
				<draggable
					v-model="value.list"
					:group="product.field.key+':custom_prices'"
					handle=".ts-repeater-head"
					filter=".no-drag"
					item-key="id"
					class="ts-repeater-container"
					ref="prices"
				>
					<template #item="{element: pricing, index: index}">
						<div class="ts-field-repeater" :class="{collapsed: active !== pricing, disabled: !pricing.enabled}" style="pointer-events: all;">
							<div class="ts-repeater-head" @click.prevent="active = pricing === active ? null : pricing">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
								<label>
									{{ pricing.label || <?= wp_json_encode( _x( '(untitled)', 'product field custom prices', 'voxel' ) ) ?> }}
								</label>
								<div class="ts-repeater-controller">
									<a href="#" v-if="!pricing.enabled" @click.stop.prevent="pricing.enabled = true" class="ts-icon-btn ts-smaller no-drag">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
									</a>
									<a href="#" v-if="pricing.enabled" @click.stop.prevent="pricing.enabled = false" class="ts-icon-btn ts-smaller no-drag">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
									</a>
									<a href="#" @click.stop.prevent="deletePrice(pricing)" class="ts-icon-btn ts-smaller no-drag">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
									</a>
									<a href="#" class="ts-icon-btn ts-smaller no-drag" @click.prevent>
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
									</a>
								</div>
							</div>
							<div v-if="active === pricing" class="form-field-grid medium">
								<single-price :product="product" :product-type="productType" :custom-prices="this" :pricing="pricing"></single-price>
							</div>
						</div>
					</template>
				</draggable>

				<a href="#" v-if="value.list.length < field.props.limits.custom_prices" class="ts-repeater-add ts-btn ts-btn-4 form-btn" @click.prevent="createPrice">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
					<?= _x( 'Create custom pricing', 'product field custom prices', 'voxel' ) ?>
				</a>
			</template>
		</div>
	</div>

	<!-- <div class="ts-form-group">
		<pre debug>{{ field.props.limits }}</pre>
		<pre debug>{{ product.field.value }}</pre>
		<pre debug>{{ product.field.props }}</pre>
	</div> -->
</script>