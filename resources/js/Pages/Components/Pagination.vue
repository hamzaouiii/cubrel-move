<template>
  <nav v-if="meta" class="mt-3" aria-label="Pagination">
    <ul class="pagination">

      <!-- Prev -->
      <li class="page-item" :class="{ disabled: !meta.links?.prev }">
        <a
          class="page-link"
          href="#"
          @click.prevent="meta.links?.prev && goTo(meta.links.prev)"
          :aria-disabled="!meta.links?.prev"
        >
          Previous
        </a>
      </li>

      <!-- Numbered pages -->
      <li
        v-for="page in pagesToShow"
        :key="pageKey(page)"
        class="page-item"
        :class="{ active: page.active, disabled: page.ellipsis }"
      >
        <!-- Ellipsis -->
        <span
          v-if="page.ellipsis"
          class="page-link"
          aria-hidden="true"
        >
          {{ page.label }}
        </span>

        <!-- Normal pages -->
        <a
          v-else-if="!page.active"
          class="page-link"
          href="#"
          @click.prevent="goTo(page.url)"
        >
          {{ page.label }}
        </a>

        <span v-else class="page-link">
          {{ page.label }}
        </span>
      </li>

      <!-- Next -->
      <li class="page-item" :class="{ disabled: !meta.links?.next }">
        <a
          class="page-link"
          href="#"
          @click.prevent="meta.links?.next && goTo(meta.links.next)"
          :aria-disabled="!meta.links?.next"
        >
          Next
        </a>
      </li>

    </ul>
  </nav>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue'

const props = defineProps({
  meta: { type: Object, required: true },
  visibleRange: { type: Number, default: 2 },
});

const pages = computed(() => props.meta.pages || []);

// Build a nice ellipsis set client side
const pagesToShow = computed(() => {
  const all = pages.value;
  if (!all.length) return [];

  const total   = all.length;
  const current = props.meta.currentPage || 1;
  const range   = Number(props.visibleRange) || 2;

  const result = [];

  const pushPage = (p) => {
    if (!p) return;
    if (!result.find(r => r.page === p.page && !p.ellipsis)) {
      result.push(p);
    }
  };

  const byNumber = (n) => all.find(p => p.page === n);

  // always show first
  pushPage(all[0]);

  const leftStart = Math.max(2, current - range);
  if (leftStart > 2) {
    result.push({
      label: '...',
      page: `left-ellipsis-${leftStart}`,
      url: null,
      active: false,
      ellipsis: true,
    });
  }

  for (let p = leftStart; p <= Math.min(total - 1, current + range); p++) {
    pushPage(byNumber(p));
  }

  const rightEnd = Math.min(total - 1, current + range);
  if (rightEnd < total - 1) {
    result.push({
      label: '...',
      page: `right-ellipsis-${rightEnd}`,
      url: null,
      active: false,
      ellipsis: true,
    });
  }

  if (total > 1) pushPage(all[total - 1]);

  return result;
});

const goTo = (url) => {
  if (!url) return;

  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
  });
};

const pageKey = (page) => page.page;
</script>

<style scoped>
.page-item.disabled .page-link {
  pointer-events: none;
}
.page-link {
  cursor: pointer;
}
</style>
