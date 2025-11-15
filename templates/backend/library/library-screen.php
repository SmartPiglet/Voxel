<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div v-if="config.packages.loading" class="ts-tab-heading text-center">
	<p>Loading library...</p>
</div>
<div v-else-if="config.packages.list === null" class="ts-tab-heading text-center">
	<p>Could not load library.</p>
	<div class="basic-ul" style="justify-content: center; margin-top: 10px;">
		<a href="#" @click.prevent="reloadLibrary" class="ts-button ts-faded">Try again</a>
	</div>
</div>
<div v-else-if="!config.packages.list.length" class="ts-tab-heading text-center">
	<p>No library items are available currently.</p>
</div>
<div v-else class="ts-library x-row">
	<template v-for="package in config.packages.list">
		<div class="x-col-4">
			<div class="library-item" :class="{'vx-disabled': state.processing_package && state.processing_package !== package.id}">
				<img :src="package.image ? package.image : config.packages.fallback_image">
				<div v-if="package.tagline" class="pck-desc"><i class="las la-info-circle icon-sm"></i>{{ package.tagline }}</div>
				<div class="lib-content">
					<h3>{{ package.title }}</h3>

					<!-- <p v-else>Import {{ package.title }}</p> -->
					<div class="lib-buttons">
						<a :href="package.preview_url" target="_blank" class="ts-button ts-outline" :class="{'vx-disabled': !package.preview_url}">Preview</a>
						<a href="#" @click.prevent="installPackage(package)" class="ts-button ts-save-settings" :class="{'vx-inert': state.processing_package && state.processing_package === package.id}">
							{{ state.processing_package && state.processing_package === package.id ? 'Downloading' : 'Install' }}
						</a>
					</div>
				</div>
			</div>
		</div>
	</template>
</div>
