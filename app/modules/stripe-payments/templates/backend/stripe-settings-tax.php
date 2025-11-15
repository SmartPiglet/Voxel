<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'settings.tax_collection.enabled',
			'label' => 'Enable tax collection',
			'classes' => 'x-col-12',
		] ) ?>

		<template v-if="settings.tax_collection.enabled">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'settings.tax_collection.collection_method',
				'label' => 'Collection method',
				'classes' => 'x-col-12',
				'choices' => [
					'stripe_tax' => 'Automatic: Collect taxes automatically through Stripe Tax',
					'tax_rates' => 'Manual: Configure tax rates manually',
				],
			] ) ?>

			<template v-if="settings.tax_collection.collection_method === 'stripe_tax'">
				<div class="ts-form-group x-col-12 basic-ul">
					<li>
						<a class="ts-button ts-outline" href="https://stripe.com/tax" target="_blank">
							<i class="las la-external-link-alt icon-sm"></i>
							Getting started with Stripe Tax
						</a>
					</li>
					<li>
						<a class="ts-button ts-outline" href="<?= esc_url( \Voxel\Modules\Stripe_Payments\Stripe_Client::dashboard_url( '/settings/tax' ) ) ?>" target="_blank">
							<i class="las la-external-link-alt icon-sm"></i>
							Configure Stripe Tax
						</a>
					</li>
				</div>
			</template>
			<template v-if="settings.tax_collection.collection_method === 'tax_rates'">
				<div class="ts-form-group x-col-12 basic-ul">
					<li>
						<a class="ts-button ts-outline" href="<?= esc_url( \Voxel\Modules\Stripe_Payments\Stripe_Client::dashboard_url( '/tax-rates' ) ) ?>" target="_blank">
							<i class="las la-external-link-alt icon-sm"></i>
							Setup tax rates
						</a>
					</li>
				</div>
			</template>
		</template>
	</div>
</div>

