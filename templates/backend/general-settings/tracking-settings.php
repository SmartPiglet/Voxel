<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Post visits</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
			'v-model' => 'config.stats.enabled_post_types',
			'label' => 'Track visit stats for post types',
			'classes' => 'x-col-12',
			'columns' => 'three',
			'choices' => array_map( function( $post_type ) {
				return $post_type->get_label();
			}, \Voxel\Post_Type::get_voxel_types() ),
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.stats.db_ttl',
			'label' => 'Delete data older than',
			'classes' => 'x-col-8',
		] ) ?>

		<div class="ts-form-group x-col-4 vx-inert">
			<label>&nbsp;</label>
			<input type="text" value="Days">
		</div>

		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.stats.cache_ttl.value',
			'label' => 'Refresh stats cache every',
			'classes' => 'x-col-8',
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.stats.cache_ttl.unit',
			'label' => 'Refresh stats cache every',
			'classes' => 'x-col-4',
			'choices' => [
				'minutes' => 'Minutes',
				'hours' => 'Hours',
				'days' => 'Days',
			],
		] ) ?>

		<div class="ts-form-group x-col-12">
			<label>Actions</label>
			<a href="#" @click.prevent="purgeStatsCache($event)" class="ts-button ts-outline">Purge cache</a>
		</div>
	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>IP Geolocation services</h3>
	</div>
	<div class="x-row">
		<div class="ts-form-group x-col-12">
			<p>
				IP geolocation is required to retrieve the visitor's country for statistic tracking.
				If no external service is configured, we'll attempt to retrieve the country code from
				server side headers (this will only work if your hosting provider sets the necessary headers.)
			</p>
		</div>

		<draggable v-model="config.ipgeo.providers" group="ipgeo_providers" handle=".field-head" item-key="key" @start="" @end="" class="x-col-12 ts-drag-animation">
			<template #item="{element: provider, index: index}">
				<div class="single-field wide" :class="{open: ipgeo.activeProvider === provider.key}">
					<div class="field-head" @click.prevent="ipgeo.activeProvider = ( ipgeo.activeProvider === provider.key ) ? null : provider.key">
						<p class="field-name">{{ getIpGeoProvider( provider.key )?.label || '(unknown)' }}</p>
						<span class="field-type">{{ provider.key }}</span>
						<div class="field-actions" @click.prevent.stop="deleteIpGeoProvider( provider.key )">
							<a href="#" class="field-action all-center"><i class="lar la-trash-alt icon-sm"></i></a>
						</div>
					</div>
					<div v-if="ipgeo.activeProvider === provider.key" class="field-body">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<p>{{ getIpGeoProvider( provider.key )?.description }}</p>
							</div>
							<div v-if="getIpGeoProvider( provider.key )?.api_key_param" class="ts-form-group x-col-12">
								<label>API key</label>
								<input type="text" v-model="provider.api_key">
							</div>
						</div>
					</div>
				</div>
			</template>
		</draggable>
	</div>
	<template v-if="remainingIpGeoProviders.length">
		<div class="ts-spacer"></div>
		<div class="ts-group-head">
			<h3>Available providers</h3>
		</div>
		<div class="x-row x-col-12">
			<div class="add-field">
				<template v-for="provider in remainingIpGeoProviders">
					<a href="#" @click.prevent="addIpGeoProvider(provider)" class="ts-button ts-outline">{{ provider.label }}</a>
				</template>
			</div>
		</div>
	</template>

	<!-- <pre debug>{{ config.ipgeo }}</pre> -->
	<!-- <pre debug>{{ config.editor.ipgeo.providers }}</pre> -->
</div>
