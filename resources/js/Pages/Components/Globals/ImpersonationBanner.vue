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
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
    </button>
  </div>
</template>

<style scoped lang="scss">
.impersonation-banner {
  position: fixed;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  z-index: 1001;
  padding: 2px 10px;
  background-color: var(--primary-color);
  color: whitesmoke;

  border-bottom: 1px solid var(--color-border-glass);

  &__content {
    display: flex;
    align-items: center;
    padding: 0 50px;
  }

  &__text {
    font-size: 0.8rem;

    .user {
      font-weight: 600;
      margin: 0 4px;
      text-decoration: underline;
    }
  }

  &__button {
    i {
      transform: rotate(180deg);
    }
    background-color: #111827;
    color: #ffffff;

    padding: 6px;
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
