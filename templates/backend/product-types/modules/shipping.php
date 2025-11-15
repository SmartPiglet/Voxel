<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-shipping-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Shipping</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.required',
				'label' => 'Is shipping required for products of this type?',
				'classes' => 'x-col-12',
			] ) ?>

			<div class="ts-form-group x-col-12">
				<label>
					Default shipping class
					<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-product-types-settings&tab=shipping' ) ) ?>" target="_blank" style="float: right;">Configure shipping classes</a>
				</label>
				<select v-model="config.default_shipping_class">
					<option value="">None</option>
					<?php foreach ( \Voxel\Product_Types\Shipping\Shipping_Class::get_all() as $shipping_class ): ?>
						<option value="<?= esc_attr( $shipping_class->get_key() ) ?>"><?= esc_html( $shipping_class->get_label() ) ?></option>
					<?php endforeach ?>
				</select>
			</div>

			<!-- <div class="x-col-12">
				<pre debug>{{ config }}</pre>
			</div> -->
		</div>
	</div>
</script>
