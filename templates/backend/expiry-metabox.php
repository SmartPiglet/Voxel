<div id="vx-post-expiry" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div class="expiry-options">
		<div class="expiry-option">
			<label>
				<input type="radio" v-model="expiry_mode" value="follow_rules" name="vx_expiry_mode">
				Follow expiration rules
			</label>
			<div v-if="expiry_mode === 'follow_rules'" class="expiry-mode-details">
				<div>
					<?php if ( ! empty( $rule_expirations ) ): ?>
						<label style="display: block;">Expires on</label>
						<strong><?= \Voxel\datetime_format( min( $rule_expirations ) ) ?></strong>
					<?php else: ?>
						<label style="display: block;">Expires on</label>
						<strong>Never</strong>
					<?php endif ?>
				</div>
				<div style="margin-top: 15px;">
					<a href="#" @click.prevent="show_rules = ! show_rules">Show rules</a>
				</div>
				<div v-if="show_rules">
					<ul style="margin: 10px 0 0 20px; list-style: disc;">
						<?php if ( ! empty( $rules ) ): ?>
							<?php foreach ( $rules as $rule ): ?>
								<?php if ( $rule['type'] === 'fixed' ): ?>
									<li>Expires <?= $rule['amount'] ?> days after publishing</li>
								<?php elseif ( $rule['type'] === 'field' ):
									$field = $post->get_field( $rule['field'] ); ?>
									<li>Expires when the end date for <u><?= $field->get_label() ?></u> field is reached</li>
								<?php endif ?>
							<?php endforeach ?>
						<?php else: ?>
							<li>No expiration rules have been configured for this post type</li>
						<?php endif ?>
						<li style="list-style: none;"><a href="<?= esc_url( add_query_arg( 'tab', 'general.expiration', $post->post_type->get_edit_link() ) ) ?>">Modify rules</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="expiry-option">
			<label>
				<input type="radio" v-model="expiry_mode" value="custom" name="vx_expiry_mode">
				Custom expiration date
			</label>
			<div v-if="expiry_mode === 'custom'" class="expiry-mode-details">
				<label style="display: block;">Expires on</label>
				<input style="width: 100%; margin-top: 3px;" v-model="custom_expiry" name="vx_expiry_custom" type="datetime-local" step="any">
			</div>
		</div>
		<div class="expiry-option">
			<label>
				<input type="radio" v-model="expiry_mode" value="never" name="vx_expiry_mode">
				Never expire
			</label>
		</div>
	</div>
</div>
