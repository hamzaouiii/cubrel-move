
<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue';

defineOptions({
  layout: Layout,
});

const pageProps = defineProps({
  settings: Object,
})
console.log(pageProps.settings)
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
        type="text"
        class="setting-search"
        placeholder="Search Settings">

      </div>
    </div>
    <div class="settings_content">
      <div v-for="s in settings" class="settings_content_section">
        <div class="settings_content_section_header">
          <div class="settings_content_section_header_title">
            <h6>{{ s.name }}</h6>
          </div>
          <div class="settings_content_section_header_desc">
            <p>{{ s.description }}</p>
          </div>
        </div>
      </div>


    </div>
  </div>
</template>