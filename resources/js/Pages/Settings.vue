
<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue';
import { ref, computed } from 'vue'
defineOptions({
  layout: Layout,
});

const pageProps = defineProps({
  settings: Object,
})
const search = ref('')
const filteredSettings = computed(() => {
  if (!search.value) {
    return pageProps.settings
  }
    const term = search.value.toLowerCase()
    
  return pageProps.settings
    .map(setting => {
      const matchesSetting =
        (setting.name && setting.name.toLowerCase().includes(term)) ||
        (setting.category && setting.category.toLowerCase().includes(term))

      const filteredItems = (setting.items || []).filter(item => {
        return (
          (item.name && item.name.toLowerCase().includes(term)) ||
          (item.category && item.category.toLowerCase().includes(term)) ||
          (item.path && item.path.toLowerCase().includes(term))
        )
      })

      // If the setting itself matches, keep all its items
      if (matchesSetting) {
        return { ...setting, items: setting.items || [] }
      }

      // Otherwise only keep it if any items matched
      if (filteredItems.length > 0) {
        return { ...setting, items: filteredItems }
      }

      return null
    })
    .filter(Boolean)
})

</script>

<template>
  <Head>
    <title>Settings - Automatisierung Regensburg</title>
  </Head>
  <div class="settings">
    <div class="settings_header">
      <div class="settings_header_title">
        <h3>Settings</h3>
    </div>
      <div class="settings_header_search">


            <input
      v-model="search"
      type="search"
      class="setting-search"
      placeholder="Search settings..."
    />

      </div>
    </div>
    <div class="settings_content">
      <div v-for="s in filteredSettings" class="settings_content_section">
        <div class="settings_content_section_header">
          <div class="settings_content_section_header_title">
            <h6>{{ s.name }}</h6>
          </div>
          <div class="settings_content_section_header_desc">
            <p>{{ s.description }}</p>
          </div>
        </div>
        <div  class="settings_content_section_links">
          <Link v-for="i in s.items" :href="i.path">
            <i :class="i.icon"></i>
            {{ i.name }}
          </Link>
        </div>
      </div>


    </div>
  </div>
</template>