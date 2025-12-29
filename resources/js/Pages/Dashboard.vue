<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";

defineOptions({
  layout: Layout,
});
</script>

<template>
  <Head>
    <title>Dashboard - Automatisierung Regensburg</title>
  </Head>
  <div class="dashboard__container">
    <!-- Top metrics -->
    <div class="dashboard__grid">
      <!-- Main chart card -->
      <div class="card card--shadow">
        <div class="card__header">
          <span class="card__title">Total Revenue</span>
          <div class="badge--group">
            <span class="badge badge--primary">2024</span>
            <span class="badge badge--secondary">2023</span>
          </div>
        </div>
        <div class="card__body">
          <div class="chart-area chart-area--21x9">
            <div class="chart-area__placeholder"></div>
          </div>
        </div>
      </div>

      <!-- Side metrics cards -->
      <div>
        <div class="card card--shadow card--hover-scale metric__item">
          <div class="card__body">
            <div class="metric__header">
              <span class="metric__label">Profit</span>
              <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
            </div>
            <div class="metric__value">$12,628</div>
            <small class="metric__change metric__change--positive"
              >▲ 72.8%</small
            >
          </div>
        </div>

        <div class="card card--shadow card--hover-scale metric__item">
          <div class="card__body">
            <div class="metric__header">
              <span class="metric__label">Sales</span>
              <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
            </div>
            <div class="metric__value">$4,679</div>
            <small class="metric__change metric__change--positive"
              >▲ 28.42%</small
            >
          </div>
        </div>

        <div class="card card--shadow card--hover-scale">
          <div class="card__body">
            <div class="metric__header">
              <span class="metric__label">Transactions</span>
              <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
            </div>
            <div class="metric__value">$14,857</div>
            <small class="metric__change metric__change--positive"
              >▲ 28.14%</small
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom content -->
    <div class="dashboard__content-grid">
      <!-- Order Statistics -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header">
          <span class="card__title">Order Statistics</span>
          <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
        </div>
        <div class="card__body">
          <div class="metric__value">8,258</div>
          <small class="text--muted">Total Orders</small>
          <hr class="divider" />
          <ul class="stats-list">
            <li class="stats-list__item">
              <span class="stats-list__category">
                <i
                  class="fa-solid fa-mobile-screen-button stats-list__icon stats-list__icon--primary"
                ></i>
                Electronic
              </span>
              <span class="stats-list__value">82.5k</span>
            </li>
            <li class="stats-list__item">
              <span class="stats-list__category">
                <i
                  class="fa-solid fa-shirt stats-list__icon stats-list__icon--success"
                ></i>
                Fashion
              </span>
              <span class="stats-list__value">23.8k</span>
            </li>
            <li class="stats-list__item">
              <span class="stats-list__category">
                <i
                  class="fa-solid fa-couch stats-list__icon stats-list__icon--info"
                ></i>
                Decor
              </span>
              <span class="stats-list__value">849</span>
            </li>
            <li class="stats-list__item">
              <span class="stats-list__category">
                <i
                  class="fa-solid fa-football stats-list__icon stats-list__icon--warning"
                ></i>
                Sports
              </span>
              <span class="stats-list__value">99</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Income/Expenses Chart -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header card__header--no-border">
          <div class="badge--group">
            <span class="badge badge--solid-primary">Income</span>
            <span class="badge badge--outline">Expenses</span>
            <span class="badge badge--outline">Profit</span>
          </div>
        </div>
        <div class="card__body">
          <div class="chart-area chart-area--21x9">
            <div class="chart-area__placeholder"></div>
          </div>
        </div>
      </div>

      <!-- Transactions -->
      <div class="card card--shadow card--hover-scale card--full-height">
        <div class="card__header">
          <span class="card__title">Transactions</span>
          <i class="fa-solid fa-ellipsis-vertical metric__icon"></i>
        </div>
        <div class="card__body">
          <ul class="transactions-list">
            <li class="transactions-list__item">
              <span class="transactions-list__info">
                <i
                  class="fa-brands fa-paypal transactions-list__icon transactions-list__icon--primary"
                ></i>
                Send money
              </span>
              <span
                class="transactions-list__amount transactions-list__amount--positive"
                >+ $82.6</span
              >
            </li>
            <li class="transactions-list__item">
              <span class="transactions-list__info">
                <i
                  class="fa-solid fa-wallet transactions-list__icon transactions-list__icon--info"
                ></i>
                Mac'D
              </span>
              <span
                class="transactions-list__amount transactions-list__amount--positive"
                >+ $270.69</span
              >
            </li>
            <li class="transactions-list__item">
              <span class="transactions-list__info">
                <i
                  class="fa-solid fa-rotate-left transactions-list__icon transactions-list__icon--success"
                ></i>
                Refund
              </span>
              <span
                class="transactions-list__amount transactions-list__amount--positive"
                >+ $637.91</span
              >
            </li>
            <li class="transactions-list__item">
              <span class="transactions-list__info">
                <i
                  class="fa-solid fa-credit-card transactions-list__icon transactions-list__icon--danger"
                ></i>
                Ordered Food
              </span>
              <span
                class="transactions-list__amount transactions-list__amount--negative"
                >- $838.71</span
              >
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
//placeholder style for a placholder dashboard
// Variables
$spacing-unit: 1rem;
$border-radius: 0.5rem;
$transition-speed: 0.2s;
$shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
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

