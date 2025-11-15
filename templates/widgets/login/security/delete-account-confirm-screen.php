<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="login-section">
	
	<div class="ts-form-group">
		<label><?= _x( 'This will irreversibly delete your account and all associated data. Are you sure you want to proceed?', 'auth', 'voxel' ) ?></label>
		<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" @click.prevent="deleteAccountPermanently(true)" :class="{'vx-pending': privacy.delete_account.pending}">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			<?= _x( 'Yes, permanently delete my account', 'auth', 'voxel' ) ?>
		</button>
	</div>
	<div class="ts-form-group">
		<a href="#" @click.prevent="screen = 'security_privacy'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			<?= _x( 'Cancel account deletion', 'auth', 'voxel' ) ?>
		</a>
	</div>
</div>
