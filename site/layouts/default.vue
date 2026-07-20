<template>
  <div class="page">
    <SiteHeader />
    <main class="page__main">
      <slot />
    </main>
    <SiteFooter />
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.page__main {
  flex: 1;
}
</style>
