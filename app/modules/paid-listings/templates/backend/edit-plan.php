<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-edit-listing-plan" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit.prevent>
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2><?= $plan->get_label() ?></h2>
				<div class="">
					<a href="#" @click.prevent="deletePlan" class="ts-button ts-transparent">Delete</a>
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
						<li :class="{'current-item': tab === 'pricing'}">
							<a href="#" @click.prevent="setTab('pricing')">Pricing</a>
						</li>
						<li :class="{'current-item': tab === 'limits'}">
							<a href="#" @click.prevent="setTab('limits')">Limits</a>
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
				</div>
				<div v-else-if="tab === 'limits'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Configure submission limits</h3>
						</div>
						<div class="x-row">
							<div v-if="plan.limits.length" class="x-col-12">
								<draggable v-model="plan.limits" group="limits" item-key="key" class="field-container" handle=".field-head">
									<template #item="{element: limit, index: index}">
										<div class="single-field wide" :class="{open: limit === activeLimit}">
											<div class="field-head" @click.prevent="activeLimit = limit === activeLimit ? null : limit">
												<p class="field-name">
													{{ getLimitTitle(limit) }}
												</p>
												<span v-if="limit.total" class="field-type">
													{{ limit.total }} submissions
												</span>
												<div class="field-actions left-actions">
													<span class="field-action all-center">
														<a href="#" @click.stop.prevent="plan.limits.splice(index,1)">
															<i class="las la-trash"></i>
														</a>
													</span>
													<span class="field-action all-center">
														<a href="#" @click.prevent><i class="las la-angle-down"></i></a>
													</span>
												</div>
											</div>
											<div class="field-body" v-if="limit === activeLimit">
												<div class="x-row">
													<div class="ts-form-group x-col-12 ts-checkbox">
														<label>Post types</label>
														<div class="ts-checkbox-container two-column min-scroll">
															<template v-for="post_type in config.post_types">
																<label v-if="isPostTypeAvailable(post_type, limit)" class="container-checkbox">
																	{{ post_type.label }}
																	<input type="checkbox" :value="post_type.key" v-model="limit.post_types">
																	<span class="checkmark"></span>
																</label>
															</template>
														</div>
													</div>

													<?php \Voxel\Utils\Form_Models\Number_Model::render( [
														'v-model' => 'limit.total',
														'label' => 'Submission limit',
														'classes' => 'x-col-12',
														'infobox' => 'Set the maximum number of posts a user can publish from the selected post types.',
													] ) ?>

													<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
														'v-model' => 'limit.mark_verified',
														'label' => 'Mark verified?',
														'classes' => 'x-col-12',
														'infobox' => 'If checked, posts published with this plan will be automatically marked as verified.',
													] ) ?>

													<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
														'v-model' => 'limit.priority.enabled',
														'label' => 'Custom priority?',
														'classes' => 'x-col-12',
														'infobox' => 'If checked, posts published with this plan will be automatically assigned the configured priority level.',
													] ) ?>

													<?php \Voxel\Utils\Form_Models\Number_Model::render( [
														'v-if' => 'limit.priority.enabled',
														'v-model' => 'limit.priority.value',
														'label' => 'Priority level',
														'classes' => 'x-col-12',
														'infobox' => '<b>Priority levels</b><br>Medium (Default): 0<br>High: 1<br>Custom: 2+',
													] ) ?>

													<template v-if="plan.billing.mode === 'payment'">
														<?php \Voxel\Utils\Form_Models\Select_Model::render( [
															'v-model' => 'limit.expiration.mode',
															'label' => 'Expiration mode',
															'classes' => 'x-col-12',
															'choices' => [
																'auto' => 'None: Posts never expire',
																'fixed_days' => 'Fixed: Posts expire after a fixed amount of time',
															],
														] ) ?>

														<?php \Voxel\Utils\Form_Models\Number_Model::render( [
															'v-if' => 'limit.expiration.mode === "fixed_days"',
															'v-model' => 'limit.expiration.fixed_days',
															'label' => 'Expire after (days)',
															'classes' => 'x-col-12',
														] ) ?>
													</template>
												</div>
											</div>
										</div>
									</template>
								</draggable>
							</div>
							<div v-else class="ts-form-group x-col-12">
								<p>No submission limits added yet.</p>
							</div>
							<div v-if="canAddLimit()" class="x-col-12">
								<a href="#" @click.prevent="addLimit" class="ts-button ts-outline">
									<i class="las la-plus icon-sm"></i> Add limit
								</a>
							</div>
						</div>
					</div>
				</div>
				<div v-else-if="tab === 'pricing'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Configure pricing</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'v-model' => 'plan.billing.mode',
								'label' => 'Payment mode',
								'classes' => 'x-col-12',
								'choices' => [
									'payment' => 'Single payment',
									'subscription' => 'Subscription',
								],
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'plan.billing.amount',
								'label' => 'Price',
								'classes' => 'x-col-6',
								'placeholder' => 'Enter amount',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'plan.billing.discount_amount',
								'label' => 'Discount price',
								'classes' => 'x-col-6',
								'placeholder' => 'Optional',
							] ) ?>

							<template v-if="plan.billing.mode === 'subscription'">
								<?php \Voxel\Utils\Form_Models\Number_Model::render( [
									'v-model' => 'plan.billing.frequency',
									'label' => 'Subscription interval',
									'classes' => 'x-col-6',
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => 'plan.billing.interval',
									'label' => '&nbsp;',
									'classes' => 'x-col-6',
									'choices' => [
										'day' => 'Day(s)',
										'week' => 'Week(s)',
										'month' => 'Month(s)',
										'year' => 'Year(s)',
									],
								] ) ?>
							</template>

					<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
						'v-model' => 'plan.billing.disable_repeat_purchase',
						'label' => 'Limit this plan to one purchase per user?',
						'classes' => 'x-col-12',
					] ) ?>

					<template v-if="plan.billing.mode === 'subscription'">
						<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
							'v-model' => 'plan.billing.restore_slot_on_delete',
							'label' => 'Restore slot if user deletes post',
							'classes' => 'x-col-12',
							'infobox' => 'If enabled, when a user deletes a post, the slot will be restored and they can create a new post using the same subscription.',
						] ) ?>
					</template>
				</div>
			</div>
		</div>
				<div v-else-if="tab === 'pricingf'" class="x-col-9">
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
				</div>
				<!-- <div class="x-col-12">
					<pre debug>{{ plan }}</pre>
				</div> -->
			</div>
		</div>
	</form>
</div>
