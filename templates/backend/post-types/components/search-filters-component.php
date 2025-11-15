<?php
/**
 * Search filters - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-search-filters-template">
	<div class="x-row">
		<div class="used-fields x-col-6">
			<div class="sub-heading">
				<p>Used filters</p>
			</div>
			<div class="field-container ts-draggable-inserts" ref="fields-container">
				<draggable v-model="$root.config.search.filters" group="filters" handle=".field-head" item-key="key" @start="dragStart" @end="dragEnd" @add="onAdd">
					<template #item="{element: filter}">
						<div :class="{open: isActive(filter)}" class="single-field wide">
							<div class="field-head" @click="toggleActive(filter)">
								<p class="field-name">{{ filter.label }}</p>
								<span class="field-type">{{ filter.type }}</span>
								<div class="field-actions">
									<span class="field-action all-center" v-if="filter.conditions_enabled">
										<a href="#" title="Conditional logic is enabled for this filter" @click.stop.prevent="toggleActive(filter, 'conditions')">
											<i class="las la-code-branch icon-sm"></i>
										</a>
									</span>
									<span class="field-action all-center">
										<a href="#" @click.stop.prevent="deleteFilter(filter)">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
									</span>
								</div>
							</div>
						</div>
					</template>
				</draggable>
			</div>
		</div>
		<div class="x-col-1"></div>
		<div class="x-col-5">
			<div class="available-fields-container">
				<div class="sub-heading">
					<p>Available filters</p>
				</div>
				<draggable class="add-field" :list="Object.values(filter_types)" :group="{ name: 'filters', pull: 'clone', put: false }" :sort="false" item-key="key">
					<template #item="{element: filter_type}">
						<div>
							<div v-if="canAddFilter(filter_type)" class="">
								<div @click.prevent="addFilter(filter_type)" class="ts-button ts-outline c-move">
									{{ filter_type.label }}
								</div>
							</div>
						</div>
					</template>
				</draggable>
			</div>
		</div>
	</div>

	<filter-modal v-if="active" ref="filterModal" :filter="active"></filter-modal>
</script>

<script type="text/html" id="post-type-filter-modal-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Filter options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow ts-save-settings"><i class="las la-check icon-sm"></i>Save</a>
					</div>

					<div class="ts-field-props">
						<div class="field-modal-tabs">
							<ul class="inner-tabs">
								<li :class="{'current-item': filter._tab !== 'conditions'}">
									<a href="#" @click.prevent="filter._tab = null">General</a>
								</li>
								<li :class="{'current-item': filter._tab === 'conditions'}">
									<a href="#" @click.prevent="filter._tab = 'conditions'">Conditional logic</a>
								</li>
							</ul>
						</div>

						<div class="field-modal-body">
							<div v-if="filter._tab === 'conditions'" class="x-row">
								<filter-conditions :filter="filter"></filter-conditions>
							</div>
							<div v-else class="x-row">
								<?= $filter_options_markup ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>