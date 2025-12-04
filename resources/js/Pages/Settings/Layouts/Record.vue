<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'

 

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object
})


const page = usePage()
const currentPath = page.url;

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
        <h6>{{module.label }}</h6>
        <!-- <h6>{{ $t('settings.create_new_layouts')  }}</h6> -->
      </div>
    </div>

      <div class="layout_list"> 
          <Link class="layout_list_item" :href="currentPath+'/list'">
            <span class="layout_list_item_icon">
              <i class="fa-solid fa-table-list"></i>
            </span>
            <span class="layout_list_item_label">
              {{$t('layouts.list')}}
            </span>
          </Link>

          <Link class="layout_list_item" :href="currentPath+'/record'">
            <span class="layout_list_item_icon">
              <i class="fa-regular fa-address-card"></i>
            </span>
            <span class="layout_list_item_label">
              {{$t('layouts.record')}}
            </span>
          </Link>
        </div>
    </div>
</template>