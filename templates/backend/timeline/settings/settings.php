<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

wp_enqueue_script('vue');
wp_enqueue_script('vx:general-settings.js');

?>
<div id="vx-general-settings" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="state.submit_config = JSON.stringify( config )">
		<div class="sticky-top">
			<div class="vx-head x-container">
				<h2 v-if="tab === 'timeline'">General</h2>
				<h2 v-if="tab === 'statuses'">Statuses</h2>
				<h2 v-if="tab === 'replies'">Replies</h2>
				<h2 v-if="tab === 'moderation'">Moderation</h2>
				<h2 v-if="tab === 'followers'">Followers</h2>
				<div class="vxh-actions">
					<input type="hidden" name="config" :value="state.submit_config">
					<input type="hidden" name="action" value="voxel_save_timeline_settings">
					<?php wp_nonce_field( 'voxel_save_timeline_settings' ) ?>
					<button type="submit" class="ts-button btn-shadow ts-save-settings">
						<?php \Voxel\svg( 'floppy-disk.svg' ) ?>
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
						<li :class="{'current-item': tab === 'timeline'}">
							<a href="#" @click.prevent="setTab('timeline')">General</a>
						</li>
						<li :class="{'current-item': tab === 'statuses'}">
							<a href="#" @click.prevent="setTab('statuses')">Statuses</a>
						</li>
						<li :class="{'current-item': tab === 'replies'}">
							<a href="#" @click.prevent="setTab('replies')">Replies</a>
						</li>
						<li :class="{'current-item': tab === 'moderation'}">
							<a href="#" @click.prevent="setTab('moderation')">Moderation</a>
						</li>
						<li :class="{'current-item': tab === 'followers'}">
							<a href="#" @click.prevent="setTab('followers')">Followers</a>
						</li>
					</ul>
				</div>

				<div v-if="tab === 'timeline'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>User timeline</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Select_Model::render( [
								'v-model' => 'config.timeline.user_timeline.visibility',
								'label' => 'Visibility of posts',
								'classes' => 'x-col-12',
								'choices' => [
									'public' => 'Public: Visible to everyone',
									'logged_in' => 'Logged-in: Visible to all logged in users',
									'followers_only' => 'Followers: Visible to followers only',
									'customers_only' => 'Customers: Visible to customers only',
									'private' => 'Private: Visible to the user only',
								],
							] ) ?>
						</div>
					</div>
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Timeline feed</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.per_page',
								'label' => 'Posts per page',
								'classes' => 'x-col-12',
							] ) ?>
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.per_page',
								'label' => 'Replies per page',
								'classes' => 'x-col-12',
							] ) ?>
							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.reposts.enabled',
								'label' => 'Enable reposts & quotes',
								'classes' => 'x-col-12',
							] ) ?>
							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.author.show_username',
								'label' => 'Show author username',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
					<div class="ts-group">
						<div class="x-row">
							<div class="ts-form-group x-col-12">
								<label>Actions</label>
								<a href="#" @click.prevent="purgeTimelineCache($event)" class="ts-button ts-outline">Purge timeline & follow stats cache</a>
							</div>
						</div>
					</div>
				</div>
				<div v-else-if="tab === 'statuses'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Create status</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.maxlength',
								'label' => 'Max. content length',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.posts.images.enabled',
								'label' => 'Allow image uploads',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-if' => 'config.timeline.posts.images.enabled',
								'v-model' => 'config.timeline.posts.images.max_count',
								'label' => 'Max. image count',
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-if' => 'config.timeline.posts.images.enabled',
								'v-model' => 'config.timeline.posts.images.max_size',
								'label' => 'Max. image size (in kB)',
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
								'v-if' => 'config.timeline.posts.images.enabled',
								'v-model' => 'config.timeline.posts.images.allowed_formats',
								'label' => 'Allowed image formats',
								'classes' => 'x-col-12',
								'choices' => [
									'image/jpeg' => 'image/jpeg',
									'image/gif' => 'image/gif',
									'image/png' => 'image/png',
									'image/webp' => 'image/webp',
								],
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.posts.editable',
								'label' => 'Allow editing of published posts',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>

					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Display</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.truncate_at',
								'label' => 'Display "Read more" toggle after (in characters)',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>

					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Quotes</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.quotes.truncate_at',
								'label' => 'Truncate content after (in characters)',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>

					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Post rate limiting</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.rate_limit.time_between',
								'label' => 'Minimum time between posts (in seconds)',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.rate_limit.hourly_limit',
								'label' => 'Maximum number of posts allowed in an hour',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.rate_limit.daily_limit',
								'label' => 'Maximum number of posts allowed in a day',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
				</div>
				<div v-else-if="tab === 'replies'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Create reply</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.maxlength',
								'label' => 'Max. content length',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.replies.images.enabled',
								'label' => 'Allow image uploads',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-if' => 'config.timeline.replies.images.enabled',
								'v-model' => 'config.timeline.replies.images.max_count',
								'label' => 'Max. image count',
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-if' => 'config.timeline.replies.images.enabled',
								'v-model' => 'config.timeline.replies.images.max_size',
								'label' => 'Max. image size (in kB)',
								'classes' => 'x-col-6',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
								'v-if' => 'config.timeline.replies.images.enabled',
								'v-model' => 'config.timeline.replies.images.allowed_formats',
								'label' => 'Allowed image formats',
								'classes' => 'x-col-12',
								'choices' => [
									'image/jpeg' => 'image/jpeg',
									'image/gif' => 'image/gif',
									'image/png' => 'image/png',
									'image/webp' => 'image/webp',
								],
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
								'v-model' => 'config.timeline.replies.editable',
								'label' => 'Allow editing of published replies',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Display</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.posts.truncate_at',
								'label' => 'Display "Read more" toggle after (in characters)',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.max_nest_level',
								'label' => 'Max. reply depth',
								'classes' => 'x-col-12',
								'placeholder' => 1,
							] ) ?>
						</div>
					</div>
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Reply rate limiting</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.rate_limit.time_between',
								'label' => 'Minimum time between replies (in seconds)',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.rate_limit.hourly_limit',
								'label' => 'Maximum number of replies allowed in an hour',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Number_Model::render( [
								'v-model' => 'config.timeline.replies.rate_limit.daily_limit',
								'label' => 'Maximum number of replies allowed in a day',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
				</div>
				<div v-else-if="tab === 'moderation'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-form-group">
							<p>
								Configure the content moderation settings for each timeline mode. Moderation can be performed by users with the Administrator or Editor role. Certain modes allow additional parties to handle moderation duties.
							</p>
						</div>
					</div>
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>User timeline</h3>
						</div>
						<div class="x-row">
							<div class="ts-form-group x-col-6">
								<label>Require post approval</label>
								<select v-model="config.timeline.moderation.user_timeline.posts.require_approval">
									<option :value="true">Yes</option>
									<option :value="false">No</option>
								</select>
							</div>

							<div class="ts-form-group x-col-6">
								<label>Require comment approval</label>
								<select v-model="config.timeline.moderation.user_timeline.comments.require_approval">
									<option :value="true">Yes</option>
									<option :value="false">No</option>
								</select>
							</div>
						</div>
					</div>

					<?php foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ):
						if ( ! (
							$post_type->get_setting('timeline.enabled')
							|| $post_type->get_setting('timeline.wall') !== 'disabled'
							|| $post_type->get_setting('timeline.reviews') !== 'disabled' )
						) {
							continue;
						}

						$model_path = sprintf( 'config.timeline.moderation.post_types["%s"]', $post_type->get_key() );
						$getModel = function( $path ) use ( $model_path ) {
							return esc_attr( sprintf( '%s.%s', $model_path, $path ) );
						};
						?>

						<div class="ts-group">
							<div class="ts-group-head">
								<h3><?= esc_html( $post_type->get_label() ) ?></h3>
							</div>
							<div class="x-row">
								<?php if ( $post_type->get_setting('timeline.enabled') ): ?>
									<div class="ts-form-group x-col-12">
										<h3>Post timeline</h3>
									</div>
									<div class="ts-form-group x-col-6">
										<label>Require post approval</label>
										<select v-model="<?= $getModel('post_timeline.posts.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>

									<div class="ts-form-group x-col-6">
										<label>Require comment approval</label>
										<select v-model="<?= $getModel('post_timeline.comments.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>
								<?php endif ?>

								<?php if ( $post_type->get_setting('timeline.wall') !== 'disabled' ): ?>
									<div class="ts-form-group x-col-12">
										<h3>Post wall</h3>
									</div>
									<div class="ts-form-group x-col-6">
										<label>Require post approval</label>
										<select v-model="<?= $getModel('post_wall.posts.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>
									<div class="ts-form-group x-col-6">
										<label>Require comment approval</label>
										<select v-model="<?= $getModel('post_wall.comments.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>
									<div class="x-col-12 ts-checkbox-container min-scroll">
										<label class="container-checkbox">
											Post author can moderate
											<input type="checkbox" v-model="<?= $getModel('post_wall.moderators.post_author') ?>">
											<span class="checkmark"></span>
										</label>
									</div>
								<?php endif ?>

								<?php if ( $post_type->get_setting('timeline.reviews') !== 'disabled' ): ?>
									<div class="ts-form-group x-col-12">
										<h3>Post reviews</h3>
									</div>
									<div class="ts-form-group x-col-6">
										<label>Require post approval</label>
										<select v-model="<?= $getModel('post_reviews.posts.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>
									<div class="ts-form-group x-col-6">
										<label>Require comment approval</label>
										<select v-model="<?= $getModel('post_reviews.comments.require_approval') ?>">
											<option :value="true">Yes</option>
											<option :value="false">No</option>
										</select>
									</div>
									<div class="x-col-12 ts-checkbox-container min-scroll">
										<label class="container-checkbox">
											Post author can moderate
											<input type="checkbox" v-model="<?= $getModel('post_reviews.moderators.post_author') ?>">
											<span class="checkmark"></span>
										</label>
									</div>
								<?php endif ?>
							</div>
						</div>
					<?php endforeach ?>

					<!-- <div class="ts-group">
						<div class="x-row">
							<div class="x-col-12">
								<pre debug>{{ config.timeline.moderation }}</pre>
							</div>
						</div>
					</div> -->
				</div>
				<div v-else-if="tab === 'followers'" class="x-col-9">
					<div class="ts-group">
						<div class="ts-group-head">
							<h3>Enable automatic follows for users and posts when a new account is created</h3>
						</div>
						<div class="x-row">
							<?php \Voxel\Utils\Form_Models\Text_Model::render( [
								'v-model' => 'config.timeline.followers.autofollow.users',
								'label' => 'Auto-follow users (provide a comma-separated list of user ids)',
								'classes' => 'x-col-12',
							] ) ?>

							<?php \Voxel\Utils\Form_Models\Text_Model::render( [
								'v-model' => 'config.timeline.followers.autofollow.posts',
								'label' => 'Auto-follow posts (provide a comma-separated list of post ids)',
								'classes' => 'x-col-12',
							] ) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>