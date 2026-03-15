<script setup>
import { ref, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: { type: String, default: "" },
  color: { type: String, default: "#000000" },
});

const emit = defineEmits(["update:modelValue"]);

const search = ref("");
const icons = ref([]);
const meta = ref(null);
const loading = ref(false);
const page = ref(1);
const showSelector = ref(false);
const selected = ref(props.modelValue || "fa-solid fa-bahai");

const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener("click", el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener("click", el.clickOutsideEvent);
  },
};

const fetchIcons = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("/api/icons", {
      params: { q: search.value || undefined, page: page.value },
    });
    icons.value = data.data;
    meta.value = { current_page: data.current_page, last_page: data.last_page };
  } finally {
    loading.value = false;
  }
};

const selectIcon = (icon) => {
  emit("update:modelValue", icon.class);
  selected.value = icon.class;
  showSelector.value = false;
};

const toggleSelector = () => (showSelector.value = !showSelector.value);
const closeSelector = () => (showSelector.value = false);

const changePage = (newPage) => {
  if (!meta.value || newPage < 1 || newPage > meta.value.last_page) return;
  page.value = newPage;
  fetchIcons();
};

watch(search, () => {
  page.value = 1;
  fetchIcons();
});

onMounted(fetchIcons);
</script>

<template>
  <div class="icon-picker" v-click-outside="closeSelector">
    <div
      class="icon-picker__trigger"
      @click="toggleSelector"
      :class="{ 'is-active': showSelector }"
    >
      <div class="icon-picker__preview">
        <i :class="selected" :style="{ color: props.color }"></i>
      </div>
      <div class="icon-picker__label">
        <span class="icon-picker__name">
          {{
            selected
              ? selected.replace("fa-solid ", "").replace("fa-", "")
              : $t("settings.iconpicker.none")
          }}
        </span>
        <i class="fa-solid fa-chevron-down icon-picker__chevron"></i>
      </div>
    </div>

    <transition name="expande">
      <div v-if="showSelector" class="icon-picker__selector">
        <div class="icon-picker__dropdown-search">
          <input
            type="text"
            :placeholder="$t('settings.iconpicker.search_placeholder')"
            v-model="search"
            @click.stop
          />
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <div class="icon-picker__content-wrapper">
          <div v-if="loading" class="icon-picker__loading-overlay">
            <div class="loader"></div>
            <span>{{ $t("settings.iconpicker.loading") }}</span>
          </div>

          <div
            class="icon-picker__selector__grid"
            :class="{ 'is-loading': loading }"
          >
            <div
              v-for="icon in icons"
              :key="icon.id"
              class="icon-picker__selector__grid__item"
              :class="{ 'is-selected': icon.class === props.modelValue }"
              @click="selectIcon(icon)"
            >
              <i :class="icon.class"></i>
            </div>
          </div>

          <div v-if="icons.length == 0 && !loading" class="icon-picker__empty">
            {{ $t("settings.iconpicker.no_results") }}
          </div>
        </div>

        <div v-if="meta && icons.length > 0" class="icon-picker__pagination">
          <button
            type="button"
            @click.stop="changePage(meta.current_page - 1)"
            :disabled="meta.current_page <= 1 || loading"
          >
            {{ $t("settings.iconpicker.prev") }}
          </button>
          <span class="pagination-info"
            >{{ meta.current_page }} / {{ meta.last_page }}</span
          >
          <button
            type="button"
            @click.stop="changePage(meta.current_page + 1)"
            :disabled="meta.current_page >= meta.last_page || loading"
          >
            {{ $t("settings.iconpicker.next") }}
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
