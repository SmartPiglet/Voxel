<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/backend/templates/_edit-base-template.php' );
require_once locate_template( 'templates/backend/templates/_edit-custom-template.php' );
require_once locate_template( 'templates/backend/templates/_create-custom-template.php' );
?>
<div id="vx-template-manager" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div class="sticky-top">
		<div class="vx-head x-container">
			<h2>Header & Footer</h2>
		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">
		<div class="x-row">
			<div class="x-col-12">
				<ul class="inner-tabs inner-tabs">
					<li :class="{'current-item': tab === 'header'}">
						<a href="#" @click.prevent="setTab('header')">Header</a>
					</li>
					<li :class="{'current-item': tab === 'footer'}">
						<a href="#" @click.prevent="setTab('footer')">Footer</a>
					</li>
				</ul>
			</div>
			<div class="x-col-12 x-templates" style="padding-bottom: 5px;">
				<template v-for="template in config.templates">
					<div v-if="tab === template.category" class="x-template base-template">
						<div class="xt-drag" style="pointer-events: none;"><i class="las la-lock"></i></div>
						<div class="xt-info">
							<h3>{{ template.label }}</h3>
						</div>
						<div class="xt-actions">

							<a href="#" @click.prevent="template.editSettings = true; template.key = 'templates.'+tab" class="ts-button ts-outline icon-only">
								<i class="las la-ellipsis-h"></i>
							</a>

							<a :href="previewLink(template.id)" target="_blank" class="ts-button ts-outline icon-only">
								<i class="las la-eye "></i>
							</a>
							<a :href="editLink(template.id)" target="_blank" class="ts-button ts-outline">Edit template</a>
						</div>

						<edit-base-template v-if="template.editSettings" :template="template"></edit-base-template>
					</div>
				</template>
			</div>
			
			<draggable
				v-model="config.custom_templates[tab]"
				:group="'templates:'+tab"
				handle=".xt-drag"
				item-key="id"
				@start="dragStart"
				@end="dragEnd"
				class="x-col-12 x-templates"
			>
				<template #item="{element: template, index: index}">

					<div class="x-template">
						<div class="xt-drag"><i class="las la-grip-vertical"></i></div>
						<div class="xt-info">
							<h3>{{ template.label }}</h3>
						</div>
						<div class="xt-actions">
							<a @click.prevent="template.editRules = true; template.group = tab " class="ts-button ts-outline icon-only x-condition" href="#"><i class="las la-code-branch "></i></a>
							<a href="#" @click.prevent="template._editing = true; template.group = tab" class="ts-button ts-outline icon-only">
								<i class="las la-ellipsis-h"></i>
							</a>
							<a href="#" @click.prevent="delete_custom_template(template, tab)" class="ts-button ts-outline icon-only"><i class="las la-trash"></i></a>
							<a :href="previewLink(template.id)" target="_blank" class="ts-button ts-outline icon-only">
								<i class="las la-eye "></i>
							</a>
							<a :href="editLink(template.id)" target="_blank" class="ts-button ts-outline">Edit template</a>
						</div>

						<edit-custom-template v-if="template._editing" :template="template"></edit-custom-template>
						<edit-custom-template-rules v-if="template.editRules" :template="template"></edit-custom-template-rules>
					</div>
				</template>
			</draggable>
			<div class="x-col-12 x-templates">
				<div style="margin: 10px 0;">
					
					<a href="#" class="ts-button ts-outline full-width" @click.prevent="insertTemplate(tab)">
						<i class="las la-plus icon-sm"></i>
						Create template
					</a>
				</div>
				<create-custom-template v-if="$root.config.editTemplate"></create-custom-template>
			</div>
			<div class="x-col-12">
				<div class="ts-spacer"></div>
			</div>
			<div class="x-col-12 x-faq-ui">
				<details>
					<summary>Frequently asked questions</summary>
					<details>
					<summary>Default templates</summary>
						<p>Default templates are utilized by default across the site unless overwritten by custom templates. Default templates can't be deleted <i class="las la-lock"></i></p>
					</details>
					<details>
						<summary>Custom templates</summary>
						<p>Custom templates can overwrite default templates under certain conditions which you set. 
							Once the template is created, you can click the <i class="las la-code-branch"></i> condition icon to apply conditions</p>
					</details>
					<details>
						<summary>Priority</summary>
						<p>If multiple custom templates meet their conditions, the first one on the list is utilized. You can use <i class="las la-grip-vertical"></i> drag and drop to reorder custom templates</p>
					</details>
				</details>
				
			</div>
		</div>
	</div>
</div>
