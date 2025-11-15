<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Create a listing plan</h2>
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
						<input name="plan[label]" type="text" autocomplete="off" required placeholder="e.g. Starter plan">
					</div>
					<div class="ts-form-group x-col-12">
						<label>Key</label>
						<input name="plan[key]" type="text" autocomplete="off" required placeholder="e.g. starter">
					</div>
					<div class="ts-form-group x-col-12">
						<input type="hidden" name="action" value="voxel_create_listing_plan">
						<?php wp_nonce_field( 'voxel_manage_listing_plans' ) ?>
						<button type="submit" class="ts-button ts-create-settings full-width">Create plan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>