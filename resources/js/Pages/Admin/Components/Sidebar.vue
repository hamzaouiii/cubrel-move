<template>
  
    <aside class="sidebar">
      <Link href="/" class="logo">
        <img src="/img/logo/logo.svg" alt="logo"  width="240"height="180" />
      </Link>
      <div class="module_list">
        <Link class="link-item" v-for="mod in modules" :key="mod.slug" :href="mod.path">
          <div class="module-color" :style="{ background: mod.color, color: 'white' }">
            <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
          </div>
          <div  :class="['link-label',{ active: currentUrl.startsWith(mod.path) }]">
            <i v-if="mod.icon" :class="['fa-solid', mod.icon]"></i>
            <span :style="{ '--hover-color': mod.color}">{{ mod.label }}</span>
          </div>
        </Link>
 
      </div>
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