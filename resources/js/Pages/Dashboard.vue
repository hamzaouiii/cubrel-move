<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({
  layout: AppLayout,
});

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  recordCounts: {
    type: Object,
    required: true,
  },
  recentOrders: {
    type: Array,
    required: true,
  },
  dealsOverTime: {
    type: Array,
    required: true,
  },
});

// ─── Formatters ───────────────────────────────────────────────────────────────

function formatValue(value, format) {
  if (format === "currency") {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      maximumFractionDigits: 0,
    }).format(value);
  }
  return new Intl.NumberFormat("en-US").format(value);
}

function formatCurrency(amount, currency = "USD") {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency,
    maximumFractionDigits: 2,
  }).format(amount);
}

function formatChange(change) {
  if (change === null) return null;
  const sign = change >= 0 ? "▲" : "▼";
  return `${sign} ${Math.abs(change)}%`;
}

function isPositiveChange(change) {
  return change === null || change >= 0;
}

// ─── Order status ─────────────────────────────────────────────────────────────

const orderStatusClass = {
  pending: "status-pill--warning",
  processing: "status-pill--info",
  completed: "status-pill--success",
  cancelled: "status-pill--danger",
};

function getOrderStatusClass(status) {
  return orderStatusClass[status?.toLowerCase()] ?? "status-pill--secondary";
}
</script>

<template>
  <Head>
    <title>Dashboard - Cubrel</title>
  </Head>

  <div class="dashboard__container">
    <!-- Top row: main chart + metric cards -->
    <div class="dashboard__grid">
      <!-- Main chart -->
      <div class="card card--shadow">
        <div class="card__header">
          <span class="card__title">Opportunities Over Time</span>
          <div class="badge--group">
            <span class="badge badge--solid-primary">Count</span>
            <span class="badge badge--outline">Value</span>
          </div>
        </div>
        <div class="card__body">
          <div class="chart-area chart-area--21x9">
            <!-- dealsOverTime: [{ month, count, value }] — wire chart here in phase 2 -->
            <div
              class="chart-area__placeholder chart-area__placeholder--labeled"
            >
              <span class="chart-area__label">
                {{ dealsOverTime.length }} months of data ready
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Metric cards -->
      <div class="metric__stack">
        <div
          v-for="(stat, key) in stats"
          :key="key"
          class="card card--shadow card--hover-scale metric__item"
        >
          <div class="card__body">
            <div class="metric__header">
              <span class="metric__label">{{ stat.label }}</span>
              <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
            </div>
            <div class="metric__value">
              {{ formatValue(stat.value, stat.format) }}
            </div>
            <small
              v-if="stat.change !== null"
              class="metric__change"
              :class="
                isPositiveChange(stat.change)
                  ? 'metric__change--positive'
                  : 'metric__change--negative'
              "
            >
              {{ formatChange(stat.change) }}
            </small>
            <small v-else class="metric__change metric__change--neutral">
              No prior data
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom row -->
    <div class="dashboard__content-grid">
      <!-- My Records -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header">
          <span class="card__title">My Records</span>
          <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
        </div>
        <div class="card__body">
          <div class="metric__value">
            {{ recordCounts.total.toLocaleString() }}
          </div>
          <small class="text--muted">Total Records</small>
          <hr class="divider" />
          <ul class="stats-list">
            <li
              v-for="mod in recordCounts.modules"
              :key="mod.label"
              class="stats-list__item"
            >
              <span class="stats-list__category">
                <i
                  :class="[
                    mod.icon,
                    'stats-list__icon',
                    `stats-list__icon--${mod.color}`,
                  ]"
                ></i>
                {{ mod.label }}
              </span>
              <span class="stats-list__value">{{
                mod.count.toLocaleString()
              }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Deal stages placeholder — phase 2 -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header card__header--no-border">
          <div class="badge--group">
            <span class="badge badge--solid-primary">Won</span>
            <span class="badge badge--outline">Lost</span>
            <span class="badge badge--outline">Open</span>
          </div>
        </div>
        <div class="card__body">
          <div class="chart-area chart-area--21x9">
            <div
              class="chart-area__placeholder chart-area__placeholder--labeled"
            >
              <span class="chart-area__label"
                >Opportunity stages — coming soon</span
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header">
          <span class="card__title">Recent Orders</span>
          <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
        </div>
        <div class="card__body">
          <ul v-if="recentOrders.length" class="orders-list">
            <li
              v-for="order in recentOrders"
              :key="order.order_number"
              class="orders-list__item"
            >
              <span class="orders-list__info">
                <i
                  class="fa-solid fa-box orders-list__icon orders-list__icon--primary"
                ></i>

                <span class="orders-list__detail">
                  <Link :href="`/orders/${order.id}`">
                    <span class="orders-list__number">{{
                      order.order_number
                    }}</span>
                  </Link>

                  <small class="text--muted">{{ order.date }}</small>
                </span>
              </span>
              <span class="orders-list__right">
                <span
                  class="status-pill"
                  :class="getOrderStatusClass(order.status)"
                >
                  {{ order.status }}
                </span>
                <span class="orders-list__amount">
                  {{ formatCurrency(order.total_amount, order.currency) }}
                </span>
              </span>
            </li>
          </ul>

          <div v-else class="empty-state">
            <i class="fa-solid fa-box-open empty-state__icon"></i>
            <p class="empty-state__text">No recent orders</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
$spacing-unit: 1rem;
$border-radius: 0.5rem;
$transition-speed: 0.2s;
$shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1);
$shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);

