<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/backend/templates/_edit-base-template.php' );

?>
<div id="vx-template-manager" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div class="sticky-top">
		<div class="vx-head x-container">
			<h2>General templates</h2>
		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">
		<div class="x-row">
			<div class="x-col-12">
				<ul class="inner-tabs inner-tabs">
					<li :class="{'current-item': tab === 'membership'}">
						<a href="#" @click.prevent="setTab('membership')">Membership</a>
					</li>
					<li :class="{'current-item': tab === 'orders'}">
						<a href="#" @click.prevent="setTab('orders')">Orders</a>
					</li>
					<li :class="{'current-item': tab === 'social'}">
						<a href="#" @click.prevent="setTab('social')">Social</a>
					</li>
					<li :class="{'current-item': tab === 'general'}">
						<a href="#" @click.prevent="setTab('general')">Other</a>
					</li>
					<li :class="{'current-item': tab === 'style_kits'}">
						<a href="#" @click.prevent="setTab('style_kits')">Style kits</a>
					</li>
				</ul>
			</div>
			<div class="x-col-12 x-templates">
				<template v-for="template in config.templates">
					<div v-if="tab === template.category" class="x-template">
						<template v-if="template.id">
							<div class="xt-info">
								<h3>{{ template.label }}</h3>
							</div>
							<div class="xt-actions">
								<a :href="previewLink(template.id)" target="_blank" class="ts-button ts-outline icon-only">
									<i class="las la-eye "></i>
								</a>
								<a href="#" @click.prevent="template.editSettings = true" class="ts-button ts-outline icon-only">
									<i class="las la-ellipsis-h "></i>
								</a>
								<a class="ts-button ts-outline icon-only" @click.prevent="delete_base_template(template)"><i class="las la-trash"></i></a>
								<a :href="editLink(template.id)" target="_blank" class="ts-button ts-outline">Edit template</a>
							</div>
						</template>
						<template v-else class="x-template">
							<div class="xt-info">
								<h3>{{ template.label }}</h3>
							</div>
							<div class="xt-actions">
								<a class="ts-button ts-outline" @click.prevent="create_base_template(template)">Create</a>
							</div>
						</template>

						<edit-base-template v-if="template.editSettings" :template="template"></edit-base-template>
					</div>
				</template>
			</div>
		</div>
	</div>
</div>
