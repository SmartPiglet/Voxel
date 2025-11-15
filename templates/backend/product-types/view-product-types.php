<?php
/**
 * Template for managing product types in wp-admin.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Product Types</h2>
		<a href="<?= esc_url( $add_type_url ) ?>" class="ts-button ts-save-settings btn-shadow">
			<i class="las la-plus icon-sm"></i>
			Create product type
		</a>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">
	<div class="vx-panels">
		<?php foreach ( $product_types as $product_type ): ?>
			<div class="vx-panel">

					<div class="panel-icon">
					<?php \Voxel\svg( 'shopping-bag.svg' ) ?>
					</div>
					<div class="panel-info">
						<h3><?= $product_type->get_label() ?></h3>
						<ul>
							<li><?= $product_type->get_key() ?></li>
						</ul>
					</div>

					<a href="<?= esc_url( $product_type->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
						Edit with Voxel<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">
					</a>

			</div>
		<?php endforeach ?>
		<?php if ( empty( $product_types ) ): ?>

				<p class="no-post-types">No product types created yet.</p>

		<?php endif ?>
	</div>
</div>