$color-primary: #0d6efd;
$color-primary-light: #cfe2ff;
$color-secondary: #6c757d;
$color-secondary-light: #e2e3e5;
$color-success: #198754;
$color-success-light: #d1e7dd;
$color-info: #0dcaf0;
$color-info-light: #cff4fc;
$color-warning: #ffc107;
$color-warning-light: #fff3cd;
$color-danger: #dc3545;
$color-danger-light: #f8d7da;
$color-body-bg: #f8f9fa;
$color-text-muted: #6c757d;
$color-white: #ffffff;

// Layout
.dashboard {
  &__container {
    padding: $spacing-unit * 1.5;
    max-width: 1400px;
    margin: 0 auto;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $spacing-unit;

    @media (min-width: 992px) {
      grid-template-columns: 3fr 1fr;
    }
  }

  &__content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: $spacing-unit;
    margin-top: $spacing-unit * 0.5;

    @media (min-width: 1200px) {
      grid-template-columns: 1fr 1.25fr 0.75fr;
    }
  }
}

// Card
.card {
  background-color: $color-white;
  border-radius: $border-radius;
  overflow: hidden;
  transition: all $transition-speed ease;

  &--shadow {
    box-shadow: $shadow-md;
  }
  &--full-height {
    height: 100%;
  }
  &--hover-scale:hover {
    transform: translateY(-2px);
    box-shadow: $shadow-lg;
  }

  &__header {
    padding: $spacing-unit;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: $color-white;

    &--no-border {
      border-bottom: none;
    }
  }

  &__body {
    padding: $spacing-unit;
  }
  &__title {
    font-weight: 500;
    margin: 0;
  }
}

// Badge
.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: $border-radius * 0.5;
  line-height: 1;

  &--primary {
    background-color: $color-primary-light;
    color: $color-primary;
    border: 1px solid rgba($color-primary, 0.2);
  }
  &--outline {
    background-color: transparent;
    border: 1px solid #dee2e6;
    color: $color-text-muted;
  }
  &--solid-primary {
    background-color: $color-primary;
    color: $color-white;
  }
  &--group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }
}

// Metrics
.metric {
  &__stack {
    display: flex;
    flex-direction: column;
    gap: $spacing-unit;
  }
  &__item {
    flex: 1;
  }

  &__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }

  &__label {
    font-size: 0.875rem;
    color: $color-text-muted;
  }

  &__value {
    font-size: 2.5rem;
    font-weight: 300;
    line-height: 1;
    margin: 0.5rem 0;
  }

  &__change {
    font-size: 0.875rem;
    font-weight: 500;
    &--positive {
      color: $color-success;
    }
    &--negative {
      color: $color-danger;
    }
    &--neutral {
      color: $color-text-muted;
    }
  }

  &__icon {
    color: $color-text-muted;
    opacity: 0.6;
    cursor: pointer;
  }
}

// Stats list (My Records widget)
.stats-list {
  list-style: none;
  padding: 0;
  margin: 0;

  &__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f1f1;
    &:last-child {
      border-bottom: none;
    }
  }

  &__category {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  &__icon {
    &--primary {
      color: $color-primary;
    }
    &--info {
      color: $color-info;
    }
    &--warning {
      color: $color-warning;
    }
    &--success {
      color: $color-success;
    }
  }

  &__value {
    font-weight: 500;
  }
}

// Orders list
.orders-list {
  list-style: none;
  padding: 0;
  margin: 0;

  &__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f1f1;
    gap: 0.5rem;
    &:last-child {
      border-bottom: none;
    }
  }

  &__info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    min-width: 0;
  }

  &__icon {
    flex-shrink: 0;
    &--primary {
      color: $color-primary;
    }
  }

  &__detail {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  &__number {
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
    flex-shrink: 0;
  }

  &__amount {
    font-size: 0.875rem;
    font-weight: 600;
    color: $color-success;
  }
}

// Status pill
.status-pill {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0.1rem 0.4rem;
  border-radius: 3px;

  &--success {
    background-color: $color-success-light;
    color: $color-success;
  }
  &--warning {
    background-color: $color-warning-light;
    color: darken($color-warning, 30%);
  }
  &--danger {
    background-color: $color-danger-light;
    color: $color-danger;
  }
  &--info {
    background-color: $color-info-light;
    color: darken($color-info, 30%);
  }
  &--secondary {
    background-color: $color-secondary-light;
    color: $color-secondary;
  }
}

// Empty state
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: $color-text-muted;

  &__icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.4;
  }
  &__text {
    font-size: 0.875rem;
    margin: 0;
  }
}

// Utilities
.text--muted {
  color: $color-text-muted;
  font-size: 0.875rem;
}

.divider {
  height: 1px;
  background-color: #dee2e6;
  margin: $spacing-unit 0;
  border: none;
}

// Chart placeholder
.chart-area {
  background-color: $color-body-bg;
  border-radius: $border-radius;
  position: relative;
  overflow: hidden;

  &--21x9 {
    aspect-ratio: 21 / 9;
  }

  &__placeholder {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      90deg,
      $color-body-bg 25%,
      lighten($color-body-bg, 2%) 50%,
      $color-body-bg 75%
    );
    background-size: 200% 100%;
    animation: placeholder-wave 1.5s linear infinite;

    &--labeled {
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  &__label {
    font-size: 0.8rem;
    color: $color-text-muted;
    opacity: 0.7;
    position: relative;
    z-index: 1;
  }
}

@keyframes placeholder-wave {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
