<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="vx-listing-plan" id="vx-listing-plan" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div v-if="screen === 'assign'" class="update-listing-plan">
		<template v-if="assignPlan.plans.loading">
			<div class="plan-list">
				<span>Loading plans...</span>
			</div>
		</template>
		<template v-else-if="!assignPlan.plans.list.length">
			<div class="plan-list">
				<span>This author has no available plans.</span>
			</div>
			<a href="#" @click.prevent="screen = null">Go back</a>
		</template>
		<template v-else>
			<div>
				<label>Select plan</label>
				<div class="plan-list">
					<span style="color: #000; padding: 4px 10px; position: sticky; top: 0; background: #fff; border-bottom: 1px solid #ddd;">
						<b>ID</b>
						<b>Usage</b>
					</span>
					<a v-for="package in assignPlan.plans.list" href="#"
						@click.prevent="assignPlan.selected = ( assignPlan.selected === package.id ? null : package.id )"
						:class="{'ts-selected': package.id === assignPlan.selected}"
						>
						<span><b>#{{ package.id }}</b> {{ package.plan.label || '' }}</span>
						<span>{{ package.used }}/{{ package.total }} used</span>
					</a>
				</div>
			</div>
			<div>
				<label>
					<input type="checkbox" v-model="assignPlan.consume_new_plan_slot">
					Consume submission slot?
				</label>
			</div>
			<div style="display: flex; gap: 10px; align-items: center;">
				<a href="#" @click.prevent="submitAssignPlan" class="button button-primary"
					:class="{'vx-disabled': assignPlan.processing}">Assign</a>
				<a href="#" @click.prevent="screen = null">Cancel</a>
			</div>
		</template>
	</div>
	<div v-else-if="screen === 'switch'" class="update-listing-plan">
		<template v-if="switchPlan.plans.loading">
			<div class="plan-list">
				<span>Loading plans...</span>
			</div>
		</template>
		<template v-else-if="!switchPlan.plans.list.length">
			<div class="plan-list">
				<span>This author has no available plans.</span>
			</div>
			<a href="#" @click.prevent="screen = null">Go back</a>
		</template>
		<template v-else>
			<div>
				<label>Select plan</label>
				<div class="plan-list">
					<span style="color: #000; padding: 4px 10px; position: sticky; top: 0; background: #fff; border-bottom: 1px solid #ddd;">
						<b>ID</b>
						<b>Usage</b>
					</span>
					<a v-for="package in switchPlan.plans.list" href="#"
						@click.prevent="switchPlan.selected = ( switchPlan.selected === package.id ? null : package.id )"
						:class="{'ts-selected': package.id === switchPlan.selected}"
						>
						<span><b>#{{ package.id }}</b> {{ package.plan.label || '' }}</span>
						<span>{{ package.used }}/{{ package.total }} used</span>
					</a>
				</div>
			</div>
			<div>
				<label>
					<input type="checkbox" v-model="switchPlan.consume_new_plan_slot">
					Consume slot on new plan?
				</label>
			</div>
			<div v-if="config.package.is_slot_restorable">
				<label>
					<input type="checkbox" v-model="switchPlan.restore_old_plan_slot">
					Restore slot on previous plan?
				</label>
			</div>
			<div style="display: flex; gap: 10px; align-items: center;">
				<a href="#" @click.prevent="submitSwitchPlan" class="button button-primary"
					:class="{'vx-disabled': switchPlan.processing}">Switch</a>
				<a href="#" @click.prevent="screen = null">Cancel</a>
			</div>
		</template>
	</div>
	<div v-else-if="screen === 'remove'" class="update-listing-plan" style="padding-top: 5px;">
		<div class="mt5" v-if="config.package.is_slot_restorable">
			<label>
				<input type="checkbox" v-model="removePlan.restore_old_plan_slot">
				Restore submission slot?
			</label>
		</div>
		<div style="display: flex; gap: 10px; align-items: center;">
			<a href="#" @click.prevent="submitRemovePlan" class="button button-primary"
				:class="{'vx-disabled': removePlan.processing}">Remove plan</a>
			<a href="#" @click.prevent="screen = null">Cancel</a>
		</div>
	</div>
	<div v-else-if="screen === 'set-expiry'" class="update-listing-plan">
		<div class="mt5">
			<label>Expiry date</label>
			<input type="datetime-local" :value="config.package.expires_at" ref="newExpiryInput">
		</div>
		<div style="display: flex; gap: 10px; align-items: center;">
			<a href="#" @click.prevent="submitUpdateExpiry" class="button button-primary"
				:class="{'vx-disabled': updateExpiry.processing}">Save</a>
			<a href="#" @click.prevent="screen = null">Cancel</a>
		</div>
	</div>
	<template v-else>
		<template v-if="config.package.exists">
			<h1 style="margin-bottom: 0;">
				{{ config.plan.is_deleted ? ( config.plan.key || '(deleted)' ) : config.plan.label }}
				<a :href="config.package.edit_link">#{{ config.package.id }}</a>
			</h1>
			<p style="margin: 0;">
				{{ config.package.expires_at ? `Expires ${config.package._expires_at}` : 'No expiry date' }}
				<a href="#" style="text-decoration: none;" @click.prevent="screen = 'set-expiry'">
					<span class="dashicons dashicons-edit"></span>
				</a>
			</p>
			<div class="plan-actions" style="margin-top: 5px;">
				<a href="#" @click.prevent="showSwitchScreen">Switch plan</a>
				<a href="#" @click.prevent="showRemoveScreen">Remove plan</a>
			</div>
		</template>
		<template v-else>
			<h1>No plan assigned</h1>
			<div class="plan-actions">
				<a href="#" @click.prevent="showAssignScreen">Assign plan</a>
			</div>
		</template>
	</template>

	<!-- <pre debug>{{ config }}</pre> -->
