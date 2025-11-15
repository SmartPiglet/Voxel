<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="role-field-modal-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="$root.active_field = null"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Field options</h2>
						<a href="#" @click.prevent="$root.active_field = null" class="ts-button btn-shadow"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<template v-if="field.source === 'auth'">
						<div class="field-modal-body">
							<div class="x-row">
								<div class="ts-form-group x-col-12">
									<label>Label</label>
									<input type="text" v-model="field.label">
								</div>

								<div class="ts-form-group x-col-12">
									<label>Placeholder</label>
									<input type="text" v-model="field.placeholder">
								</div>

								<div class="ts-form-group x-col-12">
									<label>Description</label>
									<textarea v-model="field.description"></textarea>
								</div>
							</div>
						</div>
					</template>
					<template v-else>
						<div class="ts-field-props">
							<div class="field-modal-tabs">
								<ul class="inner-tabs">
									<li :class="{'current-item': tab === 'general'}">
										<a href="#" @click.prevent="tab = 'general'">General</a>
									</li>
									<li :class="{'current-item': tab === 'conditions'}">
										<a href="#" @click.prevent="tab = 'conditions'">Conditional logic</a>
									</li>
								</ul>
							</div>

							<div class="field-modal-body">
								<div v-if="tab === 'general'" class="x-row">
									<div class="ts-form-group x-col-12">
										<label>Label</label>
										<input type="text" v-model="field.label" :placeholder="$root.fieldProp( field.key, 'label' )">
									</div>

									<div class="ts-form-group x-col-12" v-if="$root.fieldProp( field.key, 'placeholder' ) !== null">
										<label>Placeholder</label>
										<input type="text" v-model="field.placeholder" :placeholder="$root.fieldProp( field.key, 'placeholder' )">
									</div>
									<div class="ts-form-group x-col-12">
										<label>Description</label>
										<textarea v-model="field.description" :placeholder="$root.fieldProp( field.key, 'description' )"></textarea>
									</div>
								</div>
								<div v-else-if="tab === 'conditions'" class="x-row">
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
														<label>Source</label>
														<select v-model="condition.source">
															<template v-for="f in fields">
																<template v-if="getSubFields(f)">
																	<optgroup :label="f.label || $root.fieldProp( f.key, 'label' )">
																		<option v-for="subfield, subfield_key in getSubFields(f)" :value="f.key+'.'+subfield_key">
																			&mdash; {{ subfield.label }}
																		</option>
																	</optgroup>
																</template>
																<template v-else-if="hasConditions(f)">
																	<option :value="f.key">
																		{{ f.label || $root.fieldProp( f.key, 'label' ) }}
																	</option>
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

													<div class="ts-form-group delete-condition x-col-3 x-grow-0">
														<label>&nbsp;</label>
														<ul class="basic-ul">
															<a href="#" class="ts-button ts-faded icon-only" @click.prevent="removeCondition( conditionKey, conditionGroup, conditionGroupKey )">
																<i class="lar la-trash-alt icon-sm"></i>
															</a>
														</ul>
													</div>
												</div>
											</div>
											<div class="x-row">
												<div class="ts-form-group x-col-12">
													<a href="#" @click.prevent="conditionGroup.push( { source: '', type: '' } )" class="add-condition ts-button ts-faded">
														<i class="las la-code-branch icon-sm"></i> Add condition
													</a>
												</div>
											</div>
										</div>
										<div class="x-row">
											<div class="x-col-12">
												<a href="#" @click.prevent="field.conditions.push([])"  class="ts-button ts-faded">
													<i class="las la-layer-group icon-sm"></i> Add rule group
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</template>
				</div>
			</div>
		</div>
	</teleport>
</script>
