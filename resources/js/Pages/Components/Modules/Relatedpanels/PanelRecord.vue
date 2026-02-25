<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import RelatedRecordsActionDropdown from "./RelatedRecordsActionDropdown.vue";

const props = defineProps({
  record: Object,
  header: Object,
  related_slug: {
    type: String,
    required: false,
  },
  openMenuId: [String, Number],
});

const emit = defineEmits(["toggleMenu", "quick-edit", "unlink"]);

const isMenuOpen = computed(() => props.openMenuId === props.record.id);

const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;

const formatField = (field, value) => {
  if (value == null || value === "") return "";

  const type = field?.type?.toLowerCase();

  switch (type) {
    case "textfield":
      return value;

    case "datetime":
      return formatDateTime(value);

    case "longtext":
      return value.length > 32 ? value.slice(0, 32) + "…" : value;

    default:
      return value;
  }
};
const triggerEl = ref(null);
</script>

<template>
  <tr>
    <td v-for="field in header" :key="field.name">
      <template v-if="field.name === 'name'">
        <Link :href="getRelatedRecordurl(related_slug, record.id)">
          {{ record[field.name] }}
        </Link>
      </template>

      <template v-else>
        {{ formatField(field, record[field.name]) }}
      </template>
    </td>

    <td class="related-records__actions">
      <div class="related-records__actions__wrapper">
        <button
          ref="triggerEl"
          class="related-records__actions__menu-btn"
          @click.stop="emit('toggleMenu', record.id)"
        >
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <RelatedRecordsActionDropdown
          :isMenuOpen="isMenuOpen"
          :triggerEl="triggerEl"
          :related_slug="related_slug"
          :record="record"
          :getRelatedRecordurl="getRelatedRecordurl"
          @quick-edit="emit('quick-edit', record)"
          @unlink="emit('unlink', record)"
          @close="emit('toggleMenu', null)"
        />
      </div>
    </td>
  </tr>
</template>