// Layout Container
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

// Card Component
.card {
  background-color: $color-white;
  border-radius: $border-radius;
  overflow: hidden;
  transition: all $transition-speed ease;

  &--shadow {
    box-shadow: $shadow-md;
  }

  &--hover-scale {
    &:hover {
      transform: translateY(-2px);
      box-shadow: $shadow-lg;
    }
  }

  &--full-height {
    height: 100%;
  }

  &__header {
    background-color: $color-white;
    padding: $spacing-unit;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;

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

// Badge Component
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

  &--secondary {
    background-color: $color-secondary-light;
    color: $color-secondary;
    border: 1px solid rgba($color-secondary, 0.2);
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

// Metrics Component
.metric {
  &__item {
    margin-bottom: $spacing-unit;

    &:last-child {
      margin-bottom: 0;
    }
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
  }

  &__icon {
    color: $color-text-muted;
    opacity: 0.6;
    cursor: pointer;
  }
}

// Statistics List
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

    &--success {
      color: $color-success;
    }

    &--info {
      color: $color-info;
    }

    &--warning {
      color: $color-warning;
    }
  }

  &__value {
    font-weight: 500;
  }
}

// Transactions List
.transactions-list {
  list-style: none;
  padding: 0;
  margin: 0;

  &__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f1f1;

    &:last-child {
      border-bottom: none;
    }
  }

  &__info {
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

    &--success {
      color: $color-success;
    }

    &--danger {
      color: $color-danger;
    }
  }

  &__amount {
    font-weight: 500;

    &--positive {
      color: $color-success;
    }

    &--negative {
      color: $color-danger;
    }
  }
}

// Utility Classes
.flex {
  display: flex;

  &--between {
    justify-content: space-between;
  }

  &--align-center {
    align-items: center;
  }

  &--gap-2 {
    gap: 0.5rem;
  }
}

.text {
  &--muted {
    color: $color-text-muted;
    font-size: 0.875rem;
  }

  &--success {
    color: $color-success;
  }

  &--danger {
    color: $color-danger;
  }
}

.divider {
  height: 1px;
  background-color: #dee2e6;
  margin: $spacing-unit 0;
  border: none;
}

// Placeholder/Chart Area
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
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
      90deg,
      $color-body-bg 25%,
      lighten($color-body-bg, 5%) 50%,
      $color-body-bg 75%
    );
    background-size: 200% 100%;
    animation: placeholder-wave 1.5s linear infinite;
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
