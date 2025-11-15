<script type="text/html" id="create-post-post-relation-field">
	<form-group wrapper-class="prmr-popup" :popup-key="field.id+':'+index" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear">
		<template #trigger>
			<label>
				{{ field.label }}
				<slot name="errors"></slot>
				<div class="vx-dialog" v-if="field.description">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
					<div class="vx-dialog-content min-scroll">
						<p>{{ field.description }}</p>
					</div>
				</div>
			</label>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value !== null}" @mousedown="$root.activePopup = field.id+':'+index; onOpen();">
				<div class="ts-filter-text">
	 				<span>{{ field.value !== null ? displayValue : field.props.placeholder }}</span>
				</div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-sticky-top uib b-bottom">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input
						v-model="search.term" ref="searchInput" type="text" class="autofocus"
						:placeholder="<?= esc_attr( wp_json_encode( _x( 'Search', 'post relation field', 'voxel' ) ) ) ?>"
					>
				</div>
			</div>

			<div v-if="search.term.trim()" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown" :class="{'vx-disabled': search.loading}">
				<ul class="simplify-ul ts-term-dropdown-list min-scroll">
					<template v-if="search.list.length">
						<li v-for="post in search.list">
							<a href="#" class="flexify" @click.prevent="selectPost(post)">
								<div class="ts-checkbox-container">
									<label :class="'container-'+(field.props.multiple ? 'checkbox' : 'radio')">
										<input
											:type="field.props.multiple ? 'checkbox' : 'radio'"
											:value="post.id"
											:checked="value[post.id]"
											disabled
											hidden
										>
										<span class="checkmark"></span>
									</label>
								</div>

								<span>{{ post.title }}</span>
								<div v-if="post.logo" class="ts-term-image">
									<span v-html="post.logo"></span>
								</div>
								<!-- <div class="ts-term-icon" v-if="field.props.require_author_approval && value[post.id] && field.props.pending_ids.includes(post.id)" title="<?= esc_attr( _x( 'Pending approval', 'post relation field', 'voxel' ) ) ?>">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
								</div> -->

							</a>
						</li>
						<li>
							<a href="#" v-if="search.has_more" @click.prevent="search.loading_more = true; serverSearchPosts(this, true)" class="ts-btn ts-btn-4" :class="{'vx-pending': search.loading_more}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
								<?= __( 'Load more', 'voxel' ) ?>
							</a>
						</li>
					</template>
					<template v-else>
						<li class="ts-empty-user-tab">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
							<p v-if="search.loading"><?= _x( 'Searching posts', 'post relation field', 'voxel' ) ?></p>
							<p v-else><?= _x( 'No posts found', 'post relation field', 'voxel' ) ?></p>
						</li>
					</template>
				</ul>
			</div>
			<div v-else class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list min-scroll">
					<template v-if="posts.list.length">
						<li v-for="post in posts.list">
							<a href="#" class="flexify" @click.prevent="selectPost(post)">

								<div class="ts-checkbox-container">
									<label :class="'container-'+(field.props.multiple ? 'checkbox' : 'radio')">
										<input
											:type="field.props.multiple ? 'checkbox' : 'radio'"
											:value="post.id"
											:checked="value[post.id]"
											disabled
											hidden
										>
										<span class="checkmark"></span>
									</label>
								</div>

								<span>{{ post.title }}</span>

								<!-- <div class="ts-term-icon" v-if="field.props.require_author_approval && value[post.id] && field.props.pending_ids.includes(post.id)" title="<?= esc_attr( _x( 'Pending approval', 'post relation field', 'voxel' ) ) ?>">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
								</div> -->
								<div v-if="post.logo" class="ts-term-image">
									<span v-html="post.logo"></span>
								</div>


							</a>
						</li>
						<li>
							<a href="#" v-if="posts.has_more" @click.prevent="loadPosts" class="ts-btn ts-btn-4" :class="{'vx-pending': posts.loading}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
								<?= __( 'Load more', 'voxel' ) ?>
							</a>
						</li>
					</template>
					<template v-else>
						<li v-if="posts.loading" class="ts-empty-user-tab">

							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<p><?= __( 'Loading', 'voxel' ) ?></p>

						</li>
						<li v-else class="ts-empty-user-tab">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
							<p><?= _x( 'No posts found', 'post relation field', 'voxel' ) ?></p>
						</li>
					</template>
				</ul>
			</div>
		</template>
	</form-group>
</script>
