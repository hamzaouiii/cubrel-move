
<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3'
import ModuleManager from '../Components/ModuleManager.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
  modules: Object
})


const page = usePage()
const module = computed(() => page.props.item || page.props)

</script>
<template>
    <Head>
    <title>Settings - Automatisierung Regensburg</title>
  </Head>
  <div class="settings"></div>
      <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">Settings</Link></h5>
        <span>></span> 
        <h6>{{item.name }}</h6>
    </div>
      <div class="settings_header_search">

      </div>
    </div>
    <div class="settings_items">
      <ModuleManager v-if="modules" :modules="modules">
      
      </ModuleManager>
    </div>
    <h1></h1>
</template>