<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-data-inputs-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Data inputs</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12">
				<div class="add-field">
					<template v-for="dataInput in $root.options.data_inputs">
						<div @click.prevent="createDataInput(dataInput)" class="ts-button ts-outline">
							<p class="field-name">{{ dataInput.props.label }}</p>
						</div>
					</template>
				</div>
			</div>
			<div class="x-col-12 field-container ts-drag-animation" ref="list">
				<template v-if="config.items.length">
					<draggable
						v-model="config.items"
						group="items"
						handle=".field-head"
						item-key="key"
						@start="$refs.list.classList.add('drag-active')"
						@end="$refs.list.classList.remove('drag-active')"
					>
						<template #item="{element: dataInput}">
							<div class="single-field wide">
								<div class="field-head" @click="toggleActive(dataInput)">
									<p class="field-name">{{ dataInput.label }}</p>
									<span class="field-type">{{ dataInput.type }}</span>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.stop.prevent="deleteDataInput(dataInput)">
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
						<p>You have not added any fields yet.</p>
					</div>
				</template>
			</div>
			<!-- <div class="x-col-12">
				<pre debug>{{ config }}</pre>
				<pre debug>{{ $root.options.data_inputs }}</pre>
			</div> -->
		</div>
	</div>
	<data-input-modal v-if="active" :data-input="active"></data-input-modal>
</script>

<script type="text/html" id="pte-data-input-modal">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Data input options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<div class="field-modal-body">
						<div class="x-row">
							<?= $data_input_options_markup ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>

<script type="text/html" id="pte-data-input-select-choices">
	<div class="field-container">
		<draggable v-model="dataInput.choices" group="field-choices" handle=".field-head" item-key="key">
			<template #item="{element: choice, index: index}">
				<div class="single-field wide" :class="{ 'open': active === choice }">
					<div class="field-head" @click="active = ( active === choice ) ? null : choice">
						<p class="field-name">{{ choice.label || '(empty)' }}</p>
						<span class="field-type">{{ choice.value || '' }}</span>
						<div class="field-actions">
							<span class="field-action all-center">
								<a href="#" @click.stop.prevent="dataInput.choices.splice(index, 1)">
									<i class="lar la-trash-alt icon-sm"></i>
								</a>
							</span>
						</div>
					</div>
					<div v-if="active === choice" class="field-body">
						<div class="x-row wrap-row">
							<div class="ts-form-group x-col-6">
								<label>Label</label>
								<input type="text" v-model="choice.label" ref="choiceLabel">
							</div>
							<div class="ts-form-group x-col-6">
								<label>Value</label>
								<input type="text" v-model="choice.value">
							</div>
						</div>
					</div>
				</div>
			</template>
		</draggable>
		<a href="#" @click.prevent="add" class="ts-button ts-outline">Add choice</a>
	</div>
</script>
