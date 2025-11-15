<?php
/**
 * Template for managing active taxonomies in wp-admin.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-taxonomies-manager" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div class="sticky-top">
		<div class="vx-head x-container">
			<h2>Taxonomies</h2>
			<a href="<?= esc_url( $add_taxonomy_url ) ?>" class="ts-button ts-save-settings btn-shadow"><i class="las la-plus icon-sm"></i>Create taxonomy</a>
		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">

		<div class="x-row">
			<div class="x-col-3">
				<ul class="inner-tabs vertical-tabs">
					<template v-for="post_type in config.post_types">
						<li :class="{'current-item': tab === post_type.slug}">
							<a href="#" @click.prevent="setTab(post_type.slug)">{{post_type.label}}</a>
						</li>
					</template>
					<li v-if="config.has_unassigned_taxonomies" :class="{'current-item': tab === '(unassigned)'}">
						<a href="#" @click.prevent="setTab('(unassigned)')" >Unassigned</a>
					</li>
				</ul>
			</div>
			<div class="x-col-9">
				<div class="vx-panels">

					<template v-for="taxonomy in config.taxonomies">

						<div class="vx-panel" v-if="includesPostTypes( taxonomy.post_types, tab )">
							<div class="panel-info">
								<h3>{{taxonomy.label}}</h3>
								<ul>
									<li>{{taxonomy.slug}}</li>
								</ul>
							</div>
							<div class="panel-buttons">
								<a :href="taxonomy.reorder_terms" class="ts-button ts-outline">Reorder terms</a>
								<a :href="taxonomy.edit_taxonomy" class="ts-button ts-outline">Edit taxonomy</a>
							</div>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</div>
