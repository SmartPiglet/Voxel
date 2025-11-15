<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-mode-edit-visibility">
	<div class="nvx-editor nvx-editor-visibility">
		<div class="nvx-topbar">
			<div class="nvx-topbar__title nvx-flex nvx-v-center">
				<h2>Visibility rules</h2>
			</div>

			<div class="nvx-topbar__buttons nvx-flex nvx-v-center">
				<button @click.prevent="discard" type="button" class="ts-button ts-outline">Discard</button>
				<button @click.prevent="save" type="button" class="ts-button btn-shadow ts-save-settings"><?= \Voxel\get_svg( 'floppy-disk.svg' ) ?> Save</button>
			</div>
		</div>
		<div class="nvx-editor-body">
			<div class="nvx-scrollable nvx-visibility-rules">
				<div class="nvx-rules-container">
					<template v-for="ruleGroup, ruleGroupIndex in rules">
						<div class="nvx-rule-group">
							<div class="x-row">
								<div class="nvx-rule-group-head x-col-12">
									<h2>{{ ruleGroupIndex === 0 ? 'Rule group' : 'Or' }}</h2>
								</div>
								<template v-for="rule, ruleIndex in ruleGroup">
									<div class="nvx-rule x-col-12">
										<div class="x-row x-nowrap">
											<div class="ts-form-group x-col-2 x-grow">
												<label>{{ ruleIndex === 0 ? 'Condition' : 'And' }}</label>
												<select v-model="rule.type" @change="onRuleTypeChange(rule)">
													<option :value="null">Select condition</option>
													<template v-for="visibilityRule in allowedRules">
														<option :value="visibilityRule.type">{{ visibilityRule.label }}</option>
													</template>
												</select>
											</div>
											<template v-if="rule.type === 'dtag'">
												<div class="ts-form-group x-col-3 x-grow">
													<label>Dynamic Tag</label>
													<code-editor
														v-model="rule.tag"
														layout="input"
														placeholder="Press @ to pick tag"
													></code-editor>
												</div>
												<div class="ts-form-group x-col-2 x-grow">
													<label>Compare</label>
													<select v-model="rule.compare">
														<option :value="null">Select an option</option>
														<template v-for="modifier in $root.Dynamic_Data_Store.modifiers">
															<template v-if="modifier.type === 'control-structure' && !['then', 'else'].includes(modifier.key)">
																<option :value="modifier.key">{{ modifier.label }}</option>
															</template>
														</template>
													</select>
												</div>
												<template v-for="argData, argIndex in dtagGetCompareArguments( rule )">
													<template v-if="argData.type === 'select'">
														<div class="ts-form-group x-col-2 x-grow">
															<span v-if="argData.description" class="vx-info-box" style="float: right;">
																<?php \Voxel\svg( 'info.svg' ) ?>
																<p>{{ argData.description }}</p>
															</span>
															<label>{{ argData.label }}</label>
															<select v-model="rule.arguments[ argIndex ]">
																<template v-for="choice_label, choice_value in argData.choices">
																	<option :value="choice_value">{{ choice_label }}</option>
																</template>
															</select>
														</div>
													</template>
													<template v-else>
														<div class="ts-form-group x-col-3 x-grow">
															<span v-if="argData.description" class="vx-info-box" style="float: right;">
																<?php \Voxel\svg( 'info.svg' ) ?>
																<p>{{ argData.description }}</p>
															</span>
															<label>{{ argData.label }}</label>
																<code-editor
																	v-model="rule.arguments[ argIndex ]"
																	layout="input"
																	:placeholder="argData.placeholder || 'Enter value'"
																></code-editor>
															</div>
													</template>
												</template>
											</template>
											<template v-else>
												<template v-for="argumentData, argumentKey in getRuleArguments( rule )">
													<div v-if="argumentData.type !== 'hidden' && evaluateRuleConditions( rule, argumentData['v-if'] )" class="ts-form-group x-col-3 x-grow">
														<span v-if="argumentData.description" class="vx-info-box" style="float: right;">
															<?php \Voxel\svg( 'info.svg' ) ?>
															<p>{{ argumentData.description }}</p>
														</span>
														<label>{{ argumentData.label }}</label>
														<template v-if="argumentData.type === 'select'">
															<select v-model="rule[ argumentKey ]">
																<option :value="null">Select an option</option>
																<template v-for="choice_label, choice_value in argumentData.choices">
																	<option :value="choice_value">{{ choice_label }}</option>
																</template>
															</select>
														</template>
														<template v-else>
															<input :placeholder="argumentData.placeholder" type="text" v-model="rule[ argumentKey ]">
														</template>
													</div>
												</template>
											</template>
											<div class="x-col-2 x-grow-0 ts-form-group">
												<label>&nbsp;</label>
												<a href="#" @click.prevent="removeRule(ruleIndex, ruleGroup, ruleGroupIndex)" class="ts-button ts-outline icon-only"><?= \Voxel\get_svg( 'trash-can.svg' ) ?></a>
											</div>
										</div>
									</div>
								</template>

								<div class="x-col-12 h-center">

										<a href="#" @click.prevent="addRule( ruleGroup )" class="ts-button ts-transparent">
											<?= \Voxel\get_svg( 'plus.svg' ) ?>Add condition
										</a>

								</div>
							</div>
						</div>
					</template>

					<div v-if="canAddRuleGroup()" class="h-center">

						<a href="#" @click.prevent="addRuleGroup()" class="ts-button ts-transparent">
							<?= \Voxel\get_svg( 'cube.svg' ) ?> Add another rule group
						</a>

						<!-- <div class="x-col-12">
							<pre debug>{{ rules }}</pre>
						</div> -->
					</div>
				</div>
			</div>
		</div>
	</div>
</script>