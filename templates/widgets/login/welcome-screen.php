<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="login-section">
	<div class="ts-welcome-message ts-form-group">
		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_welcome_ico') ) ?: \Voxel\svg( 'happy-2.svg' ) ?>
		<h2><?php echo $this->get_settings_for_display( 'auth_welc_title' ); ?></h2>
	</div>
	<div class="ts-form-group">
		<a :href="config.editProfileUrl" class="ts-btn ts-btn-2 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
			<?= _x( 'Complete profile', 'auth', 'voxel' ) ?>
		</a>
	</div>
	<div class="ts-form-group">
		<a href="<?= esc_url( $config['redirectUrl'] ) ?>" class="ts-btn ts-btn-1 ts-btn-large">
			<?= _x( 'Do it later', 'auth', 'voxel' ) ?>
		</a>
	</div>
</div>
