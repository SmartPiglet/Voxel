<?php
/**
 * Admin general settings.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
}

wp_enqueue_script('vue');
wp_enqueue_script('sortable');
wp_enqueue_script('vue-draggable');
wp_enqueue_script('vx:general-settings.js');
?>
<div id="vx-general-settings" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" data-props="<?= esc_attr( wp_json_encode( $props ?? [] ) ) ?>" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="state.submit_config = JSON.stringify( config )">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2 v-if="tab === 'offline_payments'">Offline payments</h2>
				<h2 v-if="tab === 'cart_summary'">Cart summary</h2>
				<h2 v-else-if="tab === 'shipping'">Shipping</h2>
				<h2 v-else-if="tab === 'orders'">Orders</h2>

				<div class="vxh-actions">
					<input type="hidden" name="config" :value="state.submit_config">
					<input type="hidden" name="action" value="voxel_save_product_types_settings">
					<?php wp_nonce_field( 'voxel_save_product_types_settings' ) ?>
					<button type="submit" class="ts-button btn-shadow ts-save-settings">
						<?php \Voxel\svg( 'floppy-disk.svg' ) ?>
						Save changes
					</button>
				</div>
			</div>
		</div>
		<div class="ts-spacer"></div>
		<div class="x-container">
			<div class="x-row">
				<div class="x-col-3">
					<ul class="inner-tabs vertical-tabs">
						<li :class="{'current-item': tab === 'cart_summary'}">
							<a href="#" @click.prevent="setTab('cart_summary')">Cart summary</a>
						</li>
						<li :class="{'current-item': tab === 'offline_payments'}">
							<a href="#" @click.prevent="setTab('offline_payments')">Offline payments</a>
						</li>
						<li :class="{'current-item': tab === 'shipping'}">
							<a href="#" @click.prevent="setTab('shipping')">Shipping</a>
						</li>
						<li :class="{'current-item': tab === 'orders'}">
							<a href="#" @click.prevent="setTab('orders')">Orders</a>
						</li>
					</ul>
				</div>

				<div v-if="tab === 'offline_payments'" class="x-col-9">
					<?php require_once locate_template('templates/backend/product-types/settings/offline-payments.php') ?>
				</div>
				<div v-if="tab === 'cart_summary'" class="x-col-9">
					<?php require_once locate_template('templates/backend/product-types/settings/cart-summary.php') ?>
				</div>
				<div v-if="tab === 'shipping'" class="x-col-9">
					<?php require_once locate_template('templates/backend/product-types/settings/shipping.php') ?>
				</div>
				<div v-if="tab === 'orders'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Orders</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'v-model' => 'config.orders.managed_by',
								'label' => 'Orders are managed by',
								'classes' => 'x-col-12',
								'infobox' => <<<HTML
									Set the party responsible for managing customer orders.
									<br><br>
									This setting does not affect orders placed on Marketplace vendor products, which are always managed by the product vendor.
								HTML,
								'choices' => [
									'platform' => 'Platform',
									'product_author' => 'Product author',
								],
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.orders.direct_messages.enabled',
								'label' => 'Enable direct messages',
								'infobox' => 'Set whether to display the "Message vendor" and "Message customer" actions.',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
				</div>
				<!-- <div class="x-col-12">
					<pre debug>{{ config }}</pre>
				</div> -->
			</div>
		</div>
	</form>
</div>