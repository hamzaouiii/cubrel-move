<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
  Chart,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  DoughnutController,
  BarController,
} from "chart.js";
import { CHART_PALETTE } from "../dashboardUi.js";

Chart.register(
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  DoughnutController,
  BarController,
);

const props = defineProps({
  instance: { type: Object, required: true },
});

const page = usePage();
const primary = page.props.appSettings?.primary_color ?? "#3b8bff";

const chartRef = ref(null);
let chartInst = null;

const state = ref("loading");
const chartData = ref(null);

const chartType = props.instance.config.chartType ?? "donut";
const palette   = props.instance.config.palette ?? CHART_PALETTE;

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

  const labels = chartData.value.labels;
  const data = chartData.value.series[0].data;
  const colors = labels.map((_, i) => palette[i % palette.length]);

  if (chartType === "donut") {
    chartInst = new Chart(chartRef.value, {
      type: "doughnut",
      data: {
        labels,
        datasets: [
          {
            data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "68%",
        plugins: {
          legend: {
            position: "right",
            labels: { font: { size: 12 }, color: "#475569", padding: 12 },
          },
          tooltip: { enabled: true },
        },
      },
    });
  } else {
    chartInst = new Chart(chartRef.value, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            data,
            backgroundColor: colors,
            borderRadius: 4,
            barPercentage: 0.6,
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
}

onMounted(load);
onBeforeUnmount(() => chartInst?.destroy());
defineExpose({ load });
</script>

<template>
  <div class="dashboard__card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        instance.config.label || instance.config.module
      }}</span>
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
          <i class="fa-solid fa-chart-pie chart-empty__icon"></i>
          <span class="chart-empty__label">{{
            $t("globals.dashboard.no_data")
          }}</span>
        </div>
        <canvas
          v-else
          ref="chartRef"
          role="img"
          :aria-label="`${instance.config.label || 'Breakdown'} chart`"
        ></canvas>
      </div>
    </div>
  </div>
</template>
