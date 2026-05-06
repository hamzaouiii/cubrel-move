<script setup>
import { computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();

const impersonating = computed(() => page.props.auth.impersonating);
const impersonator = computed(() => page.props.auth.impersonator);
const currentUser = computed(() => page.props.auth.user);

const leaveImpersonation = () => {
  router.post(
    "/leaveimpersonate",
    {},
    {
      preserveScroll: true,
    },
  );
};
</script>

<template>
  <div v-if="impersonating" class="impersonation-banner">
    <div class="impersonation-banner__content">
      <span class="impersonation-banner__text">
        <span class="user">{{ impersonator.name }}</span>
        {{ $t("globals.impersonate.is_logged_in_as") }}
        <span class="user">{{ currentUser.name }}</span>
      </span>
    </div>

    <button class="impersonation-banner__button" @click="leaveImpersonation">
      {{ $t("globals.impersonate.return_to_original_session") }}
    </button>
  </div>
</template>

<style scoped lang="scss">
.impersonation-banner {
  position: fixed;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  z-index: 1001;
  padding: 10px 16px;
  background-color: var(--primary-color); // yellow
  color: whitesmoke;

  border-bottom: 1px solid rgba(0, 0, 0, 0.1);

  &__content {
    display: flex;
    align-items: center;
  }

  &__text {
    font-size: 14px;

    .user {
      font-weight: 600;
      margin: 0 4px;
      text-decoration: underline;
    }
  }

  &__button {
    background-color: #111827;
    color: #ffffff;

    padding: 6px 12px;
    font-size: 13px;

    border: none;
    border-radius: 4px;
    cursor: pointer;

    transition: background-color 0.2s ease;

    &:hover {
      background-color: #374151;
    }

    &:active {
      background-color: #1f2937;
    }
  }
}
</style>
