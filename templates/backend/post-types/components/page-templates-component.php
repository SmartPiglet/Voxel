<?php
/**
 * Page templates - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-templates-template">
	<div class="ts-tab-content">
		<div class="x-row">
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'base-templates'">
				<h1>Base templates</h1>
				<p>Base templates for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'preview-cards'">
				<h1>Preview cards</h1>
				<p>Create additional preview cards for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'single-post'">
				<h1>Single post</h1>
				<p>Override the base single post template under certain conditions</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'template-tabs'">
				<h1>Other</h1>
				<p>Other templates related to this post type</p>
			</div>

		</div>
		<div class="x-row">
			<div class="x-col-12">
				<ul class="inner-tabs">
					<li :class="{'current-item': $root.subtab === 'base-templates'}">
						<a href="#" @click.prevent="$root.setTab('templates', 'base-templates')">Base templates</a>
					</li>
					<li :class="{'current-item': $root.subtab === 'single-post'}">
						<a href="#" @click.prevent="$root.setTab('templates', 'single-post')">Single post</a>
					</li>
					<li :class="{'current-item': $root.subtab === 'preview-cards'}">
						<a href="#" @click.prevent="$root.setTab('templates', 'preview-cards')">Preview cards</a>
					</li>

					<li :class="{'current-item': $root.subtab === 'template-tabs'}">
						<a href="#" @click.prevent="$root.setTab('templates', 'template-tabs')">Other</a>
					</li>
				</ul>
			</div>
			<div class="x-col-12 x-templates" v-if="$root.subtab === 'base-templates'">
				<div class="x-template">
					<div class="xt-info">
						<h3>Single post</h3>
					</div>
					<div class="xt-actions">
						<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink($root.config.templates.single)" target="_blank"><i class="las la-eye"></i></a>
						<a class="ts-button ts-outline icon-only" @click.prevent="$root.activePopup = 'base-template-single:settings'"><i class="las la-ellipsis-h"></i></a>
						<a :href="editWithElementor($root.config.templates.single)" target="_blank" class="ts-button ts-outline">Edit template</a>
					</div>
				</div>
				<div class="x-template">
					<div class="xt-info">
						<h3>Preview card</h3>
					</div>
					<div class="xt-actions">
						<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink($root.config.templates.card)" target="_blank"><i class="las la-eye"></i></a>
						<a class="ts-button ts-outline icon-only" @click.prevent="$root.activePopup = 'base-template-card:settings'"><i class="las la-ellipsis-h"></i></a>
						<a :href="editWithElementor($root.config.templates.card)" target="_blank" class="ts-button ts-outline">Edit template</a>
					</div>
				</div>
				<div class="x-template">
					<div class="xt-info">
						<h3>Archive</h3>
					</div>
					<div class="xt-actions">
						<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink($root.config.templates.archive)" target="_blank"><i class="las la-eye"></i></a>
						<a class="ts-button ts-outline icon-only" @click.prevent="$root.activePopup = 'base-template-archive:settings'"><i class="las la-ellipsis-h"></i></a>
						<a :href="editWithElementor($root.config.templates.archive)" target="_blank" class="ts-button ts-outline">Edit template</a>
					</div>
				</div>
				<div class="x-template" v-if="$root.config.templates.form">
					<div class="xt-info">
						<h3>Create Post</h3>
					</div>
					<div class="xt-actions">
						<a class="ts-button ts-outline icon-only" @click.prevent="delete_base_template('form')" target="_blank"><i class="las la-trash"></i></a>
						<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink($root.config.templates.form)" target="_blank"><i class="las la-eye"></i></a>
						<a class="ts-button ts-outline icon-only" @click.prevent="$root.activePopup = 'base-template-form:settings'"><i class="las la-ellipsis-h"></i></a>
						<a :href="editWithElementor($root.config.templates.form)" target="_blank" class="ts-button ts-outline">Edit template</a>
					</div>
				</div>
				<div class="x-template" v-if="!$root.config.templates.form">
					<div class="xt-info">
						<h3>Create Post</h3>
					</div>
					<div class="xt-actions">
						<a class="ts-button ts-outline" @click.prevent="create_base_template('form')">Create</a>
					</div>
				</div>
			</div>

			<template v-for="template_id, template_key in $root.config.templates">
				<edit-base-template
					v-if="$root.activePopup === 'base-template-'+template_key+':settings'"
					:template="template_key"
				></edit-base-template>
			</template>

			<template v-if="$root.subtab === 'preview-cards'">
				<draggable
					v-model="$root.config.custom_templates.card"
					group="card_templates"
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
								<a class="ts-button ts-outline icon-only" @click.prevent="template.editSettings = true; template.group = 'card'"><i class="las la-ellipsis-h"></i></a>
								<a href="#" @click.prevent="delete_custom_template(template, 'card')" target="_blank" class="ts-button ts-outline icon-only"><i class="las la-trash"></i></a>
								<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink(template.id)" target="_blank"><i class="las la-eye"></i></a>
								<a :href="editWithElementor(template.id)" target="_blank" class="ts-button ts-outline">Edit template</a>
							</div>
							<edit-custom-template v-if="template.editSettings" :template="template"></edit-custom-template>
						</div>
					</template>
				</draggable>
				<div class="x-col-12 x-templates">
					<div class="">
						<a href="#" class="ts-button ts-outline full-width" @click.prevent="createCustomTemplate('card')">
							<i class="las la-plus icon-sm"></i>
							Create template
						</a>
						<create-custom-template v-if="$root.activePopup === 'create-custom:card'" group="card"></create-custom-template>
					</div>
				</div>
			</template>

			<template v-if="$root.subtab === 'template-tabs'">
				<draggable
					v-model="$root.config.custom_templates.single"
					group="single_templates"
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
								<a class="ts-button ts-outline icon-only" @click.prevent="template.editSettings = true; template.group = 'single'"><i class="las la-ellipsis-h"></i></a>
								<a href="#" @click.prevent="delete_custom_template(template, 'single')" target="_blank" class="ts-button ts-outline icon-only"><i class="las la-trash"></i></a>
								<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink(template.id)" target="_blank"><i class="las la-eye"></i></a>
								<a :href="editWithElementor(template.id)" target="_blank" class="ts-button ts-outline">Edit template</a>
							</div>
							<edit-custom-template v-if="template.editSettings" :template="template"></edit-custom-template>
						</div>
					</template>
				</draggable>
				<div class="x-col-12 x-templates">
					<div class="">
						<a href="#" class="ts-button ts-outline full-width" @click.prevent="createCustomTemplate('single')">
							<i class="las la-plus icon-sm"></i>
							Create template
						</a>
						<create-custom-template v-if="$root.activePopup === 'create-custom:single'" group="single"></create-custom-template>
					</div>
				</div>
			</template>

			<template v-if="$root.subtab === 'single-post'">
				<draggable
					v-model="$root.config.custom_templates.single_post"
					group="single_post_templates"
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
								<a class="ts-button ts-outline icon-only x-condition" href="#" @click.prevent="template.editRules = true; template.group = 'single_post'"><i class="las la-code-branch "></i></a>
								<a class="ts-button ts-outline icon-only" @click.prevent="template.editSettings = true; template.group = 'single_post'"><i class="las la-ellipsis-h"></i></a>
								<a href="#" class="ts-button ts-outline icon-only" @click.prevent="delete_custom_template(template, 'single_post')"><i class="las la-trash"></i></a>
								
								<a class="ts-button ts-outline icon-only" :href="elementorPreviewLink(template.id)" target="_blank"><i class="las la-eye"></i></a>
								<a target="_blank" class="ts-button ts-outline" :href="editWithElementor(template.id)">Edit template</a>
							</div>
							<edit-custom-template v-if="template.editSettings" :template="template"></edit-custom-template>
							<edit-custom-template-rules v-if="template.editRules" :template="template"></edit-custom-template-rules>
						</div>
					</template>
				</draggable>

				<div class="x-col-12 x-templates">
					<div class="">
						<a href="#" class="ts-button ts-outline full-width" @click.prevent="createCustomTemplate('single_post')">
							<i class="las la-plus icon-sm"></i>
							Create template
						</a>
						<create-custom-template v-if="$root.activePopup === 'create-custom:single_post'" group="single_post"></create-custom-template>
					</div>
				</div>
			</template>
		</div>
	</div>
</script>

<script type="text/html" id="ts-create-custom-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h3>Create template</h3>
						<div>
							<a class="ts-button ts-outline" href="#" @click.prevent="$root.activePopup = null">Discard</a>
							&nbsp;
							<a :class="{'ts-saving': updating}" href="#" @click.prevent="saveId" class="ts-button btn-shadow ts-save-settings">
								<i class="las la-check icon-sm"></i>Done
							</a>
						</div>
					</div>
					<div class="ts-field-props">
						<div class="field-modal-body">
							<div class="x-row">
								<div class="ts-form-group x-col-12">
									<label>Label</label>
									<input type="text" placeholder="Label" v-model="label">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>

<script type="text/html" id="ts-edit-custom-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="template.editSettings = false"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Template options</h2>
						<div>
							<a class="ts-button ts-outline" href="#" @click.prevent="template.editSettings = false">Discard</a>
								&nbsp;
							<a :class="{'ts-saving': updating}" href="#" @click.prevent="saveId" class="ts-button btn-shadow ts-save-settings">
								<i class="las la-check icon-sm"></i>Done
							</a>
						</div>
					</div>
					<div class="ts-field-props">
						<div class="field-modal-body">
							<div class="x-row">
								<div class="ts-form-group x-col-12">
									<label>Label</label>
									<input type="text" v-model="newLabel">
								</div>

								<div v-if="modifyId" class="ts-form-group x-col-12" :class="{'vx-disabled': updating}">
									<label>Enter new template id</label>
									<input type="number" v-model="newId">
									<br><br>
									<div class="x-row">
										<div class="x-col-12">
											<a href="#" @click.prevent="modifyId = false" class="ts-button ts-outline">Cancel</a>&nbsp;
											<a href="#" @click.prevent="saveId" class="ts-button ts-save-settings">Submit</a>
										</div>
									</div>
								</div>
								<div v-else class="ts-form-group x-col-12">
									<label>Template ID</label>
									<input type="number" disabled v-model="template.id">
									<br><br>
									<div class="x-row">
										<div class="x-col-12">
											<a href="#" @click.prevent="modifyId = true" class="ts-button ts-outline">Switch template</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>

<script type="text/html" id="ts-edit-base-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Template options</h2>
						<div>
							<a class="ts-button ts-outline" href="#" @click.prevent="$root.activePopup = null">Discard</a>
								&nbsp;
							<a href="#" @click.prevent="$root.activePopup = null" class="ts-button btn-shadow ts-save-settings">
								<i class="las la-check icon-sm"></i>Done
							</a>
						</div>
					</div>
					<div class="ts-field-props">
						<div class="field-modal-body">
							<div class="x-row">
								<div v-if="modifyId" class="ts-form-group x-col-12" :class="{'vx-disabled': updating}">
									<label>Enter new post template id</label>
									<input type="number" v-model="newId">
									<br><br>
									<div class="x-row">
										<div class="x-col-12">
											<a href="#" @click.prevent="modifyId = false" class="ts-button ts-outline">Cancel</a>
											&nbsp;
											<a href="#" @click.prevent="saveId" class="ts-button ts-save-settings">Submit</a>
										</div>
									</div>
								</div>

								<div v-else class="ts-form-group x-col-12">
									<label>Template ID</label>
									<input type="number" disabled v-model="$root.config.templates[ template ]">
									<br><br>
									<div class="x-row">
										<div class="x-col-12">
											<a href="#" @click.prevent="modifyId = true" class="ts-button ts-outline">Switch template</a>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
</script>
