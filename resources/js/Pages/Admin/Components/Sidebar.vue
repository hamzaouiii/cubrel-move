<template>
  
    <aside class="sidebar">
      <Link href="/" class="logo">
        <img src="/img/logo/logo.svg" alt="logo"  width="240"height="180" />
      </Link>
      <ul class="module_list">
        <li
          v-for="mod in modules"
          :key="mod.slug"
        >
          <div class="module-color" 
          :style="{ background: mod.color, color: 'white' }">
                    <i
              v-if="mod.icon"
              :class="['fa-solid', mod.icon]"

            ></i></div>
          <Link
            :href="mod.path"
            :class="{ active: currentUrl.startsWith(mod.path) }"
          >
            <i
              v-if="mod.icon"
              :class="['fa-solid', mod.icon]"

            ></i>

            <span :style="{ '--hover-color': mod.color}">{{ mod.label }}</span>
          </Link>
        </li>
 
      </ul>
      <div class="sidebar-footer">
        <span>{{ page.props.locale?.toUpperCase?.() ?? 'DE' }}</span>
        <button class="btn btn-primary" @click="logout">Logout</button>

      </div>

    </aside>

</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'

const form = useForm({})
const logout = () => {
  form.post('/ar-admin/logout')  
}
const page = usePage()
const modules = computed(() => page.props.modules ?? [])
const currentUrl = computed(() => page.url)

</script>