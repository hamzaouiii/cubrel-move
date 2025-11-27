
<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3'
import ModuleManager from '@/Pages/Components/ModuleManager.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
  setting_modules: Object
})


const page = usePage()
const module = computed(() => page.props.item || page.props)

</script>
<template>
    <Head>
    <title>{{item.name }} - Settings - Automatisierung Regensburg</title>
  </Head>
  <div class="settings">
      <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">Settings</Link></h5>
        <span>></span> 
        <h6>{{item.name }}</h6>
    </div>
      <div class="settings_header_action">
        <Link class="create-btn" :href="page.url + '/create'">Create New Module</Link>
      </div>
    </div>
    <div class="settings_items">
      <ModuleManager v-if="setting_modules" :modules="setting_modules">
      
      </ModuleManager>
    </div>
  </div>
</template>