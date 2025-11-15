<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>Listing plans</h2>
		<div class="cpt-header-buttons ts-col-1-3">
			<a href="<?= esc_url( $add_plan_url ) ?>" class="ts-button ts-save-settings btn-shadow">
				<i class="las la-plus icon-sm"></i>
				Create plan
			</a>
		</div>
	</div>
</div>
<div class="ts-spacer"></div>
<div class="x-container">
	<div class="vx-panels">
		<?php if ( ! empty( $plans ) ): ?>
			<?php foreach ( $plans as $plan ): ?>
				<div class="vx-panel">
					<div class="panel-icon">
						<?php \Voxel\svg( 'box-dollar.svg' ) ?>
					</div>
					<div class="panel-info">
						<h3><?= $plan->get_label() ?></h3>
						<ul>
							<li><?= $plan->get_key() ?></li>
						</ul>
					</div>
					<a href="<?= esc_url( $plan->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
						Edit with Voxel
						<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">
					</a>
				</div>
			<?php endforeach ?>
		<?php else: ?>
			<div class="ts-form-group">
				<p>No plans added yet.</p>
			</div>
		<?php endif ?>
	</div>
</div>
