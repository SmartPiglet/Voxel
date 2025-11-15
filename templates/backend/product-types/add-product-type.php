<?php
/**
 * Add product type form in WP Admin.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Create product type</h2>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">

	<div class="x-row">
		<div class="x-col-4">
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>">
				<div class="x-row">
					<div class="ts-form-group x-col-12">
						<label>Label</label>
						<input name="product_type[label]" type="text" autocomplete="off" required>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Key</label>
						<input name="product_type[key]" type="text" autocomplete="off" maxlength="20" required><br>
					</div>
					<div class="ts-form-group x-col-12">
						<input type="hidden" name="action" value="voxel_create_product_type">
						<?php wp_nonce_field( 'voxel_manage_product_types' ) ?>
						<button type="submit" class="ts-button ts-save-settings full-width">Create product type</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>