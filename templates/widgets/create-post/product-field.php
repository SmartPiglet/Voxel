<?php
/**
 * Product field template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="create-post-product-field">
	<div class="ts-form-group">
		<div class="form-field-grid">
			<div class="ts-form-group switcher-label" v-if="!field.required">
				<label>
					<div  class="switch-slider">
						<div class="onoffswitch">
							<input type="checkbox" class="onoffswitch-checkbox" v-model="field.value.enabled">
							<label class="onoffswitch-label" @click.prevent="field.value.enabled = !field.value.enabled"></label>
						</div>
					</div>
					{{ field.label }}
					<slot name="errors"></slot>
					<div class="vx-dialog" v-if="field.description">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						<div class="vx-dialog-content min-scroll">
							<p>{{ field.description }}</p>
						</div>
					</div>
				</label>
			</div>
			<!-- <div class="ts-form-group ui-heading-field" v-if="field.required">
				<label>
					{{ field.label }}
					<slot name="errors"></slot>
					<div class="vx-dialog" v-if="field.description">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						<div class="vx-dialog-content">
							<p>{{ field.description }}</p>
						</div>
					</div>
				</label>
			</div> -->

			<template v-if="field.value.enabled">
				<template v-if="Object.keys(field.props.product_types).length >= 2">
					<div class="ts-form-group">
						<label><?= _x( 'Product type', 'product field', 'voxel' ) ?></label>
						<div class="ts-filter">
							<select @change="set_product_type($event.target.value)">
								<option v-for="config in field.props.product_types" :value="config.key" :selected="product_type.key === config.key">
									{{ config.label }}
								</option>
							</select>
							<div class="ts-down-icon"></div>
						</div>
					</div>
				</template>

				<template v-if="product_type !== null">
					<template v-for="field in product_type.fields" :key="[product_type.key, field.key].join(':')">
						<component
							:is="field.component_key"
							:field="field"
							:product="this"
							:product-type="product_type"
							:ref="'type:'+product_type.key+'-field:'+field.key"
						></component>
					</template>
				</template>
			</template>
		</div>
	</div>
</script>
