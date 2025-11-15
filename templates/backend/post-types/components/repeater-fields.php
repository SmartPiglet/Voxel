<?php
/**
 * Repeater fields - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-repeater-fields-template">
	<div class="x-col-12">
		<div class="sub-heading">
			<p>Used fields</p>
		</div>
		<div class="field-container ts-drag-animation" ref="fields-container">
			<draggable
				v-model="field.fields"
				group="repeater-fields"
				handle=".field-head"
				item-key="key"
				@start="dragStart"
				@end="dragEnd"
			>
				<template #item="{element: subfield, index: index}">
					<div class="single-field wide" :class="{'ts-form-step': subfield.type === 'ui-step', open: active === subfield}">
						<div class="field-head" @click="toggleActive(subfield)">
							<div v-if="subfield.type === 'ui-step'" class="field-actions left-actions">
								<span class="field-action all-center">
									<a href="#" @click.prevent><i class="las la-angle-up"></i></a>
								</span>
							</div>
							<p class="field-name">{{ subfield.label }}</p>
							<span class="field-type">{{ $root.options.field_types[ subfield.type ]?.label || subfield.type }} · {{ subfield.key }}</span>
							<div class="field-actions">
								<span class="field-action all-center">
									<a href="#" @click.stop.prevent="deleteField(subfield)">
										<i class="lar la-trash-alt icon-sm"></i>
									</a>
								</span>
							</div>
						</div>
						<div v-if="active === subfield" class="field-body">
							<field-props :field="subfield" :repeater="field"></field-props>
						</div>
					</div>
				</template>
			</draggable>
		</div>
	</div>
	<div class="x-col-12">
		<div class="available-fields-container">
			<div class="sub-heading">
				<p>Add a field</p>
			</div>
			<div class="add-field">
				<template v-for="field_type in field_types">
					<div v-if="!$root.isSingular(field_type.type) && $root.options.repeatable[field_type.type]" class="">
						<div @click.prevent="addField(field_type)" class="ts-button ts-outline">
							{{ field_type.label }}
						</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</script>
