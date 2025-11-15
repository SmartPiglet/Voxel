<?php
/**
 * Filter conditions component.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-filter-conditions-template">
	<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
		'v-model' => 'filter.conditions_enabled',
		'label' => 'Enable conditional logic for this filter?',
		'classes' => 'x-col-12',
	] ) ?>

	<div v-if="filter.conditions_enabled" class="field-conditions x-col-12">
		<div class="ts-form-group mb20">
			<select v-model="filter.conditions_behavior">
				<option value="show">Show this filter if</option>
				<option value="hide">Hide this filter if</option>
			</select>
		</div>

		<div v-for="conditionGroup, conditionGroupKey in filter.conditions" class="condition-group">
			<div v-for="condition, conditionKey in conditionGroup" class="single-condition">
				<div class="x-row">
					<div class="ts-form-group x-col-3 x-grow">
						<span class="vx-info-box wide" style="float: right;">
							<?php \Voxel\svg( 'info.svg' ) ?>
							<p>Circular dependencies are not supported. For example, if Filter A depends on Filter B, then Filter B cannot depend on Filter A.</p>
						</span>
						<label>Source</label>
						<select v-model="condition.source">
							<template v-for="f in filters">
								<template v-if="f.key !== filter.key">
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

					<?= $filter_condition_options_markup ?>

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
				<a href="#" @click.prevent="filter.conditions.push([])"  class="ts-button ts-outline ">
					<i class="las la-layer-group icon-sm"></i> Add rule group
				</a>
			</div>
		</div>
	</div>
</script>
