<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import ImageField from "@/Pages/Components/FiledTypes/ImageField.vue";

const props = defineProps({
  instance: { type: Object, required: true },
});

const state = ref("loading");
const rows = ref([]);
const peopleModuleSlug = ref("");
const aggregate = ref("count");

async function load() {
  state.value = "loading";
  try {
    const { data } = await axios.post("/dashboard/widget-data", {
      type: props.instance.type,
      config: props.instance.config,
    });
    rows.value = data.rows;
    peopleModuleSlug.value = data.peopleModuleSlug;
    aggregate.value = data.aggregate;
    state.value = rows.value.length ? "loaded" : "empty";
  } catch {
    state.value = "error";
  }
}

function formatValue(v) {
  if (v === null || v === undefined) return "—";
  if (aggregate.value === "count" || Number.isInteger(v)) {
    return Number(v).toLocaleString();
  }
  return Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 });
}

onMounted(load);
defineExpose({ load });
</script>

<template>
  <div class="dashboard__card pw-card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">
        {{ instance.config.label || instance.config.module }}
      </span>
    </div>

    <div class="pw-card__body">
      <div v-if="state === 'loading'" class="pw-card__state">
        <i class="fa-solid fa-atom fa-spin"></i>
        <span>{{ $t("globals.dashboard.loading") }}</span>
      </div>
      <div v-else-if="state === 'error'" class="pw-card__state">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>{{ $t("globals.dashboard.failed_to_load") }}</span>
      </div>
      <div v-else-if="state === 'empty'" class="pw-card__state">
        <i class="fa-solid fa-inbox"></i>
        <span>{{ $t("globals.dashboard.no_records") }}</span>
      </div>
      <ul v-else class="pw-list">
        <li v-for="row in rows" :key="row.id" class="pw-list__item">
          <a :href="`/${peopleModuleSlug}/${row.id}`" class="pw-list__link">
            <ImageField
              class="pw-list__avatar"
              :model-value="row.avatar"
              :related_label="row.name"
              mode="table"
            />
            <span class="pw-list__name">{{ row.name }}</span>
          </a>
          <span class="pw-list__value">{{ formatValue(row.value) }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>
