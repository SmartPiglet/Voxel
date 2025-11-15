<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<form @submit.prevent="submitUpdatePassword" class="login-section">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'update_password' ); ?></span>
	</div>
	
	<template v-if="update.password.successful">
		<div class="login-section">
			<div class="ts-form-group">
				<label><?= _x( 'Your password has been updated.', 'auth', 'voxel' ) ?></label>
			</div>
		</div>
	</template>
	<template v-else>
		
		
			<div class="ts-form-group">
				<label><?= _x( 'Enter your current password', 'auth', 'voxel' ) ?></label>
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
					<input class="ts-filter" type="password" v-model="update.password.current" placeholder="<?= esc_attr( _x( 'Current password', 'auth', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>
			
			<div class="ts-form-group">
				<label><?= _x( 'Choose new password', 'auth', 'voxel' ) ?>
					<div class="vx-dialog">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						<div class="vx-dialog-content min-scroll">
							<p><?= _x( 'Password must contain at least 8 characters.', 'auth', 'voxel' ) ?></p>
						</div>
					</div>
				</label>
				
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
					<input class="ts-filter" type="password" v-model="update.password.new" placeholder="<?= esc_attr( _x( 'Your new password', 'auth', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>
			<div class="ts-form-group">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
					<input class="ts-filter" type="password" v-model="update.password.confirm_new" placeholder="<?= esc_attr( _x( 'Confirm password', 'auth', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>

			<div class="ts-form-group">
				<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
					<?= _x( 'Update password', 'auth', 'voxel' ) ?>
				</button>
			</div>
			<div class="login-section">
				<div class="ts-form-group">
					<a href="#" @click.prevent="screen = 'security'" class="ts-btn ts-btn-1 ts-btn-large">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
						<?= __( 'Go back', 'voxel' ) ?>
					</a>
				</div>
			</div>
		
	</template>
</form>
