<?php
/**
 * Add post type form in WP Admin.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Create a post type</h2>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">
	
	<div class="x-row">
		<div class="x-col-4">
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>">
				
				
				<div class="x-row">
					<div class="ts-form-group x-col-12">
						<label>Singular name</label>
						<input name="post_type[singular_name]" type="text" autocomplete="off" required>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Plural name</label>
						<input name="post_type[plural_name]" type="text" autocomplete="off" required>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Key</label>
						<input name="post_type[key]" type="text" autocomplete="off" maxlength="20" required><br><br>
						<p>Must not exceed 20 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.</p>
					</div>
					<div class="ts-form-group x-col-12">
						<input type="hidden" name="action" value="voxel_create_post_type">
						<?php wp_nonce_field( 'voxel_manage_post_types' ) ?>
						<button type="submit" class="ts-button ts-save-settings full-width">Create post type</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>