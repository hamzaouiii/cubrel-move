<script setup>
import { usePage, Head } from "@inertiajs/vue3";

const page = usePage();
const appSettings = page.props.appSettings;
</script>

<template>
  <Head>
    <title>Cubrel - {{ $t("globals.setup_required.title") }}</title>
  </Head>
  <div
    class="setup-required"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="setup-required__container">
      <div class="setup-card">
        <div class="setup-card__icon">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h1 class="setup-card__title">
          {{ $t("globals.setup_required.heading") }}
        </h1>
        <p class="setup-card__desc">
          {{ $t("globals.setup_required.description") }}
        </p>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.setup-required {
  --primary-color: var(--primary-color);
  --text-main: #111827;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --bg-hover: #f9fafb;

  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Fira Sans", "Heebo", sans-serif;
  background-color: whitesmoke;

  &__container {
    position: relative;
    width: 100%;
    max-width: 440px;
    margin: 0 24px;
    z-index: 10;
    animation: slide-up 0.4s ease-out;
  }
}

.setup-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  padding: 44px 36px;
  text-align: center;

  &__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 24px;
    background: color-mix(in srgb, var(--primary-color) 10%, white);
    color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
  }

  &__title {
    margin: 0 0 12px;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-main);
    letter-spacing: -0.02em;
  }

  &__desc {
    margin: 0;
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .setup-required__container {
    margin: 0 16px;
  }

  .setup-card {
    padding: 32px 24px;
  }
}
</style>
