<?php
/**
 * Template for managing active post types in wp-admin.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
} ?>



<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Managed post types</h2>
		<a href="<?php echo esc_url( $add_type_url ) ?>" class="ts-button ts-save-settings btn-shadow"><i class="las la-plus icon-sm"></i>Create post type</a>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">

	<div class="vx-panels">

		<?php foreach ( $voxel_types as $post_type ): ?>

				<div class="vx-panel">
					<div class="panel-icon">
						<?php echo $post_type->get_icon() ? \Voxel\render_icon( $post_type->get_icon() ) : \Voxel\svg( 'cube.svg' ); ?>
					</div>

					<div class="panel-info">
						<h3><?php echo $post_type->get_label() ?></h3>
						<ul>
							<li><?php echo $post_type->get_key() ?></li>
							<!-- <li>Built-in: <?php echo $post_type->is_built_in() ? 'yes' : 'no' ?></li> -->
							<!--  <li>Description: <?php echo $post_type->get_description() ?: '<em>(empty)</em>' ?></li> -->
						</ul>
					</div>
					<a href="<?php echo esc_url( $post_type->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
						Edit with Voxel
						<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">

					</a>
				</div>


		<?php endforeach ?>
		<?php if ( empty( $voxel_types ) ): ?>

			<p class="no-post-types">You are not managing any post types with Voxel</p>

		<?php endif ?>

	</div>


</div>
<div class="ts-spacer"></div>
<div class="x-container h-center">
	<a href="#" class="ts-button ts-transparent ts-btn-small" onclick="event.preventDefault(); document.getElementById('view-other-types').classList.toggle('hide')">
		<i class="las la-arrow-down icon-sm"></i>
		Show other detected post types
	</a>
</div>
<div class="x-container hide" id="view-other-types">

	<div class="x-container">
		<div class="vx-head">
			<h2>Other detected post types</h2>

		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="vx-panels">

			<?php foreach ( $other_types as $post_type ): ?>
					<div class="vx-panel">
						<div class="panel-icon">
							<?php echo $post_type->get_icon() ? \Voxel\render_icon( $post_type->get_icon() ) : \Voxel\svg( 'cube.svg' ); ?>
						</div>

						<div class="panel-info">
							<h3><?php echo $post_type->get_label() ?></h3>
							<ul>
								<li><?php echo $post_type->get_key() ?></li>
								<!-- <li>Built-in: <?php echo $post_type->is_built_in() ? 'yes' : 'no' ?></li> -->
								<!--  <li>Description: <?php echo $post_type->get_description() ?: '<em>(empty)</em>' ?></li> -->
							</ul>
						</div>
						<a href="<?php echo esc_url( $post_type->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
							Manage with Voxel
							<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">

						</a>
					</div>



			<?php endforeach ?>

	</div>
</div>
<div class="ts-spacer"></div>
