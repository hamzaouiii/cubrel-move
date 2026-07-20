<script setup>
const { locale, locales } = useI18n();
const switchLocalePath = useSwitchLocalePath();

const flags = { de: "🇩🇪", en: "🇬🇧" };

const open = ref(false);
const rootRef = ref(null);

const current = computed(() => locales.value.find((l) => l.code === locale.value));

const toggle = () => {
  open.value = !open.value;
};
const close = () => {
  open.value = false;
};

const handleClickOutside = (event) => {
  if (rootRef.value && !rootRef.value.contains(event.target)) {
    close();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <div ref="rootRef" class="lang-switch">
    <button
      type="button"
      class="lang-switch__trigger"
      :aria-expanded="open"
      @click="toggle"
    >
      <span class="lang-switch__flag">{{ flags[current?.code] }}</span>
      <span class="lang-switch__code">{{ current?.code?.toUpperCase() }}</span>
      <i class="lang-switch__caret" aria-hidden="true">▾</i>
    </button>

    <ul v-if="open" class="lang-switch__menu">
      <li v-for="l in locales" :key="l.code">
        <NuxtLink
          :to="switchLocalePath(l.code)"
          class="lang-switch__item"
          :class="{ 'lang-switch__item--active': l.code === locale }"
          @click="close"
        >
          <span class="lang-switch__flag">{{ flags[l.code] }}</span>
          <span>{{ l.name }}</span>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.lang-switch {
  position: relative;
}

.lang-switch__trigger {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font: inherit;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-body);
  background: none;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.35rem 0.6rem;
  cursor: pointer;
}

.lang-switch__trigger:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.lang-switch__flag {
  font-size: 1rem;
  line-height: 1;
}

.lang-switch__caret {
  font-size: 0.7rem;
  font-style: normal;
  color: var(--color-muted);
}

.lang-switch__menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 150px;
  margin: 0;
  padding: 0.35rem;
  list-style: none;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  z-index: 60;
}

.lang-switch__item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0.6rem;
  border-radius: var(--radius-sm);
  text-decoration: none;
  color: var(--color-ink);
  font-size: 0.9rem;
}

.lang-switch__item:hover {
  background: var(--color-surface-alt);
}

.lang-switch__item--active {
  color: var(--color-primary);
  font-weight: 600;
}
</style>
