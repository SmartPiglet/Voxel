<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-type-rate-list-template">
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
</script>