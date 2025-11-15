<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-edit-plan" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit.prevent>
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2><?= $plan->get_label() ?></h2>
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
							<a href="#" @click.prevent="setTab('general')">General</a>
						</li>
						<li :class="{'current-item': tab === 'prices'}">
							<a href="#" @click.prevent="setTab('prices')">Prices</a>
						</li>
						<li v-if="plan.key !== 'default'" :class="{'current-item': tab === 'supported_roles'}">
							<a href="#" @click.prevent="setTab('supported_roles')">Supported roles</a>
						</li>
					</ul>
				</div>

				<div v-if="tab === 'general'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Plan details</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Text_Model::render( [
								'v-model' => 'plan.label',
								'label' => 'Label',
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Key_Model::render( [
								'v-model' => 'plan.key',
								'label' => 'Key',
								'editable' => false,
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Textarea_Model::render( [
								'v-model' => 'plan.description',
								'label' => 'Description',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
					<template v-if="plan.key !== 'default'">
						<div class="x-col-12 h-center">
							<a href="#" @click.prevent="showArchive = !showArchive" class="ts-button ts-transparent full-width ">
								<i class="las la-arrow-down icon-sm"></i>
								Advanced
							</a>
						</div>
						<template v-if="showArchive">
							<template v-if="plan.archived">
								<div class="ts-group">
									<div class="x-row">
										<div class="ts-form-group x-col-12">
											<p>Make this membership plan available to new users again.</p>
											<a href="#" class="ts-button ts-outline mt10" @click.prevent="archivePlan">
												<i class="las la-box icon-sm"></i>
												Unarchive plan
											</a>
										</div>
										<div class="ts-form-group x-col-12">
											<p>
												Delete this plan permanently. Users already on this plan will be assigned
												the default plan. This action cannot be undone.
											</p>
											<a href="#" class="ts-button ts-outline mt10" @click.prevent="deletePlan">
												<i class="las la-trash icon-sm"></i>
												Delete plan permanently
											</a>
										</div>
									</div>
								</div>
							</template>
							<template v-else>
								<div class="ts-group">
									<div class="x-row">
										<div class="ts-form-group x-col-12">
											<p>
												Archiving a membership plan prevents new users from joining it. Existing members on the
												plan will not be affected. You can restore an archived plan at any time.
											</p>
											<a href="#" class="ts-button ts-outline mt10" @click.prevent="archivePlan">
												<i class="las la-box icon-sm"></i>
												Archive this plan
											</a>
										</div>
									</div>
								</div>
							</template>
						</template>
					</template>
				</div>
				<div v-else-if="tab === 'supported_roles'" class="x-col-9">
					<template v-if="plan.key !== 'default'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Supported roles</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => 'plan.settings.supported_roles',
									'label' => 'User roles that support purchasing this plan',
									'classes' => 'x-col-12',
									'choices' => [
										'all' => 'All: Supports every user role',
										'custom' => 'Custom: Manually set supported roles'
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
									'v-if' => 'plan.settings.supported_roles === \'custom\'',
									'v-model' => 'plan.settings.supported_roles_custom',
									'label' => 'Select supported roles',
									'classes' => 'x-col-12',
									'choices' => array_map( function( $role ) {
										return $role->get_label();
									}, \Voxel\Role::get_voxel_roles() ),
								] ) ?>
							</div>
						</div>
					</template>
				</div>
				<div v-else-if="tab === 'prices'" class="x-col-9">
					<template v-if="plan.key === 'default'">
						<div class="ts-group">
							<div class="ts-form-group">
								<p>Pricing is not supported on the default plan. To enable and configure prices, create a custom plan.</p>
							</div>
						</div>
					</template>
					<template v-else>
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Configure prices</h3>
							</div>
							<div class="x-row">
								<div v-if="plan.prices.length" class="x-col-12">
									<draggable v-model="plan.prices" group="prices" item-key="key" class="field-container">
										<template #item="{element: price, index: index}">
											<div class="single-field wide" :class="{open: price === activePrice}">
												<div class="field-head" @click.prevent="activePrice = price === activePrice ? null : price">
													<p class="field-name">{{ price.label || '(untitled)' }}</p>
													<span class="field-type">
														<template v-if="price.amount && price.currency">
															<template v-if="price.discount_amount">
																<s style="text-decoration: line-through;">{{ currencyFormat( price.amount, price.currency ) }}</s>
																{{ currencyFormat( price.discount_amount, price.currency ) }}
															</template>
															<template v-else>
																{{ currencyFormat( price.amount, price.currency ) }}
															</template>
														</template>
														<template v-if="price.frequency && price.interval">
															every {{ price.frequency }} {{ price.interval }}(s)
														</template>
													</span>
													<div class="field-actions left-actions">
														<span class="field-action all-center">
															<a href="#" @click.stop.prevent="deletePrice(index)"><i class="las la-trash"></i></a>
														</span>
														<span class="field-action all-center">
															<a href="#" @click.prevent><i class="las la-angle-down"></i></a>
														</span>
													</div>
												</div>
												<div class="field-body" v-if="price === activePrice">
													<div class="x-row">
														<?php \Voxel\Utils\Form_Models\Text_Model::render( [
															'v-model' => 'price.label',
															'label' => 'Label',
															'classes' => 'x-col-12',
															'placeholder' => 'e.g. Monthly subscription',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Number_Model::render( [
															'v-model' => 'price.amount',
															'label' => 'Price',
															'classes' => 'x-col-4',
															'placeholder' => 'Enter amount',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Number_Model::render( [
															'v-model' => 'price.discount_amount',
															'label' => 'Discount price',
															'classes' => 'x-col-4',
															'placeholder' => 'Optional',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Select_Model::render( [
															'v-model' => 'price.currency',
															'label' => 'Currency',
															'choices' => \Voxel\Utils\Currency_List::all(),
															'classes' => 'x-col-4',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Number_Model::render( [
															'v-model' => 'price.frequency',
															'label' => 'Subscription interval',
															'classes' => 'x-col-6',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Select_Model::render( [
															'v-model' => 'price.interval',
															'label' => '&nbsp;',
															'classes' => 'x-col-6',
															'choices' => [
																'day' => 'Day(s)',
																'week' => 'Week(s)',
																'month' => 'Month(s)',
																'year' => 'Year(s)',
															],
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
															'v-model' => 'price.trial.enabled',
															'label' => 'Enable free trial',
															'classes' => 'x-col-12',
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Number_Model::render( [
															'v-if' => 'price.trial.enabled',
															'v-model' => 'price.trial.days',
															'label' => 'Trial days',
															'classes' => 'x-col-12',
														] ) ?>

														<!-- <pre debug>{{ price }}</pre> -->
													</div>
												</div>
											</div>
										</template>
									</draggable>
								</div>
								<div v-else class="ts-form-group x-col-12">
									<p>No prices added yet.</p>
								</div>
								<div class="x-col-12">
									<a href="#" @click.prevent="addPrice" class="ts-button ts-outline">
										<i class="las la-plus icon-sm"></i> Add price
									</a>
								</div>
							</div>
						</div>
					</template>
				</div>
			</div>
		</div>
	</form>
	<!-- <pre debug>{{ plan }}</pre> -->
</div>
