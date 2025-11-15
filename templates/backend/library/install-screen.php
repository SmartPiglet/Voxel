<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="x-row h-center">

	<div class="x-col-7 ts-content-head">
		<h1>Import options</h1>
		<p>All options provided by the selected package are listed below</p>
	</div>
	<template v-if="Object.keys(install.config.post_types).length">
		<div class="ts-form-group x-col-7 ts-tab-subheading">
			<h3>Import post types</h3>
		</div>

		<div class="ts-checkbox-container x-col-7">
			<template v-for="post_type in install.config.post_types">
				<div class="container-checkbox" @click.prevent="post_type.enabled = !post_type.enabled">
					{{ post_type.original_label }}
					<input type="checkbox" :checked="post_type.enabled">
					<span class="checkmark"></span>
				</div>
				<div v-if="post_type.enabled" class="x-row" style="padding-left: 30px; margin-bottom: 30px;">
					<div class="ts-form-group x-col-12">
						<label>Import to</label>
						<select v-model="post_type.import_to" @change="post_type.import_to === 'new' ? ( post_type.key = post_type.original_key ) : ''">
							<option value="existing">Existing post type</option>
							<option value="new">New post type</option>
						</select>
					</div>
					<template v-if="post_type.import_to === 'existing'">
						<div class="ts-form-group x-col-12">
							<label>Post type</label>
							<select v-model="post_type.key">
								<option v-for="p in config.post_types" :value="p.key">{{ p.label }} ({{ p.key }})</option>
							</select>
							<br><br>
							<p>Existing templates and configuration for this post type will be overridden</p>
						</div>
					</template>
					<template v-if="post_type.import_to === 'new'">
						<div class="ts-form-group x-col-12">
							<label>Singular name</label>
							<input type="text" v-model="post_type.singular">
						</div>

						<div class="ts-form-group x-col-12">
							<label>Plural name</label>
							<input type="text" v-model="post_type.plural">
						</div>

						<div class="ts-form-group x-col-12">
							<label>Post type key</label>
							<input type="text" v-model="post_type.key" :placeholder="post_type.original_key" maxlength="20">
							<p>Must not exceed 20 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.</p>
						</div>

						<div class="ts-form-group x-col-12 switch-slider">
							<label>Custom permalink base</label>
							<div class="onoffswitch">
								<input type="checkbox" class="onoffswitch-checkbox" tabindex="0" v-model="post_type.permalinks.custom">
								<label class="onoffswitch-label" @click.prevent="post_type.permalinks.custom = !post_type.permalinks.custom"></label>
							</div>
						</div>

						<div v-if="post_type.permalinks.custom" class="ts-form-group x-col-12">
							<label>Permalink base</label>
						 	<input type="text" v-model="post_type.permalinks.slug">
						 	<br><br>
							<p><?= home_url('/') ?>{{ post_type.permalinks.slug }}/sample-post</p>
						</div>
					</template>
				</div>
			</template>
		</div>
	</template>
	<template v-if="Object.keys(install.config.taxonomies).length">
		<div class="ts-form-group x-col-7 ts-tab-subheading">
			<h3>Import taxonomies</h3>
		</div>
		<div class="ts-checkbox-container x-col-7">
			<template v-for="taxonomy in install.config.taxonomies">
				<div class="container-checkbox" @click.prevent="taxonomy.enabled = !taxonomy.enabled">
					{{ taxonomy.original_label }}
					<input type="checkbox" :checked="taxonomy.enabled">
					<span class="checkmark"></span>
				</div>
				<div v-if="taxonomy.enabled" class="x-row" style="padding-left: 30px; margin-bottom: 30px;">
					<div class="ts-form-group x-col-12">
						<label>Import to</label>
						<select v-model="taxonomy.import_to" @change="taxonomy.import_to === 'new' ? ( taxonomy.key = taxonomy.original_key ) : ''">
							<option value="existing">Existing taxonomy</option>
							<option value="new">New taxonomy</option>
						</select>
					</div>
					<template v-if="taxonomy.import_to === 'existing'">
						<div class="ts-form-group x-col-12">
							<label>Taxonomy</label>
							<select v-model="taxonomy.key">
								<option v-for="t in config.taxonomies" :value="t.key">{{ t.label }} ({{ t.key }})</option>
							</select>
							<br><br>
							<p>Existing templates and configuration for this taxonomy will be overridden</p>
						</div>
					</template>
					<template v-if="taxonomy.import_to === 'new'">
						<div class="ts-form-group x-col-12">
							<label>Singular name</label>
							<input type="text" v-model="taxonomy.singular">
						</div>

						<div class="ts-form-group x-col-12">
							<label>Plural name</label>
							<input type="text" v-model="taxonomy.plural">
						</div>

						<div class="ts-form-group x-col-12">
							<label>Taxonomy key</label>
							<input type="text" v-model="taxonomy.key" :placeholder="taxonomy.original_key">
						</div>
					</template>
					<div v-if="taxonomy._with_terms" class="ts-form-group x-col-12 switch-slider">
						<label>Import taxonomy terms</label>
						<div class="onoffswitch">
							<input type="checkbox" class="onoffswitch-checkbox" tabindex="0" v-model="taxonomy.import_terms">
							<label class="onoffswitch-label" @click.prevent="taxonomy.import_terms = !taxonomy.import_terms"></label>
						</div>
					</div>
				</div>
			</template>
		</div>
	</template>
	<template v-if="Object.keys(install.config.product_types).length">
		<div class="ts-form-group x-col-7 ts-tab-subheading">
			<h3>Import product types</h3>
		</div>

		<div class="ts-checkbox-container x-col-7">
			<template v-for="product_type in install.config.product_types">
				<div class="container-checkbox" @click.prevent="product_type.enabled = !product_type.enabled">
					{{ product_type.original_label }}
					<input type="checkbox" :checked="product_type.enabled">
					<span class="checkmark"></span>
				</div>
				<div v-if="product_type.enabled" class="x-row" style="padding-left: 30px; margin-bottom: 30px;">
					<div class="ts-form-group x-col-12">
						<label>Import to</label>
						<select v-model="product_type.import_to" @change="product_type.import_to === 'new' ? ( product_type.key = product_type.original_key ) : ''">
							<option value="existing">Existing product type</option>
							<option value="new">New product type</option>
						</select>
					</div>
					<template v-if="product_type.import_to === 'existing'">
						<div class="ts-form-group x-col-12">
							<label>Product type</label>
							<select v-model="product_type.key">
								<option v-for="p in config.product_types" :value="p.key">{{ p.label }} ({{ p.key }})</option>
							</select>
							<br><br>
							<p>Existing configuration for this product type will be overridden</p>
						</div>
					</template>
					<template v-if="product_type.import_to === 'new'">
						<div class="ts-form-group x-col-12">
							<label>Label</label>
							<input type="text" v-model="product_type.label">
						</div>
						<div class="ts-form-group x-col-12">
							<label>Product type key</label>
							<input type="text" v-model="product_type.key" :placeholder="product_type.original_key">
						</div>
					</template>
				</div>
			</template>
		</div>
	</template>
	<template v-if="install.config.elementor_custom_colors.length">
		<div class="ts-form-group x-col-7 ts-tab-subheading">
			<h3>Import custom colors</h3>
		</div>
		<div class="x-col-7 ts-form-group">

				<template v-for="color in install.config.elementor_custom_colors">

						<div class="container-checkbox" @click.prevent="color.enabled = !color.enabled">
							{{ color.original_title }}
							<input type="checkbox" :checked="color.enabled">
							<span class="checkmark"></span>
						</div>
						<div v-if="color.enabled" class="x-row" style="padding-left: 30px;">
							<div class="ts-form-group x-col-12">
								<label>Label</label>
								<input type="text" v-model="color.details.title">
							</div>
							<div class="ts-form-group x-col-12">
								<label>Color</label>
								<color-picker v-model="color.details.color"></color-picker>
							</div>
							<div class="x-col-12 mb0 mt0"></div>
						</div>


				</template>

		</div>
	</template>
	<template v-if="install.config.elementor_system_colors.length">
		<div class="ts-form-group x-col-7 ts-tab-subheading">
			<h3>Import default colors</h3>

		</div>
		<div class="x-col-7 ts-form-group">
			<template v-for="color in install.config.elementor_system_colors">

					<div class="container-checkbox" @click.prevent="color.enabled = !color.enabled">
						{{ color.original_title }}
						<input type="checkbox" :checked="color.enabled">
						<span class="checkmark"></span>
					</div>
					<div v-if="color.enabled" class="x-row" style="padding-left: 30px;">
						<div class="ts-form-group x-col-12">
							<label>Label</label>
							<input type="text" v-model="color.details.title">
						</div>
						<div class="ts-form-group x-col-12">
							<label>Color</label>
							<color-picker v-model="color.details.color"></color-picker>
						</div>
						<div class="x-col-12 mb0 mt0"></div>
					</div>


			</template>
		</div>
	</template>
	<div class="x-col-7"></div>
	<div class="x-col-7">
		<a href="#" @click.prevent="runImport" class="ts-button ts-save-settings full-width" :class="{'vx-disabled':state.processing_install}">
			<i class="las la-cloud-upload-alt icon-sm"></i> Install package
		</a>
	</div>
</div>
