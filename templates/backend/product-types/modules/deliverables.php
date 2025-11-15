<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-deliverables-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>
				Downloads

			</h3>
		</div>
		<div class="x-row">
			<div class="x-col-12 ts-form-group">
				<p>
					Share files upon order approval.
					This can be done automatically when the order is completed, or manually by the vendor at any time.
					Downloads are stored securely, without direct link access.
				</p>
			</div>

			<div class="ts-form-group x-col-12 ts-checkbox">
				<label>Delivery methods</label>
				<div class="ts-checkbox-container">
					<label class="container-checkbox">
						Automatic
						<input type="checkbox" v-model="config.delivery_methods.automatic">
						<span class="checkmark"></span>
					</label>

					<label class="container-checkbox">
						Manual
						<input type="checkbox" v-model="config.delivery_methods.manual">
						<span class="checkmark"></span>
					</label>
				</div>
			</div>

			<!-- <?php \Voxel\Utils\Form_Models\Number_Model::render( [
				'v-model' => 'config.download_limit',
				'label' => 'Download limit (per file). Leave empty for unlimited downloads.',
				'classes' => 'x-col-12',
			] ) ?> -->
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Uploads</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Number_Model::render( [
				'v-model' => 'config.uploads.max_size',
				'label' => 'Max file size (kB)',
				'classes' => 'x-col-6',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Number_Model::render( [
				'v-model' => 'config.uploads.max_count',
				'label' => 'Max file count',
				'classes' => 'x-col-6',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
				'v-model' => 'config.uploads.allowed_file_types',
				'label' => 'Allowed file types',
				'classes' => 'x-col-12',
				'choices' => array_combine( get_allowed_mime_types(), get_allowed_mime_types() ),
			] ) ?>
		</div>
	</div>
</script>
