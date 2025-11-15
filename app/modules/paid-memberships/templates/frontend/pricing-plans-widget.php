<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<ul class="ts-plan-tabs simplify-ul flexify ts-generic-tabs">
	<?php foreach ( $groups as $group ): ?>
		<li class="<?= $group['_id'] === $default_group ? 'ts-tab-active' : '' ?>">
			<a href="#" data-id="<?= esc_attr( $group['_id'] ) ?>"><?= $group['group_label'] ?></a>
		</li>
	<?php endforeach ?>
</ul>
<div class="ts-plans-list ts-paid-members-plans">
	<?php foreach ( $prices as $price ): ?>
		<div class="ts-plan-container <?= $price['group'] !== $default_group ? 'hidden' : '' ?>" data-group="<?= esc_attr( $price['group'] ) ?>">
			<div class="ts-plan-image flexify">
				<?= $price['image'] ?>
			</div>
			<div class="ts-plan-body">
				<div class="ts-plan-details">
					<span class="ts-plan-name"><?= $price['label'] ?></span>
				</div>
				<div class="ts-plan-pricing">
					<?php if ( $price['is_free'] ): ?>
						<span class="ts-plan-price"><?= _x( 'Free', 'pricing plans', 'voxel' ) ?></span>
					<?php else: ?>
						<?php if ( $price['discount_amount'] ): ?>
							<span class="ts-plan-price"><?= $price['discount_amount'] ?></span>
							<span class="ts-plan-price"><s><?= $price['amount'] ?></s></span>
						<?php else: ?>
							<span class="ts-plan-price"><?= $price['amount'] ?></span>
						<?php endif ?>
						<?php if ( $price['period'] ): ?>
							<div class="ts-price-period">/ <?= $price['period'] ?></div>
						<?php endif ?>

						<?php if (
							$price['trial_days'] !== null
							&& ! ( is_user_logged_in() && ! \Voxel\get_current_user()->is_eligible_for_free_trial() )
						): ?>
							<p class="ts-price-trial">
								<?= sprintf(
									_x( '%d-day free trial', 'pricing plans', 'voxel' ),
									$price['trial_days']
								) ?>
							</p>
						<?php endif ?>
					<?php endif ?>
				</div>
				<?php if ( ! empty( $price['description'] ) ): ?>
					<div class="ts-plan-desc">
						<p><?= nl2br( $price['description'] ) ?></p>
					</div>
				<?php endif ?>
				<?php if ( ! empty( $price['features'] ) ): ?>
					<div class="ts-plan-features">
						<ul class="simplify-ul">
							<?php foreach ( $price['features'] as $feature ): ?>
								<li>
									<?= \Voxel\get_icon_markup( $feature['feature_ico'] ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ) ?>

									<span><?= $feature['text'] ?></span>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				<?php endif ?>
				<div class="ts-plan-footer">
					<?php if ( is_user_logged_in() ):
						$user = \Voxel\get_current_user();
						$membership = $user->get_membership();

						$is_current_plan = false;
						$current_plan_link = null;
						if (
							$membership->get_type() === 'order'
							&& ( $order = $membership->get_order() )
							&& ( $payment_method = $membership->get_payment_method() )
							&& ! $payment_method->is_subscription_canceled()
							&& $membership->get_price_key() === $price['price_id']
						) {
							$is_current_plan = true;
							// $current_plan_link = $order->get_link();
							$current_plan_link = $price['link'];
						} elseif (
							$membership->get_type() === 'default'
							&& $membership->get_active_plan()->get_key() === 'default'
							&& $price['key'] === 'default'
							&& ! $membership->is_initial_state()
						) {
							$is_current_plan = true;
							$current_plan_link = $price['link'];
						}
						?>

						<?php if ( $is_current_plan ): ?>
							<a href="<?= $current_plan_link ? esc_url( $current_plan_link ) : 'javascript:void(0)' ?>" class="ts-btn ts-btn-1 ts-btn-large vx-pick-plan">
								<?= _x( 'Current plan', 'pricing plans', 'voxel' ) ?>
							</a>
						<?php else: ?>
							<a href="<?= esc_url( $price['link'] ) ?>" class="ts-btn ts-btn-2 ts-btn-large vx-pick-plan" rel="nofollow">
								<?php if ( $membership->get_type() === 'order' ): ?>
									<?= _x( 'Switch to plan', 'pricing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								<?php else: ?>
									<?= _x( 'Buy plan', 'pricing plans', 'voxel' ) ?>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
								<?php endif ?>
							</a>
						<?php endif ?>
					<?php else: ?>
						<a href="<?= esc_url( $price['link'] ) ?>" class="ts-btn ts-btn-2 ts-btn-large vx-pick-plan" rel="nofollow">
							<?= _x( 'Buy plan', 'pricing plans', 'voxel' ) ?>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
						</a>
					<?php endif ?>
				</div>
			</div>
		</div>
	<?php endforeach ?>
</div>
