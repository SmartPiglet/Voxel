<?php
/**
 * Edit product type fields in WP Admin.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
}

$modules = $this->get_modules();
foreach ( $modules as $module_key => $module ) {
	if ( $module['settings_template'] ) {
		require_once $module['settings_template'];
	}
}

require_once locate_template('templates/backend/product-types/product-fields/product-fields.php');
require_once locate_template('templates/backend/product-types/partials/rate-list-component.php');
require_once locate_template('templates/backend/post-types/components/select-field-choices.php');
?>
<div id="voxel-edit-product-type" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="prepareSubmission">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2><?= $product_type->get_label() ?></h2>
				<div>
					<input type="hidden" name="product_type_config" :value="submit_config">
					<input type="hidden" name="action" value="voxel_save_product_type_settings">
					<?php wp_nonce_field( 'voxel_save_product_type_settings' ) ?>
					<button type="button" name="remove_product_type" value="yes" class="ts-button ts-transparent"
						onclick="return confirm('Are you sure?') ? ( this.type = 'submit' ) && true : false">
						Delete
					</button>
					&nbsp;&nbsp;
					<button type="submit" class="ts-button ts-save-settings btn-shadow">
						<?php \Voxel\svg( 'floppy-disk.svg' ) ?>
						Save changes
					</button>
				</div>
			</div>
		</div>

		<div class="x-container">
			<div class="ts-tab-content ts-container">
				<!-- <div class="ts-theme-options-nav">
					<div class="ts-nav">
						<div class="ts-nav-item" :class="{'current-item': tab === 'general'}">
							<a href="#" @click.prevent="setTab('general', 'mode')">
								<span class="item-icon all-center"><i class="las la-home"></i></span>
								<span class="item-name">General</span>
							</a>
						</div>
						<div class="ts-nav-item" :class="{'current-item': tab === 'form'}">
							<a href="#" @click.prevent="setTab('form', 'base')">
								<span class="item-icon all-center"><i class="las la-home"></i></span>
								<span class="item-name">Product form</span>
							</a>
						</div>
					</div>
				</div> -->
				<div class="ts-spacer"></div>
				<div v-if="tab === 'general'" class="inner-tab x-row">
					<div class="x-col-3">
						<ul class="inner-tabs vertical-tabs">
							<li :class="{'current-item': $root.subtab === 'mode'}">
								<a href="#" @click.prevent="$root.setTab('general', 'mode')">General</a>
							</li>
							<template v-for="module, module_key in options.modules">
								<li
									v-if="isModuleAvailable(module) && ( isModuleRequired(module) || config.modules[module_key].enabled ) && module.settings_template"
									:class="{'current-item': $root.subtab === module_key}"
								>
									<a href="#" @click.prevent="$root.setTab('general', module_key)">
										{{ module.label }}
									</a>
								</li>
							</template>
							<li :class="{'current-item': $root.subtab === 'labels'}">
								<a href="#" @click.prevent="$root.setTab('general', 'labels')">Labels</a>
							</li>
							<!-- <li style="opacity: .2;" :class="{'current-item': $root.subtab === 'modules'}">
								<a href="#" @click.prevent="$root.setTab('general', 'modules')">DEV: Modules</a>
							</li> -->
						</ul>
					</div>
					<div class="x-col-9">
						<template v-if="subtab === 'mode'">
							<div class="ts-group">
								<div class="ts-group-head"><h3>Mode</h3></div>
								<div class="x-row">
									<div class="ts-form-group x-col-12">
										<label>Select product mode</label>
										<select v-model="config.settings.product_mode">
											<option value="regular">Regular</option>
											<option value="variable">Variable</option>
											<option value="booking">Booking</option>
										</select>
									</div>

									<div class="ts-form-group x-col-12">
										<label>Payment mode</label>
										<select v-model="config.settings.payments.mode">
											<option value="payment">Single payment: Users pay once for products of this type</option>
											<option v-if="config.settings.product_mode === 'regular'" value="subscription">Subscription: Users pay on a recurring interval for products of this type</option>
											<option value="offline">Offline payment: Payments are handled off-site</option>
										</select>
									</div>

									<template v-if="false && config.settings.payments.mode === 'offline'">
										<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
											'v-model' => 'config.settings.payments.offline.require_approval',
											'label' => 'Orders require vendor approval',
											'classes' => 'x-col-12',
										] ) ?>
									</template>
								</div>
							</div>
							<div class="ts-group">
								<div class="ts-group-head"><h3>Modules</h3></div>
								<div class="x-row">
									<template v-for="module, module_key in options.modules">
										<div
											v-if="isModuleAvailable(module) && !isModuleRequired(module) && module.display_mode !== 'custom' && shouldListModule(module)"
											class="ts-form-group x-col-6 hz-group"
											:class="{'vx-disabled': module.feature_status === 'coming_soon'}"
										>
											<div class="onoffswitch">
												<input
													v-model="config.modules[module_key].enabled"
													type="checkbox"
													class="onoffswitch-checkbox"
													:id="'enable-module-'+module_key"
													tabindex="0"
												>
												<label class="onoffswitch-label" :for="'enable-module-'+module_key"></label>
											</div>
											<div>
												<label :for="'enable-module-'+module_key">
													{{ module.label }}
													<template v-if="module.feature_status === 'coming_soon'">
														(Coming soon)
													</template>
													<template v-if="module.feature_status === 'beta'">
														<small>(BETA)</small>
													</template>
												</label>
												<small>{{ module.description }}</small>
											</div>
										</div>
									</template>
								</div>
							</div>
						</template>
						<template v-else-if="subtab === 'labels'">
							<div class="ts-group">
								<div class="x-row">
									<div class="ts-form-group x-col-6">
										<label>Label</label>
										<input type="text" v-model="config.settings.label">
									</div>
									<div class="ts-form-group x-col-6">
										<label>Key</label>
										<input type="text" v-model="config.settings.key" maxlength="20" required disabled>
									</div>
								</div>
							</div>

							<product-fields></product-fields>
						</template>
						<template v-else-if="subtab === 'modules'">
							<div class="ts-group">

								<div class="x-row">
									<?php foreach ( $modules as $module_key => $module ): ?>
										<div class="ts-form-group x-col-6 hz-group">
											<div class="onoffswitch">
												<input
													v-model="config.modules[<?= esc_attr( wp_json_encode( $module_key ) ) ?>].enabled"
													type="checkbox"
													class="onoffswitch-checkbox"
													id="enable-module-<?= esc_attr( $module_key ) ?>"
													tabindex="0"
												>
												<label class="onoffswitch-label" for="enable-module-<?= esc_attr( $module_key ) ?>"></label>
											</div>
											<div>
												<label for="enable-module-<?= esc_attr( $module_key ) ?>"><?= $module['label'] ?></label>
												<small><?= $module['description'] ?></small>
											</div>
										</div>
									<?php endforeach ?>
								</div>
							</div>
						</template>

						<?php foreach ( $modules as $module_key => $module ): ?>
							<?php if ( $module['settings_template'] ): ?>
								<?= \Voxel\replace_vars(
									'<template v-else-if="subtab === @escaped_module_key">
										<@component_key :config="config.modules[@escaped_module_key]"></@component_key>
									</template>',
									[
										'@component_key' => $module['component_key'],
										'@escaped_module_key' => esc_attr( wp_json_encode( $module_key ) ),
									]
								) ?>
							<?php endif ?>
						<?php endforeach ?>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
