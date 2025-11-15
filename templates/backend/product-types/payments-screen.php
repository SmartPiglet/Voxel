<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/json" id="vx_payment_config_data">
	<?= wp_specialchars_decode( wp_json_encode( [
		'config' => $config, 'props' => $props,
	] ) ) ?>
</script>

<div id="vx-payment-config" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="state.submit_config = JSON.stringify( config )">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2 v-if="tab === 'general'">Payments</h2>
				<template v-if="tab !== 'general'">
					<a href="#" class="ts-button ts-outline" @click.prevent="$root.setTab('general')"><?php \Voxel\svg( 'arrow-left.svg' ) ?> Back</a>
					<h3>Configure {{ props.providers[subtab]?.label || '' }}</h3>
				</template>
				<div class="vxh-actions">
					<input type="hidden" name="config" :value="state.submit_config">
					<input type="hidden" name="action" value="voxel_save_payment_settings">
					<input type="hidden" name="tab" :value="[tab,subtab].filter(Boolean).join('.')">
					<?php wp_nonce_field( 'voxel_save_payment_settings' ) ?>
					<button type="submit" class="ts-button btn-shadow ts-save-settings">
					<?php \Voxel\svg( 'floppy-disk.svg' ) ?>
						Save changes
					</button>
				</div>
			</div>
		</div>
		<div class="ts-spacer"></div>
		<div class="x-container">


				<template v-if="tab === 'configure' && props.providers[subtab]">
					<provider-settings :provider="props.providers[subtab]">
						<component
							:is="'provider:'+subtab"
							:provider="props.providers[subtab]"
							:settings="config[subtab]"
							:data="props.providers[subtab].component.data"
						></component>
					</provider-settings>
				</template>
				<template v-else>
					<div class="vx-panels">

						<template v-for="provider in props.providers">
							<div class="vx-panel" :class="['provider-' + provider.key, {active: config.provider === provider.key}]" style="height: 100px;">

									<template v-if="provider.key === 'stripe'">
										<div class="panel-image stripe-panel">
											<?php \Voxel\svg( 'stripe-logo.svg' ) ?>
										</div>
									</template>
									<template v-else-if="provider.key === 'paddle'">

										<div class="panel-image paddle-panel">
										<?php \Voxel\svg( 'paddle.svg' ) ?>
										</div>
									</template>


								<div class="panel-info" style="width: 100%;">
									<h3>{{ provider.label }}</h3>
									<ul>
										<li>{{ provider.description }}</li>
									</ul>

								</div>


								<div class="ts-form-group x-col-12 switch-slider" style="width: auto; display: flex; align-items: center; gap: 15px;">

									<div class="onoffswitch">
										<input type="checkbox" class="onoffswitch-checkbox" :id="'switcher-' + provider.key" tabindex="0" :checked="config.provider === provider.key" @change="config.provider = $event.target.checked ? provider.key : null">
										<label class="onoffswitch-label" :for="'switcher-' + provider.key"></label>
									</div>

									<a href="#" @click.prevent="setTab('configure', provider.key)" class="ts-button ts-outline"><?php \Voxel\svg( 'cog.svg' ) ?>Configure</a>

								</div>

							</div>
						</template>

					</div>

				</template>

		</div>
	</form>
</div>


<script type="text/html" id="vx-provider-settings">
	<slot></slot>
</script>

<style type="text/css">
	.vx-payment-service {
		border: 2px solid rgba(255, 255, 255, .2);
		border-radius: 20px;
		padding: 15px 15px;
	}

	.vx-payment-service.active {
		border-color: var(--accent-text);
	}
</style>