<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<form @submit.prevent="submitRegister">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'auth_reg_title' ); ?></span>
	</div>

	<div class="login-section" v-if="Object.keys(config.registration.roles).length >= 2">
		<div class="ts-form-group">
			<label><?= _x( 'Join the platform as:', 'auth', 'voxel' ) ?></label>
			<div class="role-selection-hold">
				<div class="role-selection">
					<template v-for="role in config.registration.roles">
						<a @click.prevent="activeRole = role" :class="{'selected-role': activeRole === role}" href="#">{{ role.label }}</a>
					</template>
				</div>
			</div>
		</div>

	</div>

	<template v-if="activeRole">
		<?php if ( \Voxel\get( 'settings.auth.google.enabled' ) ): ?>
			<div v-if="activeRole.allow_social_login" class="login-section">
				<div class="or-group">
					<span class="or-text"><?= _x( 'Social connect', 'auth', 'voxel' ) ?></span>
					<div class="or-line"></div>
				</div>
				<div class="ts-form-group ts-social-connect">
					<!-- <label><?= _x( 'Connect with social media', 'auth', 'voxel' ) ?></label> -->
					<a :href="activeRole.social_login.google_register" class="ts-btn  ts-google-btn ts-btn-large ts-btn-1">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_google_ico') ) ?: \Voxel\svg( 'google.svg' ) ?>
						<?= _x( 'Sign in with Google', 'auth', 'voxel' ) ?>
					</a>
				</div>

			</div>
		<?php endif ?>

		<div class="login-section">
			<div class="or-group">
				<span class="or-text"><?= _x( 'Or enter your details', 'auth', 'voxel' ) ?></span>
				<div class="or-line"></div>
			</div>
			<template v-for="field in activeRole.fields">
				<template v-if="field._is_auth_field">
					<template v-if="field.key === 'voxel:auth-username'">
						<div class="ts-form-group">
							<label>
								{{ field.label }}


								<div class="vx-dialog" v-if="field.description">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
									<div class="vx-dialog-content min-scroll">
										<p>{{ field.description }}</p>
									</div>
								</div>
							</label>
							<div class="ts-input-icon flexify">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
								<input class="ts-filter" type="text" v-model="field.value" :placeholder="field.placeholder">
							</div>

						</div>
					</template>
					<template v-if="field.key === 'voxel:auth-email'">
						<div class="ts-form-group">
							<label>
								{{ field.label }}
								<div class="vx-dialog" v-if="field.description">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
									<div class="vx-dialog-content min-scroll">
										<p>{{ field.description }}</p>
									</div>
								</div>
							</label>
							<div class="ts-input-icon flexify">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
								<input class="ts-filter" type="email" v-model="field.value" :placeholder="field.placeholder" autocomplete="email">
							</div>
						</div>
					</template>
					<template v-if="field.key === 'voxel:auth-password'">
						<div class="ts-form-group">
							<label>
								{{ field.label }}
								<div class="vx-dialog" v-if="field.description">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
									<div class="vx-dialog-content min-scroll">
										<p>{{ field.description }}</p>
									</div>
								</div>
							</label>
							<div class="ts-input-icon flexify">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_pass_ico') ) ?: \Voxel\svg( 'lock-alt.svg' ) ?>
								<input class="ts-filter" type="password" v-model="field.value" :placeholder="field.placeholder" autocomplete="new-password">
							</div>
						</div>
					</template>
				</template>
				<template v-else>
					<template v-if="conditionsPass(field)">
						<template v-if="field.type === 'text' || field.type === 'title' || field.type === 'profile-name'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<input class="ts-filter" type="text" v-model="field.value" :placeholder="field.props.placeholder">
							</div>
						</template>
						<template v-if="field.type === 'textarea' || field.type === 'description'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<textarea class="ts-filter" v-model="field.value" :placeholder="field.props.placeholder"></textarea>
							</div>
						</template>
						<template v-if="field.type === 'number'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<input class="ts-filter" type="number" v-model="field.value" :placeholder="field.props.placeholder">
							</div>
						</template>
						<template v-if="field.type === 'switcher'">
							<div class="ts-form-group switcher-label">
								<label>
									<div class="switch-slider">
										<div class="onoffswitch">
											<input  v-model="field.value" :id="'_switcher:'+field.key" type="checkbox" class="onoffswitch-checkbox">
											<label class="onoffswitch-label" :for="'_switcher:'+field.key"></label>
										</div>
									</div>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>

							</div>
						</template>
						<template v-if="field.type === 'phone'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<div class="ts-input-icon flexify">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_phone_icon') ) ?: \Voxel\svg( 'phone.svg' ) ?>
									<input class="ts-filter" type="tel" v-model="field.value" :placeholder="field.props.placeholder">
								</div>
							</div>
						</template>
						<template v-if="field.type === 'url'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<div class="ts-input-icon flexify">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_link_icon') ) ?: \Voxel\svg( 'link-alt.svg' ) ?>
									<input class="ts-filter" type="url" v-model="field.value" :placeholder="field.props.placeholder">
								</div>

							</div>
						</template>
						<template v-if="field.type === 'email'">
							<div class="ts-form-group">
								<label>
									{{ field.label }}
									<span v-if="!field.required" class="is-required"><?= _x( 'Optional', 'auth', 'voxel' ) ?></span>
									<div class="vx-dialog" v-if="field.description">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
										<div class="vx-dialog-content min-scroll">
											<p>{{ field.description }}</p>
										</div>
									</div>
								</label>
								<div class="ts-input-icon flexify">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
									<input class="ts-filter" type="email" v-model="field.value" :placeholder="field.props.placeholder">
								</div>
							</div>
						</template>
						<template v-if="field.type === 'date'">
							<date-field :key="activeRole.key+':'+field.id" :field="field"></date-field>
						</template>
						<template v-if="field.type === 'taxonomy'">
							<taxonomy-field :key="activeRole.key+':'+field.id" :field="field"></taxonomy-field>
						</template>
						<template v-if="field.type === 'file' || field.type === 'image' || field.type === 'profile-avatar'">
							<file-field :key="activeRole.key+':'+field.id" :field="field"></file-field>
						</template>
						<template v-if="field.type === 'select'">
							<select-field :key="activeRole.key+':'+field.id" :field="field"></select-field>
						</template>
						<template v-if="field.type === 'multiselect'">
							<multiselect-field :key="activeRole.key+':'+field.id" :field="field"></multiselect-field>
						</template>
					</template>
				</template>
			</template>
		</div>
		<div class="login-section">

			<div class="ts-form-group tos-group">
				<div class="ts-checkbox-container">
					<label class="container-checkbox">
						<input type="checkbox" v-model="register.terms_agreed" tabindex="0">
						<span class="checkmark"></span>
					</label>
				</div>
				<p class="field-info">
					<?= \Voxel\replace_vars( _x( 'I agree to the <a:terms>Terms and Conditions</a> and <a:privacy>Privacy Policy</a>', 'auth', 'voxel' ), [
						'<a:terms>' => '<a target="_blank" href="'.esc_url( get_permalink( \Voxel\get( 'templates.terms' ) ) ?: home_url('/') ).'">',
						'<a:privacy>' => '<a target="_blank" href="'.esc_url( get_permalink( \Voxel\get( 'templates.privacy_policy' ) ) ?: home_url('/') ).'">'
					] ) ?>
				</p>
			</div>
		</div>
		<div class="login-section">
			<div class="ts-form-group">
				<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
					<?= _x( 'Sign up', 'auth', 'voxel' ) ?>
				</button>
			</div>
		</div>
		<div class="login-section">
			<div class="ts-form-group">
				<p class="field-info">
					<?= _x( 'Have an account already?', 'auth', 'voxel' ) ?>
					<a href="#" @click.prevent="screen = 'login'"><?= _x( 'Log in instead', 'auth', 'voxel' ) ?></a>
				</p>
			</div>
		</div>
	</template>
</form>
