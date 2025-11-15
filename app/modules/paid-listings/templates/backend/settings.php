<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-settings-paid-listings" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<div class="sticky-top">
		<div class="vx-head x-container">
			<h2>Settings</h2>
			<div>
				<a href="#" @click.prevent="save" class="ts-button ts-save-settings btn-shadow">
					<?php \Voxel\svg( 'floppy-disk.svg' ) ?>
					Save changes
				</a>
			</div>
		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">
		<div class="x-row">
			<div class="x-col-3">
				<ul class="inner-tabs vertical-tabs">
					<li :class="{'current-item': tab === 'general'}">
						<a href="#" @click.prevent="setTab('general')">Templates</a>
					</li>
					<li :class="{'current-item': tab === 'claims'}">
						<a href="#" @click.prevent="setTab('claims')">Claim listing</a>
					</li>
					<li :class="{'current-item': tab === 'promotions'}">
						<a href="#" @click.prevent="setTab('promotions')">Promoted listings</a>
					</li>
				</ul>
			</div>

			<div v-if="tab === 'claims'" class="x-col-9">
				<div class="ts-group">
					<div class="x-row">
						<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
							'v-model' => 'settings.claims.enabled',
							'label' => 'Enable claim listing functionality',
							'classes' => 'x-col-12',
						] ) ?>

						<template v-if="settings.claims.enabled">
							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'v-model' => 'settings.claims.proof_of_ownership',
								'label' => 'Proof of ownership',
								'classes' => 'x-col-12',
								'choices' => [
									'required' => 'Required',
									'optional' => 'Optional',
									'disabled' => 'Disabled',
								],
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'v-model' => 'settings.claims.approval',
								'label' => 'Order approval',
								'classes' => 'x-col-12',
								'choices' => [
									'automatic' => 'Automatic: Claim is approved immediately',
									'manual' => 'Manual: Claim is approved manually by the admin',
								],
							] ) ?>
						</template>
					</div>
				</div>
			</div>
			<div v-else-if="tab === 'promotions'" class="x-col-9">
				<?php require locate_template('app/modules/paid-listings/templates/backend/settings-promotions.php') ?>
			</div>
			<div v-else class="x-col-9">
				<div class="x-template mb20">
					<div class="xt-info">
						<h3>Pricing plans</h3>
					</div>
					<div class="xt-actions">
						<template v-if="settings.templates.pricing">
							<a :href="'<?= home_url( '/?p=' ) ?>'+settings.templates.pricing" target="_blank" class="ts-button ts-outline icon-only">
								<i class="las la-eye"></i>
							</a>
							<a href="#" @click.prevent="editTemplate.active = true" class="ts-button ts-outline icon-only">
								<i class="las la-ellipsis-h"></i>
							</a>
							<a href="#" @click.prevent="deletePricingTemplate" class="ts-button ts-outline icon-only">
								<i class="las la-trash"></i>
							</a>
							<a :href="'<?= admin_url( 'post.php?action=elementor&post=' ) ?>'+settings.templates.pricing" target="_blank" class="ts-button ts-outline">Edit template</a>
						</template>
						<template v-else>
							<a href="#" @click.prevent="createPricingTemplate" class="ts-button ts-outline">Create</a>
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
			</div>
		</div>
	</div>
</div>
