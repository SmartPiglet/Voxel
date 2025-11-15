const Tax_Rate_List = {
	template: `
	<div class="fields-container" v-if="modelValue.length">
		<div v-for="rate in modelValue" class="single-field single-field-sm wide">
			<div class="field-head">
				<p class="field-name" style="color: #fff;">{{ rate }}</p>
				<div class="field-actions">
					<a :href="(mode === 'live' ? 'https://dashboard.stripe.com/tax-rates/' : 'https://dashboard.stripe.com/test/tax-rates/') + rate" class="field-action all-center" target="_blank">
						<i class="las la-external-link-alt"></i>
					</a>
					<a href="#" @click.prevent="remove(rate)" class="field-action all-center">
						<i class="las la-trash"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div v-else>
		<p class="mb0 mt0">No tax rates added.</p>
	</div>
	<div class="basic-ul">
		<li>
			<a href="#" @click.prevent="show" class="ts-button ts-outline mt10">Add tax rate</a>
		</li>
	</div>
	<teleport to="body">
		<div v-if="open" class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="open = false"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Select tax rates</h2>
						<a href="#" @click.prevent="open = false" class="ts-button btn-shadow">
							<i class="las la-check icon-sm"></i>Save
						</a>
					</div>
					<div class="field-modal-body">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<template v-if="rates === null">
									<p class="text-center">Loading...</p>
								</template>
								<template v-else-if="!(rates && rates.length)">
									<p class="text-center">No tax rates found.</p>
								</template>
								<template v-else>
									<div v-for="rate in rates" class="single-field wide">
										<div class="field-head" @click.prevent="toggle(rate)">
											<p class="field-name" style="color: #fff;">{{ rate.display_name }}</p>
											<span class="field-type">{{ rate.id }}</span>
											<div class="field-actions" v-if="isSelected(rate)">
												<span class="field-action all-center">
													<i class="las la-check icon-sm"></i>
												</span>
											</div>
										</div>
									</div>
									<div class="ts-form-group x-col-12 basic-ul" style="justify-content: space-between;">
										<a href="#" :class="{'vx-disabled':rates[0].id === first_item}" @click.prevent="prev" class="ts-button ts-faded">Prev</a>
										<a href="#" :class="{'vx-disabled':is_last_page}" @click.prevent="next" class="ts-button ts-faded">Next</a>
									</div>
								</template>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</teleport>
	`,

	props: ['modelValue', 'mode', 'source', 'dynamic'],

	data() {
		return {
			loading: false,
			open: false,
			rates: null,
			first_item: null,
			is_last_page: false,
		};
	},

	methods: {
		show() {
			this.open = true;
			this.loading = true;
			if ( this.rates === null ) {
				jQuery.get( Voxel_Config.ajax_url, { action: this.source, mode: this.mode, dynamic: this.dynamic }, response => {
					this.loading = false;
					this.is_last_page = !response.has_more;
					this.rates = response.rates;
					this.first_item = this.rates?.[0]?.id;
				} );
			}
		},

		toggle( rate ) {
			var index = this.modelValue.indexOf( rate.id );
			if ( index > -1 ) {
				this.modelValue.splice( index, 1 );
			} else {
				this.modelValue.push( rate.id );
			}
		},

		isSelected( rate ) {
			return this.modelValue.indexOf( rate.id ) > -1;
		},

		remove( rate_id ) {
			var index = this.modelValue.indexOf( rate_id );
			if ( index > -1 ) {
				this.modelValue.splice( index, 1 );
			}
		},

		prev() {
			this.loading = true;
			var ending_before = this.rates[0].id;
			jQuery.get( Voxel_Config.ajax_url, { action: this.source, mode: this.mode, ending_before: ending_before }, response => {
				this.loading = false;
				this.has_more = response.has_more;
				if ( response.rates.length ) {
					this.rates = response.rates;
					this.is_last_page = false;
				}
			} );
		},

		next() {
			this.loading = true;
			var starting_after = this.rates[ this.rates.length - 1 ].id;
			jQuery.get( Voxel_Config.ajax_url, { action: this.source, mode: this.mode, starting_after: starting_after }, response => {
				this.loading = false;
				this.has_more = response.has_more;
				if ( response.rates.length ) {
					this.rates = response.rates;
					this.is_last_page = !response.has_more;
				}
			} );
		},
	},
};

export default {
	props: {
		provider: Object,
		settings: Object,
		data: Object,
	},

	data() {
		return {
			tab: 'general',
			state: {
				autoTaxProductType: null,
			},
		};
	},

	components: {
		'tax-rate-list': Tax_Rate_List,
	},

	methods: {
		setupWebhook( e, mode ) {
			const btn = e.target;
			btn?.classList.add('vx-disabled');
			jQuery.post( Voxel_Config.ajax_url, {
				action: 'stripe.admin.setup_webhook',
				mode: mode,
				api_key: this.settings[ mode ].api_key,
			} ).always( response => {
				btn?.classList.remove('vx-disabled');
				if ( response.success ) {
					if ( response.id ) {
						this.settings[ mode ].webhook.id = response.id;
					}

					if ( response.secret ) {
						this.settings[ mode ].webhook.secret = response.secret;
					}

					Voxel_Backend.alert( response.message );
				} else {
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		},

		setupConnectWebhook( e, mode ) {
			const btn = e.target;
			btn?.classList.add('vx-disabled');
			jQuery.post( Voxel_Config.ajax_url, {
				action: 'stripe.admin.setup_connect_webhook',
				mode: mode,
				api_key: this.settings[ mode ].api_key,
			} ).always( response => {
				btn?.classList.remove('vx-disabled');
				if ( response.success ) {
					if ( response.id ) {
						this.settings.stripe_connect.webhook[ mode ].id = response.id;
					}

					if ( response.secret ) {
						this.settings.stripe_connect.webhook[ mode ].secret = response.secret;
					}

					Voxel_Backend.alert( response.message );
				} else {
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		},

		setupCustomerPortal( e, mode ) {
			const btn = e.target;
			btn?.classList.add('vx-disabled');
			jQuery.post( Voxel_Config.ajax_url, {
				action: 'stripe.admin.setup_customer_portal',
				mode: mode,
				api_key: this.settings[ mode ].api_key,
				customer_portal: JSON.stringify( this.settings[ mode ].customer_portal ),
			} ).always( response => {
				btn?.classList.remove('vx-disabled');
				if ( response.success ) {
					this.settings[ mode ].customer_portal = response.customer_portal;

					Voxel_Backend.alert( response.message );
				} else {
					Voxel_Backend.alert( response.message || Voxel_Config.l10n.ajaxError, 'error' );
				}
			} );
		},
	},
};
