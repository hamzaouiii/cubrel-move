<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  fields: Array,
  item: Object,
});

const sortKey = ref(null);
const sortDirection = ref("asc");

function sortBy(key) {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = key;
    sortDirection.value = "asc";
  }
}

const sortedFields = computed(() => {
  if (!sortKey.value) return props.fields;

  return [...props.fields].sort((a, b) => {
    const valA = a[sortKey.value] ?? "";
    const valB = b[sortKey.value] ?? "";

    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});
</script>

<template>
  <Head>
    <title>
      {{ module.label }} - {{ $t("fields.label") }} - {{ $t("settings.label") }}
    </title>
  </Head>

  <div class="settings_header">
    <div class="settings_header_title">
      <h5>
        <Link href="/settings">{{ $t("settings.label") }}</Link>
      </h5>
      <span>></span>
      <h5>
        <Link href="/settings/fields/">{{ $t("fields.label") }}</Link>
      </h5>
      <span>></span>
      <h6>{{ module.label }}</h6>
    </div>
  </div>

  <div class="fields">
    <table class="fields_table">
      <thead>
        <tr>
          <th @click="sortBy('key')">
            {{ $t("fields.key") }}

            <i
              v-if="sortKey === 'key'"
              class="fa-solid sort-icon"
              :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
            ></i>

            <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
          </th>

          <th @click="sortBy('label')">
            {{ $t("fields.field_label") }}

            <i
              v-if="sortKey === 'label'"
              class="fa-solid sort-icon"
              :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
            ></i>

            <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
          </th>

          <th @click="sortBy('type')">
            {{ $t("fields.type") }}

            <i
              v-if="sortKey === 'type'"
              class="fa-solid sort-icon"
              :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
            ></i>

            <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="f in sortedFields" :key="f.key">
          <td>{{ f.key }}</td>
          <td>{{ f.label }}</td>
          <td>{{ f.type }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.fields_table th {
  cursor: pointer;
  user-select: none;
  position: relative;
}

.sort-icon {
  margin-left: 6px;
  font-size: 12px;
  opacity: 0;
  transform: rotate(0deg);
  transition: opacity 0.15s ease, transform 0.2s ease;
}

/* show icon on hover */
.fields_table th:hover .sort-icon {
  opacity: 0.4;
}

/* active column */
.sort-icon.is-active {
  opacity: 1;
}

/* descending rotation */
.sort-icon.desc {
  transform: rotate(180deg);
}
</style>
