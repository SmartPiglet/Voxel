<?php
/**
 * General settings - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-settings-template">
	<div class="ts-tab-content">
		<div class="x-row">
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'base'">
				<h1>General</h1>
				<p>Configure general details about this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'submissions'">
				<h1>Post submission</h1>
				<p>Configure front-end post submission settings for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'timeline'">
				<h1>Timeline</h1>
				<p>Configure Timeline behaviour for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'post_wall'">
				<h1>Wall posts</h1>
				<p>Configure Timeline wall posts behaviour for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'reviews'">
				<h1>Reviews</h1>
				<p>Configure review categories and styles for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'messages'">
				<h1>Direct Messages</h1>
				<p>Configure direct messages for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'quick_search'">
				<h1>Quick search</h1>
				<p>Configure Quick Search appearance for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'map'">
				<h1>Map</h1>
				<p>Configure Map marker appearance for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'permalinks'">
				<h1>Permalink</h1>
				<p>Configure permalink structure for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'archive'">
				<h1>Archive page</h1>
				<p>Configure archive page settings for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'expiration'">
				<h1>Expiration rules</h1>
				<p>Configure post expiration rules for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'revisions'">
				<h1>Revisions</h1>
				<p>View previous revisions of this post type</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'other'">
				<h1>Additional options</h1>
				<p>Configure additional post type options</p>
			</div>
			<div class="x-col-12 ts-content-head" v-if="$root.subtab === 'personal_data'">
				<h1>Personal data</h1>
				<p>Configure personal data settings for this post type</p>
			</div>
		</div>

		<div class="x-row h-center">
				<div class="x-col-4">
					<ul class="inner-tabs vertical-tabs">
						<li :class="{'current-item': $root.subtab === 'base'}">
							<a href="#" @click.prevent="$root.setTab('general', 'base')">General</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'permalinks'}">
							<a href="#" @click.prevent="$root.setTab('general', 'permalinks')">Permalinks</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'archive'}">
							<a href="#" @click.prevent="$root.setTab('general', 'archive')">Archive page</a>
						</li>
						<li v-if="!['profile'].includes($root.config.settings.key)" :class="{'current-item': $root.subtab === 'expiration'}">
							<a href="#" @click.prevent="$root.setTab('general', 'expiration')">Expiration rules</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'personal_data'}">
							<a href="#" @click.prevent="$root.setTab('general', 'personal_data')">Personal data</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'other'}">
							<a href="#" @click.prevent="$root.setTab('general', 'other')">Additional options</a>
						</li>
					<li class="ts-tab-divider"></li>
					<li v-if="!['profile'].includes($root.config.settings.key)" :class="{'current-item': $root.subtab === 'submissions'}">
						<a href="#" @click.prevent="$root.setTab('general', 'submissions')">Post submission</a>
					</li>
						<li :class="{'current-item': $root.subtab === 'messages'}">
							<a href="#" @click.prevent="$root.setTab('general', 'messages')">Direct messages</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'quick_search'}">
							<a href="#" @click.prevent="$root.setTab('general', 'quick_search')">Quick search</a>
						</li>
						<li :class="{'current-item': $root.subtab === 'map'}">
							<a href="#" @click.prevent="$root.setTab('general', 'map')">Map markers</a>
						</li>
						<?php if ( \Voxel\get( 'settings.timeline.enabled', true ) ): ?>
							<li class="ts-tab-divider"></li>
							<li :class="{'current-item': $root.subtab === 'timeline'}">
								<a href="#" @click.prevent="$root.setTab('general', 'timeline')">Post timeline</a>
							</li>
							<li :class="{'current-item': $root.subtab === 'post_wall'}">
								<a href="#" @click.prevent="$root.setTab('general', 'post_wall')">Post wall</a>
							</li>
							<li :class="{'current-item': $root.subtab === 'reviews'}">
								<a href="#" @click.prevent="$root.setTab('general', 'reviews')">Post reviews</a>
							</li>
						<?php endif ?>
						<li class="ts-tab-divider"></li>
						<li :class="{'current-item': $root.subtab === 'revisions'}">
							<a href="#" @click.prevent="$root.setTab('general', 'revisions')">Revisions</a>
						</li>
					</ul>
				</div>

				<div class="inner-tab x-col-8">
					<div v-if="$root.subtab === 'base'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Basic</h3>
							</div>

							<div class="x-row">
								<div class="ts-form-group x-col-4">
									<label>Singular name</label>
								 	<input type="text" v-model="$root.config.settings.singular">
								</div>
								<div class="ts-form-group  x-col-4">
									<label>Plural name</label>
								 	<input type="text" v-model="$root.config.settings.plural">
								</div>
								<div class="ts-form-group  x-col-4">
									<label>Post type key</label>
								 	<input type="text" v-model="$root.config.settings.key" maxlength="20" required disabled>
								</div>
							</div>

						</div>
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Icon</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
									'v-model' => '$root.config.settings.icon',
									'classes' => 'x-col-12',

								] ) ?>
							</div>
						</div>
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Configuration</h3>
							</div>

							<div class="x-row">

								<div class="ts-form-group x-col-12">
									<ul class="basic-ul">
										<li>
											<a :href="exportRevision('current')" class="ts-button ts-outline">
											<i class="las la-download icon-sm"></i>Export config
										</a>
										</li>
										<li>
											<a href="#" @click.prevent="$root.$w.confirm('This will replace your existing post type configuration. Do you want to proceed?') ? $refs.importConfig.click() : ''" class="ts-button ts-outline">
												<i class="las la-cloud-upload-alt icon-sm"></i>Import config
											</a>
											<input type="file" ref="importConfig" @change="importConfig" class="hidden">
										</li>
										<li>
											<a href="#" @click.prevent="$root.setTab('general', 'revisions')" class="ts-button ts-outline">
											<i class="las la-history icon-sm"></i>Revisions
										</a>
										</li>
									</ul>
								</div>
								<div class="ts-form-group x-col-12">
									<p>The previous 15 revisions of the post type settings are stored automatically.</p>
								</div>
							</div>
						</div>
					</div>

					<div v-else-if="$root.subtab === 'expiration'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Expiration rules</h3>
							</div>

							<template v-if="$root.config.settings.expiration.rules.length">
								<template v-for="rule in $root.config.settings.expiration.rules">
									<div v-if="isExpirationRuleValid(rule)" class="single-field wide" :class="{open: activeExpirationRule === rule}">
										<div class="field-head" @click.prevent="activeExpirationRule = ( activeExpirationRule === rule ) ? null : rule">
											<p class="field-name">{{ getExpirationRuleLabel(rule) }}</p>
											<div class="field-actions">
												<a href="#" @click.prevent.stop="deleteExpirationRule(rule)" class="field-action all-center">
													<i class="lar la-trash-alt icon-sm"></i>
												</a>
											</div>
										</div>
										<div v-if="activeExpirationRule === rule" class="field-body">
											<div class="x-row">
												<template v-if="rule.type === 'fixed'">
													<div class="ts-form-group x-col-12">
														<label>Expire posts after (days)</label>
														<input type="number" v-model="rule.amount">
													</div>
												</template>
												<template v-else>
													<div class="ts-form-group x-col-12">
														<p :title="rule.field">No additional settings available for this rule.</p>
													</div>
												</template>
											</div>
										</div>
									</div>
								</template>
							</template>
							<template v-else>
								<div class="ts-form-group">
									<p>No expiration rules have been configured for this post type</p>
								</div>
							</template>

							<template v-if="expirationRules.length">
								<div class="ts-spacer"></div>
								<div class="ts-group-head">
									<h3>Available rules</h3>
								</div>
								<div class="x-row x-col-12">
									<div class="add-field">
										<template v-for="rule in expirationRules">
											<a href="#" @click.prevent="addExpirationRule(rule)" class="ts-button ts-outline">{{ getExpirationRuleLabel( rule ) }}</a>
										</template>
									</div>
								</div>
							</template>
							<!-- <div class="x-row">
								<div class="x-col-12">
									<pre debug>{{ $root.config.settings.expiration }}</pre>
									<pre debug>{{ expirationRules }}</pre>
								</div>
							</div> -->
						</div>
					</div>
					<div v-else-if="$root.subtab === 'submissions'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Submission settings</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.submissions.enabled',
									'label' => 'Enable post submissions',
									'classes' => 'x-col-12',
									'description' => 'Allows users to submit posts of this post type through the frontend form',
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.submissions.status',
									'classes' => 'x-col-12',
									'label' => 'When a new post is submitted, set its status to',
									'choices' => [
										'publish' => 'Published: Post is published and publicly available immediately',
										'pending' => 'Pending Review: Admin review and approval is required before it\'s published',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.submissions.update_status',
									'label' => 'When an existing post is edited',
									'classes' => 'x-col-12',
									'choices' => [
										'publish' => 'Publish: Apply edits immediately and keep the post published',
										'pending' => 'Pending Review: Apply edits immediately and set the post status to pending',
										// 'pending_merge' => 'Pending Merge: Post remains published, but edits are not applied until the admin has reviewed and approved them.',
										'disabled' => 'Disabled: Posts cannot be edited',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.submissions.update_slug',
									'label' => 'Always update post slug when the post is updated',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.submissions.deletable',
									'classes' => 'x-col-12',
									'label' => 'Authors are allowed to delete their posts',
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'timeline'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Post timeline</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.timeline.enabled',
									'label' => 'Enable post timeline',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.timeline.visibility',
									'label' => 'Visibility of timeline posts',
									'classes' => 'x-col-12',
									'choices' => [
										'public' => 'Public: Visible to everyone',
										'logged_in' => 'Logged-in: Visible to all logged in users',
										'followers_only' => 'Followers: Visible to post followers only',
										'customers_only' => 'Customers: Visible to post customers only',
										'private' => 'Private: Visible to post author only',
									],
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'post_wall'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Wall posts</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.timeline.wall',
									'label' => 'Allow wall posts',
									'classes' => 'x-col-12',
									'choices' => [
										'public' => 'From all logged-in users',
										'followers_only' => 'From followers only',
										'customers_only' => 'From customers only',
										'disabled' => 'Disabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.timeline.wall_visibility',
									'label' => 'Visibility of wall posts',
									'classes' => 'x-col-12',
									'choices' => [
										'public' => 'Public: Visible to everyone',
										'logged_in' => 'Logged-in: Visible to all logged in users',
										'followers_only' => 'Followers: Visible to post followers only',
										'customers_only' => 'Customers: Visible to post customers only',
										'private' => 'Private: Visible to post author only',
									],
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'reviews'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Post reviews</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.timeline.reviews',
									'label' => 'Allow post reviews',
									'classes' => 'x-col-12',
									'choices' => [
										'public' => 'From all logged-in users',
										'followers_only' => 'From followers only',
										'customers_only' => 'From customers only',
										'disabled' => 'Disabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.timeline.review_visibility',
									'label' => 'Visibility of post reviews',
									'classes' => 'x-col-12',
									'choices' => [
										'public' => 'Public: Visible to everyone',
										'logged_in' => 'Logged-in: Visible to all logged in users',
										'followers_only' => 'Followers: Visible to post followers only',
										'customers_only' => 'Customers: Visible to post customers only',
										'private' => 'Private: Visible to post author only',
									],
								] ) ?>
							</div>
						</div>

						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Review categories</h3>
							</div>

							<draggable
								v-model="$root.config.settings.reviews.categories"
								group="review_categories"
								handle=".field-head"
								item-key="key"
								@start=""
								@end=""
								class="ts-drag-animation"
							>
								<template #item="{element: category, index: index}">
									<div class="single-field wide" :class="{open: activeReviewTab === category.key}">
										<div class="field-head" @click.prevent="activeReviewTab = ( activeReviewTab === category.key ) ? null : category.key">
											<p class="field-name">{{ category.label }}</p>
											<div v-if="category.key !== 'score'" class="field-actions" @click.prevent.stop="deleteReviewCategory( category.key )">
												<a href="#" class="field-action all-center">
													<i class="lar la-trash-alt icon-sm"></i>
												</a>
											</div>
										</div>
										<div v-if="activeReviewTab === category.key" class="field-body">
											<div class="x-row">
												<div class="ts-form-group x-col-12">
													<label>Label</label>
													<input type="text" v-model="category.label">
												</div>

												<?php \Voxel\Utils\Form_Models\Key_Model::render( [
													'editable' => 'category.key !== \'score\'',
													'v-model' => 'category.key',
													'classes' => 'x-col-12',
													'label' => 'Unique key',
												] ) ?>

												<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
													'v-model' => 'category.icon',
													'classes' => 'x-col-12',
													'label' => 'Icon',
												] ) ?>

												<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
													'label' => 'Is required?',
													'classes' => 'x-col-12',
													'v-model' => 'category.required',
												] ); ?>
											</div>
										</div>
									</div>
								</template>
							</draggable>

							<div class="x-row x-col-12">
								<div class="add-field">
									<a href="#" class="ts-button ts-outline" @click.prevent="createReviewCategory">Add Category</a>
								</div>
							</div>
						</div>

						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Rating levels</h3>
							</div>
							<div class="x-row">
								<?php foreach ( $post_type->reviews->get_default_rating_levels() as $key => $level ): ?>
									<div class="ts-form-group x-col-12">
										<label><strong><?= $level['label'] ?></strong></label>
										<div class="x-row">
											<?php \Voxel\Utils\Form_Models\Text_Model::render( [
												'v-model' => sprintf( '$root.config.settings.reviews.rating_levels.%s.label', $key ),
												'label' => 'Label',
												'classes' => 'x-col-6',
												'placeholder' => $level['label'],
											] ) ?>

											<?php \Voxel\Utils\Form_Models\Color_Model::render( [
												'v-model' => sprintf( '$root.config.settings.reviews.rating_levels.%s.color', $key ),
												'label' => 'Color',
												'classes' => 'x-col-6',
											] ) ?>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						</div>

						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Review settings</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.reviews.input_mode',
									'label' => 'Review input mode',
									'classes' => 'x-col-12',
									'choices' => [
										'numeric' => 'Numeric',
										'stars' => 'Stars',
									],
								] ) ?>

								<template v-if="$root.config.settings.reviews.input_mode === 'stars'">
									<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
										'v-model' => '$root.config.settings.reviews.icons.active',
										'classes' => 'x-col-12',
										'label' => 'Active icon',
										':allow-fonticons' => 'false',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
										'v-model' => '$root.config.settings.reviews.icons.inactive',
										'classes' => 'x-col-12',
										'label' => 'Inactive icon',
										':allow-fonticons' => 'false',
									] ) ?>
								</template>
							</div>
						</div>
					</div>

					<div v-else-if="$root.subtab === 'messages'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Direct Messages</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.messages.enabled',
									'label' => 'Enable messages',
									'classes' => 'x-col-12',
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'quick_search'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Quick search</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.quick_search.text.type',
									'label' => 'Search result text displays',
									'classes' => 'x-col-12',
									'choices' => [
										'title' => 'Post title',
										'dynamic' => 'Dynamic content',
									],
								] ) ?>

								<template v-if="$root.config.settings.quick_search.text.type === 'dynamic'">
									<?php \Voxel\Utils\Form_Models\DTag_Model::render( [
										'v-model' => '$root.config.settings.quick_search.text.dynamic.content',
										'classes' => 'x-col-12',
										'label' => 'Dynamic content',
										':tag-groups' => '$root.getDynamicDataGroupsForQuickSearch()',
									] ) ?>
								</template>

								<div class="ts-form-group x-col-12">
									<label>Search result thumbnail displays</label>
									<select v-model="$root.config.settings.quick_search.thumbnail.source">
										<option :value="''">None</option>
										<option v-for="field in $root.getFieldsByType(['image', 'profile-avatar'])" :value="field.key">
											{{ field.label }}
										</option>
									</select>
								</div>

								<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
									'v-model' => '$root.config.settings.quick_search.thumbnail.default_icon',
									'label' => 'Default thumbnail icon',
									'classes' => 'x-col-12',
								] ) ?>

								<!-- <div class="x-col-12">
									<pre debug>{{ $root.config.settings.quick_search }}</pre>
								</div> -->
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'map'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Map marker</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.map.markers.type',
									'label' => 'Marker type',
									'classes' => 'x-col-12',
									'choices' => [
										'icon' => 'Icon',
										'image' => 'Image + Icon',
										'text' => 'Text',
									],
								] ) ?>

								<template v-if="$root.config.settings.map.markers.type === 'icon'">
									<div class="ts-form-group x-col-12">
										<label>Get icon from taxonomy term</label>
										<select v-model="$root.config.settings.map.markers.type_icon.source">
											<option :value="null">None</option>
											<option v-for="field in $root.getFieldsByType('taxonomy')" :value="field.key">
												{{ field.label }}
											</option>
										</select>
									</div>
									<?php \Voxel\Utils\Form_Models\Icon_Model::render( [
										'v-model' => '$root.config.settings.map.markers.type_icon.default',
										'label' => 'Default icon',
										'classes' => 'x-col-12',
									] ) ?>
								</template>
								<template v-else-if="$root.config.settings.map.markers.type === 'image'">
									<div class="ts-form-group x-col-12">
										<label>Get image from field</label>
										<select v-model="$root.config.settings.map.markers.type_image.image_source">
											<option :value="null">None</option>
											<option v-for="field in $root.getFieldsByType('image')" :value="field.key">
												{{ field.label }}
											</option>
										</select>
									</div>
									<?php \Voxel\Utils\Form_Models\Media_Model::render( [
										'v-model' => '$root.config.settings.map.markers.type_image.default_image',
										'label' => 'Default image',
										'classes' => 'x-col-12',
									] ) ?>
									<div class="ts-form-group x-col-12">
										<label>Get icon from taxonomy term</label>
										<select v-model="$root.config.settings.map.markers.type_image.icon_source">
											<option :value="null">None</option>
											<option v-for="field in $root.getFieldsByType('taxonomy')" :value="field.key">
												{{ field.label }}
											</option>
										</select>
									</div>
								</template>
								<template v-else-if="$root.config.settings.map.markers.type === 'text'">
									<?php \Voxel\Utils\Form_Models\DTag_Model::render( [
										'v-model' => '$root.config.settings.map.markers.type_text.text',
										'classes' => 'x-col-12',
										'label' => 'Marker text',
										':tag-groups' => '$root.getDynamicDataGroups()',
									] ) ?>
								</template>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'permalinks'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Post permalinks</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.permalinks.custom',
									'label' => 'Custom permalink base',
									'classes' => 'x-col-12',
								] ) ?>

								<template v-if="$root.config.settings.permalinks.custom">
									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'v-model' => '$root.config.settings.permalinks.slug',
										'label' => 'Permalink base',
										'classes' => 'x-col-12',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
										'label' => 'With front',
										'v-model' => '$root.config.settings.permalinks.with_front',
										'v-if' => '$root.options.permalink_front !== "/"',
										'classes' => 'x-col-12',
										'infobox' => 'If enabled, the static permalink front configured in WP Admin > Settings > Permalinks will be prepended to the post permalink',
									] ) ?>
								</template>

								<div class="ts-form-group x-col-12">
									<label>Preview</label>
									<pre class="ts-snippet mt0 mb0"><span class="ts-blue"><?= home_url('/') ?></span><span class="ts-yellow" v-if="$root.config.settings.permalinks.with_front && $root.options.permalink_front !== '/'">{{ $root.options.permalink_front.substr(1) }}</span><span class="ts-green">{{ $root.config.settings.permalinks.slug }}/</span><span class="ts-blue">sample-post</span></pre>
								</div>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'archive'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Archive page</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.archive.has_archive',
									'label' => 'Enable archive page',
									'classes' => 'x-col-12',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
										'disabled' => 'Disabled',
									],
								] ) ?>

								<template v-if="$root.config.settings.options.archive.has_archive === 'enabled'">
									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'v-model' => '$root.config.settings.options.archive.slug',
										'label' => 'Archive page slug',
										'classes' => 'x-col-12',
										'choices' => [
											'default' => 'Default: Use the permalink base',
											'custom' => 'Custom: Set a custom slug',
										],
									] ) ?>

									<template v-if="$root.config.settings.options.archive.slug === 'custom'">
										<?php \Voxel\Utils\Form_Models\Text_Model::render( [
											'v-model' => '$root.config.settings.options.archive.custom_slug',
											'label' => 'Custom archive slug',
											'classes' => 'x-col-12',
										] ) ?>
									</template>
								</template>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.default_archive_query',
									'label' => 'Native archive query',
									'infobox' => <<<TEXT
										If enabled, the native WordPress query will run in the post type archive page.<br><br>
										This query is only necessary if you intend to display posts using the "WP default archive" mode of the Post feed (VX) widget.<br><br>
										If you're instead using one of the "Search form", "Filters", or "Manual" modes, this query should be disabled to avoid any performance impact.
										TEXT,
									'classes' => 'x-col-12',
									'choices' => [
										'disabled' => 'Disabled (recommended)',
										'enabled' => 'Enabled',
									],
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'personal_data'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Personal data</h3>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
									'v-model' => '$root.config.settings.options.export_to_personal_data',
									'label' => 'Export posts to personal data',
									'infobox' => 'Set whether to include posts of this post type in the author\'s personal data export.',
									'classes' => 'x-col-12',
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.delete_with_user',
									'label' => 'Delete posts with user',
									'infobox' => 'Set whether posts of this post type should be deleted when the author\'s account is deleted.',
									'classes' => 'x-col-12',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
										'disabled' => 'Disabled',
									],
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'other'">
						<div class="ts-group">
							<div class="ts-group-head">
								<h3>Additional options</h3>

								<div class="vx-info-box wide">
									<?php \Voxel\svg( 'info.svg' ) ?>
									<p>
										The value "Auto" instructs Voxel not to modify the original behavior for that setting.<br><br>
										For post types registered natively by WordPress, through the child theme, or through a 3rd-party plugin, "Auto" preserves the original configuration used during the registration of that post type.
									</p>
								</div>
							</div>
							<div class="x-row">
								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.gutenberg',
									'label' => 'Block editor',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether to use the block editor when editing posts of this post type.',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.hierarchical',
									'label' => 'Hierarchical',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether the post type is hierarchical (e.g. the native Pages post type).',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
										'disabled' => 'Disabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.supports.page_attributes',
									'label' => 'Page attributes',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether to display the native Page Attributes metabox when editing posts of this post type',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.excerpt',
									'label' => 'Post excerpt',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether to display the Post excerpt metabox when editing posts of this post type.',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.author',
									'label' => 'Post author',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether to display the native post author metabox and the author column when editing posts of this post type.',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
									],
								] ) ?>

								<?php \Voxel\Utils\Form_Models\Select_Model::render( [
									'v-model' => '$root.config.settings.options.publicly_queryable',
									'label' => 'Publicly queryable',
									'classes' => 'x-col-12',
									'infobox' => 'Set whether posts of this post type can be accessed via standard URLs on the front end. Disable to prevent direct access to individual posts and archives through their URLs.',
									'choices' => [
										'auto' => 'Auto',
										'enabled' => 'Enabled',
										'disabled' => 'Disabled',
									],
								] ) ?>
							</div>
						</div>
					</div>
					<div v-else-if="$root.subtab === 'revisions'">
						<template v-if="$root.options.revisions.length">
							<div v-for="revision in $root.options.revisions" class="ts-group">
								<div class="x-row">
									<div class="ts-form-group x-col-12">
										<p style="margin-bottom: 15px;">Revision by {{ revision.author }} on {{ revision.date }}</p>
										<ul class="basic-ul">
											<li>
												<a :href="rollbackRevision(revision.timestamp)" onclick="return confirm('This will replace your existing post type configuration. Do you want to proceed?')" class="ts-button ts-outline">
													<i class="las la-cloud-upload-alt icon-sm"></i>Rollback
												</a>
											</li>
											<li>
												<a :href="exportRevision(revision.timestamp)" class="ts-button ts-outline">
													<i class="las la-download icon-sm"></i>Export
												</a>
											</li>
											<li>
												<a href="#" @click.prevent="removeRevision(revision)" class="ts-button ts-outline">
													<i class="las la-trash icon-sm"></i>Remove
												</a>
											</li>
										</ul>
									</div>

								</div>

							</div>
						</template>
						<template v-else>
							<div class="single-revision ts-group">
								<div class="x-row">
									<div class="ts-form-group x-col-12">
										<p>No revisions made to this post type yet.</p>
									</div>
								</div>
							</div>
						</template>


					</div>
				</div>

		</div>
	</div>
</script>
