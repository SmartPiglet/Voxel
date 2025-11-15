<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Nav menus</h3>
	</div>
	<template v-if="config.nav_menus.custom_locations.length">
		<div v-for="location, location_index in config.nav_menus.custom_locations" class="x-row x-nowrap x-end">

			<div class="ts-form-group x-col-auto x-grow">
				<label>Key</label>
				<input type="text" v-model="location.key" :disabled="!location.is_new">
			</div>
			<div class="ts-form-group x-col-auto x-grow">
				<label>Label</label>
				<input type="text" v-model="location.label">
			</div>

			<div class="ts-form-group x-col-auto">
				<label>&nbsp;</label>
				<ul class="basic-ul">
					<a href="#" @click.prevent="removeMenuLocation(location_index)" class="ts-button ts-outline icon-only">
						<i class="lar la-trash-alt icon-sm"></i>
					</a>
				</ul>
			</div>

		</div>
	</template>
	<template v-else>
		<div class="x-row">
			<div class="ts-form-group x-col-12">
				<p>No custom menu locations added yet.</p>
			</div>
		</div>
	</template>
	<div class="x-row">
		<div class="ts-form-group x-col-12">
			<a href="#" @click.prevent="config.nav_menus.custom_locations.push( { key: '', label: '', is_new: true } )" class="ts-button ts-outline">
				<i class="las la-plus icon-sm"></i> Add custom location
			</a>
		</div>
	</div>
</div>
