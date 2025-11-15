<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-edit-tag">
	<div class="nvx-right-sidebar nvx-scrollable" >
		<div class="mod-head">
			<h3>{{ getDisplayLabel() }}</h3>
			<div class="nvx-placeholder nvx-tag-path">
				<template v-for="item, itemIndex in getDisplayPath()">
					<span v-if="itemIndex !== 0"> / </span>
					<span :title="typeof item.title === 'string' && item.title.length ? item.title : null">
						{{ item.textContent }}
					</span>
				</template>
			</div>
		</div>

		<div class="ts-form-group">
			<select v-model="newModifier">
				<option :value="null" disabled selected>Add a mod</option>
				<template v-for="modifierCategory in getAvailableModifiers()">
					<optgroup :label="modifierCategory.label">
						<template v-for="modifier in modifierCategory.items">
							<option :value="modifier">{{ modifier.label }}</option>
						</template>
					</optgroup>
				</template>
			</select>
		</div>

		<div class="nvx-divider"></div>

		<template v-if="modifiers.length">
			<draggable class="nvx-mod-list" v-model="modifiers" group="modifiers" handle=".nvx-mod-title" item-key="_id">
				<template #item="{element: modifier, index: index}">
					<div class="nvx-mod" :class="{'mod-open': modifier._open, 'mod-unknown': modifier.unknown}">
						<div class="nvx-mod-title" @click.prevent="toggleModifier(modifier)">
							<template v-if="modifier.key === 'then' || modifier.key === 'else'">
								&rarr;
							</template>
							{{ modifier.label }}
							<button type="button" class="ts-button ts-outline icon-only" @click.stop.prevent="modifiers.splice(index, 1)">
								<?= \Voxel\get_svg( 'trash-can.svg' ) ?>
							</button>
						</div>
						<div v-if="modifier._open" class="nvx-mod-content">
							<div class="x-row">
								<template v-if="modifier.description">
									<div class="ts-form-group x-col-12">
										<p>{{ modifier.description }}</p>
									</div>
								</template>
								<template v-if="modifier.unknown">
									<div class="ts-form-group x-col-12">
										<p>Unknown modifier.</p>
									</div>
								</template>
								<template v-else>
									<template v-for="argument in getValidArgs(modifier)">
										<template v-if="argument.type === 'select'">
											<div class="ts-form-group x-col-12">
												<span v-if="argument.description" class="vx-info-box" style="float: right;">
													<?php \Voxel\svg( 'info.svg' ) ?>
													<p>{{ argument.description }}</p>
												</span>
												<label>{{ argument.label }}</label>
												<select v-model="argument.value">
													<template v-for="choice_label, choice_value in argument.choices">
														<option :value="choice_value">{{ choice_label }}</option>
													</template>
												</select>
											</div>
										</template>
										<template v-else>
											<div class="ts-form-group x-col-12">
												<span v-if="argument.description" class="vx-info-box" style="float: right;">
													<?php \Voxel\svg( 'info.svg' ) ?>
													<p>{{ argument.description }}</p>
												</span>
												<label>{{ argument.label }}</label>
												<code-editor
													v-model="argument.value"
													layout="input"
													:placeholder="argument.placeholder || ''"
													tag-autocomplete-context="modifier"
												></code-editor>
											</div>
										</template>
									</template>

									<template v-if="hasInvalidArgs(modifier)">
										<div class="x-col-12">
											<details>
												<summary style="font-size: 12px; opacity: .3;">Unknown parameters detected</summary>
												<div class="ts-form-group">
													<template v-for="argument in getInvalidArgs(modifier)">
														<textarea style="width: 100%; display: block; margin-top: 7px;" type="text" v-model="argument.value"></textarea>
													</template>
												</div>
											</details>
										</div>
									</template>

									<template v-if="!modifier.arguments.length">
										<div class="ts-form-group x-col-12">
											<p>No additional settings.</p>
										</div>
									</template>
								</template>
							</div>
						</div>
					</div>
				</template>
			</draggable>
		</template>
		<template v-else>
			<div class="nvx-placeholder placeholder-center">No mods added</div>
		</template>
	</div>
</script>