<script type="text/html" id="sf-terms-filter">
	<template v-if="filter.props.display_as === 'inline'">
		<div class="ts-form-group inline-terms-wrapper ts-inline-filter min-scroll"
			:class="{'vx-inert':isPending, hidden: !visibleFlatTerms.length}">
			<label v-if="$root.config.showLabels">{{ filter.label }}</label>
			<div class="ts-term-dropdown ts-multilevel-dropdown inline-multilevel">
				<ul class="simplify-ul ts-term-dropdown-list">
					<template v-for="term, index in visibleFlatTerms">
						<li v-if="index < (page*perPage)"
							:class="{'ts-selected': !!value[term.slug]}">
							<a href="#" class="flexify" @click.prevent="selectFlatTerm(term)">
								<div class="ts-checkbox-container">
									<label :class="filter.props.multiple ? 'container-checkbox' : 'container-radio'">
										<input disabled hidden
											:type="filter.props.multiple ? 'checkbox' : 'radio'"
											:value="term.slug"
											:checked="value[term.slug]">
										<span class="checkmark"></span>
									</label>
								</div>
								<span>{{ '— '.repeat(term.depth) }}{{ term.label }}</span>
								<div v-if="term.postCount !== null" class="ts-term-count">
									{{ term.postCount }}
								</div>
								<div v-if="!isAdaptive && term.icon" class="ts-term-icon">
									<span v-html="term.icon"></span>
								</div>
							</a>
						</li>
					</template>
					<li v-if="(page*perPage) < visibleFlatTerms.length" class="ts-term-centered" >
						<a href="#" @click.prevent="page++" class="flexify">
							<div class="ts-term-icon">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							</div>
							<span><?= __( 'Load more', 'voxel' ) ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</template>
	<template v-else-if="filter.props.display_as === 'buttons'">
		<div class="ts-form-group" :class="{'vx-inert':isPending, hidden: !flatTerms.length}">
			<label v-if="$root.config.showLabels">{{ filter.label }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<template v-for="term in flatTerms">
					<li class="flexify" @click.prevent="selectFlatTerm( term )" :class="{'adb-selected': !!value[ term.slug ]}">
						{{ term.label }}
						<div v-if="term.postCount !== null" class="ts-term-count">
							{{ term.postCount }}
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
	<form-group v-else :popup-key="filter.id" ref="formGroup" @save="onSave" @blur="saveValue" @clear="onClear"
		:wrapper-class="[repeaterId, 'vx-full-popup'].join(' ')"
		:class="{'vx-inert':isPending, hidden: !shouldShowPopup}"
		v-bind="{ 'controller-class': filter.props.multiple ? null : 'hide-d' }">
		<template #trigger>
			<label v-if="$root.config.showLabels">{{ filter.label }}</label>
	 		<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = filter.id" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
	 			<div class="ts-filter-text">
	 				<template v-if="filter.value">
	 					{{ firstLabel }}
	 					<span v-if="remainingCount > 0" class="term-count">
	 						+{{ remainingCount.toLocaleString() }}
	 					</span>
	 				</template>
	 				<template v-else>{{ filter.props.placeholder }}</template>
	 			</div>
	 			<div class="ts-down-icon"></div>
	 		</div>
	 	</template>
		<template #popup>
			<div class="ts-sticky-top uib b-bottom" v-if="flatTerms.length >= 15">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_icon_in') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input v-model="search" ref="searchInput" type="text" placeholder="<?= esc_attr( _x( 'Search', 'terms filter', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>
			<div v-if="searchResults" class="ts-term-dropdown ts-multilevel-dropdown ts-md-group">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="term in searchResults" :class="{'ts-selected': !!value[term.slug]}">
						<a href="#" class="flexify" @click.prevent="selectTerm( term )">
							<div class="ts-checkbox-container">
								<label :class="filter.props.multiple ? 'container-checkbox' : 'container-radio'">
									<input :type="filter.props.multiple ? 'checkbox' : 'radio'" :value="term.slug"
										:checked="value[ term.slug ]" disabled hidden>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ term.label }}</span>
							<div v-if="term.postCount !== null" class="ts-term-count">
								{{ term.postCount }}
							</div>
							<div v-if="!isAdaptive && term.icon" class="ts-term-icon">
								<span v-html="term.icon"></span>
							</div>
						</a>
					</li>
				</ul>
				<div v-if="!searchResults.length" class="ts-empty-user-tab">
					<span v-html="filter.icon"></span>
					<p><?= _x( 'No results found', 'terms filter', 'voxel' ) ?></p>
				</div>
			</div>
			<div v-else class="ts-term-dropdown ts-multilevel-dropdown ts-md-group">
				<term-list :terms="terms" list-key="toplevel" key="toplevel" :main="this"></term-list>
			</div>
		</template>
	</form-group>
</script>

<script type="text/html" id="sf-term-list">
	<transition :name="'slide-from-'+main.slide_from" @beforeEnter="beforeEnter($event, listKey)" @beforeLeave="beforeLeave($event, listKey)">
		<ul v-if="main.active_list === listKey" :key="listKey" class="simplify-ul ts-term-dropdown-list">
			<li v-if="main.active_list !== 'toplevel'" class="ts-term-centered">
				<a href="#" class="flexify" @click.prevent="goBack">
      	            <div class="ts-left-icon"></div>
      	            <span><?= __( 'Go back', 'voxel' ) ?></span>
	  	        </a>
			</li>
			<li v-if="parentTerm" class="ts-parent-item">
				<a href="#" class="flexify" @click.prevent="main.selectTerm( parentTerm )">
					<div class="ts-checkbox-container">
						<label :class="main.filter.props.multiple ? 'container-checkbox' : 'container-radio'">
							<input :type="main.filter.props.multiple ? 'checkbox' : 'radio'" :value="parentTerm.slug"
								:checked="main.value[ parentTerm.slug ]" disabled hidden>
							<span class="checkmark"></span>
						</label>
					</div>
					<span><?= _x( 'All in', 'terms filter', 'voxel' ) ?> {{ parentTerm.label }}</span>
				</a>
			</li>
			<template v-for="term, index in activeTerms">
				<li v-if="index < (page*perPage)" :class="{'ts-selected': !!main.value[term.slug] || term.hasSelection}">
					<a href="#" class="flexify" @click.prevent="selectTerm( term )">
						<div class="ts-checkbox-container">
							<label :class="main.filter.props.multiple ? 'container-checkbox' : 'container-radio'">
								<input
									:type="main.filter.props.multiple ? 'checkbox' : 'radio'"
									:value="term.slug"
									:checked="main.value[ term.slug ] || term.hasSelection"
									disabled
									hidden
								>
								<span class="checkmark"></span>
							</label>
						</div>
						<span>{{ term.label }}</span>
						<div class="ts-right-icon" v-if="term.children && term.children.length"></div>
						<div v-if="term.postCount !== null" class="ts-term-count">
							{{ term.postCount }}
						</div>
						<div v-if="!main.isAdaptive && term.icon" class="ts-term-icon">
							<span v-html="term.icon"></span>
						</div>
					</a>
				</li>
			</template>
			<li v-if="(page*perPage) < activeTerms.length" class="ts-term-centered">
				<a href="#" @click.prevent="page++" class="flexify">
					<div class="ts-term-icon">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
					</div>
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
		:main="main"
	></term-list>
</script>