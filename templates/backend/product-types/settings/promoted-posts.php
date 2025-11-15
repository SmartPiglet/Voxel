<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.promotions.enabled',
			'label' => 'Enable promoted posts',
			'classes' => 'x-col-12',
		] ) ?>

		<!-- <div class="x-col-12">
			<pre debug>{{ config.promotions }}</pre>
		</div> -->
	</div>
</div>

<template v-if="config.promotions.enabled">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Payments</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.promotions.payments.mode',
				'label' => 'Payment mode',
				'classes' => 'x-col-12',
				'choices' => [
					'payment' => 'Single payment: User pays a one time fee',
					'offline' => 'Offline payment: Payments are handled off-site',
				],
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.promotions.order_approval',
				'label' => 'Order approval',
				'classes' => 'x-col-12',
				'choices' => [
					'automatic' => 'Automatic: Order is approved immediately',
					'manual' => 'Manual: Order is approved manually',
				],
			] ) ?>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Packages</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12 field-container ts-drag-animation">
				<template v-if="config.promotions.packages.length">
					<draggable
						v-model="config.promotions.packages"
						group="promotion_packages"
						handle=".field-head"
						item-key="key"
					>
						<template #item="{element: package, index: index}">
							<div class="single-field wide" :class="{open: state.activePromoPackage === package}">
								<div class="field-head" @click="state.activePromoPackage = state.activePromoPackage === package ? null : package">
									<p class="field-name">{{ package.ui.label || '(untitled)' }}</p>
									<p class="field-type">
										{{ ( package.price.amount !== null && package.price.amount !== '' ) ? currencyFormat( package.price.amount ) : 'No price added' }}
										<span style="display: none;">{{ package.key }}</span>
									</p>
									<div class="field-actions">
										<span class="field-action all-center">
											<a href="#" @click.prevent="config.promotions.packages.splice(index, 1)">
												<i class="lar la-trash-alt icon-sm"></i>
											</a>
										</span>
									</div>
								</div>
								<div v-if="state.activePromoPackage === package" class="field-body">
									<div class="x-row">
										<?php \Voxel\Utils\Form_Models\Text_Model::render( [
											'v-model' => 'package.ui.label',
											'label' => 'Label',
											'classes' => 'x-col-6',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Text_Model::render( [
											'v-model' => 'package.ui.description',
											'label' => 'Description',
											'classes' => 'x-col-6',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Color_Model::render( [
											'v-model' => 'package.ui.color',
											'label' => 'Accent color',
											'classes' => 'x-col-6',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
											'v-model' => 'package.ui.icon',
											'label' => 'Icon',
											'classes' => 'x-col-6',
											':allow-fonticons' => 'false',
										] ) ?>

										<div class="ts-form-group x-col-12">
											<label style="padding-bottom: 0;"><strong>Promotion details</strong></label>
										</div>

										<?php \Voxel\Utils\Form_Models\Number_Model::render( [
											'v-model' => 'package.price.amount',
											'label' => 'Price',
											'classes' => 'x-col-12',
											'step' => 'any',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Number_Model::render( [
											'v-model' => 'package.duration.amount',
											'label' => 'Duration (days)',
											'classes' => 'x-col-6',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Number_Model::render( [
											'v-model' => 'package.priority',
											'label' => 'Priority',
											'classes' => 'x-col-6',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
											'v-model' => 'package.post_types',
											'label' => 'Post type(s)',
											'classes' => 'x-col-12',
											'columns' => 'three',
											'choices' => array_map( function( $post_type ) {
												return $post_type->get_label();
											}, \Voxel\Post_Type::get_voxel_types() ),
										] ) ?>
									</div>
								</div>
							</div>
						</template>
					</draggable>
				</template>
				<div v-else class="ts-form-group">
					<p>You have not added any promotion packages yet.</p>
				</div>
			</div>

			<div class="x-col-12">
				<div class="add-field">
					<div
						class="ts-button ts-outline"
						@click.prevent="config.promotions.packages.push( {
							key: $w.Voxel_Backend.helpers.randomId(8),
							post_types: [],
							duration: {
								type: 'days',
								amount: null,
							},
							priority: 2,
							price: {
								amount: null,
							},
							ui: {
								label: null,
								description: null,
								icon: null,
								color: null,
							},
						} )"
					>
						<p class="field-name">Add package</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
