<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-product-fields">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Fields</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12 ts-form-group">
				<p>Configure the field order and labels</p>
			</div>
			<div class="x-col-12 field-container ts-drag-animation" ref="fields-container">
				<template v-if="has_fields">
					<draggable
						v-model="$root.product_fields"
						group="product_fields"
						handle=".field-head"
						item-key="key"
						@start="dragStart"
						@end="dragEnd"
					>
						<template #item="{element: field}">
							<div v-if="$root.evaluate_conditions(field.conditions)" class="single-field wide">
								<div class="field-head" @click="toggleActive(field)">
									<p class="field-name">{{ field.props.label }}</p>
									<span class="field-type">{{ field.props.key }}</span>
								</div>
							</div>
						</template>
					</draggable>
				</template>
				<template v-else>
					<div class="ts-form-group">
						<p>No product fields enabled</p>
					</div>
				</template>
			</div>
		</div>
	</div>

	<field-modal v-if="activeField" :field="activeField"></field-modal>
</script>

<script type="text/html" id="pte-product-field-modal">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Field options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<div class="field-modal-body">
						<div class="x-row">
							<?= $product_field_options_markup ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>
