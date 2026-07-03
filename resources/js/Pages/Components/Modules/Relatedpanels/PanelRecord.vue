<script setup>
import { formatDateTime, formatDate } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import RelatedRecordsActionDropdown from "./RelatedRecordsActionDropdown.vue";
import FieldRenderer from "../../Globals/FieldRenderer.vue";
const props = defineProps({
  record: Object,
  header: Object,
  related_slug: {
    type: String,
    required: false,
  },
  openMenuId: [String, Number],
  isUnlinking: Boolean,
  fields: Object,
  color: String,
});
const emit = defineEmits(["toggleMenu", "quick-edit", "unlink"]);

const isMenuOpen = computed(() => props.openMenuId === props.record.id);

const getRelatedRecordurl = (slug, id) => `/${slug}/${id}`;

const triggerEl = ref(null);

const fieldResolver = (name) => {
  return props.fields?.find((field) => field.name === name);
};
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
        <FieldRenderer
          :field="fieldResolver(field.name)"
          v-model="record[field.name]"
          mode="related-panel"
          :module-color="color"
        ></FieldRenderer>
      </template>
    </td>

    <td class="related-records__actions">
      <div class="related-records__actions__wrapper">
        <button
          ref="triggerEl"
          class="related-records__actions__menu-btn"
          :class="{ 'related-records__actions__menu-btn--open': isMenuOpen }"
          @click.stop="emit('toggleMenu', record.id)"
        >
          <i v-if="isUnlinking" class="fa-solid fa-atom fa-spin"></i>
          <i v-else class="fa-solid fa-ellipsis-vertical"></i>
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