<template v-if="settings.tax_collection.enabled">
	<template v-if="settings.tax_collection.collection_method === 'stripe_tax'">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Product types</h3>
			</div>
			<div class="x-row">
				<div class="x-col-12 field-container">
					<template v-if="settings.tax_collection.stripe_tax.product_types !== null && Object.keys(settings.tax_collection.stripe_tax.product_types).length">
						<template v-for="product_type, product_type_key in settings.tax_collection.stripe_tax.product_types">
							<div
								v-if="$root.props.product_types[ product_type_key ]"
								class="single-field wide"
								:class="{open: state.autoTaxProductType === product_type}"
							>
								<div class="field-head" @click="state.autoTaxProductType = state.autoTaxProductType === product_type ? null : product_type">
									<p class="field-name">{{ $root.props.product_types[ product_type_key ].label }}</p>
									<p class="field-type">{{ product_type_key }}</p>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.stop.prevent="delete settings.tax_collection.stripe_tax.product_types[ product_type_key ]">
												<i class="lar la-trash-alt icon-sm"></i>
											</a>
										</span>
									</div>
								</div>
								<div v-if="state.autoTaxProductType === product_type" class="field-body">
									<div class="x-row">
										<?php \Voxel\Utils\Form_Models\Select_Model::render( [
											'v-model' => 'product_type.tax_code',
											'label' => 'Product tax code <a style="float:right;" href="https://stripe.com/docs/tax/tax-codes" target="_blank">View available tax codes</a>',
											'classes' => 'x-col-12',
											'choices' => [ '' => 'Select a code' ] + \Voxel\Modules\Stripe_Payments\Tax_Codes::all(),
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Select_Model::render( [
											'v-model' => 'product_type.tax_behavior',
											'label' => sprintf( 'Tax behavior <a style="float:right;" href="%s" target="_blank">Set default tax behavior</a>', esc_url( \Voxel\Modules\Stripe_Payments\Stripe_Client::dashboard_url( '/settings/tax' ) ) ),
											'classes' => 'x-col-12',
											'choices' => [
												'default' => 'Default: Use default tax behavior configured in your Stripe dashboard',
												'inclusive' => 'Inclusive: Tax is included in the price',
												'exclusive' => 'Exclusive: Tax is added on top of the price',
											],
										] ) ?>
									</div>
								</div>
							</div>
						</template>
					</template>
					<div v-else class="ts-form-group">
						<p>You have not added any product types yet.</p>
					</div>
				</div>

				<div class="x-col-12">
					<div class="add-field">
						<template v-for="product_type in $root.props.product_types">
							<div
								v-if="!settings.tax_collection.stripe_tax.product_types?.[ product_type.key ]"
								class="ts-button ts-outline"
								@click.prevent="
									settings.tax_collection.stripe_tax.product_types === null && ( settings.tax_collection.stripe_tax.product_types = {} );
									settings.tax_collection.stripe_tax.product_types[ product_type.key ] = {
										tax_behavior: 'default',
										tax_code: '',
									};
								"
							>
								<p class="field-name">{{ product_type.label }}</p>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>
	</template>
	<template v-if="settings.tax_collection.collection_method === 'tax_rates'">
		<div class="ts-group">
			<div class="ts-group-head">
				<h3>Product types</h3>
			</div>
			<div class="x-row">
				<div class="x-col-12 field-container">
					<template v-if="settings.tax_collection.tax_rates.product_types !== null && Object.keys(settings.tax_collection.tax_rates.product_types).length">
						<template v-for="product_type, product_type_key in settings.tax_collection.tax_rates.product_types">
							<div
								v-if="$root.props.product_types[ product_type_key ]"
								class="single-field wide"
								:class="{open: state.autoTaxProductType === product_type}"
							>
								<div class="field-head" @click="state.autoTaxProductType = state.autoTaxProductType === product_type ? null : product_type">
									<p class="field-name">{{ $root.props.product_types[ product_type_key ].label }}</p>
									<p class="field-type">{{ product_type_key }}</p>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.stop.prevent="delete settings.tax_collection.tax_rates.product_types[ product_type_key ]">
												<i class="lar la-trash-alt icon-sm"></i>
											</a>
										</span>
									</div>
								</div>
								<div v-if="state.autoTaxProductType === product_type" class="field-body">
									<div class="x-row">
										<?php \Voxel\Utils\Form_Models\Radio_Buttons_Model::render( [
											'v-model' => 'product_type.calculation_method',
											'label' => 'Tax calculation method',
											'classes' => 'x-col-12',
											'description' => join( "\n\n", [
												'Use fixed tax rates when you know the exact tax rate to charge your customer before they start the checkout process (for example, you only sell to customers in the UK and always charge 20% VAT).',
												'Use dynamic tax rates when you need more information from your customer (for example, their billing or shipping address) to determine the tax rate to charge. With dynamic tax rates, you create tax rates for different regions (for example, a 20% VAT tax rate for customers in the UK and a 7.25% sales tax rate for customers in California, US) and Stripe attempts to match your customer’s location to one of those tax rates.',
											] ),
											'choices' => [
												'fixed' => '<strong>Fixed Tax Rates</strong> <p style="display: inline;">Use fixed tax rates when you know the exact tax rate to charge your customer (for example, you only sell to customers in the UK and always charge 20% VAT).</p>',
												'dynamic' => '<strong>Dynamic Tax Rates</strong> <p style="display: inline;">Use dynamic tax rates when you need more information from your customer (for example, their billing or shipping address) to determine the tax rate to charge.</p>',
											],
										] ) ?>

										<template v-if="product_type.calculation_method === 'fixed'">
											<div class="ts-form-group x-col-12">
												<label style="opacity: 1;">Live mode</label>
												<tax-rate-list
													v-model="product_type.fixed_rates.live_mode"
													mode="live"
													source="backend.list_tax_rates"
												></tax-rate-list>
											</div>
											<div class="ts-form-group x-col-12">
												<label style="opacity: 1;">Test mode</label>
												<tax-rate-list
													v-model="product_type.fixed_rates.test_mode"
													mode="test"
													source="backend.list_tax_rates"
												></tax-rate-list>
											</div>
										</template>
										<template v-if="product_type.calculation_method === 'dynamic'">
											<div class="ts-form-group x-col-12">
												<label style="opacity: 1;">Live mode</label>
												<tax-rate-list
													v-model="product_type.dynamic_rates.live_mode"
													mode="live"
													source="backend.list_tax_rates"
													dynamic="yes"
												></tax-rate-list>
											</div>
											<div class="ts-form-group x-col-12">
												<label style="opacity: 1;">Test mode</label>
												<tax-rate-list
													v-model="product_type.dynamic_rates.test_mode"
													mode="test"
													source="backend.list_tax_rates"
													dynamic="yes"
												></tax-rate-list>
											</div>
										</template>
									</div>
								</div>
							</div>
						</template>
					</template>
					<div v-else class="ts-form-group">
						<p>You have not added any product types yet.</p>
					</div>
				</div>

				<div class="x-col-12">
					<div class="add-field">
						<template v-for="product_type in $root.props.product_types">
							<div
								v-if="!settings.tax_collection.tax_rates.product_types?.[ product_type.key ]"
								class="ts-button ts-outline"
								@click.prevent="
									settings.tax_collection.tax_rates.product_types === null && ( settings.tax_collection.tax_rates.product_types = {} );
									settings.tax_collection.tax_rates.product_types[ product_type.key ] = {
										fixed_rates: {
											live_mode: [],
											test_mode: [],
										},
										dynamic_rates: {
											live_mode: [],
											test_mode: [],
										},
										calculation_method: 'fixed',
									};
								"
							>
								<p class="field-name">{{ product_type.label }}</p>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>
	</template>
</template>
