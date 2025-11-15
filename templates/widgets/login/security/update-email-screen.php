<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>


<div class="login-section">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'update_email' ); ?></span>
	</div>
	<form @submit.prevent="submitUpdateEmail">
		<template v-if="update.email.state === 'confirmed'">
			<div class="ts-form-group">
				<label><?= _x( 'Your email address has been updated.', 'auth', 'voxel' ) ?></label>
			</div>
		</template>
		<template v-else>
			
			<?php if ( is_user_logged_in() ): ?>
				<div class="ts-form-group">
					<label><?= _x( 'Your current email address', 'auth', 'voxel' ) ?></label>
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
						<input class="ts-filter" type="email" disabled value="<?= esc_attr( \Voxel\current_user()->get_email() ) ?>" class="autofocus">
					</div>
				</div>
			<?php endif ?>
			
			<div class="ts-form-group">
				<label><?= _x( 'Enter new email address', 'auth', 'voxel' ) ?></label>
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
					<input class="ts-filter" type="email" v-model="update.email.new" placeholder="<?= esc_attr( _x( 'Enter email address', 'auth', 'voxel' ) ) ?>" class="autofocus" :disabled="update.email.state !== 'send_code'">
				</div>
			</div>
			<template v-if="update.email.state === 'verify_code'">
				<div class="ts-form-group">
					<label><?= _x( 'Confirmation code', 'auth', 'voxel' ) ?></label>
					<small><?= _x( 'Please type the confirmation code which sent to your new email', 'auth', 'voxel' ) ?></small>
				</div>
				<div class="ts-form-group">
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
						<input class="ts-filter" type="text" v-model="update.email.code" placeholder="<?= esc_attr( _x( 'Confirmation code', 'auth', 'voxel' ) ) ?>" class="autofocus">
					</div>
				</div>
			</template>
			<div class="ts-form-group">
				<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
					<template v-if="update.email.state === 'send_code'">
						<?= _x( 'Send confirmation code', 'auth', 'voxel' ) ?>
					</template>
					<template v-else>
						<?= _x( 'Update email address', 'auth', 'voxel' ) ?>
					</template>
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
</div>
