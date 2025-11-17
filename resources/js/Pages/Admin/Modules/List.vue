

<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: AdminLayout,
});

const { props } = usePage();
const module = props.module; 
</script>

<template>
    <Head>
    <title>{{module}} - Automatisierung Regensburg</title>
  </Head>
  <div class="module-list-view">
    <h1 class="h4 mb-3">{{module}}</h1>
  </div>
</template>
