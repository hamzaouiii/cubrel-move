<script setup>
import { computed, ref } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'

const form = useForm({})
const logout = () => {
  form.post('/logout')
}

const page = usePage()
const modules = computed(() => page.props.modules ?? [])
const currentUrl = computed(() => page.url)

const collapsedSidebar = ref(false)
const toggleSidebar = () => {
  collapsedSidebar.value = !collapsedSidebar.value
}

const appSettings = usePage().props.appSettings

// --- Tooltip state ---
const showTooltip = ref(false)
const tooltipText = ref('')
const tooltipcolor = ref('')
const tooltipPosition = ref({ top: 0, left: 0 })

const onModuleMouseEnter = (event, mod) => {
  if (!collapsedSidebar.value) return

  const rect = event.currentTarget.getBoundingClientRect()

  tooltipText.value = mod.label
  tooltipcolor.value = mod.color
  tooltipPosition.value = {
    top: rect.top + rect.height / 2,
    left: rect.right + 10, // 10px to the right of sidebar
  }
  showTooltip.value = true
}

const onModuleMouseLeave = () => {
  showTooltip.value = false
}
</script>

<template>
  <aside :class="{ 'sidebar': !collapsedSidebar, 'collapsed': collapsedSidebar }">
    <div @click="toggleSidebar" class="collapser">
      <div v-if="collapsedSidebar" class="collapser_icon">
        <i class="fa-solid fa-bars"></i>
      </div>
      <div v-else class="collapser_icon">
        <i class="fa-solid fa-down-left-and-up-right-to-center"></i>
      </div>
    </div>

    <div class="module_list">
      <Link
        class="link-item"
        v-for="mod in modules"
        :key="mod.slug"
        :href="mod.path"
        :style="appSettings.use_individual_module_colors == '0'
          ? { '--module-color': appSettings.primary_color }
          : { '--module-color': mod.color }
        "
        @mouseenter="onModuleMouseEnter($event, mod)"
        @mouseleave="onModuleMouseLeave"
      >
        <div v-if="!collapsedSidebar" class="module-color">
          <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
        </div>

        <div :class="['link-label', { active: currentUrl.startsWith(mod.path) }]">
          <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
          <span v-if="!collapsedSidebar">{{ mod.label }}</span>
        </div>
      </Link>
    </div>

    <div class="sidebar-footer">
      <!-- footer stuff -->
    </div>
  </aside>

  <!-- Global floating tooltip -->
  <div
    v-if="showTooltip && collapsedSidebar"
    class="sidebar-tooltip"
:style="[
  {
    top: tooltipPosition.top + 'px',
    left: tooltipPosition.left + 'px'
  },
  appSettings.use_individual_module_colors == '0'
    ? { '--module-color': appSettings.primary_color }
    : { '--module-color': tooltipcolor }
]"
  >
    {{ tooltipText }}
  </div>
</template>
