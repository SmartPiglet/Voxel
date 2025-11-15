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
			<h2>Taxonomy terms</h2>
		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">
		<div class="x-row">
			<div class="x-col-12">
				<ul class="inner-tabs inner-tabs">
					<li :class="{'current-item': tab === 'term_single'}">
						<a href="#" @click.prevent="setTab('term_single')">Single term</a>
					</li>
					<li :class="{'current-item': tab === 'term_card'}">
						<a href="#" @click.prevent="setTab('term_card')">Preview card</a>
					</li>
				</ul>
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
							<a
								href="#"
								v-if="template.visibility_rules"
								class="ts-button ts-outline icon-only x-condition"
								@click.prevent="template.editRules = true; template.group = tab"
							><i class="las la-code-branch "></i></a>
							<a href="#" @click.prevent="template._editing = true; template.group = tab" class="ts-button ts-outline icon-only">
								<i class="las la-ellipsis-h"></i>
							</a>

							<a
								href="#"
								class="ts-button ts-outline icon-only"
								@click.prevent="delete_custom_template(template, tab)"
							><i class="las la-trash"></i></a>
							

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
						<summary>Single term templates</summary>
						<p>The single term template is utilized when you load an individual taxonomy term e.g yoursite.com/category/general <br>
							You can create unlimited single term templates, and apply them to different taxonomies, or individual terms on those taxonomies. <br>
							Once the template is created, you can click the <i class="las la-code-branch"></i> condition icon to apply conditions e.g Is Single Term > Categories</p>
					</details>
					<details>
						<summary>Single term templates priority</summary>
						<p>If multiple single term templates meet their conditions, the first one on the list is utilized. You can use <i class="las la-grip-vertical"></i> drag and drop to reorder custom templates</p>
					</details>
					<details>
						<summary>Preview card templates</summary>
						<p>Preview card templates are utilized to loop terms via the Term feed widget. You can select from the templates you have created when configuring that widget.</p>
					</details>
				</details>
				
			</div>
		</div>
	</div>
</div>
