

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: AdminLayout,
});

const { props } = usePage();
const module = props.module; 
</script>

<template>
  <div class="module-list-view">
    <h1 class="h4 mb-3">{{module}}</h1>
  </div>
</template>
