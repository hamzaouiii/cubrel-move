<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'

 

defineOptions({
  layout: Layout,
});
const props = defineProps({
  item: Object,
  module: Object,
  type: String
})


console.log(props.module)
</script>

<template>
      <Head>
      <title>{{module.label}} - {{ $t('settings.label')}}</title>
    </Head>
    <div class="layout">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5><Link href="/settings">{{ $t('settings.label')  }} </Link></h5>
        <span>></span>
        <h5><Link href="/settings/customisation/layouts">{{ $t('layouts.label')  }} </Link></h5>
        <span>></span>
        <h5><Link :href="'/settings/customisation/layouts/'+module.id">{{ module.label }} </Link></h5>
        <span>></span>
        <h6>{{type }}</h6>
      </div>
    </div>
    </div>
</template>