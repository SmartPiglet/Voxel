<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div id="vx-edit-role" v-cloak data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="prepareSubmission">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2><?= $role->get_label() ?>
				</h2>
				<div class="">
					<input type="hidden" name="role_config" :value="submit_config">
					<input type="hidden" name="action" value="voxel_update_membership_role">
					<?php wp_nonce_field( 'voxel_manage_membership_roles' ) ?>
					<button v-if="config.settings.key !== 'subscriber'" type="button" name="remove_role" value="yes" class="ts-button ts-transparent"
						onclick="return confirm('Are you sure?') ? ( this.type = 'submit' ) && true : false">
						Stop managing with Voxel
					</button>
					&nbsp;&nbsp;
					<button type="submit" href="#" class="ts-button ts-save-settings btn-shadow">
						<i class="las la-save icon-sm"></i>
						Save changes
					</button>
				</div>
			</div>
		</div>
		<div class="ts-spacer"></div>
		<div class="x-container">
			<div class="x-row">
				<div class="x-col-3">
					<ul class="inner-tabs vertical-tabs">
						<li :class="{'current-item': tab === 'general'}">
							<a href="#" @click.prevent="setTab('general')">General</a>
						</li>
						<li :class="{'current-item': tab === 'fields'}">
							<a href="#" @click.prevent="setTab('fields')">Fields</a>
						</li>
						<?php if ( !! \Voxel\get('settings.addons.paid_memberships.enabled') ): ?>
							<li :class="{'current-item': tab === 'paid_members'}">
								<a href="#" @click.prevent="setTab('paid_members')">Paid members</a>
							</li>
						<?php endif ?>
					</ul>
				</div>

				<div v-if="tab === 'general'" class="x-col-9">
					<?php require_once locate_template( 'templates/backend/roles/components/role-general.php' ) ?>
				</div>
				<div v-else-if="tab === 'fields'" class="x-col-9">
					<?php require_once locate_template( 'templates/backend/roles/components/role-registration-fields.php' ) ?>
				</div>
				<?php if ( !! \Voxel\get('settings.addons.paid_memberships.enabled') ): ?>
					<div v-else-if="tab === 'paid_members'" class="x-col-9">
						<?php require_once locate_template( 'templates/backend/roles/components/role-plans.php' ) ?>
					</div>
				<?php endif ?>
			</div>
		</div>
	</form>
</div>

<?php require_once locate_template( 'templates/backend/roles/components/role-field-modal.php' ) ?>