<script setup>
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  getCurrentInstance,
} from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: [String, Number, Boolean, Object, null],
});

const emit = defineEmits(["update:modelValue", "change"]);
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const isOpen = ref(false);
const root = ref(null);
const options = ref([]);

const search = ref("");
const searchInput = ref(null);
const loading = ref(false);

const fetchList = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("/api/dropdown-lists", {});

    options.value = data.list;
  } catch (error) {
    console.error("Failed to fetch dropdown lists:", error);
  } finally {
    loading.value = false;
  }
};

const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase();

  return options.value.filter((o) => {
    const key = String(o.key ?? "").toLowerCase();
    return key.includes(q);
  });
});

const selectedOption = computed(
  () => options.value.find((o) => o.id === props.modelValue)?.key ?? null,
);

const toggle = async () => {
  if (props.disabled) return;

  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    await nextTick();
    if (props.searchable) {
      searchInput.value?.focus();
    }
  } else {
    search.value = "";
  }
};

const close = () => {
  isOpen.value = false;
  search.value = "";
};

const selectOption = (value) => {
  if (value !== props.modelValue) {
    emit("update:modelValue", value);
    emit("change", value);
  }
  close();
};

const handleClickOutside = (event) => {
  if (!root.value) return;
  if (!root.value.contains(event.target)) {
    close();
  }
};

onMounted(() => {
  fetchList();
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <div class="dropdown-field" ref="root">
    <div
      class="dropdown-field_button"
      :class="{
        'is-open': isOpen,
        'is-invalid': error,
        'is-disabled': disabled,
      }"
      @click="toggle"
    >
      <span class="dropdown-field_selected">
        {{ selectedOption ?? $t("settings.select_dropdown_list") }}
      </span>

      <i
        class="dropdown-field__icon"
        :class="isOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"
      ></i>
    </div>

    <transition name="dropdown-fade">
      <div v-if="isOpen" class="dropdown-field_menu" role="listbox" @click.stop>
        <div class="dropdown-field_search">
          <input
            ref="searchInput"
            v-model="search"
            type="text"
            class="dropdown-field_search_input"
            :placeholder="t('settings.search_in_drop_down')"
            @keydown.stop
          />
        </div>

        <ul class="dropdown-field_list">
          <li
            v-for="option in filteredOptions"
            :key="option.key"
            class="dropdown-field_option"
            role="option"
            @click="selectOption(option.id)"
          >
            <div class="dropdown-field_option_label">
              {{ option.key }}
            </div>
          </li>

          <li
            v-if="filteredOptions.length === 0"
            class="dropdown-field_no_results"
          >
            {{ $t("settings.dropdown_no_results") }}
          </li>
        </ul>
      </div>
    </transition>
  </div>
  <button class="btn" :disabled="selectedOption === null">
    <i class="fa-solid fa-pen-to-square"></i>
  </button>
  <button class="btn">
    <i class="fa-solid fa-circle-plus"></i>
  </button>
</template>
