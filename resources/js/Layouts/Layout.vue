<script setup>
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

const user = usePage().props.auth?.user;
</script>

<template>
  <AppLayout v-if="user">
    <slot />
  </AppLayout>
  <GuestLayout v-else>
    <slot />
  </GuestLayout>
</template>
