
<script setup>
import Sidebar from '@/Pages/Admin/Components/Sidebar.vue';
import Topbar from '@/Pages/Admin/Components/Topbar.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user ?? null;
const csrf = page.props.csrf_token ?? document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content');
</script>

<template>
  <div class="admin d-flex">
    <sidebar></sidebar>
    <main class="content flex-grow-1">
      <Topbar></Topbar>
      <slot />
    </main>
  </div>
</template>
