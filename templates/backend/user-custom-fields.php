<h3>Membership</h3>
<table class="form-table vx-edit-profile">
	<tr>
		<th><label for="address">Membership plan</label></th>
		<td>
			<a href="<?= esc_url( admin_url( 'admin.php?page=voxel-paid-members&customer='.$user->get_id() ) ) ?>">
				<?= esc_html( $plan->get_label() ) ?>
			</a>
		</td>
	</tr>
	<tr>
		<th><label for="address">Profile ID</label></th>
		<td>
			<a href="<?= esc_url( get_edit_post_link( $profile->get_id() ) ) ?>">
				<?= sprintf( '#%d', $profile->get_id() ) ?>
			</a>
		</td>
	</tr>
</table>
