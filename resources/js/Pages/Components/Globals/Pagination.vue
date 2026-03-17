<script setup>
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  meta: { type: Object, required: true },
  visibleRange: { type: Number, default: 2 },
});

const pages = computed(() => props.meta.pages || []);

const pagesToShow = computed(() => {
  const all = pages.value;
  if (!all.length) return [];

  const total = all.length;
  const current = props.meta.currentPage || 1;
  const range = Number(props.visibleRange) || 2;

  const result = [];

  const pushPage = (p) => {
    if (!p) return;
    if (!result.find((r) => r.page === p.page && !p.ellipsis)) {
      result.push(p);
    }
  };

  const byNumber = (n) => all.find((p) => p.page === n);

  pushPage(all[0]);

  const leftStart = Math.max(2, current - range);
  if (leftStart > 2) {
    result.push({
      label: "...",
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
      label: "...",
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

<template>
  <nav v-if="meta" aria-label="Pagination">
    <ul class="pagination">
      <!-- Previous -->
      <li
        :class="[
          'pagination__item',
          { 'pagination__item--disabled': !meta.links?.prev },
        ]"
        role="link"
        tabindex="0"
        :aria-disabled="!meta.links?.prev"
        @click="meta.links?.prev && goTo(meta.links.prev)"
        @keydown.enter.prevent="meta.links?.prev && goTo(meta.links.prev)"
        @keydown.space.prevent="meta.links?.prev && goTo(meta.links.prev)"
      >
        <span>{{ $t("modules.pagination.previous") }}</span>
      </li>

      <!-- Pages -->
      <li
        v-for="page in pagesToShow"
        :key="pageKey(page)"
        :class="[
          'pagination__item',
          {
            'pagination__item--active': page.active,
            'pagination__item--disabled': page.ellipsis,
          },
        ]"
        role="link"
        tabindex="0"
        :aria-disabled="page.active || page.ellipsis"
        @click="!page.active && !page.ellipsis && goTo(page.url)"
        @keydown.enter.prevent="
          !page.active && !page.ellipsis && goTo(page.url)
        "
        @keydown.space.prevent="
          !page.active && !page.ellipsis && goTo(page.url)
        "
      >
        <span>{{ page.label }}</span>
      </li>

      <!-- Next -->
      <li
        :class="[
          'pagination__item',
          { 'pagination__item--disabled': !meta.links?.next },
        ]"
        role="link"
        tabindex="0"
        :aria-disabled="!meta.links?.next"
        @click="meta.links?.next && goTo(meta.links.next)"
        @keydown.enter.prevent="meta.links?.next && goTo(meta.links.next)"
        @keydown.space.prevent="meta.links?.next && goTo(meta.links.next)"
      >
        <span>{{ $t("modules.pagination.next") }}</span>
      </li>
    </ul>
  </nav>
</template>