</div>

<?php
add_action( 'admin_footer', function() { ?>
<script>
	jQuery( () => {
		const el = document.getElementById('vx-listing-plan');
		const app = Vue.createApp( {
			el: el,
			data() {
				return {
					config: JSON.parse( el.dataset.config ),
					screen: null,
					assignPlan: {
						selected: null,
						consume_new_plan_slot: true,
						plans: {
							loading: true,
							list: null,
						},
						processing: false,
					},
					switchPlan: {
						selected: null,
						restore_old_plan_slot: false,
						consume_new_plan_slot: true,
						plans: {
							loading: true,
							list: null,
						},
						processing: false,
					},
					removePlan: {
						restore_old_plan_slot: false,
						processing: false,
					},
					updateExpiry: {
						processing: false,
					}
				};
			},
			methods: {
				showAssignScreen() {
					this.screen = 'assign';
					if ( this.assignPlan.plans.list === null ) {
						this.assignPlan.plans.loading = true;
						this.assignPlan.plans.list = [];
						jQuery.get( `${Voxel_Config.ajax_url}&action=paid_listings.backend.load_user_packages`, {
							user_id: this.config.author.id,
							post_type: this.config.post_type.key,
							_wpnonce: this.config._wpnonce,
						} ).always( response => {
							this.assignPlan.plans.loading = false;
							if ( response.success ) {
								this.assignPlan.plans.list.push( ...response.list );
							} else {
								Voxel.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
							}
						} );
					}
				},

				submitAssignPlan() {
					this.assignPlan.processing = true;
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_listings.backend.posts.assign_package`, {
						config: JSON.stringify( {
							user_id: this.config.author.id,
							post_id: this.config.post.id,
							new_package_id: this.assignPlan.selected,
							consume_new_plan_slot: this.assignPlan.consume_new_plan_slot,
						} ),
						_wpnonce: this.config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							this.savePost();
						} else {
							this.assignPlan.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},

				showSwitchScreen() {
					this.screen = 'switch';
					if ( this.switchPlan.plans.list === null ) {
						this.switchPlan.plans.loading = true;
						this.switchPlan.plans.list = [];
						jQuery.get( `${Voxel_Config.ajax_url}&action=paid_listings.backend.load_user_packages`, {
							user_id: this.config.author.id,
							post_type: this.config.post_type.key,
							_wpnonce: this.config._wpnonce,
						} ).always( response => {
							this.switchPlan.plans.loading = false;
							if ( response.success ) {
								const list = response.list.filter( package => package.id !== this.config.package.id );
								this.switchPlan.plans.list.push( ...list );
							} else {
								Voxel.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
							}
						} );
					}
				},

				submitSwitchPlan() {
					this.switchPlan.processing = true;
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_listings.backend.posts.switch_package`, {
						config: JSON.stringify( {
							user_id: this.config.author.id,
							post_id: this.config.post.id,
							old_package_id: this.config.package.id,
							new_package_id: this.switchPlan.selected,
							consume_new_plan_slot: this.switchPlan.consume_new_plan_slot,
							restore_old_plan_slot: this.switchPlan.restore_old_plan_slot,
						} ),
						_wpnonce: this.config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							this.savePost();
						} else {
							this.switchPlan.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},

				showRemoveScreen() {
					this.screen = 'remove';
				},

				submitRemovePlan() {
					this.removePlan.processing = true;
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_listings.backend.posts.remove_package`, {
						config: JSON.stringify( {
							user_id: this.config.author.id,
							post_id: this.config.post.id,
							old_package_id: this.config.package.id,
							restore_old_plan_slot: this.removePlan.restore_old_plan_slot,
						} ),
						_wpnonce: this.config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							this.savePost();
						} else {
							this.removePlan.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},

				submitUpdateExpiry() {
					this.updateExpiry.processing = true;

					const input = this.$refs.newExpiryInput;
					const new_value = input.value;
					this.$nextTick( () => input.value = new_value );
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_listings.backend.posts.update_expiry`, {
						config: JSON.stringify( {
							user_id: this.config.author.id,
							post_id: this.config.post.id,
							new_expiry_date: new_value,
						} ),
						_wpnonce: this.config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							this.updateExpiry.processing = false;
							this.config.package.expires_at = response.expires_at;
							this.config.package._expires_at = response._expires_at;
							this.screen = null;
						} else {
							this.updateExpiry.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},

				savePost() {
					const is_block_editor = document.body.classList.contains('block-editor-page');
					if ( is_block_editor ) {
						jQuery('#vx_verification, #vx_expiry, #vx_priority').remove();
						wp.data.dispatch('core/editor').savePost().then( () => {
							location.href = this.config.post.backend_edit_link;
						} );
					} else {
						jQuery('#vx_verification, #vx_expiry, #vx_priority').remove();
						document.querySelector('form#post')?.submit();
					}
				},
			},
		} );
		app.mount('#vx-listing-plan');
	} );
</script>
<style>
	.vx-listing-plan {
		h1 {
			padding: 0;
			margin: 10px 0 5px;
			font-size: 18px;

			a { text-decoration: none; }
		}

		.plan-actions { display: flex; gap: 8px; }
		.update-listing-plan { display: grid; gap: 10px; }
		.plan-list {
			display: grid;
			margin-top: 5px;
			border: 1px solid #ddd;
			border-radius: 5px;
			max-height: 250px;
			overflow-y: auto;

			> * {
				display: flex;
				justify-content: space-between;
				padding: 8px 10px;
				text-decoration: none;

				&:not(:last-child) { border-bottom: 1px solid #ddd; }
			}

			a {
				&:hover { background: #f3f3f3; }
				&.ts-selected { background: #2371b23b; }
				&:hover, &:focus { box-shadow: none; }
			}
		}
	}
</style>
<?php } );