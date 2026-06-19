<script setup>
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import MyRecords from "@/Pages/Components/Dashbaord/MyRecords.vue";
import NewRecords from "@/Pages/Components/Dashbaord/NewRecords.vue";
import DealsOverTime from "@/Pages/Components/Dashbaord/DealsOverTime.vue";
import DealStages from "@/Pages/Components/Dashbaord/DealStages.vue";
import RecentOrders from "@/Pages/Components/Dashbaord/RecentOrders.vue";

import {
  Chart,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  DoughnutController,
  BarController,
} from "chart.js";

Chart.register(
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  DoughnutController,
  BarController,
);

defineOptions({ layout: AppLayout });

const props = defineProps({
  leads: { type: Object, required: true },
  recordCounts: { type: Object, required: true },
  recentOrders: { type: Array, required: true },
  dealsOverTime: { type: Array, required: true },
  ownedRecords: { type: Array },
  dealStages: {
    type: Object,
    default: () => ({ won: 0, lost: 0, open: 0 }),
  },
  invoiceOverview: {
    type: Array,
    default: () => [],
  },
});

const invoiceBarColor = {
  overdue: "#7F77DD",
  not_paid: "#D85A30",
  partially_paid: "#378ADD",
  fully_paid: "#1d9e75",
  draft: "#EF9F27",
};

function getBarColor(key) {
  return invoiceBarColor[key] ?? "#3B8BFF";
}
</script>

<template>
  <Head>
    <title>Dashboard - Cubrel</title>
  </Head>
  <div class="dashboard">
    <div class="dashboard__actions"></div>
    <div class="dashboard__main">
      <aside class="db__right">
        <NewRecords :new-records="leads" module-slug="leads"></NewRecords>
      </aside>
      <div class="db__center">
        <DealsOverTime :deals-over-time="dealsOverTime" />
        <DealStages :deal-stages="dealStages" />
        <RecentOrders :recent-orders="recentOrders" />
      </div>
      <MyRecords :owned-records="ownedRecords"></MyRecords>
    </div>
  </div>
</template>
