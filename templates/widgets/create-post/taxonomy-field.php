<script type="text/html" id="create-post-taxonomy-field">
	<template v-if="field.props.display_as === 'inline'">
		<div class="ts-form-group inline-terms-wrapper ts-inline-filter">
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
			<!-- <div class="ts-input-icon flexify" v-if="termCount >= 10">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
				<input
					v-model="search" ref="searchInput" type="text" class="autofocus"
					:placeholder="<?= esc_attr( wp_json_encode( _x( 'Search', 'taxonomy field', 'voxel' ) ) ) ?>+' '+field.props.taxonomy.label"
				>
			</div> -->
			<div v-if="searchResults" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown inline-multilevel">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="term in searchResults" :class="{'ts-selected': !!value[term.slug]}">
						<a href="#" class="flexify" @click.prevent="selectTerm( term )">
							<div class="ts-checkbox-container">
								<label :class="field.props.multiple ? 'container-checkbox' : 'container-radio'">
									<input
										:type="field.props.multiple ? 'checkbox' : 'radio'"
										:value="term.slug"
										:checked="value[ term.slug ]"
										disabled
										hidden
									>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ term.label }}</span>
							<div class="ts-term-icon">
								<span v-html="term.icon"></span>
							</div>
						</a>
					</li>
					<li v-if="!searchResults.length">
						<a href="#" class="flexify" @click.prevent>
							<p><?= _x( 'No results found.', 'taxonomy field', 'voxel' ) ?></p>
						</a>
					</li>
				</ul>
			</div>
			<div v-else class="ts-term-dropdown ts-md-group ts-multilevel-dropdown inline-multilevel min-scroll">
				<term-list :terms="terms" list-key="toplevel" key="toplevel"></term-list>
			</div>

			
		</div>
	</template>
	<form-group v-else wrapper-class="prmr-popup vx-full-popup" :popup-key="field.id+':'+index" ref="formGroup" @blur="saveValue" @save="onSave" @clear="onClear">
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
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value !== null}" @mousedown="$root.activePopup = field.id+':'+index">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('popup_icon') ) ?: \Voxel\svg( 'menu.svg' ) ?>
				<div class="ts-filter-text">
					<span v-if="field.value !== null">{{ displayValue }}</span>
					<span v-else>{{ field.props.placeholder }}</span>
				</div>
				<div class="ts-down-icon"></div>
			</div>

			
		</template>
		<template #popup>
			<div class="ts-sticky-top uib b-bottom" v-if="termCount >= 15">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input
						v-model="search" ref="searchInput" type="text" class="autofocus"
						:placeholder="<?= esc_attr( wp_json_encode( _x( 'Search', 'taxonomy field', 'voxel' ) ) ) ?>+' '+field.props.taxonomy.label"
					>
				</div>
			</div>

			<div v-if="searchResults" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="term in searchResults" :class="{'ts-selected': !!value[term.slug]}">
						<a href="#" class="flexify" @click.prevent="selectTerm( term )">
							<div class="ts-checkbox-container">
								<label :class="field.props.multiple ? 'container-checkbox' : 'container-radio'">
									<input
										:type="field.props.multiple ? 'checkbox' : 'radio'"
										:value="term.slug"
										:checked="value[ term.slug ]"
										disabled
										hidden
									>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ term.label }}</span>
							<div class="ts-term-icon">
								<span v-html="term.icon"></span>
							</div>
						</a>
					</li>

				</ul>
				<div v-if="!searchResults.length" class="ts-empty-user-tab">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('popup_icon') ) ?: \Voxel\svg( 'menu.svg' ) ?>
					<p><?= _x( 'No results found', 'terms filter', 'voxel' ) ?></p>
				</div>
			</div>
			<div v-else class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<term-list :terms="terms" list-key="toplevel" key="toplevel"></term-list>
			</div>
		</template>
	</form-group>
</script>

<script type="text/html" id="create-post-term-list">
	<transition :name="'slide-from-'+taxonomyField.slide_from" @beforeEnter="afterEnter($event, listKey)" @beforeLeave="beforeLeave($event, listKey)">
		<ul
			v-if="taxonomyField.active_list === listKey"
			:key="listKey"
			class="simplify-ul ts-term-dropdown-list"
			ref="list"
		>
			 <li class="ts-term-centered" v-if="taxonomyField.active_list !== 'toplevel'">
			 	<a  href="#" class="flexify" @click.prevent="goBack">
			 		<div class="ts-left-icon"></div>
			 		<span><?= __( 'Go back', 'voxel' ) ?></span>
			 	</a>
			 </li>

			<li v-if="parentTerm" class="ts-parent-item">
				<a href="#" class="flexify" @click.prevent="taxonomyField.selectTerm( parentTerm )">
					<div class="ts-checkbox-container">
						<label :class="taxonomyField.field.props.multiple ? 'container-checkbox' : 'container-radio'">
							<input
								:type="taxonomyField.field.props.multiple ? 'checkbox' : 'radio'"
								:value="parentTerm.slug"
								:checked="taxonomyField.value[ parentTerm.slug ]"
								disabled
								hidden
							>
							<span class="checkmark"></span>
						</label>
					</div>
					<span>{{ parentTerm.label }}</span>
					<div class="ts-term-icon">
						<span v-html="parentTerm.icon"></span>
					</div>
				</a>
			</li>
			<template v-for="term, index in terms">
				<li v-if="index < (page*perPage)" :class="{'ts-selected': !!taxonomyField.value[term.slug] || term.hasSelection}">
					<a href="#" class="flexify" @click.prevent="selectTerm( term )">
						<div class="ts-checkbox-container">
							<label :class="taxonomyField.field.props.multiple ? 'container-checkbox' : 'container-radio'">
								<input
									:type="taxonomyField.field.props.multiple ? 'checkbox' : 'radio'"
									:value="term.slug"
									:checked="taxonomyField.value[ term.slug ]"
									disabled
									hidden
								>
								<span class="checkmark"></span>
							</label>
						</div>
						<span>{{ term.label }}</span>
						<div class="ts-right-icon" v-if="term.children && term.children.length"></div>
						<div class="ts-term-icon">
							<span v-html="term.icon"></span>
						</div>
					</a>
				</li>
			</template>
			<li v-if="(page*perPage) < terms.length" class="ts-term-centered">
				<a href="#" @click.prevent="page++" class="flexify">
					<div class="ts-term-icon"><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_load_more') ) ?: \Voxel\svg( 'reload.svg' ) ?></div>
					<span><?= __( 'Load more', 'voxel' ) ?></span>
				</a>
			</li>
		</ul>
	</transition>
	<term-list
		v-for="term in termsWithChildren"
		:terms="term.children"
		:parent-term="term"
		:previous-list="listKey"
		:list-key="'terms_'+term.id"
		:key="'terms_'+term.id"
	></term-list>
</script>
