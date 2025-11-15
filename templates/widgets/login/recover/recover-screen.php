<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<form @submit.prevent="submitRecover">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'reset_pass_title' ); ?></span>
	</div>
	<div class="login-section">

		<div class="ts-form-group">
			<label><?= _x( 'Your email', 'auth', 'voxel' ) ?></label>
			<div class="ts-input-icon flexify">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
				<input class="ts-filter autofocus" type="email" v-model="recovery.email" placeholder="<?= esc_attr( _x( 'Your account email', 'auth', 'voxel' ) ) ?>" >
			</div>
		</div>

		<div class="ts-form-group">
			<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
				<?= _x( 'Reset password', 'auth', 'voxel' ) ?>
			</button>
		</div>
	</div>
	<div class="login-section">
		<div class="ts-form-group">
			<a href="#" @click.prevent="screen = 'login'" class="ts-btn ts-btn-1 ts-btn-large">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
				<?= __( 'Go back', 'voxel' ) ?>
			</a>
		</div>
	</div>
</form>
