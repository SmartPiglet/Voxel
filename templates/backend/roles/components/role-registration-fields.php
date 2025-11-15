<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Registration fields</h3>
		<span class="vx-info-box wide" style="float: right;">
			<?php \Voxel\svg( 'info.svg' ) ?>
			<p>
				Configure fields that will show up in the user registration form for this
				role. To add more profile fields, edit the Profiles post type.
			</p>
		</span>
	</div>
	<div class="x-row">
		<div class="used-fields x-col-12">
			<div class="field-container ts-draggable-inserts" ref="fields-container">
				<draggable
					v-model="$root.config.registration.fields"
					group="fields"
					handle=".field-head"
					item-key="key"
					@start="$refs['fields-container'].classList.add('drag-active')"
					@end="$refs['fields-container'].classList.remove('drag-active')"
					@add="onRegistrationFieldAdd"
				>
					<template #item="{element: field, index: index}">
						<template v-if="field.source === 'auth'">
							<div class="single-field wide">
								<div class="field-head" @click="active_field = field">
									<p class="field-name">{{ field.label }}</p>
									<span class="field-type">{{ field.key.replace('voxel:auth-', '') }}</span>
								</div>
							</div>
						</template>
						<template v-else>
							<div class="single-field wide">
								<div class="field-head" @click="active_field = field">
									<p class="field-name">{{ field.label || fieldProp( field.key, 'label' ) }}</p>
									<span class="field-type">{{ fieldProp( field.key, 'type' ) }}</span>
									<div class="field-actions">
										<span class="field-action all-center" v-if="field['enable-conditions']">
											<a href="#" @click.prevent="active_field = field" title="Conditional logic is enabled for this field">
												<i class="las la-code-branch icon-sm"></i>
											</a>
										</span>
										<span class="field-action all-center" @click.stop.prevent="deleteField(field)">
											<i class="lar la-trash-alt icon-sm"></i>
										</span>
									</div>
								</div>
							</div>
						</template>
					</template>
				</draggable>
			</div>
		</div>
		<div class="x-col-12">
			<div class="ts-form-group">
				<label>Available fields</label>
			</div>

			<draggable class="add-field" :list="Object.values(available_fields)" :group="{ name: 'fields', pull: 'clone', put: false }" :sort="false" item-key="key">
				<template #item="{element: field}">
					<div :class="{'vx-disabled': !canUseField(field)}">
						<div @click.prevent="useField( field )" class="ts-button ts-outline c-move">
							{{ field.props.label }}
						</div>
					</div>
				</template>
			</draggable>
		</div>
	</div>
</div>

<field-modal v-if="active_field" :field="active_field"></field-modal>
