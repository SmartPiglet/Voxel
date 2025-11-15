<?php
/**
 * Search filters - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-search-forms-template">
	<div class="ts-tab-content">
		<div class="x-row">
			<div class="x-col-12 ts-content-head"  v-if="$root.subtab === 'general'">
				<h1>Filters</h1>
				<p>Create filters for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head"  v-if="$root.subtab === 'order'">
				<h1>Orders</h1>
				<p>Create ordering options for this post type</p>
			</div>
			<div class="x-col-12 ts-content-head"  v-if="$root.subtab === 'indexing'">
				<h1>Indexing</h1>
				<p>Select post statuses included in the index.</p>
			</div>
			<div class="x-col-12 ts-content-head"  v-if="$root.subtab === 'status'">
				<h1>Indexing status</h1>
				<p>The index table for this post type</p>
			</div>
		</div>
		<div class="x-row">
			<div class="x-col-12">
				<ul class="inner-tabs">
					<li :class="{'current-item': $root.subtab === 'general'}">
						<a href="#" @click.prevent="$root.setTab('filters', 'general')">Search filters</a>
					</li>
					<li :class="{'current-item': $root.subtab === 'order'}">
						<a href="#" @click.prevent="$root.setTab('filters', 'order')">Search order</a>
					</li>
					<li :class="{'current-item': $root.subtab === 'indexing'}">
						<a href="#" @click.prevent="$root.setTab('filters', 'indexing')">Indexing</a>
					</li>
					<li :class="{'current-item': $root.subtab === 'status'}">
						<a href="#" @click.prevent="$root.setTab('filters', 'status')">Indexing status</a>
					</li>
				</ul>
			</div>
		</div>
		<div v-if="$root.subtab === 'general'" class="inner-tab fields-layout">
			<search-filters></search-filters>
		</div>
		<div v-if="$root.subtab === 'order'" class="inner-tab fields-layout">
			<search-order></search-order>
		</div>
		<div v-if="$root.subtab === 'indexing'" class="inner-tab">
			<div class="x-row">
				<div class="x-col-12">
					<div class="sub-heading">
						<p>Enable search index for:</p>
					</div>
					<div class="">
						<div class="ts-checkbox-container">
							<label class="container-checkbox vx-disabled">
								Published posts
								<input type="checkbox" checked disabled>
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Pending posts
								<input type="checkbox" value="pending" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Rejected posts
								<input type="checkbox" value="rejected" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Draft posts
								<input type="checkbox" value="draft" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Expired posts
								<input type="checkbox" value="expired" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Unpublished posts
								<input type="checkbox" value="unpublished" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
							<label class="container-checkbox">
								Scheduled posts
								<input type="checkbox" value="future" v-model="$root.config.settings.indexing.post_statuses">
								<span class="checkmark"></span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="$root.subtab === 'status'" class="inner-tab fields-layout">
			<div class="x-row">
				<div class="x-col-6">
					<div v-if="!$root.indexing.loaded">
						<p>Loading...</p>
						{{ $root.getIndexData() }}
					</div>
					<div class="x-row" v-else>
						<div class="ts-form-group x-col-12">
							<label>Database table</label>
							<input type="text" :value="$root.indexing.table_name" readonly>
						</div>
						<div class="ts-form-group x-col-6">
							<label>Total posts</label>
							<input type="text" :value="$root.indexing.items_total" readonly>
						</div>
						<div v-if="$root.indexing.running" class="ts-form-group x-col-6">
							<label>Status</label>
							<input type="text" :value="$root.indexingStatus" readonly>
						</div>
						<div v-else class="ts-form-group x-col-6">
							<label>Indexed posts</label>
							<input type="text" :value="$root.indexing.items_indexed" readonly>
						</div>
						<div class="ts-form-group x-col-12" :class="{'vx-disabled': $root.indexing.running && !$root.indexing.run_finished}">
							<div class="x-row">
								<div class="x-col-12">
									<a class="ts-button full-width" href="#" @click.prevent="$root.indexPosts">Index all posts</a>
								</div>
								<div class="x-col-12">
									<a class="ts-button ts-outline full-width" href="#" @click.prevent="$root.forceIndexPosts">Recreate table and index all posts</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>