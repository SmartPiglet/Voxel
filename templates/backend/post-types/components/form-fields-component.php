<?php
/**
 * Post fields - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-fields-template">
	<div class="ts-tab-content">
		<div class="x-row">
			<div class="x-col-12 ts-content-head">
				<h1>Fields</h1>
				<p>Create and manage fields for this post type</p>
			</div>
		</div>
		<div class="x-row">
			<div class="used-fields x-col-6">
				<div class="sub-heading">
					<p>Used fields</p>
				</div>
				<div class="field-container ts-draggable-inserts" ref="fields-container">
					<field-list-item
						:field="$root.config.fields[0]"
						:show-delete="false"
						@click:edit="toggleActive( $root.config.fields[0] )"
						@click:edit-visibility="toggleActive( $root.config.fields[0], 'visibility' )"
						@click:edit-conditions="toggleActive( $root.config.fields[0], 'conditions' )"
						@click:duplicate="duplicateField( $root.config.fields[0] )"
						@click:delete="deleteField( $root.config.fields[0] )"
					></field-list-item>

					<draggable
						v-model="$root.config.fields"
						group="fields"
						handle=".field-head"
						item-key="key"
						@start="dragStart"
						@end="dragEnd"
						@add="onAdd"
					>
						<template #item="{element: field, index: index}">
							<field-list-item
								v-if="index !== 0"
								:field="field"
								:show-delete="true"
								@click:edit="toggleActive(field)"
								@click:edit-visibility="toggleActive( field, 'visibility' )"
								@click:edit-conditions="toggleActive( field, 'conditions' )"
								@click:duplicate="duplicateField( field )"
								@click:delete="deleteField(field)"
							></field-list-item>
						</template>
					</draggable>
				</div>
			</div>
			<div class="x-col-1"></div>
			<div class="x-col-5">
				<div class="available-fields-container">
					<div class="sub-heading">
						<p>Available fields</p>
					</div>

					<div class="ts-form-group mb20">
						<input v-model="search" type="text" placeholder="Search fields">
					</div>

					<ul v-if="!search.trim().length" class="inner-tabs">
						<li :class="{'current-item': active_set === 'presets'}">
							<a @click.prevent="active_set = 'presets'" href="#">Presets</a>
						</li>
						<li :class="{'current-item': active_set === 'custom'}">
							<a @click.prevent="active_set = 'custom'" href="#">Custom fields</a>
						</li>
						<li :class="{'current-item': active_set === 'ui'}">
							<a @click.prevent="active_set = 'ui'" href="#">Layout</a>
						</li>
					</ul>

					<div class="">
						<template v-if="search.trim().length">
							<template v-if="search_results.presets.length || search_results.field_types.length">
								<template v-if="search_results.presets.length">
									<div class="sub-heading">
										<p>Presets</p>
									</div>
									<draggable class="add-field" :list="[...search_results.presets]" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
										<template #item="{element: preset}">
											<div :class="{'vx-disabled': !canAddPreset(preset)}">
												<div @click.prevent="addPreset(preset)" class="ts-button ts-outline c-move">
													{{ preset.label }}
												</div>
											</div>
										</template>
									</draggable>
								</template>

								<template v-if="search_results.field_types.length">
									<div class="sub-heading mt20">
										<p>Custom fields</p>
									</div>
									<draggable class="add-field" :list="[...search_results.field_types]" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="type">
										<template #item="{element: field_type}">
											<div v-if="!$root.isSingular(field_type.type)">
												<div @click.prevent="addField(field_type)" class="ts-button ts-outline c-move">
													{{ field_type.label }}
												</div>
											</div>
										</template>
									</draggable>
								</template>

							</template>
							<template v-if="!(search_results.presets.length || search_results.field_types.length)">
								<div class="sub-heading">
									<p>No fields found</p>
								</div>
							</template>
						</template>
						<template v-else-if="active_set === 'presets'">
							<draggable class="add-field" :list="field_presets" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
								<template #item="{element: preset}">
									<div :class="{'vx-disabled': !canAddPreset(preset)}">
										<div @click.prevent="addPreset(preset)" class="ts-button ts-outline c-move">
											{{ preset.label }}
										</div>
									</div>
								</template>
							</draggable>
						</template>
						<template v-else-if="active_set === 'custom'">
							<draggable class="add-field" :list="Object.values(field_types)" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
								<template #item="{element: field_type}">
									<div v-if="!$root.isSingular(field_type.type) && !$root.options.is_ui[field_type.type]">
										<div @click.prevent="addField(field_type)" class="ts-button ts-outline c-move">
											{{ field_type.label }}
										</div>
									</div>
								</template>
							</draggable>
						</template>
						<template v-else-if="active_set === 'ui'">
							<draggable class="add-field" :list="Object.values(field_types)" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
								<template #item="{element: field_type}">
									<div v-if="!$root.isSingular(field_type.type) && $root.options.is_ui[field_type.type]">
										<div @click.prevent="addField(field_type)" class="ts-button ts-outline c-move">
											{{ field_type.label }}
										</div>
									</div>
								</template>
							</draggable>
						</template>
					</div>
				</div>
			</div>
		</div>
	</div>

	<field-modal v-if="active" ref="fieldModal" :field="active"></field-modal>
</script>

<script type="text/html" id="post-type-field-list-item">
	<div class="single-field wide" :class="{'ts-form-step': field.type === 'ui-step'}">
		<div class="field-head" @click="$emit('click:edit')">

			<p class="field-name has-field-details">{{ field.label }}<span class="field-type">{{ $root.options.field_types[ field.type ]?.label || field.type }} · {{ field.key }}</span></p>

			<div class="field-actions">
				<span class="field-action all-center" v-if="!$root.isSingular(field.type)">
					<a href="#" title="Duplicate this field" @click.stop.prevent="$emit('click:duplicate')">
						<i class="las la-copy icon-sm"></i>
					</a>
				</span>
				<span class="field-action all-center" v-if="field['enable-conditions']">
					<a href="#" title="Conditional logic is enabled for this field" @click.stop.prevent="$emit('click:edit-conditions')">
						<i class="las la-code-branch icon-sm"></i>
					</a>
				</span>
				<span class="field-action all-center" v-if="field.visibility_rules.length">
					<a href="#" title="This field has visibility rules" @click.stop.prevent="$emit('click:edit-visibility')">
						<i class="las la-user-lock"></i>
					</a>
				</span>
				<span class="field-action all-center" v-if="showDelete" @click.stop.prevent="$emit('click:delete')">
					<i class="lar la-trash-alt icon-sm"></i>
				</span>
			</div>
		</div>
	</div>
</script>
