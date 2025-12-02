<script setup>
import { computed, ref, onMounted } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { getCurrentInstance } from 'vue'

const form = useForm({})
const logout = () => {
  form.post('/logout')
}

const { proxy } = getCurrentInstance()
const t = proxy.$t

const SIDEBAR_KEY = 'sidebar-collapsed'
const page = usePage()
const modules = computed(() => page.props.modules ?? [])
const currentUrl = computed(() => page.url)
const appSettings = usePage().props.appSettings

const collapsedSidebar = ref(false)
const toggleSidebar = () => {
  collapsedSidebar.value = !collapsedSidebar.value
  localStorage.setItem(SIDEBAR_KEY, collapsedSidebar.value ? '1' : '0')
}

onMounted(() => {
  const saved = localStorage.getItem(SIDEBAR_KEY)
  collapsedSidebar.value = saved === '1'
})

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
    left: rect.right + 10,
  }
  showTooltip.value = true
}

const onModuleMouseLeave = () => {
  showTooltip.value = false
}

const onCollapserMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect()
  tooltipText.value = collapsedSidebar.value ? t('sidebar.expand') : t('sidebar.close')
  tooltipcolor.value = appSettings.primary_color
  tooltipPosition.value = {
    top: rect.top + rect.height / 2,
    left: rect.right + 10,
  }
  showTooltip.value = true
}

const onCollapserMouseLeave = () => {
  showTooltip.value = false
}
</script>

<template>
  <aside :class="{ 'open': !collapsedSidebar, 'collapsed': collapsedSidebar }">
    <div @click="toggleSidebar" class="collapser">
      <div
        class="collapser_icon"
        :style="{ '--setting-primary-color': appSettings.primary_color }"
        @mouseenter="onCollapserMouseEnter($event)"
        @mouseleave="onCollapserMouseLeave"
      >
        <i :class="!collapsedSidebar ? 'fa-solid fa-angles-left' : 'fa-solid fa-bars'"></i>
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
        <div :class="['link-label', { active: currentUrl.startsWith(mod.path) }]">
          <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
          <span :class="[{hide: collapsedSidebar}]">{{ mod.label }}</span>
        </div>
      </Link>
    </div>

    <div class="sidebar-footer"></div>
  </aside>

  <div
    v-if="showTooltip"
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