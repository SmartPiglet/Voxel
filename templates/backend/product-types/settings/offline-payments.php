<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.offline_payments.order_approval',
			'label' => 'Order approval',
			'classes' => 'x-col-12',
			'choices' => [
				'automatic' => 'Automatic: Order is approved immediately',
				'manual' => 'Manual: Order is approved manually by vendor',
			],
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
			'v-model' => 'config.offline_payments.notes_to_customer.enabled',
			'label' => 'Share notes with customer after order is placed',
			'classes' => 'x-col-12',
		] ) ?>

		<div v-if="config.offline_payments.notes_to_customer.enabled" class="ts-form-group x-col-12">
			<label>Notes to share</label>
			<textarea
				v-model="config.offline_payments.notes_to_customer.content"
				readonly
				@click.prevent="editOfflinePaymentNotes"
				style="height: 240px;"
			></textarea>
		</div>
	</div>
</div>
