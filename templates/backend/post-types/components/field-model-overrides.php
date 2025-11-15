<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-field-model-overrides">
	<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
		'v-model' => 'field.overrides_enabled',
		'label' => 'Enable overrides',
		'classes' => 'x-col-12',
		'infobox_class' => 'wide-xxl',
		'infobox_element' => 'div',
		'infobox' => <<<HTML
		<b>Overrides</b> let you customize field options based on conditions.<br>
		By default, a field has one set of rules (like "Gallery upload limit: 3 images"),
		but sometimes you may want different rules for certain users or plans.
		Overrides allow you to do exactly that.<br><br>
		For example:<br><br>
		<ul style="list-style: disc;padding-left: 15px;">
			<li>Normally, users can upload <b>up to 3 images</b>.</li>
			<li>But with an Override, you can allow Pro plan users to upload <b>10 images with a larger image size</b>.</li>
		</ul>
		HTML,
	] ) ?>

	<template v-if="field.overrides_enabled">
		<draggable
			v-model="field.overrides"
			group="field_overrides"
			handle=".field-head"
			item-key="key"
			class="x-col-12"
		>
			<template #item="{element: rule, index: index}">
				<div class="ts-group">
					<div class="x-row">
						<div class="x-col-12 override-rule-list">
							<template v-for="model in $root.options.overridable_models[field.type]">
								<a v-if="typeof rule.models[model.key] === 'undefined'" href="#"
									class="override-rule" @click.prevent="overrideModel(model, rule)">
									<i class="la la-plus"></i>
									{{ model.label || model.key }}
								</a>
							</template>
						</div>
						<template v-for="model in $root.options.overridable_models[field.type]">
							<template v-if="typeof rule.models[model.key] !== 'undefined'">
								<div class="ts-form-group x-col-6">
									<label>
										{{ model.label }}
										<a href="#" @click.prevent="delete rule.models[model.key]" style="float: right;">Remove</a>
									</label>
									<template v-if="model.type === 'number'">
										<input type="number" v-model="rule.models[model.key]">
									</template>
									<template v-else-if="model.type === 'textarea'">
										<textarea v-model="rule.models[model.key]"></textarea>
									</template>
									<template v-else-if="model.type === 'switcher'">
										<div class="onoffswitch" @click.prevent="rule.models[model.key] = !rule.models[model.key]">
											<input type="checkbox" class="onoffswitch-checkbox" tabindex="0" v-model="rule.models[model.key]">
											<label class="onoffswitch-label"></label>
										</div>
									</template>
									<template v-else-if="model.type === 'select'">
										<select v-model="rule.models[model.key]">
											<template v-for="choice_label, choice_key in model.choices">
												<option :value="choice_key">{{ choice_label }}</option>
											</template>
										</select>
									</template>
									<template v-else>
										<input type="text" v-model="rule.models[model.key]">
									</template>
								</div>
							</template>
						</template>
						<div class="ts-form-group x-col-12 override-settings">
							<a href="#" class="ts-button ts-outline" @click.prevent="editRules(rule)">
								Set rules
							</a>
							<a href="#" class="ts-button ts-outline icon-only" @click.prevent="deleteRule(index)">
								<i class="lar la-trash-alt"></i>
							</a>
							<div class="vx-visibility-rules" v-html="displayRules(rule)"></div>
						</div>
					</div>
				</div>
			</template>
		</draggable>

		<div class="x-col-12">
			<a href="#" class="ts-button ts-outline" @click.prevent="addOverrideGroup">
				<i class="las la-plus icon-sm"></i>
				Add override group
			</a>
		</div>
	</template>
</script>
