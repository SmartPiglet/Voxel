<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-addons-module">

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Addons</h3>
		</div>

		<div class="x-row">
			<div class="x-col-12">

				<div class="add-field">
					<template v-for="addon in $root.options.product_addons">
						<div
							v-if="addon.props.type !== 'select' && addon.props.type !== 'multiselect'"
							@click.prevent="createAddon(addon)"
							class="ts-button ts-outline"
						>
							<p class="field-name">{{ addon.props.label }}</p>
						</div>
					</template>
				</div>
			</div>
			<div class="x-col-12 field-container ts-drag-animation" ref="fields-container">
				<template v-if="config.items.length">
					<draggable
						v-model="config.items"
						group="addons"
						handle=".field-head"
						item-key="key"
						@start="dragStart"
						@end="dragEnd"
					>
						<template #item="{element: addon}">
							<div class="single-field wide">
								<div class="field-head" @click="toggleActive(addon)">
									<p class="field-name">{{ addon.label }}</p>
									<span class="field-type">{{ addon.type }}</span>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.stop.prevent="deleteAddon(addon)">
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
						<p>You have not created any addons yet.</p>
					</div>
				</template>
			</div>


		</div>
	</div>
	<addon-modal v-if="active" :addon="active"></addon-modal>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Additional settings</h3>
		</div>

		<div class="x-row">
			<div class="x-col-12">
				<div class="ts-form-group switch-slider">
					<label>
						Disable base price
						<span title="If checked, product price will be determined based on add-ons only.">[?]</span>
					</label>
					<div class="onoffswitch">
						<input type="checkbox" class="onoffswitch-checkbox" tabindex="0" :checked="!$root.config.modules.base_price.enabled">
						<label class="onoffswitch-label" @click.prevent="$root.config.modules.base_price.enabled = !$root.config.modules.base_price.enabled"></label>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/html" id="pte-addon-modal">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Addon options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<div class="field-modal-body">
						<div class="x-row">
							<?= $addon_options_markup ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>

<script type="text/html" id="pte-addons-choices">
	<div class="field-container ts-addon-choices" ref="fields-container">
		<div class="ts-form-group">
			<label>Choices</label>
		</div>
		<template v-if="addon.choices.length">
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
				v-model="addon.choices"
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
									<addon-choice :parent="this" :choice="choice" :ref="'choice:'+index"></addon-choice>
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

		<a href="#" @click.prevent="add" class="ts-button ts-outline">Add choice</a>
	</div>
</script>

<script type="text/html" id="pte-addons-choice">
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
		</div>
	</template>
</script>
