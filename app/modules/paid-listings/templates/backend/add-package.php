<?php

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;

if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="assign-listing-plan" class="vx-single-customer" v-cloak
	data-config="<?= esc_attr( wp_json_encode( [
		'_wpnonce' => wp_create_nonce( 'paid_listings.add_package' ),
	] ) ) ?>">
	<div class="vx-card-ui">
		<div class="vx-card full no-wp-style">
			<div class="vx-card-head">
				<p>Assign plan to user</p>
			</div>
			<div class="vx-card-content">
				<form @submit.prevent="onSubmit">
					<table class="form-table">
						<tbody>
							<tr>
								<th>User</th>
								<td>
									<div>
										<div v-if="user" class="vx-user-details vx-selected" @click.prevent="user = null; form.user_id = null;">
											<div class="vx-user-avatar" v-html="user.avatar"></div>
											<div>
												<div><b>{{ user.display_name }}</b></div>
												<span style="opacity: .5;">User ID: {{ user.id }}</span>
											</div>
										</div>
										<input v-show="!user" type="text" placeholder="Search by name, email, or ID"
											@input="searchUsers(this)" ref="searchInput" style="min-width: 300px;" autocomplete="do-not-autofill">
										<div class="vx-search-users" v-if="!user && search.term.trim().length">
											<div class="vx-card">
												<template v-if="search.results !== null && search.results.length">
													<div class="vx-results">
														<template v-for="user in search.results">
															<a href="#" @click.prevent="selectUser(user)">
																<div class="vx-user-details">
																	<div class="vx-user-avatar" v-html="user.avatar"></div>
																	<div>
																		<div><b>{{ user.display_name }}</b></div>
																		<span style="opacity: .5;">User ID: {{ user.id }}</span>
																	</div>
																</div>
															</a>
														</template>
													</div>
												</template>
												<template v-else>
													<p style="padding: 5px 10px;">{{ search.loading ? 'Loading...' : 'No users found' }}</p>
												</template>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<th>Plan</th>
								<td>
									<select v-model="form.plan" required style="min-width: 300px;">
										<option :value="null">Select plan</option>
										<?php foreach ( Module\Listing_Plan::all() as $plan ): ?>
											<option value="<?= esc_attr( $plan->get_key() ) ?>">
												<?= esc_html( $plan->get_label() ) ?>
											</option>
										<?php endforeach ?>
									</select>
								</td>
							</tr>
							<?php if ( Payment_Service::get_active()?->get_key() === 'stripe' ): ?>
								<tr>
									<th></th>
									<td style="display: grid; gap: 5px;">
										<label class="mt5">
											<input v-model="form.stripe_map.enabled" type="checkbox">
											Link to a Stripe payment or subscription
										</label>
										<div v-if="form.stripe_map.enabled">
											<input type="text" placeholder="Enter ID e.g. sub_xxxxxxxxxxx or pi_xxxxxxxxxxx"
												style="min-width: 300px;" v-model="form.stripe_map.transaction_id">
											<ul style="margin: 5px 0 10px 15px;font-size: 13px;list-style: disc;">
												<li>The payment or subscription must belong to the selected user.</li>
												<li>The payment or subscription must not be linked to an existing order in <b>Ecommerce > Orders</b>.</li>
											</ul>
										</div>
									</td>
								</tr>
							<?php endif ?>
							<tr :class="{'vx-disabled':processing}">
								<th></th>
								<td>
									<button type="submit" class="button button-primary">Assign</button>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery( () => {
		const el = document.getElementById('assign-listing-plan');
		const config = JSON.parse( el.dataset.config );
		const app = Vue.createApp( {
			el: el,
			data() {
				return {
					user: null,
					search: {
						term: '',
						loading: false,
						results: null,
					},
					form: {
						user_id: null,
						plan: null,
						stripe_map: {
							enabled: false,
							transaction_id: null,
						},
					},
					processing: false,
				};
			},

			methods: {
				searchUsers: Voxel_Backend.helpers.debounce( ctx => {
					ctx.search.term = ctx.$refs.searchInput.value;
					if ( ! ctx.search.term.trim().length ) {
						ctx.search.results = [];
						return;
					}

					ctx.search.loading = true;
					jQuery.get( Voxel_Config.ajax_url, {
						action: 'general.search_users',
						search: ctx.search.term,
					} ).always( response => {
						ctx.search.loading = false;
						if ( response.success ) {
							ctx.search.results = response.results;
						} else {
							Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				}, 150 ),

				selectUser( user ) {
					this.user = user;
					this.form.user_id = user.id;
					this.search.term = '';
					this.$refs.searchInput.value = '';
				},

				onSubmit() {
					this.processing = true;
					jQuery.post( `${Voxel_Config.ajax_url}&action=paid_listings.add_package`, {
						config: JSON.stringify( this.form ),
						_wpnonce: config._wpnonce,
					} ).always( response => {
						if ( response.success ) {
							location.href = response.redirect_to;
						} else {
							this.processing = false;
					    	Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
						}
					} );
				},
			},
		} );

		app.mount('#assign-listing-plan');
	} );
</script>

<style>
	.vx-search-users {
		position: relative;

		.vx-card {
			min-width: 300px;
			position: absolute;
			z-index: 10000;
			top: 5px;
			overflow: hidden;

			&:empty {
				display: none;
			}
		}

		a {
			border: 0 !important;
		}
	}

	.vx-user-details {
		cursor: pointer;
		display: flex;
		gap: 10px;
		padding: 5px 10px;
		box-sizing: border-box;

		&:hover {
			background: #f0f0f0 !important;
		}

		&.vx-selected {
			min-width: 300px;
			border: 1px solid #8c8f94;
			border-radius: 3px;
		}

		.vx-user-avatar img {
			width: 32px !important;
			height: 32px !important;
		}
	}
</style>