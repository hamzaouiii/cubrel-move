<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
  ownedRecords: Object,
});

const modules = computed(() =>
  Object.entries(props.ownedRecords).map(([key, records]) => ({
    key,
    label: records[0]?.label ?? key,
    slug: records[0]?.slug ?? key,
    count: records.length,
  })),
);

const total = computed(() =>
  modules.value.reduce((sum, m) => sum + m.count, 0),
);

const getModuleBySlug = (slug) => {
  return usePage().props.modules.find((m) => m.slug === slug);
};
</script>

<template>
  <div class="dashboard__card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        $t("globals.dashboard.your_records")
      }}</span>
    </div>
    <div class="dashboard__card__body">
      <div class="dashboard__card__header-value">
        {{ total.toLocaleString() }}
      </div>
      <p class="text--muted">{{ $t("globals.dashboard.total_records") }}</p>
      <hr class="divider" />
      <ul class="mod-list">
        <li
          v-for="mod in modules"
          :key="mod.key"
          class="mod-list__item"
          :style="{ '--mod-color-dash': getModuleBySlug(mod.slug).color }"
        >
          <span class="mod-list__link">
            <i :class="getModuleBySlug(mod.slug).icon"></i>
            <Link :href="`/${mod.slug}?filter=my_records`">
              {{ $t(mod.label) }}
            </Link>
          </span>

          <span class="mod-list__count">{{ mod.count.toLocaleString() }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>
