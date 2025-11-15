<?php
/**
 * Add taxonomy form in WP Admin.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Create taxonomy</h2>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">
	
	<div class="x-row h-center">
		<div class="x-col-6">
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>">
				<div class="x-row">
					
					<div class="ts-form-group x-col-12">
						<label>Singular name</label>
						<input name="taxonomy[singular_name]" type="text" required>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Plural name</label>
						<input name="taxonomy[plural_name]" type="text" required>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Key</label>
						<input name="taxonomy[key]" type="text" maxlength="32" required><br><br>
						<p>Must not exceed 32 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.</p>
					</div>
					<div class="ts-form-group x-col-12">
						<label>Post Type(s)</label>
						<select name="taxonomy[post_type][]" required multiple style="padding-top: 15px; height: 200px;" class="min-scroll">
							<?php foreach ( \Voxel\Post_Type::get_all() as $post_type ): ?>
								<option value="<?= esc_attr( $post_type->get_key() ) ?>">
									<?= esc_html( $post_type->get_label() ) ?>
								</option>
							<?php endforeach ?>
						</select>
						<br><br>
						<p>To select multiple options hold down CTRL (Win) or command button (Mac)</p>
					</div>

					<div class="x-col-12 ts-form-group">
						<input type="hidden" name="action" value="voxel_create_taxonomy">
						<?php wp_nonce_field( 'voxel_manage_taxonomies' ) ?>
						<button type="submit" class="ts-button ts-save-settings full-width">Create taxonomy</button>
					</div>
				</div>
			</form>
		</div>

	</div>
</div>