
<script setup>
import { Head } from '@inertiajs/vue3'
import Sidebar from './Components/Sidebar.vue';
import Navis from './Components/Navis.vue';

</script>

<template>
    <Head>
    <title>Dashboard - Automatisierung Regensburg</title>
  </Head>
  <div class="dashboard d-flex">
    <sidebar></sidebar>
     <!-- <Navis></Navis> -->
    <main class="content flex-grow-1">
      <div class="top-bar">
        <form class="flex-grow-1 card-shadow">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input class="form-control" placeholder="Search…" />
          </div>
        </form>
        <button class="btn btn-link"><i class="fa-brands fa-github"></i></button>
        <button class="btn btn-link"><i class="fa-regular fa-bell"></i></button>
        <img src=" https://i.pravatar.cc/40" class="rounded-circle" width="36" height="36" alt="avatar" />
      </div>

      <div class="container-fluid py-3">
        <!-- Top metrics -->
        <div class="row g-3">
          <div class="col-lg-9">
            <div class="card card-border-radius card-shadow mb-3">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Total Revenue</span>
                <div>
                  <span class="badge bg-primary-subtle text-primary">2024</span>
                  <span class="badge bg-secondary-subtle text-secondary">2023</span>
                </div>
              </div>
              <div class="card-body">
                <div class="ratio ratio-21x9 bg-body-secondary rounded placeholder-wave"></div>
              </div>
            </div>
          </div>

          <div class="col-lg-3">
            <div class="card card-border-radius scale-at-hover card-shadow mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <span>Profit</span><i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
                <div class="display-6">$12,628</div>
                <small class="text-success">▲ 72.8%</small>
              </div>
            </div>

            <div class="card card-border-radius scale-at-hover card-shadow mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <span>Sales</span><i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
                <div class="display-6">$4,679</div>
                <small class="text-success">▲ 28.42%</small>
              </div>
            </div>

            <div class="card card-border-radius scale-at-hover card-shadow">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <span>Transactions</span><i class="fa-solid fa-ellipsis-vertical"></i>
                </div>
                <div class="display-6">$14,857</div>
                <small class="text-success">▲ 28.14%</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom content -->
        <div class="row g-3 mt-1">
          <div class="col-xl-4">
            <div class="card card-border-radius scale-at-hover card-shadow h-100">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Order Statistics</span>
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </div>
              <div class="card-body">
                <div class="display-6">8,258</div>
                <small class="text-muted">Total Orders</small>
                <hr />
                <ul class="list-unstyled mb-0">
                  <li class="d-flex justify-content-between py-2">
                    <span><i class="fa-solid fa-mobile-screen-button me-2 text-primary"></i>Electronic</span>
                    <span>82.5k</span>
                  </li>
                  <li class="d-flex justify-content-between py-2">
                    <span><i class="fa-solid fa-shirt me-2 text-success"></i>Fashion</span>
                    <span>23.8k</span>
                  </li>
                  <li class="d-flex justify-content-between py-2">
                    <span><i class="fa-solid fa-couch me-2 text-info"></i>Decor</span>
                    <span>849</span>
                  </li>
                  <li class="d-flex justify-content-between py-2">
                    <span><i class="fa-solid fa-football me-2 text-warning"></i>Sports</span>
                    <span>99</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-xl-5">
            <div class="card card-border-radius scale-at-hover card-shadow h-100">
              <div class="card-header bg-white d-flex gap-2">
                <span class="badge text-bg-primary">Income</span>
                <span class="badge text-bg-light border">Expenses</span>
                <span class="badge text-bg-light border">Profit</span>
              </div>
              <div class="card-body">
                <div class="ratio ratio-21x9 bg-body-secondary rounded placeholder-wave"></div>
              </div>
            </div>
          </div>

          <div class="col-xl-3">
            <div class="card card-border-radius scale-at-hover card-shadow h-100">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Transactions</span>
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-brands fa-paypal me-2 text-primary"></i>Send money</span>
                    <span class="text-success">+ $82.6</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-wallet me-2 text-info"></i>Mac’D</span>
                    <span class="text-success">+ $270.69</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-rotate-left me-2 text-success"></i>Refund</span>
                    <span class="text-success">+ $637.91</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-credit-card card-border-radius scale-at-hover me-2 text-danger"></i>Ordered Food</span>
                    <span class="text-danger">- $838.71</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>


<style scoped>

</style>
