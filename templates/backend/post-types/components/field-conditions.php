<?php
/**
 * Field conditions component.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-field-conditions-template">
	<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
		'v-model' => 'field[\'enable-conditions\']',
		'label' => 'Enable conditional logic for this field?',
		'classes' => 'x-col-12',
	] ) ?>

	<div v-if="field['enable-conditions']" class="field-conditions x-col-12">
		<div class="ts-form-group mb20">
			<select v-model="field.conditions_behavior">
				<option value="show">Show this field if</option>
				<option value="hide">Hide this field if</option>
			</select>
		</div>

		<div v-for="conditionGroup, conditionGroupKey in field.conditions" class="condition-group">

			<div v-for="condition, conditionKey in conditionGroup" class="single-condition">
				<div class="x-row">
					<div class="ts-form-group x-col-3 x-grow">
						<span class="vx-info-box wide" style="float: right;">
							<?php \Voxel\svg( 'info.svg' ) ?>
							<p>Circular dependencies are not supported. For example, if Field A depends on Field B, then Field B cannot depend on Field A.</p>
						</span>
						<label>Source</label>
						<select v-model="condition.source">
							<template v-for="f in fields">
								<template v-if="f.key !== field.key">
									<template v-if="getSubFields(f)">
										<optgroup :label="f.label">
											<option v-for="subfield, subfield_key in getSubFields(f)" :value="f.key+'.'+subfield_key">
												&mdash; {{ subfield.label }}
											</option>
										</optgroup>
									</template>
									<template v-else-if="hasConditions(f)">
										<option :value="f.key">
											{{ f.label }}
										</option>
									</template>
								</template>
							</template>
						</select>
					</div>

					<div class="ts-form-group x-col-3 x-grow">
						<label>Condition</label>
						<select v-model="condition.type" @change="setProps( condition )">
							<template v-for="group in getConditionGroups( condition )">
								<optgroup :label="group.label">
									<option
										v-for="conditionType in group.types"
										:value="conditionType.type"
									>{{ conditionType.label }}</option>
								</optgroup>
							</template>
						</select>
					</div>

					<?= $condition_options_markup ?>

					<div class="ts-form-group x-col-3 delete-condition x-grow-0">
						<label>&nbsp;</label>
						<ul class="basic-ul">
							<a href="#" class="ts-button ts-outline icon-only" @click.prevent="removeCondition( conditionKey, conditionGroup, conditionGroupKey )">
								<i class="lar la-trash-alt"></i>
							</a>
						</ul>
					</div>
				</div>
			</div>

			<div class="x-row">
				<div class="ts-form-group x-col-12">
					<a href="#" @click.prevent="conditionGroup.push( { source: '', type: '' } )" class="add-condition ts-button ts-outline ">
						<i class="las la-code-branch icon-sm"></i> Add condition
					</a>

				</div>

			</div>

		</div>
		<div class="x-row">
			<div class="x-col-12">
				<a href="#" @click.prevent="field.conditions.push([])"  class="ts-button ts-outline ">
					<i class="las la-layer-group icon-sm"></i> Add rule group
				</a>
			</div>
		</div>
	</div>
</script>
