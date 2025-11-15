<div id="vx-post-author" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<input type="hidden" ref="input" name="vx_author" value="<?= $author ? $author->get_id() : '' ?>">
	<div v-if="author" class="author-details">
		<div class="author-avatar" v-html="author.avatar"></div>
		<div>
			<div><a :href="author.edit_link" class="author-link">{{ author.display_name }}</a></div>
			<span class="author-role">{{ author.roles.join(', ') }}</span>
		</div>
	</div>
	<a href="#" @click.prevent="showSearch" class="change-author">Change author</a>
	<div v-if="search.show" class="search-author mt10">
		<input type="text" ref="searchInput" placeholder="Search users..." @input="searchUsers(this)" style="margin:0;">
		<template v-if="search.results !== null && search.results.length">
			<div class="search-results">
				<template v-for="user in search.results">
					<a href="#" class="single-result" @click.prevent="setAuthor(user)">
						<div class="author-details">
							<div class="author-avatar" v-html="user.avatar"></div>
							<div>
								<div style="color: #2271b1;"><strong>{{ user.display_name }}</strong></div>
								<div>{{ user.roles.join(', ') }}</div>
							</div>
						</div>
					</a>
				</template>
			</div>
		</template>
		<template v-else>
			<template v-if="search.loading">
				<p class="search-status">Loading...</p>
			</template>
			<template v-else-if="search.term.trim().length">
				<p class="search-status">No users found</p>
			</template>
		</template>
	</div>
</div>
