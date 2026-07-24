<script setup>
import { ref, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  instance: { type: Object, required: true },
});

const appSettings = usePage().props.appSettings;

const state = ref("loading");
const rows = ref([]);
const moduleSlug = ref("");
const moduleIcon = ref("");
const moduleColor = ref("");

const displayColor = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : moduleColor.value;
});

async function load() {
  state.value = "loading";
  try {
    const { data } = await axios.post("/dashboard/widget-data", {
      type: props.instance.type,
      config: props.instance.config,
    });
    rows.value = data.rows;
    moduleSlug.value = data.moduleSlug;
    moduleIcon.value = data.moduleIcon;
    moduleColor.value = data.moduleColor;
    state.value = rows.value.length ? "loaded" : "empty";
  } catch {
    state.value = "error";
  }
}

onMounted(load);
defineExpose({ load });
</script>

<template>
  <div class="dashboard__card rl-card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">
        {{ instance.config.label || instance.config.module }}
      </span>
    </div>

    <div class="rl-card__body">
      <div v-if="state === 'loading'" class="rl-card__state">
        <i class="fa-solid fa-atom fa-spin"></i>
        <span>{{ $t("globals.dashboard.loading") }}</span>
      </div>
      <div v-else-if="state === 'error'" class="rl-card__state">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>{{ $t("globals.dashboard.failed_to_load") }}</span>
      </div>
      <div v-else-if="state === 'empty'" class="rl-card__state">
        <i class="fa-solid fa-inbox"></i>
        <span>{{ $t("globals.dashboard.no_records") }}</span>
      </div>
      <ul v-else class="rl-list">
        <li v-for="row in rows" :key="row.id" class="rl-list__item">
          <span
            class="rl-list__icon"
            :style="{ background: displayColor + '20', color: displayColor }"
          >
            <i :class="moduleIcon"></i>
          </span>
          <a :href="`/${moduleSlug}/${row.id}`" class="rl-list__name">
            {{ row.name }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>
