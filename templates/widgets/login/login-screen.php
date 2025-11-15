<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<form @submit.prevent="submitLogin">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'auth_title' ); ?></span>
	</div>

	<?php if ( \Voxel\get( 'settings.auth.google.enabled' ) ): ?>
		<div class="login-section">

			<div class="or-group">

				<span class="or-text"><?= _x( 'Social connect', 'auth', 'voxel' ) ?></span>
				<div class="or-line"></div>
			</div>
			<div class="ts-form-group ts-social-connect">
				<a href="<?= esc_url( \Voxel\get_google_auth_link() ) ?>" class="ts-btn  ts-google-btn ts-btn-large ts-btn-1">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_google_ico') ) ?: \Voxel\svg( 'google.svg' ) ?>
					<?= _x( 'Sign in with Google', 'auth', 'voxel' ) ?>
				</a>
			</div>

		</div>
	<?php endif ?>

	<div class="login-section">
		<div class="or-group">
			<span class="or-text"><?= _x( 'Enter details', 'auth', 'voxel' ) ?></span>
			<div class="or-line"></div>
		</div>
		<div class="ts-form-group">
			<div class="ts-input-icon flexify">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
				<input class="ts-filter autofocus" type="text" v-model="login.username" placeholder="<?= esc_attr( _x( 'Username', 'auth', 'voxel' ) ) ?>" name="login_username">
			</div>
		</div>
		<div class="ts-form-group ts-password-field">
			<div class="ts-input-icon flexify">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
				<input class="ts-filter autofocus" type="password" v-model="login.password" ref="loginPassword" placeholder="<?= esc_attr( _x( 'Password', 'auth', 'voxel' ) ) ?>" name="login_password">
			</div>
		</div>
		<div class="ts-form-group">
			<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
				<?= _x( 'Log in', 'auth', 'voxel' ) ?>
			</button>
		</div>
		<div class="ts-form-group">
			<p class="field-info">
				<?= _x( 'Forgot password?', 'auth', 'voxel' ) ?>
				<a href="#" @click.prevent="screen = 'recover'"><?= _x( 'Recover account', 'auth', 'voxel' ) ?></a>
			</p>
		</div>
	</div>
	<div v-if="canRegister()" class="login-section">
		<div class="or-group">

			<span class="or-text"><?= _x( 'Don\'t have an account?', 'auth', 'voxel' ) ?></span>
			<div class="or-line"></div>
		</div>
		<div class="ts-form-group">

			<a class="ts-btn ts-btn-1 ts-btn-large" href="#" @click.prevent="screen = 'register'"><?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_reg_ico') ) ?: \Voxel\svg( 'user.svg' ) ?><?= _x( 'Sign up', 'auth', 'voxel' ) ?></a>

		</div>

	</div>
</form>
