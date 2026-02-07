<script setup>
import { ref, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  color: {
    type: String,
    default: "#000000",
  },
});

const emit = defineEmits(["update:modelValue"]);

const search = ref("");
const style = ref("");
const icons = ref([]);
const meta = ref(null);
const loading = ref(false);
const page = ref(1);
const selected = ref(props.modelValue || "fa-solid fa-bahai");
const fetchIcons = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("/api/icons", {
      params: {
        q: search.value || undefined,
        style: style.value || undefined,
        page: page.value,
      },
    });

    icons.value = data.data;
    meta.value = {
      current_page: data.current_page,
      last_page: data.last_page,
    };
  } finally {
    loading.value = false;
  }
};

const selectIcon = (icon) => {
  emit("update:modelValue", icon.class);
  selected.value = icon.class;
};

const isSelected = (icon) => {
  return icon.class === props.modelValue;
};

const changePage = (newPage) => {
  if (!meta.value) return;
  if (newPage < 1 || newPage > meta.value.last_page) return;
  page.value = newPage;
  fetchIcons();
};

watch([search, style], () => {
  page.value = 1;
  fetchIcons();
});

onMounted(() => {
  fetchIcons();
});
const showSelector = ref(false);
const toggleSelector = () => {
  if (search.value.length) {
    showSelector.value = true;
  } else {
    showSelector.value = !showSelector.value;
  }
};
const clearSearch = () => {
  search.value = "";
};
// a nice to do here would be to implement some smart search based on module name
</script>

<template>
  <div class="icon-picker">
    <div class="icon-picker__search">
      <input
        type="search"
        :placeholder="selected ? selected : 'Search for Icon...'"
        v-model="search"
        @click="toggleSelector"
        @keyup="toggleSelector"
      />
      <span
        v-if="search.length"
        class="icon-picker__search__close"
        @click="clearSearch"
      >
        <i
          class="icon-picker__search__close__icon fa-solid fa-circle-xmark"
        ></i>
      </span>
    </div>

    <div class="icon-picker__selected">
      <i :class="selected" :style="{ color: props.color }"></i>
    </div>
    <transition name="expande">
      <div v-if="showSelector" class="icon-picker__selector">
        <div v-if="loading" class="icon-picker__selector__loader">
          <div class="loader"></div>
        </div>

        <div v-else class="icon-picker__selector__grid">
          <div
            v-for="icon in icons"
            :key="icon.id"
            class="icon-picker__selector__grid__item"
            :class="{ 'border-primary': isSelected(icon) }"
            @click="selectIcon(icon)"
          >
            <i :class="icon.class"></i>
            <span>
              <!-- {{ icon.name }} -->
            </span>
          </div>
        </div>
        <div v-if="icons.length == 0" class="text-muted small mb-2">
          No icons found!
        </div>

        <div
          v-if="meta"
          class="d-flex justify-content-between align-items-center mt-2"
        >
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            @click="changePage(meta.current_page - 1)"
            :disabled="meta.current_page <= 1 || loading"
          >
            Previous
          </button>

          <span class="small">
            {{ meta.current_page }} / {{ meta.last_page }}
          </span>

          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            @click="changePage(meta.current_page + 1)"
            :disabled="meta.current_page >= meta.last_page || loading"
          >
            Next
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
