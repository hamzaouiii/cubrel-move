
<script setup>
import Sidebar from '@/Pages/Components/Sidebar.vue';
import Topbar from '@/Pages/Components/Topbar.vue';
import Alerts from '@/Pages/Components/Alerts.vue';

import { usePage } from '@inertiajs/vue3';
import { computed, provide } from 'vue'

const page = usePage();
const user = page.props.auth?.user ?? null;
const csrf = page.props.csrf_token ?? document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content');

const appSettings = computed(() => page.props.appSettings || {})
const useModuleColors = computed(() => appSettings.value.useModuleColors)

provide('useModuleColors', useModuleColors)

</script>



<template>
  <div class="admin d-flex">
    <sidebar></sidebar>
    <Alerts />
    <main class="content flex-grow-1">
      <Topbar></Topbar>
      <slot />
    </main>
  </div>
</template>
