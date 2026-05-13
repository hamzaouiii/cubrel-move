<script setup>
import { ref, onMounted, onBeforeUnmount, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
  Chart,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
} from "chart.js";

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
  dealsOverTime: { type: Array, required: true },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;
const oppChartRef = ref(null);
let oppChartInstance = null;

// Chart mode toggle
const chartMode = ref("count");

function switchMode(mode) {
  chartMode.value = mode;
  if (!oppChartInstance) return;
  const newData =
    mode === "count"
      ? props.dealsOverTime.map((d) => d.count)
      : props.dealsOverTime.map((d) => d.value);
  oppChartInstance.data.datasets[0].data = newData;
  oppChartInstance.data.datasets[0].label =
    mode === "count"
      ? t("globals.dashboard.count")
      : t("globals.dashboard.value") + " (€)";
  oppChartInstance.update();
}

onMounted(() => {
  if (oppChartRef.value && props.dealsOverTime.length) {
    oppChartInstance = new Chart(oppChartRef.value, {
      type: "bar",
      data: {
        labels: props.dealsOverTime.map((d) => d.month),
        datasets: [
          {
            label: t("globals.dashboard.count"),
            data: props.dealsOverTime.map((d) => d.count),
            backgroundColor: appSettings.primary_color,
            borderRadius: 4,
            barPercentage: 0.5,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, color: "#888" },
          },
          y: {
            grid: { color: "rgba(0,0,0,0.05)" },
            ticks: { font: { size: 11 }, color: "#888" },
            beginAtZero: true,
          },
        },
      },
    });
  }
});

onBeforeUnmount(() => {
  oppChartInstance?.destroy();
});
</script>

<template>
  <div class="dashboard__card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        $t("globals.dashboard.deals_over_time")
      }}</span>
      <div class="badge-group">
        <button
          class="badge"
          :class="chartMode === 'count' ? 'badge--active' : 'badge--outline'"
          @click="switchMode('count')"
        >
          {{ $t("globals.dashboard.count") }}
        </button>
        <button
          class="badge"
          :class="chartMode === 'value' ? 'badge--active' : 'badge--outline'"
          @click="switchMode('value')"
        >
          {{ $t("globals.dashboard.value") }}
        </button>
      </div>
    </div>
    <div class="dashboard__card__body">
      <div class="chart-wrap">
        <canvas
          v-if="dealsOverTime.length"
          ref="oppChartRef"
          role="img"
          aria-label="Bar chart showing opportunities count or value per month"
        ></canvas>
        <div v-else class="chart-empty">
          <i
            class="fa-solid fa-chart-bar chart-empty__icon"
            aria-hidden="true"
          ></i>
          <span class="chart-empty__label">
            {{ dealsOverTime.length }}
            {{ $t("globals.dashboard.months_of_data_ready") }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
