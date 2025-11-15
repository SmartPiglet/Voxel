<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-variations-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Product attributes</h3>
			<p></p>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_model::render( [
				'v-model' => 'config.vendor_attributes.enabled',
				'label' => 'Enable custom attributes',
				'description' => 'Allow custom attributes to be created during submission. Custom attributes are unique to a specific product',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_model::render( [
				'v-model' => 'config.predefined_attributes.enabled',
				'label' => 'Enable pre-defined attributes',
				'description' => 'Pre-define attributes on the product type. Pre-defined attributes can be shared across products of this type',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>

	<div v-if="config.predefined_attributes.enabled" class="ts-group">
		<div class="ts-group-head">
			<h3>Create pre-defined attributes</h3>
		</div>

		<div class="x-row">
			<div class="x-col-12">
				
				<div class="add-field">
					<template v-for="label, display_mode in {
						buttons: 'Buttons',
						radio: 'Radio',
						dropdown: 'Dropdown',
						colors: 'Colors',
					}">
						<div @click.prevent="createAttribute(display_mode, label)" class="ts-button ts-outline">
							<p class="field-name">{{ label }}</p>
						</div>
					</template>
				</div>
			</div>
			<div class="x-col-12 field-container ts-drag-animation" ref="fields-container">
				<template v-if="config.attributes.length">
					<draggable
						v-model="config.attributes"
						group="attributes"
						handle=".field-head"
						item-key="key"
						@start="dragStart"
						@end="dragEnd"
					>
						<template #item="{element: attribute}">
							<div class="single-field wide">
								<div class="field-head" @click="toggleActive(attribute)">
									<p class="field-name">{{ attribute.label }}</p>
									<span class="field-type">{{ attribute.display_mode }}</span>
									<span class="field-type">{{ attribute.type }}</span>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.stop.prevent="deleteAttribute(attribute)">
												<i class="lar la-trash-alt icon-sm"></i>
											</a>
										</span>
									</div>
								</div>
							</div>
						</template>
					</draggable>
				</template>
				<template v-else>
					<div class="ts-form-group">
						<p>You have not created any attributes yet.</p>
					</div>
				</template>
			</div>
		</div>
	</div>

	<attribute-modal v-if="active" :attribute="active"></attribute-modal>
</script>

<script type="text/html" id="pte-attribute-modal">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Attribute options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<div class="field-modal-body">
						<div class="x-row">

							<?php \Voxel\Utils\Form_Models\Text_Model::render( [
								'v-model' => 'attribute.label',
								'classes' => 'x-col-6',
								'label' => 'Name',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Key_Model::render( [
								'v-model' => 'attribute.key',
								'classes' => 'x-col-6',
								'label' => 'Key',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Textarea_Model::render( [
								'v-model' => 'attribute.description',
								'classes' => 'x-col-12',
								'label' => 'Description',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'label' => 'Display mode',
								'v-model' => 'attribute.display_mode',
								'classes' => 'x-col-12',
								'choices' => [
									'buttons' => 'Buttons',
									'radio' => 'Radio',
									'dropdown' => 'Dropdown',
									'colors' => 'Colors',
								],
							] ) ?>

							<div class="ts-form-group x-col-12">
								<template v-if="attribute.display_mode === 'colors'">
									<attribute-choices :attribute="attribute" :models="[
										{ key: 'label', type: 'text', columns: 3, columnLabel: 'Label' },
										{ key: 'value', type: 'key', columns: 3, columnLabel: 'Value' },
										{ key: 'color', type: 'color', columns: 4, columnLabel: 'Color' },
									].filter(Boolean)"></attribute-choices>
								</template>
								<template v-else>
									<attribute-choices :attribute="attribute" :models="[
										{ key: 'label', type: 'text', columns: 5, columnLabel: 'Label' },
										{ key: 'value', type: 'key', columns: 5, columnLabel: 'Value' },
									].filter(Boolean)"></attribute-choices>
								</template>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>

<script type="text/html" id="pte-attribute-choices">
	<div class="field-container ts-addon-choices" ref="fields-container">
		<div class="ts-form-group">
			<label>Add attribute values</label>
		</div>
		<template v-if="attribute.choices.length">
			<div class="x-row">
				<template v-for="model in models">
					<div class="ts-form-group" :class="'x-col-'+model.columns">
						<label>{{ model.columnLabel }}</label>
					</div>
				</template>
				<div class="x-col-2 ts-form-group">
					<label></label>
				</div>
			</div>
			<draggable
				v-model="attribute.choices"
				group="field-choices"
				handle=".drag-field"
				item-key="key"
				@start="dragStart"
				@end="dragEnd"
			>
				<template #item="{element: choice, index: index}">
					<div class="single-field wide">
						<div class="x-row wrap-row">
							<div class="x-col-12">
								<div class="x-row">
									<attribute-choice :parent="this" :choice="choice" :ref="'choice:'+index"></attribute-choice>
									<div class="x-col-2 text-right">
										<a @click.prevent="remove(choice)" href="#" class="ts-button ts-outline icon-only" style="margin-right: 8px;">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
										<a href="#" class="ts-button ts-outline icon-only drag-field">
											<i class="las la-grip-lines icon-sm"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</template>
			</draggable>
		</template>

		<a href="#" @click.prevent="add" class="ts-button ts-outline">Add value</a>
	</div>
</script>

<script type="text/html" id="pte-attribute-choice">
	<template v-for="model in parent.models">
		<div class="ts-form-group" :class="'x-col-'+model.columns">
			<template v-if="model.type === 'key'">
				<field-key
					:editable="true"
					:ref="'model:'+model.key"
					v-model="choice[model.key]"
					@update:modelValue="delete choice.__autovalue"
					:unlocked="choice.__autovalue"
				></field-key>
			</template>
			<template v-else-if="model.type === 'icon'">
				<icon-picker v-model="choice[ model.key ]" :ref="'model:'+model.key" :allow-fonticons="false"></icon-picker>
			</template>
			<template v-else-if="model.type === 'text'">
				<input
					type="text"
					:ref="'model:'+model.key"
					v-model="choice[model.key]"
					@input="choice.__autovalue ? ( choice.value = choice.label ) : ''"
					@blur="delete choice.__autovalue"
				>
			</template>
			<template v-else-if="model.type === 'color'">
				<color-picker v-model="choice[ model.key ]" :ref="'model:'+model.key"></color-picker>
			</template>
		</div>
	</template>
</script>
