<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="x-template mb20">
	<div class="xt-info">
		<h3>Pricing page for this role</h3>
	</div>
	<div class="xt-actions">
		<template v-if="config.settings.templates.pricing">
			<a :href="'<?= home_url( '/?p=' ) ?>'+config.settings.templates.pricing" target="_blank" class="ts-button ts-outline icon-only">
				<i class="las la-eye"></i>
			</a>
			<a href="#" @click.prevent="editTemplate.active = true" class="ts-button ts-outline icon-only">
				<i class="las la-ellipsis-h"></i>
			</a>
			<a href="#" @click.prevent="deletePricingTemplate" class="ts-button ts-outline icon-only">
				<i class="las la-trash"></i>
			</a>
			<a :href="'<?= admin_url( 'post.php?action=elementor&post=' ) ?>'+config.settings.templates.pricing" target="_blank" class="ts-button ts-outline">Edit template</a>
		</template>
		<template v-else>
			<a href="#" @click.prevent="createPricingTemplate" class="ts-button ts-outline">Create</a>
		</template>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Paid members</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.registration.plans_enabled',
			'label' => 'Enable membership plans for this role',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.registration.plans_enabled">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.registration.show_plans_on_signup',
				'label' => 'Show plans during registration',
				'classes' => 'x-col-12',
			] ) ?>
		</template>
	</div>
</div>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Role switch</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.settings.role_switch.enabled',
			'label' => 'Allow registered users to switch to this role',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="config.settings.role_switch.enabled">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.settings.role_switch.show_plans_on_switch',
				'label' => 'Show plans during switch process',
				'classes' => 'x-col-12',
			] ) ?>
		</template>
	</div>
</div>

<teleport to="body">
	<div v-if="editTemplate.active" class="ts-field-modal ts-theme-options">
		<div class="ts-modal-backdrop"></div>
		<div class="ts-modal-content min-scroll">
			<div class="x-container">
				<div class="field-modal-head">
					<h2>Template options</h2>
					<a href="#" @click.prevent="editTemplate.active = false" class="ts-button btn-shadow">
						<i class="las la-check "></i>Done
					</a>
				</div>
				<div class="ts-field-props">
					<div class="field-modal-body">
						<div class="x-row">
							<div v-if="editTemplate.modifyId" class="ts-form-group x-col-12" :class="{'vx-disabled': editTemplate.updating}">
								<label>Enter new page template id</label>
								<input type="number" v-model="editTemplate.newId">
								<p class="text-right">
									<a href="#" @click.prevent="editTemplate.modifyId = false" class="ts-button ts-transparent ts-btn-small">Cancel</a>
									<a href="#" @click.prevent="updatePricingTemplate" class="ts-button ts-outline ts-btn-small">Submit</a>
								</p>
							</div>
							<div v-else class="ts-form-group x-col-12">
								<label>Template ID</label>
								<input type="number" disabled v-model="config.settings.templates.pricing">
								<p class="text-right">
									<a href="#" @click.prevent="editTemplate.modifyId = true" class="ts-button ts-transparent ts-btn-small">Switch template</a>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</teleport>
