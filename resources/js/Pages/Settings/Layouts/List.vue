<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3' 
import LayoutManager from '@/Pages/Components/Settings/LayoutManager.vue';
defineOptions({
  layout: Layout,
});


const props = defineProps({
  item: Object,
  setting_modules: Object
})
const page = usePage()


</script>

<template>
    <Head>
    <title>{{item.label }} - {{ $t('settings.label')  }} - Automatisierung Regensburg</title>
  </Head>
  <div class="settings">
      <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">{{ $t('settings.label')  }}</Link></h5>
        <span>></span> 
        <h6>{{item.label }}</h6>
    </div>
      <div class="settings_header_action">
      </div>
    </div>
    <div class="settings_items">
      <LayoutManager v-if="setting_modules" :modules="setting_modules">
      
      </LayoutManager>
    </div>
  </div>

</template>