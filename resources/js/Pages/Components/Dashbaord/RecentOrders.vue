<!-- components/Dashboard/RecentOrders.vue -->
<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  recentOrders: { type: Array, required: true },
});

const orderStatusClass = {
  warning: "status-pill--warning",
  default: "status-pill--default",
  info: "status-pill--info",
  success: "status-pill--success",
  danger: "status-pill--danger",
};

function getOrderStatusClass(status) {
  return orderStatusClass[status?.toLowerCase()] ?? "status-pill--secondary";
}

function formatCurrency(amount, currency = "EUR") {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency,
    maximumFractionDigits: 2,
  }).format(amount);
}
</script>

<template>
  <div class="dashboard__card dashboard__card--full">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        $t("globals.dashboard.recent_orders")
      }}</span>
      <i
        class="fa-solid fa-ellipsis-vertical icon--muted"
        aria-hidden="true"
      ></i>
    </div>
    <div class="dashboard__card__body">
      <ul v-if="recentOrders.length" class="orders-list">
        <li
          v-for="order in recentOrders"
          :key="order.order_number"
          class="orders-list__item"
        >
          <div class="orders-list__left">
            <div class="orders-list__icon-wrap">
              <i class="fa-solid fa-box" aria-hidden="true"></i>
            </div>
            <div class="orders-list__detail">
              <Link :href="`/orders/${order.id}`" class="orders-list__num">
                {{ order.order_number }}
              </Link>
              <span class="text--muted">{{ order.date }}</span>
            </div>
          </div>
          <div class="orders-list__right">
            <span
              class="status-pill"
              :class="getOrderStatusClass(order.status.status)"
            >
              {{ $t(order.status.label) }}
            </span>
            <span
              v-if="order.status.value === 'cancelled'"
              class="orders-list__amt orders-list__amt--cancelled"
              >{{ formatCurrency(order.total_amount, order.currency) }}</span
            >
            <span class="orders-list__amt" v-else>
              {{ formatCurrency(order.total_amount, order.currency) }}
            </span>
          </div>
        </li>
      </ul>
      <div v-else class="empty-state">
        <i
          class="fa-solid fa-box-open empty-state__icon"
          aria-hidden="true"
        ></i>
        <p class="empty-state__text">
          {{ $t("globals.dashboard.no_recent_orders") }}
        </p>
      </div>
    </div>
  </div>
</template>
