<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-settings-share-menu">
	<div class="field-container" ref="fields-container">
		<div v-if="hasUnusedPresets" class="ts-group">
			<div class="ts-group-head">
				<h3>Available items</h3>
			</div>
			<div class="x-row">
				<div class="ts-form-group x-col-12">
					<div class="basic-ul">
						<template v-for="preset, key in presets">
							<a href="#" v-if="!isUsed(key)" @click.prevent="usePreset(key)" class="ts-button ts-outline">{{ preset.label }}</a>
						</template>
						<!-- <a href="#" @click.prevent="addHeading" class="ts-button ts-outline">UI: Heading</a> -->
					</div>
				</div>
			</div>
		</div>

		<draggable v-model="$root.config.share.networks" handle=".field-head" item-key="key" @start="dragStart" @end="dragEnd" class="ts-drag-animation">
			<template #item="{element: item}">
				<div :class="{open: item === active}" class="single-field wide">
					<div class="field-head" @click="toggleActive(item)">
						<p class="field-name">{{ item.label }}</p>
						<span class="field-type">{{ item.type }}</span>
						<div class="field-actions">
							<span class="field-action all-center">
								<a href="#" @click.prevent="deleteItem(item)">
									<i class="lar la-trash-alt icon-sm"></i>
								</a>
							</span>
						</div>
					</div>
					<div v-if="item === active" class="field-body">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<label>Label</label>
								<input type="text" v-model="item.label">
							</div>

							<div v-if="item.type !== 'ui-heading'" class="ts-form-group x-col-12">
								<label>Custom icon</label>
								<icon-picker v-model="item.icon" :allow-fonticons="false"></icon-picker>
							</div>
						</div>
					</div>
				</div>
			</template>
		</draggable>
	</div>
</script>
