<!-- components/Dashboard/RecentOrders.vue -->
<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  newRecords: { type: Array, required: true },
  moduleSlug: { Type: String, required: true },
});

const module = computed(() =>
  usePage().props.modules.find((m) => m.slug === props.moduleSlug),
);
</script>

<template>
  <div class="dashboard__card dashboard__card--full">
    <div class="dashboard__card__header">
      <span class="dashboard__card__title">{{
        $t(`globals.dashboard.recent_${moduleSlug}`)
      }}</span>
      <i
        class="fa-solid fa-ellipsis-vertical icon--muted"
        aria-hidden="true"
      ></i>
    </div>
    <div
      class="dashboard__card__body"
      :style="{ '--module-color-dash': module.color }"
    >
      <ul v-if="newRecords.length" class="orders-list">
        <li v-for="lead in newRecords" :key="lead.id" class="orders-list__item">
          <div class="orders-list__left">
            <div class="orders-list__icon-wrap">
              <i :class="module.icon" aria-hidden="true"></i>
            </div>
            <div class="orders-list__detail">
              <Link :href="`/leads/${lead.id}`" class="orders-list__num">
                {{ lead.name }}
              </Link>
              <!-- <span class="text--muted">{{ order.date }}</span> -->
            </div>
          </div>
          <!-- <div class="orders-list__right">
            <span
              class="status-pill"
              :class="getOrderStatusClass(order.status.status)"
            >
              {{ $t(order.status.label) }}
            </span>
            <span class="orders-list__amt">
              {{ formatCurrency(order.total_amount, order.currency) }}
            </span>
          </div> -->
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
