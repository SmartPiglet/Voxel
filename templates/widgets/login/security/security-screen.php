<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>


<div class="login-section">

	<div class="ts-form-group">
		
		<a href="<?= esc_url( home_url('/') ) ?>" @click.prevent="screen = 'security_update_email'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
			<?= _x( 'Update email address', 'auth', 'voxel' ) ?>
		</a>
	</div>
	<div class="ts-form-group">
		<a href="<?= esc_url( home_url('/') ) ?>" @click.prevent="screen = 'security_update_password'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
			<?= _x( 'Update password', 'auth', 'voxel' ) ?>
		</a>
	</div>
	<div class="ts-form-group">
		<a href="<?= esc_url( home_url('/') ) ?>" @click.prevent="screen = 'security_privacy'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_privacy') ) ?: \Voxel\svg( 'shield.svg' ) ?>
			<?= _x( 'Privacy', 'auth', 'voxel' ) ?>
		</a>
	</div>
	<div class="ts-form-group">
		<a href="<?= esc_url( \Voxel\get_logout_url() ) ?>" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_logout') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
			<?= _x( 'Log out', 'auth', 'voxel' ) ?>
		</a>
	</div>
</div>
