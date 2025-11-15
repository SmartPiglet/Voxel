<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="login-section">
	
	<div class="ts-login-head">
		<span class="vx-step-title"><?= _x( 'Delete account & data', 'auth', 'voxel' ) ?></span>
	</div>

	
	<div class="ts-form-group">
		<label><?= _x( 'Enter your password', 'auth', 'voxel' ) ?></label>
		<div class="ts-input-icon flexify">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
			<input type="password" v-model="privacy.delete_account.password" placeholder="<?= esc_attr( _x( 'Password', 'auth', 'voxel' ) ) ?>" class="autofocus">
		</div>
	</div>
	<div class="ts-form-group">
		<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" @click.prevent="deleteAccountPermanently()" :class="{'vx-pending': privacy.delete_account.pending}">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			<?= _x( 'Delete my account', 'auth', 'voxel' ) ?>
		</button>
	</div>
	<div class="ts-form-group">
		<a href="#" @click.prevent="screen = 'security_privacy'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			<?= __( 'Go back', 'voxel' ) ?>
		</a>
	</div>
</div>
