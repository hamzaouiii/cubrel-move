<script setup>
defineProps({
  impersonator: { type: Object, default: null },
});
</script>

<template>
  <span v-if="impersonator" class="impersonation-badge">
    <i class="fa-solid fa-user-secret"></i>
    {{ $t("globals.audit_trail.as_impersonator", { name: impersonator.name }) }}
  </span>
</template>
