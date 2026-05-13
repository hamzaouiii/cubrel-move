<!-- components/Dashboard/DealStages.vue -->
<script setup>
import { ref, onMounted, onBeforeUnmount, getCurrentInstance } from "vue";
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend,
  DoughnutController,
} from "chart.js";

Chart.register(ArcElement, Tooltip, Legend, DoughnutController);

const props = defineProps({
  dealStages: {
    type: Object,
    required: true,
    default: () => ({ won: 0, lost: 0, open: 0 }),
  },
  appSettings: Object,
});

const donutChartRef = ref(null);
let donutChartInstance = null;

const { proxy } = getCurrentInstance();
const t = proxy.$t;

onMounted(() => {
  if (donutChartRef.value) {
    const { won, lost, open } = props.dealStages;
    const total = won + lost + open;
    donutChartInstance = new Chart(donutChartRef.value, {
      type: "doughnut",
      data: {
        labels: [
          t("globals.dashbaord.won"),
          t("globals.dashbaord.open"),
          t("globals.dashbaord.lost"),
        ],
        datasets: [
          {
            data: total > 0 ? [won, open, lost] : [1, 1, 1],
            backgroundColor:
              total > 0
                ? ["#1d9e75", "#3B8BFF", "#D85A30"]
                : ["#e5e7eb", "#e5e7eb", "#e5e7eb"],
            borderWidth: 2,
            borderColor: "#fff",
            hoverOffset: 8,
          },
        ],
      },
      options: {
        responsive: false,
        cutout: "72%",
        plugins: {
          legend: { display: false },
          tooltip: { enabled: total > 0 },
        },
      },
    });
  }
});

onBeforeUnmount(() => {
  donutChartInstance?.destroy();
});
</script>

<template>
  <div class="dashboard__card">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        $t("globals.dashboard.deal_stages")
      }}</span>
    </div>
    <div class="dashboard__card__body">
      <!-- Donut -->
      <div class="donut-block">
        <div class="donut-wrap">
          <canvas
            ref="donutChartRef"
            width="110"
            height="110"
            role="img"
            aria-label="Donut chart of deal stages: won, open, lost"
          ></canvas>
          <div class="donut-center">
            <span class="donut-center__num">
              {{ dealStages.won + dealStages.lost + dealStages.open }}
            </span>
            <span class="donut-center__sub">{{
              $t("globals.dashboard.total")
            }}</span>
          </div>
        </div>
        <ul class="stage-legend">
          <li class="stage-legend__item">
            <span class="stage-legend__dot stage-legend__dot--won"></span>
            <span class="text--muted">{{ $t("globals.dashboard.won") }}</span>
            <span class="stage-legend__val">{{ dealStages.won }}</span>
          </li>
          <li class="stage-legend__item">
            <span class="stage-legend__dot stage-legend__dot--open"></span>
            <span class="text--muted">{{ $t("globals.dashboard.open") }}</span>
            <span class="stage-legend__val">{{ dealStages.open }}</span>
          </li>
          <li class="stage-legend__item">
            <span class="stage-legend__dot stage-legend__dot--lost"></span>
            <span class="text--muted">{{ $t("globals.dashboard.lost") }}</span>
            <span class="stage-legend__val">{{ dealStages.lost }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
