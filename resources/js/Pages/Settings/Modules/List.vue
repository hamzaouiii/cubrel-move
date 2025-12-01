
<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3'
import ModuleManager from '@/Pages/Components/Settings/ModuleManager.vue';

import { useTrans } from '@/Composables/useTrans'
const { t } = useTrans()

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
    <title>{{item.label }} - {{ t('settings.label')  }} - Automatisierung Regensburg</title>
  </Head>
  <div class="settings">
      <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">{{ t('settings.label')  }}</Link></h5>
        <span>></span> 
        <h6>{{item.label }}</h6>
    </div>
      <div class="settings_header_action">
        <Link class="create-btn" :href="page.url + '/create'">{{t('settings.create_new_module')}}</Link>
      </div>
    </div>
    <div class="settings_items">
      <ModuleManager v-if="setting_modules" :modules="setting_modules">
      
      </ModuleManager>
    </div>
  </div>
</template>