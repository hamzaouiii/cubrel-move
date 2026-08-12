<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
  Chart,
  BarElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  BarController,
  LineController,
} from "chart.js";
import { useChartTheme } from "@/Composables/useChartTheme.js";

Chart.register(
  BarElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  BarController,
  LineController,
);

const props = defineProps({
  instance: { type: Object, required: true },
});

const page    = usePage();
const primary = page.props.appSettings?.primary_color ?? "#3b8bff";
const color   = computed(() => props.instance.config.color ?? primary);
const chartRef = ref(null);
let chartInst = null;

const { onThemeChange, axisTextColor, gridColor } = useChartTheme();

const state = ref("loading");
const chartData = ref(null);

async function load() {
  state.value = "loading";
  chartInst?.destroy();
  chartInst = null;

  try {
    const { data } = await axios.post("/dashboard/widget-data", {
      type: props.instance.type,
      config: props.instance.config,
    });
    chartData.value = data;
    state.value = data.labels?.length ? "loaded" : "empty";

    if (state.value === "loaded") {
      await nextTick();
      renderChart();
    }
  } catch {
    state.value = "error";
  }
}

function renderChart() {
  if (!chartRef.value || !chartData.value) return;

  const type = props.instance.config.chartType ?? "bar";

  chartInst = new Chart(chartRef.value, {
    type,
    data: {
      labels: chartData.value.labels,
      datasets: [
        {
          data: chartData.value.series[0].data,
          backgroundColor: type === "bar" ? color.value : "transparent",
          borderColor: color.value,
          borderWidth: 2,
          borderRadius: type === "bar" ? 4 : 0,
          pointRadius: type === "line" ? 3 : 0,
          barPercentage: 0.5,
          fill: false,
          tension: 0.3,
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
          ticks: { font: { size: 11 }, color: axisTextColor() },
        },
        y: {
          grid: { color: gridColor() },
          ticks: { font: { size: 11 }, color: axisTextColor() },
          beginAtZero: true,
        },
      },
    },
  });
}

onMounted(load);
onBeforeUnmount(() => chartInst?.destroy());
onThemeChange(() => {
  if (state.value === "loaded") renderChart();
});
defineExpose({ load });
</script>

<template>
  <div class="dashboard__card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">
        {{ instance.config.label || "Records over time" }}
      </span>
    </div>
    <div class="dashboard__card__body">
      <div class="chart-wrap">
        <div v-if="state === 'loading'" class="chart-empty">
          <i class="fa-solid fa-atom fa-spin chart-empty__icon"></i>
          <span class="chart-empty__label">{{
            $t("globals.dashboard.loading")
          }}</span>
        </div>
        <div v-else-if="state === 'error'" class="chart-empty">
          <i class="fa-solid fa-triangle-exclamation chart-empty__icon"></i>
          <span class="chart-empty__label">{{
            $t("globals.dashboard.failed_to_load")
          }}</span>
        </div>
        <div v-else-if="state === 'empty'" class="chart-empty">
          <i class="fa-solid fa-chart-bar chart-empty__icon"></i>
          <span class="chart-empty__label">{{
            $t("globals.dashboard.no_data_period")
          }}</span>
        </div>
        <canvas
          v-else
          ref="chartRef"
          role="img"
          :aria-label="`${instance.config.label || 'Time series'} chart`"
        ></canvas>
      </div>
    </div>
  </div>
</template>
