<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";

const user = usePage().props.auth?.user;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  url: String,
  error: {
    type: String,
    required: true,
    default: "404",
  },
});

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit("/");
  }
};
</script>

<template>
  <Head>
    <title>{{ $t(`globals.errorpage.${error}.title`) }} - Cubrel</title>
  </Head>

  <div class="error-page" :style="{ '--error-code': `'${error}'` }">
    <div class="error-page__inner">
      <div class="error-page__code">{{ error }}</div>
      <h1 class="error-page__title">
        {{ $t(`globals.errorpage.${error}.heading`) }}
      </h1>

      <p class="error-page__desc">
        {{ $t(`globals.errorpage.${error}.description`) }}
        <code v-if="error == '404' || error == '403'">{{ $page.url }}</code>
      </p>

      <div class="error-page__actions" v-if="user">
        <Link href="/" class="error-page__btn error-page__btn--primary">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path
              d="M7 1L1 7l6 6M1 7h12"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          {{ $t(`globals.errorpage.back_to_dashboard`) }}
        </Link>

        <button
          class="error-page__btn error-page__btn--secondary"
          @click="goBack()"
        >
          {{ $t(`globals.errorpage.go_back`) }}
        </button>
      </div>
    </div>
  </div>
</template>
