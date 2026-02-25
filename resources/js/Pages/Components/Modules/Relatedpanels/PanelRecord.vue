<script setup>
import { formatDateTime } from "@/utils/datetime";
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  record: Object,
  header: Object,
  related_slug: {
    type: String,
    required: false,
  },
  openMenuId: [String, Number],
});
const emit = defineEmits(["toggleMenu"]);
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
          class="related-records__actions__menu-btn"
          @click.stop="emit('toggleMenu', record.id)"
        >
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <ul v-if="isMenuOpen" class="related-records__actions__menu">
          <a
            :href="getRelatedRecordurl(related_slug, record.id)"
            target="_blank"
            rel="noopener noreferrer"
            ><li>
              <i class="fa-solid fa-up-right-from-square"></i>

              <span>Open in a new Tab</span>
            </li>
          </a>
          <li>
            <i class="fa-solid fa-brush"></i>

            <span> Quick edit</span>
          </li>
          <li class="related-records__actions__menu__divider"></li>
          <li class="related-records__actions__menu__unlink">
            <i class="fa-solid fa-link-slash"></i>
            <span> Unlink</span>
          </li>
        </ul>
      </div>
    </td>
  </tr>
</template>
