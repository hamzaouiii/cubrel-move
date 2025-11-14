<!-- resources/js/Components/Sidebar.vue -->
<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const modules = computed(() => page.props.modules ?? [])
const currentUrl = computed(() => page.url)
</script>

<template>
  <nav class="bg-light border-end" style="min-height: 100vh;">
    <div class="p-3">
      <div class="fw-bold mb-3">
        <!-- Optional: show current locale -->
        {{ page.props.locale?.toUpperCase?.() ?? 'DE' }}
      </div>

      <ul class="nav nav-pills flex-column gap-1">
        <li
          v-for="mod in modules"
          :key="mod.slug"
          class="nav-item"
        >
          <Link
            :href="mod.path"
            class="nav-link d-flex align-items-center"
            :class="{ active: currentUrl.startsWith(mod.path) }"
          >
            <i
              v-if="mod.icon"
              class="me-2"
              :class="['fa-solid', mod.icon]"
              :style="{ color: mod.color }"
            ></i>

            <span>{{ mod.label }}</span>
          </Link>
        </li>
      </ul>
    </div>
  </nav>
</template>

<style scoped>
.nav-link.active {
  font-weight: 600;
}
</style>
