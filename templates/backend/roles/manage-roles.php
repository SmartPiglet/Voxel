<?php
/**
 * Admin membership settings.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="sticky-top">
	<div class="vx-head x-container">
		<h2>User roles
		</h2>
		<div class="cpt-header-buttons ts-col-1-3">
			<a href="<?= esc_url( admin_url('admin.php?page=voxel-roles&action=create-role') ) ?>" class="ts-button ts-save-settings btn-shadow">
					<i class="las la-plus icon-sm"></i>
					Create role
			</a>
		</div>
	</div>
</div>

<div class="ts-spacer"></div>
<div class="x-container">
	<div class="vx-panels">
		
		<?php foreach ( $voxel_roles as $role ): ?>
			<div class="vx-panel">
				
				<div class="panel-icon">
					<?php \Voxel\svg( 'user.svg' ) ?>
				</div>
				<div class="panel-info">
					<h3><?= $role->get_label() ?></h3>
					<ul>
						<li><?= $role->get_key() ?></li>
					</ul>
				</div>
				<a href="<?= esc_url( $role->get_edit_link() ) ?>" class="ts-button edit-voxel ts-outline">
					Edit with Voxel
						<img src="<?php echo esc_url( \Voxel\get_image('post-types/logo.svg') ) ?>">
				</a>
				
			</div>
		<?php endforeach ?>
		
	</div>
</div>
